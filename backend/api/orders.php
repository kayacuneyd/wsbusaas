<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../services/JwtService.php';

use App\Services\OrderService;
use App\Services\JwtService;

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

    $orderId = $orderService->createOrder($input);

    if ($orderId) {
        $initialStatus = OrderService::STATUS_MESSAGES['pending_confirmation'];

        // Try to fetch payment URL from packages table first
        $packageType = $input['package_type'] ?? 'starter';
        $baseUrl = null;

        // Check if packages table exists and try to get payment link from package
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS packages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                price DECIMAL(10,2),
                payment_link TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                display_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");

            $queryPackage = "SELECT payment_link FROM packages WHERE slug = :slug AND is_active = 1 LIMIT 1";
            $stmtPackage = $conn->prepare($queryPackage);
            $stmtPackage->bindParam(':slug', $packageType);
            $stmtPackage->execute();
            $packagePaymentLink = $stmtPackage->fetchColumn();
            
            if ($packagePaymentLink) {
                $baseUrl = $packagePaymentLink;
            }
        } catch (Exception $e) {
            // Packages table might not exist yet, fall back to settings
        }

        // Fallback to settings if no package payment link found
        if (!$baseUrl) {
            $querySettings = "SELECT value FROM settings WHERE `key` = 'payment_url' LIMIT 1";
            $stmtSettings = $conn->prepare($querySettings);
            $stmtSettings->execute();
            $baseUrl = $stmtSettings->fetchColumn();
        }

        if (!$baseUrl) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Payment URL is not configured. Please set it in Admin > Packages or Settings.']);
            exit;
        }

        // Append order_id to payment URL
        $paymentUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '%3F') . 'order_id%3D' . $orderId;

        echo json_encode([
            'success' => true,
            'order' => [
                'order_id' => $orderId,
                'status' => 'pending_confirmation',
                'status_message' => $initialStatus['en'],
                'status_messages' => $initialStatus
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
        echo json_encode(['success' => true, 'order' => $order]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
    }
}
