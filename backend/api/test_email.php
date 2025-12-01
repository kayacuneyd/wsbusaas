<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../services/EmailService.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$to = $_GET['to'] ?? null;

if (!$to) {
    echo json_encode(['success' => false, 'error' => 'Email address required (param: to)']);
    exit;
}

try {
    $emailService = new \App\Services\EmailService();

    // Use the new method we just added
    $result = $emailService->sendOrderProcessingEmail(
        $to,
        'Test User',
        'test-domain.com',
        'Starter Package',
        'TEST-12345'
    );

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Email sent successfully to ' . $to]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send email']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
