<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');
$allowedOrigin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
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

