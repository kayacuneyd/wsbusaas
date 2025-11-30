<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/OrderService.php';

use App\Services\OrderService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$config = require __DIR__ . '/../../config/config.php';
$secret = $_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? '';

if ($secret !== $config['webhook_secret']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['order_id'] ?? '';
$email = $input['email'] ?? '';
$customerName = $input['customer_name'] ?? '';
$paymentStatus = $input['payment_status'] ?? '';

if ($paymentStatus !== 'paid') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment status']);
    exit;
}

$orderService = new OrderService();
$conn = $orderService->conn;

try {
    // If no Order ID, try to find by email or name
    if (empty($orderId)) {
        if (!empty($email)) {
            // Find by email
            $query = "SELECT order_id FROM orders WHERE customer_email = :email AND payment_status = 'pending' ORDER BY created_at DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result)
                $orderId = $result['order_id'];
        }

        if (empty($orderId) && !empty($input['customer_name'])) {
            // Find by Customer Name (Fuzzy match or exact)
            // We assume the name in Ruul matches the name provided during checkout
            $name = $input['customer_name'];
            $query = "SELECT order_id FROM orders WHERE customer_name LIKE :name AND payment_status = 'pending' ORDER BY created_at DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $likeName = "%" . $name . "%";
            $stmt->bindParam(':name', $likeName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $orderId = $result['order_id'];
                // Log that we matched by name
                $queryLog = "INSERT INTO order_logs (order_id, log_type, message) VALUES (:order_id, 'warning', 'Sipariş ID bulunamadı, İsim ile eşleştirildi: $name')";
                $stmtLog = $conn->prepare($queryLog);
                $stmtLog->bindParam(':order_id', $orderId);
                $stmtLog->execute();
            }
        }
    }

    // Ensure unmatched_payments table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS unmatched_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255),
        email VARCHAR(255),
        amount DECIMAL(10,2),
        currency VARCHAR(10),
        raw_payload JSON,
        status ENUM('pending', 'resolved', 'ignored') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    if (empty($orderId)) {
        // Insert into unmatched_payments
        $query = "INSERT INTO unmatched_payments (customer_name, email, raw_payload) VALUES (:name, :email, :payload)";
        $stmt = $conn->prepare($query);
        $payloadJson = json_encode($input);
        $stmt->bindParam(':name', $customerName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':payload', $payloadJson);
        $stmt->execute();

        // Log to webhook_logs as well for debugging
        $queryLog = "INSERT INTO webhook_logs (payload, error_message) VALUES (:payload, 'Unmatched Payment: Saved to review queue')";
        $stmtLog = $conn->prepare($queryLog);
        $stmtLog->bindParam(':payload', $payloadJson);
        $stmtLog->execute();

        // Return 200 so Ruul/Script doesn't retry endlessly
        echo json_encode(['success' => true, 'message' => 'Payment received but unmatched. Saved to queue.']);
        exit;
    }

    // Update payment flag
    $query = "UPDATE orders SET payment_status = 'paid', updated_at = NOW() WHERE order_id = :order_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':order_id', $orderId);
    $stmt->execute();

    // Get order details for domain info
    $queryOrder = "SELECT domain_name FROM orders WHERE order_id = :order_id";
    $stmtOrder = $conn->prepare($queryOrder);
    $stmtOrder->bindParam(':order_id', $orderId);
    $stmtOrder->execute();
    $orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);
    $domainName = $orderData['domain_name'] ?? $input['domain_name'] ?? null;

    // Update progress status + history
    $orderService->updateOrderStatus($orderId, 'payment_received', 'Ruul.io webhook bildirimi ile ödeme doğrulandı.', 'ruul-webhook');

    // Log payment received
    $queryLog = "INSERT INTO order_logs (order_id, log_type, message, details) VALUES (:order_id, 'success', 'Ödeme alındı (Webhook)', :details)";
    $stmtLog = $conn->prepare($queryLog);
    $logDetails = json_encode(['domain' => $domainName, 'source' => 'google_apps_script']);
    $stmtLog->bindParam(':order_id', $orderId);
    $stmtLog->bindParam(':details', $logDetails);
    $stmtLog->execute();

    // Prepare for website deployment (log for now, can be extended with Hostinger API)
    // Prepare for website deployment
    if ($domainName) {
        try {
            require_once __DIR__ . '/../../services/DeploymentService.php';
            $deploymentService = new \App\Services\DeploymentService();

            // Create deployment job
            $jobId = $deploymentService->createDeploymentJob($orderId);

            // Update order with job ID
            $queryJob = "UPDATE orders SET deployment_job_id = :job_id WHERE order_id = :order_id";
            $stmtJob = $conn->prepare($queryJob);
            $stmtJob->execute([':job_id' => $jobId, ':order_id' => $orderId]);

            $deploymentLog = "INSERT INTO order_logs (order_id, log_type, message, details) VALUES (:order_id, 'info', 'Otomatik website kurulumu başlatıldı', :details)";
            $stmtDeploy = $conn->prepare($deploymentLog);
            $deployDetails = json_encode([
                'domain' => $domainName,
                'job_id' => $jobId,
                'status' => 'queued'
            ]);
            $stmtDeploy->bindParam(':order_id', $orderId);
            $stmtDeploy->bindParam(':details', $deployDetails);
            $stmtDeploy->execute();

        } catch (Exception $e) {
            // Log failure but don't fail the webhook response
            $errorLog = "INSERT INTO order_logs (order_id, log_type, message, details) VALUES (:order_id, 'error', 'Otomatik kurulum başlatılamadı', :details)";
            $stmtError = $conn->prepare($errorLog);
            $errorDetails = json_encode(['error' => $e->getMessage()]);
            $stmtError->bindParam(':order_id', $orderId);
            $stmtError->bindParam(':details', $errorDetails);
            $stmtError->execute();
        }
    }

    echo json_encode(['success' => true, 'domain' => $domainName]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
