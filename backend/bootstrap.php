<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    global $config;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = new PDO(
        $config['db']['dsn'],
        $config['db']['user'],
        $config['db']['pass'],
        $config['db']['options']
    );
    return $pdo;
}

function jsonResponse($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireAuth(): void
{
    global $config;
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (stripos($header, 'Bearer ') !== 0) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    $token = substr($header, 7);
    if ($token !== $config['auth']['api_token']) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

function verifySignature(string $rawBody): void
{
    global $config;
    $provided = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
    $expected = hash_hmac('sha256', $rawBody, $config['auth']['hmac_secret']);
    if (!hash_equals($expected, $provided)) {
        jsonResponse(['error' => 'Invalid signature'], 401);
    }
}

function appsScriptAuth(): void
{
    global $config;
    $secret = $_SERVER['HTTP_X_APPS_SCRIPT_TOKEN'] ?? '';
    if (!hash_equals($config['auth']['apps_script_secret'], $secret)) {
        jsonResponse(['error' => 'Forbidden'], 403);
    }
}

function logMessage(string $level, string $message, array $context = []): void
{
    global $config;
    $line = sprintf(
        "[%s] %s %s %s\n",
        date('c'),
        strtoupper($level),
        $message,
        $context ? json_encode($context) : ''
    );
    file_put_contents($config['paths']['logs'] . '/app.log', $line, FILE_APPEND);
}

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/functions/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
