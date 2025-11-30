<?php
// backend/api/test_db.php

// Enable error reporting for this test script
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load configuration (which loads .env)
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');

$response = [
    'status' => 'unknown',
    'env_vars_loaded' => false,
    'db_connection' => 'pending',
    'error' => null
];

// Check if ENV vars are loaded
if (!empty($_ENV['DB_HOST'])) {
    $response['env_vars_loaded'] = true;
    $response['db_host_configured'] = $_ENV['DB_HOST']; // Safe to show host
    // Do NOT show password
} else {
    $response['env_vars_loaded'] = false;
    $response['error'] = 'Environment variables not loaded. DB_HOST is empty.';
    echo json_encode($response);
    exit;
}

// Test Connection
try {
    $database = new \App\Config\Database();
    $conn = $database->getConnection();

    if ($conn) {
        $response['db_connection'] = 'success';

        // Test Query
        $stmt = $conn->query("SELECT count(*) as count FROM admin_users");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['admin_users_count'] = $row['count'];

        $response['status'] = 'ok';
    } else {
        $response['db_connection'] = 'failed';
        $response['error'] = 'Database::getConnection() returned null. Check logs.';
    }
} catch (Exception $e) {
    $response['db_connection'] = 'exception';
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
