<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');

try {
    $database = new \App\Config\Database();
    $conn = $database->getConnection();

    $sql = file_get_contents(__DIR__ . '/../deployment_schema.sql');

    // Split by semicolon to execute multiple queries
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Schema updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
