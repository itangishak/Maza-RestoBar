<?php
require_once 'connection.php';

echo "<h1>Database Tables Check</h1>";

// List all tables in the database
echo "<h2>All Tables in Database:</h2>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "<ul>";
    while($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Error listing tables: " . $conn->error . "</p>";
}

// Check specifically for payroll_records and employee tables
$required_tables = ['payroll_records', 'employee'];
echo "<h2>Required Tables Check:</h2>";
echo "<ul>";

foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<li style='color:green'>✓ Table $table exists</li>";
        
        // Show table structure
        echo "<ul>";
        $cols = $conn->query("DESCRIBE $table");
        if ($cols) {
            while($col = $cols->fetch_assoc()) {
                echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<li style='color:red'>✗ Table $table does not exist!</li>";
    }
}
echo "</ul>";

// Check if there's any data in the payroll_records table
$result = $conn->query("SELECT COUNT(*) as count FROM payroll_records");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Number of payroll records: " . $row['count'] . "</p>";
} else {
    echo "<p>Could not check payroll records count: " . $conn->error . "</p>";
}

// Close connection
$conn->close();
?> 