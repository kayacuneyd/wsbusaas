<?php
/**
 * Centralized CORS Configuration - PRODUCTION VERSION
 * Include this file at the top of all API endpoints
 *
 * IMPORTANT: This is the production version with only production origins.
 * For development, use cors.php instead.
 */

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// PRODUCTION Allowed origins - NO localhost!
$allowedOrigins = [
    'https://bezmidar.de',
    'https://www.bezmidar.de'
];

// Set CORS headers only for allowed origins
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
} elseif (empty($origin)) {
    // Allow requests without Origin header (e.g., same-origin, Postman, curl)
    header("Access-Control-Allow-Origin: *");
}
// If origin is not in allowed list and exists, don't set CORS headers (block the request)

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}
