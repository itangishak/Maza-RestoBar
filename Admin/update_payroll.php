<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Add table verification and column creation
$check_column = $conn->query("SHOW COLUMNS FROM payroll_records LIKE 'loan_repayment'");
if ($check_column->num_rows === 0) {
    // Add the missing column with logging
    error_log('Adding loan_repayment column to payroll_records table');
    $result = $conn->query("ALTER TABLE payroll_records 
        ADD COLUMN loan_repayment DECIMAL(10,2) DEFAULT 0.00,
        ADD COLUMN loan_repayment_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    
    if ($result === false) {
        error_log('Failed to add loan_repayment column: ' . $conn->error);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    // Gather form data
    $payroll_id       = $_POST['payroll_id'];
    $employee_id      = $_POST['employee_id'];
    $pay_period_start = $_POST['pay_period_start'];
    $pay_period_end   = $_POST['pay_period_end'];
    $bonus            = $_POST['bonus'] ?? 0;
    $deductions       = $_POST['deductions'] ?? 0;
    if (!isset($_POST['loan_repayment'])) {
        $_POST['loan_repayment'] = 0.00;
    } else {
        $_POST['loan_repayment'] = (float)$_POST['loan_repayment'];
    }
    $loan_repayment   = $_POST['loan_repayment'];
    $payment_date     = $_POST['payment_date'];
    $notes            = $_POST['notes'] ?? '';

    // Get employee's base salary
    $salary_query = "SELECT salary FROM employees WHERE employee_id = ?";
    $salary_stmt = $conn->prepare($salary_query);
    if ($salary_stmt === false) {
        $response = [
            'status' => 'error',
            'message' => 'Database error: ' . $conn->error
        ];
        echo json_encode($response);
        exit;
    }
    
    $salary_stmt->bind_param("i", $employee_id);
    $salary_stmt->execute();
    $salary_result = $salary_stmt->get_result();
    
    if ($salary_result->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
        exit;
    }
    
    $employee = $salary_result->fetch_assoc();
    $gross_pay = $employee['salary'];
    
    // Calculate net pay with loan repayment
    $net_pay = $gross_pay + $bonus - $deductions - $loan_repayment;
    
    // Check for duplicate payroll records (excluding the current record)
    $duplicate_check_sql = "SELECT payroll_id FROM payroll_records 
                          WHERE employee_id = ? 
                          AND payroll_id != ?
                          AND (
                              (pay_period_start BETWEEN ? AND ?) OR
                              (pay_period_end BETWEEN ? AND ?) OR
                              (? BETWEEN pay_period_start AND pay_period_end) OR
                              (? BETWEEN pay_period_start AND pay_period_end)
                          )";
    
    $check_stmt = $conn->prepare($duplicate_check_sql);
    if ($check_stmt === false) {
        $response = [
            'status' => 'error',
            'message' => 'Database error: ' . $conn->error
        ];
        echo json_encode($response);
        exit;
    }
    
    $check_stmt->bind_param("iissssss", 
        $employee_id,
        $payroll_id,
        $pay_period_start, $pay_period_end, 
        $pay_period_start, $pay_period_end,
        $pay_period_start, $pay_period_end
    );
    
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Found overlapping record
        $check_stmt->close();
        echo json_encode([
            'status' => 'error', 
            'message' => 'This employee already has a payroll record for the same period or an overlapping period.'
        ]);
        exit;
    }
    
    $check_stmt->close();
    
    // Start transaction for atomic updates
    $conn->begin_transaction();

    try {
        // 1. Get existing payroll data
        $get_stmt = $conn->prepare("SELECT employee_id, loan_repayment FROM payroll_records WHERE payroll_id = ?");
        if ($get_stmt === false) {
            $response = [
                'status' => 'error',
                'message' => 'Database error: ' . $conn->error
            ];
            echo json_encode($response);
            exit;
        }
        $get_stmt->bind_param("i", $payroll_id);
        $get_stmt->execute();
        $existing = $get_stmt->get_result()->fetch_assoc();
        
        // 2. Update payroll record
        $update_stmt = $conn->prepare("UPDATE payroll_records SET 
            employee_id = ?, pay_period_start = ?, pay_period_end = ?, 
            gross_pay = ?, bonus = ?, deductions = ?, loan_repayment = ?, net_pay = ?, 
            payment_date = ?, notes = ? 
            WHERE payroll_id = ?");
        if ($update_stmt === false) {
            $response = [
                'status' => 'error',
                'message' => 'Database error: ' . $conn->error
            ];
            echo json_encode($response);
            exit;
        }
        
        $update_stmt->bind_param("issdddddssi",
            $employee_id,
            $pay_period_start,
            $pay_period_end,
            $gross_pay,
            $bonus,
            $deductions,
            $loan_repayment,
            $net_pay,
            $payment_date,
            $notes,
            $payroll_id
        );
        $update_stmt->execute();
        
        // 3. Handle loan repayment changes
        $new_repayment = floatval($loan_repayment);
        $old_repayment = floatval($existing['loan_repayment'] ?? 0);
        $repayment_diff = $new_repayment - $old_repayment;
        
        if ($repayment_diff != 0) {
            // Reverse old repayments if amount decreased
            if ($repayment_diff < 0) {
                $reverse_stmt = $conn->prepare("
                    UPDATE employee_loans el
                    JOIN loan_repayments lr ON el.loan_id = lr.loan_id
                    SET el.outstanding_balance = el.outstanding_balance + lr.repayment_amount
                    WHERE lr.payroll_id = ?");
                if ($reverse_stmt === false) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Database error: ' . $conn->error
                    ];
                    echo json_encode($response);
                    exit;
                }
                $reverse_stmt->bind_param("i", $payroll_id);
                $reverse_stmt->execute();
                
                // Delete old repayment records
                $delete_stmt = $conn->prepare("DELETE FROM loan_repayments WHERE payroll_id = ?");
                if ($delete_stmt === false) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Database error: ' . $conn->error
                    ];
                    echo json_encode($response);
                    exit;
                }
                $delete_stmt->bind_param("i", $payroll_id);
                $delete_stmt->execute();
            }
            
            // Apply new repayments if amount exists
            if ($new_repayment > 0) {
                // Get active loans
                $loan_stmt = $conn->prepare("SELECT loan_id, outstanding_balance FROM employee_loans 
                    WHERE employee_id = ? AND status = 'active' ORDER BY loan_date ASC");
                if ($loan_stmt === false) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Database error: ' . $conn->error
                    ];
                    echo json_encode($response);
                    exit;
                }
                $loan_stmt->bind_param("i", $employee_id);
                $loan_stmt->execute();
                $loans = $loan_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                // Apply repayment to oldest loans first
                $remaining_repayment = $new_repayment;
                foreach ($loans as $loan) {
                    if ($remaining_repayment <= 0) break;
                    
                    $deduction = min($loan['outstanding_balance'], $remaining_repayment);
                    
                    // Update loan balance
                    $update_loan = $conn->prepare("UPDATE employee_loans 
                        SET outstanding_balance = outstanding_balance - ? 
                        WHERE loan_id = ?");
                    if ($update_loan === false) {
                        $response = [
                            'status' => 'error',
                            'message' => 'Database error: ' . $conn->error
                        ];
                        echo json_encode($response);
                        exit;
                    }
                    $update_loan->bind_param("di", $deduction, $loan['loan_id']);
                    $update_loan->execute();
                    
                    // Record repayment
                    $repayment_stmt = $conn->prepare("INSERT INTO loan_repayments 
                        (loan_id, payroll_id, repayment_amount, repayment_date, repayment_method)
                        VALUES (?, ?, ?, ?, 'salary_deduction')");
                    if ($repayment_stmt === false) {
                        $response = [
                            'status' => 'error',
                            'message' => 'Database error: ' . $conn->error
                        ];
                        echo json_encode($response);
                        exit;
                    }
                    $repayment_stmt->bind_param("iids", 
                        $loan['loan_id'],
                        $payroll_id,
                        $deduction,
                        $payment_date
                    );
                    $repayment_stmt->execute();
                    
                    $remaining_repayment -= $deduction;
                }
            }
        }
        
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Payroll updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update payroll: ' . $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>