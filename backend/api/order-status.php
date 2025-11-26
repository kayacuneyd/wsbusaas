<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

requireAuth();
$orderId = (int)($_GET['order_id'] ?? 0);

try {
    if (!$orderId) {
        throw new InvalidArgumentException('order_id required');
    }
    $order = Order::get(db(), $orderId);
    if (!$order) {
        throw new InvalidArgumentException('Order not found');
    }
    jsonResponse([
        'status' => $order['status'],
        'domain' => $order['domain'],
        'payment_status' => $order['status'] === 'paid' || $order['status'] === 'live' ? 'paid' : 'pending',
    ]);
} catch (Throwable $e) {
    logMessage('ERROR', 'order-status failed', ['error' => $e->getMessage()]);
    jsonResponse(['error' => $e->getMessage()], 400);
}
