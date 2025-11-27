<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/OrderService.php';

use App\Services\OrderService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
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

    $decoded = base64_decode($token);
    $parts = explode(':', $decoded);
    if (count($parts) >= 2) {
        $userId = $parts[0];
    }
}

// Require Login? User requested "must be member".
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Üye girişi yapmanız gerekmektedir.']);
    exit;
}

$orderService = new OrderService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $input['user_id'] = $userId; // Add user_id to input for service

    // Basic validation
    if (empty($input['customer_email']) || empty($input['domain_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    $orderId = $orderService->createOrder($input);

    if ($orderId) {
        // Fetch Payment URL from Settings
        $querySettings = "SELECT value FROM settings WHERE `key` = 'payment_url' LIMIT 1";
        $stmtSettings = $orderService->conn->prepare($querySettings);
        $stmtSettings->execute();
        $baseUrl = $stmtSettings->fetchColumn();

        if (!$baseUrl) {
            // Fallback if not set
            $baseUrl = "https://ruul.space/payment/cs_live_a1pcdSzZ9W0GJwTGV7ybtlRtwiyDxl7mIFaVsCGJ6NZH8Q642veiC4XEyA_secret_fidnandhYHdWcXxpYCc%2FJ2FgY2RwaXEnKSdwbEhqYWAnPydmcHZxamgneCUl?from=%2Fcuneytkaya%2Fproducts%2F13347";
        }

        // Check if URL already has query params to decide between ? or & (though Ruul usually has params)
        // Our stored URL might already have ?from=...
        $separator = (strpos($baseUrl, '?') !== false) ? '%3F' : '%3F'; // Using encoded ? as requested for Ruul
        // Actually, looking at previous request, user wanted %3Forder_id%3D
        // Let's stick to the requested format: append encoded ?order_id=...

        $paymentUrl = $baseUrl . "%3Forder_id%3D" . $orderId;

        echo json_encode([
            'success' => true,
            'order' => [
                'order_id' => $orderId,
                'status' => 'created'
            ],
            'payment' => [
                'url' => $paymentUrl
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create order']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get order ID from URL path (handled by .htaccess routing if implemented, or query param)
    // Assuming /api/orders/{id} maps to orders.php?id={id} via .htaccess or we parse URI

    // Simple parsing if .htaccess passes route
    $route = $_GET['route'] ?? '';
    // If route is "orders/WB123", we extract WB123
    $parts = explode('/', $route);
    $orderId = end($parts);

    if (empty($orderId) || $orderId === 'orders') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Order ID required']);
        exit;
    }

    $order = $orderService->getOrder($orderId);

    if ($order) {
        echo json_encode(['success' => true, 'order' => $order]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
    }
}
