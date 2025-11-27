<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';

use App\Services\JwtService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Simple Auth Check
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$decoded = null;

if (!empty($authHeader)) {
    if (preg_match('/Bearer\\s(\\S+)/', $authHeader, $matches)) {
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

// Get Pending Orders
$queryPending = "SELECT COUNT(*) as count FROM orders WHERE order_status != 'completed' AND order_status != 'failed'";
$stmtPending = $conn->prepare($queryPending);
$stmtPending->execute();
$pending = $stmtPending->fetch(PDO::FETCH_ASSOC)['count'];

// Get Completed Orders
$queryCompleted = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'completed'";
$stmtCompleted = $conn->prepare($queryCompleted);
$stmtCompleted->execute();
$completed = $stmtCompleted->fetch(PDO::FETCH_ASSOC)['count'];

// Get Revenue (Mock calculation: 299 * paid orders)
$queryRevenue = "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'paid'";
$stmtRevenue = $conn->prepare($queryRevenue);
$stmtRevenue->execute();
$paidCount = $stmtRevenue->fetch(PDO::FETCH_ASSOC)['count'];
$revenue = $paidCount * 299;

echo json_encode([
    'success' => true,
    'stats' => [
        'pending' => $pending,
        'completed' => $completed,
        'revenue_mtd' => $revenue
    ]
]);
