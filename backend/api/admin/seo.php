<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';

use App\Services\JwtService;

header('Content-Type: application/json');

// Auth Check
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$decoded = null;

if (!empty($authHeader)) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        $token = $authHeader;
    }
    $decoded = JwtService::decode($token);
}

if (!$decoded || ($decoded['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$database = new App\Config\Database();
$conn = $database->getConnection();

// Ensure settings table exists
$conn->exec("CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get SEO settings
    $seoKeys = ['seo_title', 'seo_description', 'seo_keywords', 'seo_og_image', 'seo_og_title', 'seo_og_description'];
    $placeholders = implode(',', array_fill(0, count($seoKeys), '?'));
    $query = "SELECT `key`, `value` FROM settings WHERE `key` IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->execute($seoKeys);
    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $seoSettings = [];
    foreach ($seoKeys as $key) {
        $seoSettings[$key] = $results[$key] ?? '';
    }

    echo json_encode(['success' => true, 'settings' => $seoSettings]);

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
            if (strpos($key, 'seo_') === 0) {
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
        }

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

