<?php
require_once 'connection.php';

echo "<h1>Employees Table Check</h1>";

// Check structure of employees table
$cols = $conn->query("DESCRIBE employees");
if ($cols) {
    echo "<h2>Columns in 'employees' table:</h2>";
    echo "<ul>";
    $has_employee_name = false;
    $name_column = "";
    
    while($col = $cols->fetch_assoc()) {
        echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
        
        // Look for employee_name column or any column that might contain the name
        if ($col['Field'] == 'employee_name') {
            $has_employee_name = true;
        }
        
        if (strpos($col['Field'], 'name') !== false) {
            $name_column = $col['Field'];
        }
    }
    echo "</ul>";
    
    if (!$has_employee_name && $name_column) {
        echo "<p style='color:orange;'>Warning: No 'employee_name' column found, but found potential name column: '$name_column'</p>";
    } elseif (!$has_employee_name) {
        echo "<p style='color:red;'>Error: No 'employee_name' column found!</p>";
    } else {
        echo "<p style='color:green;'>✓ Found 'employee_name' column</p>";
    }
    
    // Show first few employee records
    $employees = $conn->query("SELECT * FROM employees LIMIT 5");
    if ($employees && $employees->num_rows > 0) {
        echo "<h2>Sample Employee Records:</h2>";
        echo "<table border='1' cellpadding='5'>";
        
        // Print column headers
        echo "<tr>";
        $fields = $employees->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        
        // Reset result pointer
        $employees->data_seek(0);
        
        // Print data rows
        while ($employee = $employees->fetch_assoc()) {
            echo "<tr>";
            foreach ($employee as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No employee records found or error retrieving data.</p>";
    }
} else {
    echo "<p style='color:red;'>Error describing employees table: " . $conn->error . "</p>";
}

// Close connection
$conn->close();
?> 