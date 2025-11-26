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
    $order = Order::get(db(), $orderId);
    if (!$order) {
        throw new InvalidArgumentException('Order not found');
    }
    $link = Payment::initiate(db(), $orderId, (float)$order['total_amount']);
    jsonResponse(['payment_link' => $link]);
} catch (Throwable $e) {
    logMessage('ERROR', 'initiate-payment failed', ['error' => $e->getMessage()]);
    jsonResponse(['error' => $e->getMessage()], 400);
}
