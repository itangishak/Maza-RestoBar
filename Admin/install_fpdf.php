<?php
// Script to download and install FPDF library

// Create fpdf directory if it doesn't exist
if (!file_exists('../fpdf')) {
    mkdir('../fpdf', 0755, true);
}

// URL of the latest FPDF
$fpdf_url = 'http://www.fpdf.org/en/download/fpdf184.tgz';
$temp_file = '../fpdf/fpdf.tgz';

// Download the file
echo "Downloading FPDF...<br>";
if (file_put_contents($temp_file, file_get_contents($fpdf_url))) {
    echo "Download complete.<br>";
    
    // Extract the archive
    echo "Extracting files...<br>";
    $phar = new PharData($temp_file);
    $phar->extractTo('../fpdf', null, true); // Extract all files, overwrite
    
    // Move files from subfolder to main fpdf folder
    $files = scandir('../fpdf/fpdf184');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            rename("../fpdf/fpdf184/$file", "../fpdf/$file");
        }
    }
    
    // Remove the temporary download and extraction directory
    unlink($temp_file);
    rmdir('../fpdf/fpdf184');
    
    echo "FPDF installed successfully!<br>";
    echo "<a href='payroll_dashboard.php'>Return to Payroll Dashboard</a>";
} else {
    echo "Failed to download FPDF. Please install it manually by downloading from <a href='http://www.fpdf.org'>fpdf.org</a> and extracting to the '../fpdf' directory.";
}
?> 