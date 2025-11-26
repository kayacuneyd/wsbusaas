<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

requireAuth();
$body = json_decode(file_get_contents('php://input'), true) ?: [];

try {
    $domain = $body['domain'] ?? '';
    Domain::validate($domain);
    $orderId = Order::create(db(), [
        'email' => $body['email'],
        'theme_id' => $body['theme_id'],
        'amount' => (float)$body['amount'],
        'domain' => $domain,
        'primary_color' => $body['primary_color'] ?? '#3b82f6',
        'secondary_color' => $body['secondary_color'] ?? '#f97316',
        'font' => $body['font'] ?? 'Inter',
        'sections' => $body['sections'] ?? [],
    ]);
    jsonResponse(['order_id' => $orderId, 'status' => 'pending']);
} catch (Throwable $e) {
    logMessage('ERROR', 'create-order failed', ['error' => $e->getMessage()]);
    jsonResponse(['error' => $e->getMessage()], 400);
}
