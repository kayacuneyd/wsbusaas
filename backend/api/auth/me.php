<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';

use App\Services\JwtService;

header('Content-Type: application/json');

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (empty($authHeader)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Extract token (Bearer <token>)
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
} else {
    $token = $authHeader;
}

// Decode token (Simple base64 decode for MVP)
$decoded = base64_decode($token);
$parts = explode(':', $decoded);

if (count($parts) < 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

$userId = $parts[0];

$database = new App\Config\Database();
$conn = $database->getConnection();

$query = "SELECT id, email, full_name, created_at FROM users WHERE id = :id LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
