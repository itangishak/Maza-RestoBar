<?php
// Create a PDF payroll receipt in French
header('Content-Type: text/html; charset=utf-8');

require_once 'connection.php';
require_once '../fpdf/fpdf.php'; // Assuming FPDF is installed in this location

// Set encoding for database connection
if ($conn && method_exists($conn, 'set_charset')) {
    $conn->set_charset("utf8");
}

// Check if payroll_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Aucun ID de paie fourni.");
}

$payroll_id = $_GET['id'];

try {
    // Get payroll data with proper error handling
    $sql = "SELECT pr.*, CONCAT(u.firstname, ' ', u.lastname) as employee_name, 
            e.position, e.employee_id as matricule
            FROM payroll_records pr
            JOIN employees e ON pr.employee_id = e.employee_id
            JOIN user u ON e.user_id = u.UserId
            WHERE pr.payroll_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    // Check if prepare statement succeeded
    if ($stmt === false) {
        die("Erreur de préparation de la requête SQL: " . $conn->error);
    }
    
    $stmt->bind_param("i", $payroll_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        die("Fiche de paie non trouvée.");
    }
    
    $payroll = $result->fetch_assoc();
    
    // Create PDF
    class PayrollPDF extends FPDF {
        function Header() {
            // Logo - if available
            $logo_path = '../assets/images/logo.png'; // Adjust path as needed
            if (file_exists($logo_path)) {
                $this->Image($logo_path, 10, 10, 30);
                $this->Ln(2);
            }
            
            // Company details
            $this->SetFont('Arial', 'B', 15);
            $this->Cell(0, 10, 'MAZA RESTO-BAR', 0, 1, 'C');
            
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 6, 'Adresse: Boulevard Melchior Ndadaye, Peace Corner, Bujumbura', 0, 1, 'C');
            $this->Cell(0, 6, 'Telephone: +257 69 80 58 98 | Email: barmazaresto@gmail.com', 0, 1, 'C');
            
            // Title
            $this->SetFont('Arial', 'B', 14);
            $this->Ln(10);
            $this->Cell(0, 10, 'FICHE DE PAIE', 0, 1, 'C');
            $this->Ln(5);
        }
        
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
        
        // Add a method to create data rows with consistent spacing
        function LabelValueRow($label, $value, $fontStyle = '') {
            $this->SetFont('Arial', $fontStyle, 11);
            $this->Cell(60, 8, $label . ':', 0);
            $this->Cell(0, 8, $value, 0, 1);
        }
    }
    
    // Initialize PDF with UTF-8 support
    $pdf = new PayrollPDF();
    // Use DejaVu font which supports UTF-8 better (if available)
    if (file_exists('../fpdf/font/DejaVuSans.php') || file_exists('../fpdf/font/DejaVu/DejaVuSans.php')) {
        $pdf->AddFont('DejaVu', '', 'DejaVuSans.php');
        $pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.php');
        $pdf->AddFont('DejaVu', 'I', 'DejaVuSans-Oblique.php');
        $pdf->SetFont('DejaVu', '', 11);
    } else {
        // Fallback to Arial if DejaVu is not available
        $pdf->SetFont('Arial', '', 11);
    }
    $pdf->AddPage();
    
    // Format dates
    $pay_period_start = date("d/m/Y", strtotime($payroll['pay_period_start']));
    $pay_period_end = date("d/m/Y", strtotime($payroll['pay_period_end']));
    $payment_date = date("d/m/Y", strtotime($payroll['payment_date']));
    
    // Employee info
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Informations de l\'employe', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    
    $pdf->LabelValueRow('Nom de l\'employe', $payroll['employee_name']);
    $pdf->LabelValueRow('Matricule', $payroll['matricule']);
    $pdf->LabelValueRow('Poste', $payroll['position'] ? $payroll['position'] : 'Non specifie');
    
    // Payroll info
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Details de la paie', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    
    $pdf->LabelValueRow('Periode de paie', "Du $pay_period_start au $pay_period_end");
    $pdf->LabelValueRow('Date de paiement', $payment_date);
    
    $pdf->Ln(5);
    
    // Payment details in a table
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(60, 8, 'Description', 1);
    $pdf->Cell(60, 8, 'Montant (BIF)', 1);
    $pdf->Cell(0, 8, 'Details', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 11);
    
    // Gross pay
    $pdf->Cell(60, 8, 'Salaire brut', 1);
    $pdf->Cell(60, 8, number_format($payroll['gross_pay'], 2, ',', ' ') . ' BIF', 1);
    $pdf->Cell(0, 8, 'Salaire de base mensuel', 1, 1);
    
    // Bonus
    $pdf->Cell(60, 8, 'Prime', 1);
    $pdf->Cell(60, 8, number_format($payroll['bonus'] ?? 0, 2, ',', ' ') . ' BIF', 1);
    $pdf->Cell(0, 8, ($payroll['bonus'] > 0) ? 'Prime de performance' : 'Aucune prime', 1, 1);
    
    // Loan repayment
    $pdf->Cell(60, 8, 'Remboursement de pret', 1);
    $pdf->Cell(60, 8, number_format($payroll['loan_repayment'] ?? 0, 2, ',', ' ') . ' BIF', 1);
    $pdf->Cell(0, 8, ($payroll['loan_repayment'] > 0) ? 'Deduction pour remboursement de pret' : 'Aucun remboursement', 1, 1);
    
    // Deductions
    $pdf->Cell(60, 8, 'Deductions', 1);
    $pdf->Cell(60, 8, number_format($payroll['deductions'], 2, ',', ' ') . ' BIF', 1);
    $pdf->Cell(0, 8, $payroll['deductions'] > 0 ? 'Retenues salariales' : 'Aucune deduction', 1, 1);
    
    // Net pay
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(60, 8, 'Salaire net', 1);
    $pdf->Cell(60, 8, number_format($payroll['net_pay'], 2, ',', ' ') . ' BIF', 1);
    $pdf->Cell(0, 8, 'Montant final a recevoir', 1, 1);
    
    // Notes
    if (!empty($payroll['notes'])) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, 'Notes:', 0, 1);
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 8, $payroll['notes'], 0, 'L');
    }
    
    // Signature lines
    $pdf->Ln(15);
    $pdf->Cell(90, 8, 'Signature du gestionnaire', 0, 0, 'C');
    $pdf->Cell(90, 8, 'Signature de l\'employe', 0, 1, 'C');
    $pdf->Cell(90, 20, '', 'B', 0, 'C');
    $pdf->Cell(90, 20, '', 'B', 1, 'C');
    
    // Legal notice
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->MultiCell(0, 6, 'Ce document constitue une preuve officielle de paiement. Merci de votre travail et devouement envers MAZA RESTO-BAR.', 0, 'C');
    
    // Output the PDF
    $pdf_filename = 'Fiche_de_Paie_' . $payroll['employee_name'] . '_' . $pay_period_end . '.pdf';
    $pdf->Output('D', $pdf_filename); // 'D' forces download
    
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}
?>