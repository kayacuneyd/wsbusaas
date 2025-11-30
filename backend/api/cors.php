<?php
// Centralized CORS Handling
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'https://bezmidar.de',
    'https://www.bezmidar.de',
    'http://localhost:5173',
    'http://localhost:4173'
];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Fallback for testing/development or if origin is missing
    header("Access-Control-Allow-Origin: *");
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
