<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$raw = file_get_contents('php://input');
appsScriptAuth();
verifySignature($raw);
$body = json_decode($raw, true) ?: [];
$orderId = (int)($body['order_id'] ?? 0);

try {
    if (!$orderId) {
        throw new InvalidArgumentException('order_id required');
    }
    Payment::markPaid(db(), $orderId, $body);
    Deploy::run(db(), $orderId, $config['paths']);
    jsonResponse(['ok' => true]);
} catch (Throwable $e) {
    logMessage('ERROR', 'ruul-webhook failed', ['error' => $e->getMessage(), 'body' => $body]);
    jsonResponse(['error' => $e->getMessage()], 400);
}
