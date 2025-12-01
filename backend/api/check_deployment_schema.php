<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');

try {
    $database = new \App\Config\Database();
    $conn = $database->getConnection();

    $tables = ['deployment_jobs', 'deployment_steps'];
    $results = [];

    foreach ($tables as $table) {
        $stmt = $conn->prepare("SHOW TABLES LIKE :table");
        $stmt->execute([':table' => $table]);
        $results[$table] = $stmt->rowCount() > 0;
    }

    echo json_encode(['success' => true, 'tables' => $results]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
