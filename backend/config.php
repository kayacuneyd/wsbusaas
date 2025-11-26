<?php

return [
    // Database connection
    'db' => [
        'dsn' => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=saas;charset=utf8mb4',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
    // Shared secrets
    'auth' => [
        'api_token' => getenv('API_TOKEN') ?: 'change-me-api-token',
        'apps_script_secret' => getenv('APPS_SCRIPT_SECRET') ?: 'change-me-apps-script-secret',
        'hmac_secret' => getenv('HMAC_SECRET') ?: 'change-me-hmac',
    ],
    // Paths
    'paths' => [
        'templates' => __DIR__ . '/templates',
        'sites' => __DIR__ . '/sites',
        'uploads' => __DIR__ . '/uploads',
        'logs' => __DIR__ . '/logs',
    ],
    // Mail settings (adjust to your SMTP)
    'mail' => [
        'from' => 'noreply@example.com',
        'transport' => 'log', // log | smtp
        'smtp_host' => '',
        'smtp_user' => '',
        'smtp_pass' => '',
    ],
];
