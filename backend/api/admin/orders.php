<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';

use App\Services\JwtService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PATCH, OPTIONS');
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

// Parse ID from URL if present (e.g. /api/admin/orders/WB123)
// Assuming .htaccess passes route param
$route = $_GET['route'] ?? '';
$parts = explode('/', $route);
// route is like "admin/orders" or "admin/orders/WB123"
$orderId = null;
if (count($parts) > 2) {
    $orderId = $parts[2];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($orderId) {
        // Get single order
        $query = "SELECT * FROM orders WHERE order_id = :order_id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Get logs
            $queryLogs = "SELECT * FROM order_logs WHERE order_id = :order_id ORDER BY created_at DESC";
            $stmtLogs = $conn->prepare($queryLogs);
            $stmtLogs->bindParam(':order_id', $orderId);
            $stmtLogs->execute();
            $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'order' => $order, 'logs' => $logs]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Order not found']);
        }
    } else {
        // List all orders
        $query = "SELECT * FROM orders ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'orders' => $orders]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    if (!$orderId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Order ID required']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $status = $input['status'] ?? '';

    if (empty($status)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Status required']);
        exit;
    }

    try {
        $query = "UPDATE orders SET order_status = :status, updated_at = NOW() WHERE order_id = :order_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        // Log
        $queryLog = "INSERT INTO order_logs (order_id, log_type, message) VALUES (:order_id, 'info', :message)";
        $stmtLog = $conn->prepare($queryLog);
        $stmtLog->bindParam(':order_id', $orderId);
        $msg = "Durum güncellendi: " . $status;
        $stmtLog->bindParam(':message', $msg);
        $stmtLog->execute();

        // Fetch updated order
        $query = "SELECT * FROM orders WHERE order_id = :order_id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'order' => $order]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
