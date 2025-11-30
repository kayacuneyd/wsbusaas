<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';
require_once __DIR__ . '/../../services/OrderService.php';

use App\Services\JwtService;
use App\Services\OrderService;

header('Content-Type: application/json');

// Auth Check
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$userId = null;

if (!empty($authHeader)) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        $token = $authHeader;
    }

    $decoded = JwtService::decode($token);
    if ($decoded && isset($decoded['sub'])) {
        $userId = $decoded['sub'];
    }
}

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $orderService = new OrderService();
    $conn = $orderService->conn;

    $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        if (empty($order['payment_link']) && ($order['order_status'] ?? '') === 'pending_confirmation') {
            $order['payment_link'] = $orderService->ensurePaymentLinkForOrder($order['order_id'], $order['package_type'] ?? null);
        }
    }

    echo json_encode(['success' => true, 'orders' => $orders]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server Error: ' . $e->getMessage()]);
}
