<?php
// backend/api/check_schema.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');

try {
    $database = new \App\Config\Database();
    $conn = $database->getConnection();

    $results = [];

    // Check 'orders' table columns
    $stmt = $conn->query("SHOW COLUMNS FROM orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $results['orders_columns'] = $columns;
    $results['has_status_message'] = in_array('status_message', $columns);
    $results['has_status_updated_by'] = in_array('status_updated_by', $columns);

    // Check 'order_status_history' table
    $stmt = $conn->query("SHOW TABLES LIKE 'order_status_history'");
    $results['has_history_table'] = $stmt->rowCount() > 0;

    echo json_encode(['success' => true, 'schema' => $results]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
