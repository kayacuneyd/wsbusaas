<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Simple Auth Check
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (empty($authHeader)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$database = new App\Config\Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM settings";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Returns ['key' => 'value']

    echo json_encode(['success' => true, 'settings' => $settings]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No data provided']);
        exit;
    }

    try {
        $conn->beginTransaction();

        $query = "INSERT INTO settings (`key`, `value`) VALUES (:key, :value) ON DUPLICATE KEY UPDATE `value` = :value";
        $stmt = $conn->prepare($query);

        foreach ($input as $key => $value) {
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
