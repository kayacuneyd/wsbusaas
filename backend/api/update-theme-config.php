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
    $logoPath = null;
    if (!empty($body['logo_base64'])) {
        $logoPath = saveLogo($orderId, $body['logo_base64']);
    }
    Order::updateThemeConfig(db(), $orderId, [
        'primary_color' => $body['primary_color'] ?? '#3b82f6',
        'secondary_color' => $body['secondary_color'] ?? '#f97316',
        'font' => $body['font'] ?? 'Inter',
        'logo_path' => $logoPath,
        'sections' => $body['sections'] ?? [],
    ]);
    jsonResponse(['ok' => true]);
} catch (Throwable $e) {
    logMessage('ERROR', 'update-theme-config failed', ['error' => $e->getMessage()]);
    jsonResponse(['error' => $e->getMessage()], 400);
}

function saveLogo(int $orderId, string $data): string
{
    global $config;
    if (!preg_match('#^data:image/(png|jpeg);base64,#', $data, $m)) {
        throw new InvalidArgumentException('Invalid logo encoding');
    }
    $raw = base64_decode(preg_replace('#^data:image/[^;]+;base64,#', '', $data));
    if ($raw === false || strlen($raw) > 1024 * 1024) {
        throw new InvalidArgumentException('Logo too large or invalid');
    }
    $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
    $path = $config['paths']['uploads'] . "/order_{$orderId}_logo.{$ext}";
    file_put_contents($path, $raw);
    return $path;
}
