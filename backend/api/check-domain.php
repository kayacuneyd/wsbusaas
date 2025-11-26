<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

requireAuth();
$body = json_decode(file_get_contents('php://input'), true) ?: [];
try {
    $domain = $body['domain'] ?? '';
    $result = Domain::check($domain);
    jsonResponse($result);
} catch (Throwable $e) {
    logMessage('ERROR', 'check-domain failed', ['error' => $e->getMessage()]);
    jsonResponse(['error' => $e->getMessage()], 400);
}
