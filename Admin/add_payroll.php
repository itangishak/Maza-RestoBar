<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    // Gather form data
    $employee_id      = $_POST['employee_id'];
    $pay_period_start = $_POST['pay_period_start'];
    $pay_period_end   = $_POST['pay_period_end'];
    $bonus            = $_POST['bonus'] ?? 0;
    $deductions       = $_POST['deductions'] ?? 0;
    $payment_date     = $_POST['payment_date'];
    $notes            = $_POST['notes'] ?? '';
    $loan_repayment   = floatval($_POST['loan_repayment'] ?? 0);

    // Get employee's base salary
    $salary_query = "SELECT salary FROM employees WHERE employee_id = ?";
    $salary_stmt = $conn->prepare($salary_query);
    if (!$salary_stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Salary query prepare failed: ' . $conn->error]);
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
    
    // Check for duplicate payroll records
    $duplicate_check_sql = "SELECT payroll_id FROM payroll_records 
                            WHERE employee_id = ? 
                            AND (
                                (pay_period_start BETWEEN ? AND ?) OR
                                (pay_period_end BETWEEN ? AND ?) OR
                                (? BETWEEN pay_period_start AND pay_period_end) OR
                                (? BETWEEN pay_period_start AND pay_period_end)
                            )";
    
    $check_stmt = $conn->prepare($duplicate_check_sql);
    if (!$check_stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Duplicate check prepare failed: ' . $conn->error]);
        exit;
    }
    
    $check_stmt->bind_param("issssss", 
        $employee_id, 
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

    // Start transaction
    $conn->begin_transaction();

    try {
        // Prepare insert with loan_repayment column
        $stmt = $conn->prepare("INSERT INTO payroll_records (
            employee_id, pay_period_start, pay_period_end, 
            gross_pay, bonus, deductions, net_pay, 
            payment_date, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        // Bind parameters (9 total: issdddsss)
        $stmt->bind_param("issdddsss",
            $employee_id,
            $pay_period_start,
            $pay_period_end,
            $gross_pay,
            $bonus,
            $deductions,
            $net_pay,
            $payment_date,
            $notes
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $payroll_id = $stmt->insert_id;
        
        // Process loan repayment if exists
        if ($loan_repayment > 0) {
            // Get active loans for employee
            $loan_stmt = $conn->prepare("SELECT loan_id, outstanding_balance FROM employee_loans 
                WHERE employee_id = ? AND status = 'active' ORDER BY loan_date ASC");
            
            if (!$loan_stmt) {
                throw new Exception('Loan prepare failed: ' . $conn->error);
            }
            
            $loan_stmt->bind_param("i", $employee_id);
            
            if (!$loan_stmt->execute()) {
                throw new Exception('Loan execute failed: ' . $loan_stmt->error);
            }
            
            $loans = $loan_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Apply repayment to oldest loans first
            $remaining_repayment = $loan_repayment;
            foreach ($loans as $loan) {
                if ($remaining_repayment <= 0) break;
                
                $deduction = min($loan['outstanding_balance'], $remaining_repayment);
                
                // Update loan balance and check if fully repaid
                $update_loan = $conn->prepare("UPDATE employee_loans 
                    SET outstanding_balance = outstanding_balance - ?,
                        status = IF(outstanding_balance - ? <= 0, 'repaid', status)
                    WHERE loan_id = ?");
                
                if (!$update_loan) {
                    throw new Exception('Loan update prepare failed: ' . $conn->error);
                }
                
                $update_loan->bind_param("ddi", $deduction, $deduction, $loan['loan_id']);
                
                if (!$update_loan->execute()) {
                    throw new Exception('Loan update execute failed: ' . $update_loan->error);
                }
                
                // Record repayment
                $repayment_stmt = $conn->prepare("INSERT INTO loan_repayments 
                    (loan_id, payroll_id, repayment_amount, repayment_date, repayment_method)
                    VALUES (?, ?, ?, ?, 'salary_deduction')");
                
                if (!$repayment_stmt) {
                    throw new Exception('Repayment prepare failed: ' . $conn->error);
                }
                
                $repayment_stmt->bind_param("iids", 
                    $loan['loan_id'],
                    $payroll_id,
                    $deduction,
                    $payment_date
                );
                
                if (!$repayment_stmt->execute()) {
                    throw new Exception('Repayment execute failed: ' . $repayment_stmt->error);
                }
                
                $remaining_repayment -= $deduction;
            }
        }
        
        $conn->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => 'Payroll added successfully.',
            'payroll_id' => $payroll_id,
            'pdf_url' => "generate_payroll_pdf.php?id=" . $payroll_id
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to add payroll: ' . $e->getMessage()]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>