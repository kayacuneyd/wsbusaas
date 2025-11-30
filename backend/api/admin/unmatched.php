<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';
require_once __DIR__ . '/../../services/OrderService.php';
require_once __DIR__ . '/../../services/DeploymentService.php';

use App\Services\JwtService;
use App\Services\OrderService;
use App\Services\DeploymentService;

header('Content-Type: application/json');
$allowedOrigin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Auth Check
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
$payload = JwtService::validate($token);

if (!$payload || $payload['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$database = new App\Config\Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // List pending unmatched payments
    $query = "SELECT * FROM unmatched_payments WHERE status = 'pending' ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'payments' => $payments]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $paymentId = $input['payment_id'] ?? '';

    if (empty($paymentId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Payment ID required']);
        exit;
    }

    if ($action === 'ignore') {
        $query = "UPDATE unmatched_payments SET status = 'ignored' WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $paymentId);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'assign') {
        $orderId = $input['order_id'] ?? '';
        if (empty($orderId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Order ID required for assignment']);
            exit;
        }

        try {
            $conn->beginTransaction();

            // 1. Mark payment as resolved
            $query = "UPDATE unmatched_payments SET status = 'resolved', order_id_assigned = :order_id WHERE id = :id";
            // Note: order_id_assigned column might not exist, let's just update status for now or add column if needed. 
            // For simplicity, just update status. Ideally we'd link them.
            // Let's stick to status update for MVP.
            $query = "UPDATE unmatched_payments SET status = 'resolved' WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $paymentId);
            $stmt->execute();

            // 2. Update Order Status
            $orderService = new OrderService();
            $orderService->updateOrderStatus($orderId, 'payment_received', 'Admin tarafından manuel eşleştirildi.', 'admin-manual-match');

            // 3. Trigger Deployment
            $deploymentService = new DeploymentService();
            $jobId = $deploymentService->createDeploymentJob($orderId);

            // 4. Log
            $logQuery = "INSERT INTO order_logs (order_id, log_type, message) VALUES (:order_id, 'info', 'Sahipsiz ödeme manuel olarak eşleştirildi.')";
            $stmtLog = $conn->prepare($logQuery);
            $stmtLog->bindParam(':order_id', $orderId);
            $stmtLog->execute();

            $conn->commit();
            echo json_encode(['success' => true, 'job_id' => $jobId]);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
