<?php
// Manual CORS to ensure visibility
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$results = [
    'step_1_cors_file' => file_exists(__DIR__ . '/../cors.php') ? 'FOUND' : 'MISSING',
    'step_2_config_file' => file_exists(__DIR__ . '/../../config/config.php') ? 'FOUND' : 'MISSING',
    'step_3_vendor_autoload' => file_exists(__DIR__ . '/../../vendor/autoload.php') ? 'FOUND' : 'MISSING',
];

try {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/Database.php';

    $database = new App\Config\Database();
    $conn = $database->getConnection();

    if ($conn) {
        $results['step_4_db_connection'] = 'SUCCESS';

        $stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $results['step_5_orders_count'] = $count;

        $stmt = $conn->query("SELECT * FROM orders LIMIT 1");
        $results['step_6_sample_order'] = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $results['step_4_db_connection'] = 'FAILED';
    }

} catch (Exception $e) {
    $results['error'] = $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);
