<?php
require_once 'connection.php';

// Read SQL commands from update_schema.sql
$sql = file_get_contents('update_schema.sql');

// Split into individual statements
$statements = explode(';', $sql);

// Execute each statement
$successCount = 0;
$errors = [];

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    echo "Executing: $statement\n";
    
    if ($conn->query($statement)) {
        $successCount++;
        echo "Success!\n";
    } else {
        $errors[] = "Error executing: $statement\nError: " . $conn->error;
        echo "Error: " . $conn->error . "\n";
    }
}

echo "\nCompleted with $successCount successful statements and " . count($errors) . " errors.\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo $error . "\n\n";
    }
}

$conn->close();
?> 