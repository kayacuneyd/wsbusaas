<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

requireAuth();
$body = json_decode(file_get_contents('php://input'), true) ?: [];
$orderId = (int)($body['order_id'] ?? 0);

try {
    if (!$orderId) {
        throw new InvalidArgumentException('order_id required');
    }
    Deploy::run(db(), $orderId, $config['paths']);
    jsonResponse(['status' => 'live']);
} catch (Throwable $e) {
    logMessage('ERROR', 'deploy-site failed', ['error' => $e->getMessage()]);
    jsonResponse(['error' => $e->getMessage()], 400);
}
