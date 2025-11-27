<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

return [
    'app_url' => $_ENV['APP_URL'] ?? 'https://bezmidar.de/api',
    'frontend_url' => $_ENV['FRONTEND_URL'] ?? 'https://bezmidar.de',
    'webhook_secret' => $_ENV['WEBHOOK_SECRET'] ?? 'your-secret-key',
    'email' => [
        'host' => $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com',
        'port' => $_ENV['SMTP_PORT'] ?? 587,
        'username' => $_ENV['SMTP_USER'] ?? '',
        'password' => $_ENV['SMTP_PASS'] ?? '',
        'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@example.com',
        'from_name' => $_ENV['FROM_NAME'] ?? 'Website Builder'
    ]
];
