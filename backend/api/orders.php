<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../services/JwtService.php';

use App\Services\OrderService;
use App\Services\JwtService;

header('Content-Type: application/json');
$allowedOrigin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Auth Check for Customer
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$userId = null;

if (!empty($authHeader)) {
    // Extract token
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

// Require Login? User requested "must be member".
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Üye girişi yapmanız gerekmektedir.']);
    exit;
}

$orderService = new OrderService();
$conn = $orderService->conn;

// Ensure settings table exists (for legacy databases)
$conn->exec("CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $input['user_id'] = $userId; // Add user_id to input for service

    // Basic validation
    if (empty($input['customer_email']) || empty($input['domain_name'])) {
        file_put_contents($logFile, "Error: Missing required fields\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields', 'debug_input' => $input]);
        exit;
    }

    try {
        $created = $orderService->createOrder($input);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }

    $orderId = $created['order_id'] ?? null;
    $paymentUrl = $created['payment_link'] ?? null;

    if (!$orderId || !$paymentUrl) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create order']);
        exit;
    }

    $order = $orderService->getOrderWithStatus($orderId);

    echo json_encode([
        'success' => true,
        'order' => $order ?: ['order_id' => $orderId],
        'payment' => [
            'url' => $paymentUrl
        ]
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get order ID from URL path (handled by .htaccess routing if implemented, or query param)
    // Assuming /api/orders/{id} maps to orders.php?id={id} via .htaccess or we parse URI

    // Simple parsing if .htaccess passes route
    $route = $_GET['route'] ?? '';

    // If no route is provided (e.g. Apache/Nginx rewrite not setting it), attempt to parse from REQUEST_URI
    if (empty($route)) {
        $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // Expecting something like /api/orders/WB123
        $segments = explode('/', trim($uriPath, '/'));
        $ordersIndex = array_search('orders', $segments);
        if ($ordersIndex !== false && isset($segments[$ordersIndex + 1])) {
            $route = 'orders/' . $segments[$ordersIndex + 1];
        }
    }

    // If route is "orders/WB123", we extract WB123
    $parts = explode('/', $route);
    $orderId = end($parts);

    if (empty($orderId) || $orderId === 'orders') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Order ID required']);
        exit;
    }

    $order = $orderService->getOrderWithStatus($orderId);

    if ($order) {
        $paymentLink = $order['payment_link'] ?? $orderService->ensurePaymentLinkForOrder($orderId, $order['package_type'] ?? null);
        $order['payment_link'] = $paymentLink;

        echo json_encode([
            'success' => true,
            'order' => $order,
            'payment' => [
                'url' => $paymentLink
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
    }
}
