<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

$database = new App\Config\Database();
$conn = $database->getConnection();

$query = "SELECT * FROM admin_users WHERE username = :username LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':username', $username);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    // Generate simple token (In production use JWT)
    $token = base64_encode($user['id'] . ':' . time());

    echo json_encode(['success' => true, 'token' => $token]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
}
