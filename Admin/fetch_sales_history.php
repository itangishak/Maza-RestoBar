<?php
require_once 'connection.php';

header('Content-Type: application/json');

// For debugging purposes
error_log('Starting fetch_sales_history.php');

// Get DataTables request parameters
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0; // Offset for pagination
$length = isset($_GET['length']) ? intval($_GET['length']) : 10; // Number of records per page
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : ''; // Search query
$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 5; // Default to date column
$orderDirection = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc'; // Default descending
$saleType = isset($_GET['saleType']) ? $_GET['saleType'] : 'all'; // Type of sale to filter by

// Log all request parameters for debugging
error_log("Sales History Parameters: draw=$draw, start=$start, length=$length, search=$search, orderCol=$orderColumnIndex, orderDir=$orderDirection, saleType=$saleType");

// **Step 1: Get total records count**
// We need to count all types of sales: menu, drink, and buffet
$totalRecords = 0;

// Verify each table exists before counting
// Count menu sales
if ($conn->query("SHOW TABLES LIKE 'menu_sales'")) {
    $menuCountQuery = "SELECT COUNT(*) as total FROM menu_sales WHERE status = 'active' OR status = 'canceled'";
    $menuCountResult = $conn->query($menuCountQuery);
    if ($menuCountResult) {
        $totalRecords += $menuCountResult->fetch_assoc()['total'];
    }
}

// Count drink sales
if ($conn->query("SHOW TABLES LIKE 'drink_sales'")) {
    $drinkCountQuery = "SELECT COUNT(*) as total FROM drink_sales WHERE status = 'active' OR status = 'canceled'";
    $drinkCountResult = $conn->query($drinkCountQuery);
    if ($drinkCountResult) {
        $totalRecords += $drinkCountResult->fetch_assoc()['total'];
    }
}

// We're intentionally not counting buffet_sale_items as per requirement
// Count buffet sales (using buffet_sales table directly instead of buffet_sale_items)
if ($conn->query("SHOW TABLES LIKE 'buffet_sales'")) {
    $buffetCountQuery = "SELECT COUNT(*) as total FROM buffet_sales WHERE status = 'active' OR status = 'canceled'";
    $buffetCountResult = $conn->query($buffetCountQuery);
    if ($buffetCountResult) {
        $totalRecords += $buffetCountResult->fetch_assoc()['total'];
    }
}

// Count buffet accompaniments
if ($conn->query("SHOW TABLES LIKE 'buffet_accompaniments'")) {
    $accompanimentCountQuery = "SELECT COUNT(*) as total FROM buffet_accompaniments WHERE status = 'active' OR status = 'canceled'";
    $accompanimentCountResult = $conn->query($accompanimentCountQuery);
    if ($accompanimentCountResult) {
        $totalRecords += $accompanimentCountResult->fetch_assoc()['total'];
    }
}

// **Step 2: Apply search filter**
$searchQuery = "";
if (!empty($search)) {
    // Note: item_name isn't a real column in any of the tables, so don't filter by it
    // Each table has different column names, so we'll handle search in individual queries
    $searchQuery = " AND (1=1)"; // We'll add table-specific filters in each UNION query
}

// For debugging purposes
error_log("Search query: $search");
error_log("Sale type: $saleType");

// Total records after filtering (will be calculated based on the UNION query)

// Define columns for sorting (must match the table order in DataTables)
$columns = [
    'sale_id',         // 0
    'item_name',       // 1
    'quantity',        // 2
    'price',           // 3
    'sale_date',       // 4
    'sale_type',       // 5 (hidden column for data access)
    'status',          // 6 (only for Boss users)
    null               // 7 (actions column - not sortable)
];
$orderColumns = [
    0 => 'sale_id',
    1 => 'item_name',
    2 => 'quantity',
    3 => 'price',
    4 => 'sale_date',
    5 => 'sale_type',
    6 => 'status'
    // 7 - actions column is not orderable
];
$orderColumn = $orderColumns[$orderColumnIndex] ?? 'sale_date';

// Debug info
error_log("Order column: $orderColumn, Direction: $orderDirection, Sale Type: $saleType"); // Default to Sale Date

// **Step 3: Build UNION query to get all sale types**
// Start with an empty query
$unionQuery = '';
$whereClause = '';

// Debug the sale type filter
error_log('Filtering by sale type: ' . $saleType);
// Show the complete GET parameters for debugging
error_log('GET parameters: ' . json_encode($_GET));

// We don't need WHERE clause filtering because we'll include only the relevant tables
// based on the saleType parameter

// Build UNION queries for each sale type
// First, check if each table exists
$tablesExist = [
    'menu_sales' => !!$conn->query("SHOW TABLES LIKE 'menu_sales'"),
    'menu' => !!$conn->query("SHOW TABLES LIKE 'menu'"),
    'drink_sales' => !!$conn->query("SHOW TABLES LIKE 'drink_sales'"),
    'inventory_items' => !!$conn->query("SHOW TABLES LIKE 'inventory_items'"),
    // 'buffet_sale_items' is intentionally removed as per requirement
    'buffet_sales' => !!$conn->query("SHOW TABLES LIKE 'buffet_sales'"),
    'buffet_accompaniments' => !!$conn->query("SHOW TABLES LIKE 'buffet_accompaniments'")
];

// Build appropriate queries based on sale type - this is what controls filtering
// Note: For buffet sales, we're only showing buffet_sales and buffet_accompaniments, not buffet_sale_items
if ($saleType === 'all' || $saleType === 'menu') {
    // Check if menu_items table exists (this is the correct table structure)
    $menuTableCheck = $conn->query("SHOW TABLES LIKE 'menu_items'");
    
    error_log('Adding menu sales to query');
    error_log('Menu items table exists: ' . ($menuTableCheck && $menuTableCheck->num_rows > 0 ? 'Yes' : 'No'));
    
    if ($menuTableCheck && $menuTableCheck->num_rows > 0) {
        // Menu items table exists, use JOIN with the correct table and column names
        $unionQuery .= "
            SELECT
                ms.sale_id,
                'menu' AS sale_type,
                IFNULL(mi.name, CONCAT('Menu Item #', ms.menu_id)) AS item_name,
                ms.quantity_sold AS quantity,
                ms.sale_price AS price,
                ms.sale_date,
                ms.status,
                ms.cancellation_reason
            FROM menu_sales ms
            LEFT JOIN menu_items mi ON ms.menu_id = mi.menu_id
            WHERE 1=1";
    } else {
        // Menu items table doesn't exist, use simple query
        $unionQuery .= "
            SELECT
                sale_id,
                'menu' AS sale_type,
                CONCAT('Menu Item #', menu_id) AS item_name,
                quantity_sold AS quantity,
                sale_price AS price,
                sale_date,
                status,
                cancellation_reason
            FROM menu_sales
            WHERE 1=1";
    }
}

if ($saleType === 'all' || $saleType === 'drink') {
    // Add UNION separator if needed
    if (!empty($unionQuery)) {
        $unionQuery .= " UNION ";
    }
    
    // Check if inventory_items table exists and has item_name column
    $inventoryTableCheck = $conn->query("SHOW TABLES LIKE 'inventory_items'");
    $itemNameCheck = $conn->query("SHOW COLUMNS FROM inventory_items LIKE 'item_name'");
    
    error_log('Adding drink sales to query');
    error_log('Inventory table exists: ' . ($inventoryTableCheck && $inventoryTableCheck->num_rows > 0 ? 'Yes' : 'No'));
    
    if ($inventoryTableCheck && $inventoryTableCheck->num_rows > 0 && $itemNameCheck && $itemNameCheck->num_rows > 0) {
        // Use JOIN to get actual item names
        $unionQuery .= "
            SELECT
                ds.sale_id,
                'drink' AS sale_type,
                IFNULL(ii.item_name, CONCAT('Drink #', ds.inventory_id)) AS item_name,
                ds.quantity_sold AS quantity,
                ds.sale_price AS price,
                ds.sale_date,
                ds.status,
                ds.cancellation_reason
            FROM drink_sales ds
            LEFT JOIN inventory_items ii ON ds.inventory_id = ii.inventory_id
            WHERE 1=1";
    } else {
        // Simple query without JOIN
        $unionQuery .= "
            SELECT
                sale_id,
                'drink' AS sale_type,
                CONCAT('Drink #', inventory_id) AS item_name,
                quantity_sold AS quantity,
                sale_price AS price,
                sale_date,
                status,
                cancellation_reason
            FROM drink_sales
            WHERE 1=1";
    }
}

// Handle buffet_sale type
if ($saleType === 'all' || $saleType === 'buffet' || $saleType === 'buffet_sale') {
    // Add UNION separator if needed
    if (!empty($unionQuery)) {
        $unionQuery .= " UNION ";
    }
    
    error_log('Adding buffet_sales to query');
    // Enhanced query for buffet_sales with better formatting - no DATE_FORMAT for compatibility
    $unionQuery .= "
        SELECT
            sale_id,
            'buffet_sale' AS sale_type,
            CONCAT('Buffet Sale - ', sale_date, ' (', dishes_sold, ' dishes)') AS item_name,
            dishes_sold AS quantity,
            total_price AS price,
            created_at AS sale_date,
            status,
            cancellation_reason
        FROM buffet_sales
        WHERE 1=1";
}

// Handle buffet_accompaniment type
if ($saleType === 'all' || $saleType === 'buffet' || $saleType === 'buffet_accompaniment') {
    // Add UNION separator if needed
    if (!empty($unionQuery)) {
        $unionQuery .= " UNION ";
    }
    
    // Check if menu_items table exists
    $menuItemsCheck = $conn->query("SHOW TABLES LIKE 'menu_items'");
    
    error_log('Adding buffet_accompaniments to query');
    error_log('Menu items table for accompaniments exists: ' . ($menuItemsCheck && $menuItemsCheck->num_rows > 0 ? 'Yes' : 'No'));
    
    if ($menuItemsCheck && $menuItemsCheck->num_rows > 0) {
        // Join with menu_items table to get actual menu names for accompaniments
        $unionQuery .= "
            SELECT
                ba.accompaniment_id AS sale_id,
                'buffet_accompaniment' AS sale_type,
                IFNULL(mi.name, CONCAT('Accompaniment for menu #', ba.menu_id)) AS item_name,
                1 AS quantity,
                ba.accompaniment_price AS price,
                ba.created_at AS sale_date,
                ba.status,
                ba.cancellation_reason
            FROM buffet_accompaniments ba
            LEFT JOIN menu_items mi ON ba.menu_id = mi.menu_id
            WHERE 1=1";
    } else {
        // Simple query without JOIN
        $unionQuery .= "
            SELECT
                accompaniment_id AS sale_id,
                'buffet_accompaniment' AS sale_type,
                CONCAT('Accompaniment for menu #', menu_id) AS item_name,
                1 AS quantity,
                accompaniment_price AS price,
                created_at AS sale_date,
                status,
                cancellation_reason
            FROM buffet_accompaniments
            WHERE 1=1";
    }
}

// Before running the final query, check if we have any data to query
if (empty($unionQuery)) {
    // No tables were included in the query, return empty results
    error_log('No tables were included in the query. Check if your database tables exist and contain data.');
    $response = [
        "draw" => intval($draw),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ];
    echo json_encode($response);
    exit;
}

// Add debugging to see what tables were found
error_log('Tables exist: ' . json_encode($tablesExist));

// Complete the final query with ORDER BY and LIMIT clauses
$sql = "$unionQuery ORDER BY $orderColumn $orderDirection LIMIT $start, $length";

// Log the complete SQL query for debugging
error_log('Complete SQL query: ' . $sql);

// Get total filtered count (must run the same UNION without LIMIT to count results)
$countSql = "SELECT COUNT(*) as filtered_count FROM ($unionQuery) AS combined_results";
$countResult = $conn->query($countSql);

if (!$countResult) {
    error_log('Error in count query: ' . $conn->error);
    $totalFiltered = 0;
} else {
    $totalFiltered = $countResult->fetch_assoc()['filtered_count'];
    error_log('Total filtered records: ' . $totalFiltered);
}

// Execute the final paginated query
$result = $conn->query($sql);
$data = [];

if (!$result) {
    // Log SQL error for debugging
    error_log('SQL Error: ' . $conn->error);
    error_log('Failed SQL query: ' . $sql);
} else {
    // Process results
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    // Log result count for debugging
    error_log('Rows returned: ' . count($data));
}

// **Step 4: Return JSON response**
$response = [
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
];

echo json_encode($response);
?>
