<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';
require_once __DIR__ . '/../../services/Logger.php';

use App\Services\JwtService;
use App\Services\Logger;

header('Content-Type: application/json');
$allowedOrigin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

try {
    $database = new App\Config\Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new \Exception('Database connection failed');
    }

    $query = "SELECT * FROM admin_users WHERE username = :username LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $verified = $user ? password_verify($password, $user['password_hash']) : false;
    if (!$user) {
        Logger::error('admin/login user not found', ['username' => $username]);
    } elseif (!$verified) {
        Logger::error('admin/login password mismatch', ['username' => $username]);
    }

    if ($user && $verified) {
        // Generate JWT token
        $token = JwtService::generate([
            'sub' => $user['id'],
            'username' => $user['username'],
            'role' => 'admin'
        ]);

        echo json_encode(['success' => true, 'token' => $token]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
} catch (\Throwable $e) {
    Logger::error('admin/login failed', ['error' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
