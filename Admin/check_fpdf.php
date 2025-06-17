<?php
// Simple script to check if FPDF is installed correctly

// Path to check
$fpdf_path = '../fpdf/fpdf.php';

echo "<h2>FPDF Installation Check</h2>";

if (file_exists($fpdf_path)) {
    echo "<p style='color:green;'>✓ FPDF is installed at: $fpdf_path</p>";
    
    // Try to include the file
    try {
        require_once $fpdf_path;
        if (class_exists('FPDF')) {
            echo "<p style='color:green;'>✓ FPDF class loaded successfully!</p>";
            echo "<p>FPDF version: " . FPDF_VERSION . "</p>";
            echo "<p>Your PDF generation feature is ready to use.</p>";
        } else {
            echo "<p style='color:red;'>✗ FPDF class not found even though the file exists.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>✗ Error loading FPDF: " . $e->getMessage() . "</p>";
    }
    
    echo "<p><a href='payroll_dashboard.php'>Return to Payroll Dashboard</a></p>";
} else {
    echo "<p style='color:red;'>✗ FPDF is not installed at: $fpdf_path</p>";
    echo "<p>Would you like to install FPDF automatically?</p>";
    echo "<a href='install_fpdf.php' class='btn btn-primary'>Install FPDF Now</a> ";
    echo "<a href='payroll_dashboard.php' class='btn btn-secondary'>Return to Dashboard</a>";
    
    echo "<p style='margin-top:20px;'><strong>Manual Installation:</strong></p>";
    echo "<ol>";
    echo "<li>Download FPDF from <a href='http://www.fpdf.org' target='_blank'>www.fpdf.org</a></li>";
    echo "<li>Extract the files to the '../fpdf/' directory</li>";
    echo "<li>Make sure 'fpdf.php' is directly in the '../fpdf/' directory</li>";
    echo "</ol>";
}
?> 