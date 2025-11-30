<?php
// Admin endpoint to manually trigger deployment

// Robust CORS handling - MUST BE FIRST
require_once __DIR__ . '/../cors.php';
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/DeploymentService.php';

// TODO: Add Admin Authentication Check Here
// For MVP, we assume this endpoint is protected by .htaccess or similar, 
// but ideally we should check for a valid admin session token.

$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['order_id'] ?? '';

if (empty($orderId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Order ID is required']);
    exit;
}

try {
    $deploymentService = new \App\Services\DeploymentService();

    // Create deployment job (this will throw if job already exists)
    $jobId = $deploymentService->createDeploymentJob($orderId);

    // Trigger the worker immediately for this job (optional, or just let cron pick it up)
    // For manual trigger, user expects immediate feedback, so let's try to process one step
    $deploymentService->processQueue();

    echo json_encode([
        'success' => true,
        'message' => 'Deployment started successfully',
        'job_id' => $jobId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
