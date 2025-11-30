<?php
/**
 * Comprehensive CORS Diagnostics Tool
 * Tests all CORS configurations and identifies issues
 */

// Allow all origins for diagnostic purposes
header("Access-Control-Allow-Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Test-Header');
header('Access-Control-Max-Age: 86400');
header('Vary: Origin');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

// Collect all CORS-related information
$diagnostics = [
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'test_type' => $_GET['test'] ?? 'basic',

    'request_info' => [
        'method' => $_SERVER['REQUEST_METHOD'],
        'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'not-provided',
        'referer' => $_SERVER['HTTP_REFERER'] ?? 'not-provided',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'not-provided',
        'host' => $_SERVER['HTTP_HOST'] ?? 'not-provided',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'not-provided',
    ],

    'cors_headers_sent' => [
        'Access-Control-Allow-Origin' => $_SERVER['HTTP_ORIGIN'] ?? '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-Test-Header',
        'Access-Control-Max-Age' => '86400',
        'Vary' => 'Origin'
    ],

    'request_headers' => [],
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'unknown',
    ],

    'allowed_origins' => [
        'https://bezmidar.de',
        'https://www.bezmidar.de',
        'http://localhost:5173',
        'http://localhost:4173'
    ],
];

// Capture all request headers
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        $headerName = str_replace('_', '-', substr($key, 5));
        $diagnostics['request_headers'][$headerName] = $value;
    }
}

// Test specific scenarios
$testType = $_GET['test'] ?? 'basic';

switch ($testType) {
    case 'preflight':
        $diagnostics['test_details'] = [
            'description' => 'Preflight OPTIONS request test',
            'expected' => 'Should return 204 with CORS headers',
            'status' => 'This should not appear for OPTIONS requests'
        ];
        break;

    case 'credentials':
        $diagnostics['test_details'] = [
            'description' => 'Credentials test',
            'cookies_received' => $_COOKIE,
            'has_auth_header' => isset($_SERVER['HTTP_AUTHORIZATION']),
            'auth_header' => $_SERVER['HTTP_AUTHORIZATION'] ?? null
        ];
        break;

    case 'custom-headers':
        $diagnostics['test_details'] = [
            'description' => 'Custom headers test',
            'custom_header_received' => $_SERVER['HTTP_X_TEST_HEADER'] ?? 'not-received',
            'all_custom_headers' => array_filter(
                $diagnostics['request_headers'],
                function($key) {
                    return strpos($key, 'X-') === 0;
                },
                ARRAY_FILTER_USE_KEY
            )
        ];
        break;

    case 'methods':
        $diagnostics['test_details'] = [
            'description' => 'HTTP methods test',
            'method_used' => $_SERVER['REQUEST_METHOD'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
            'method_allowed' => in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'])
        ];
        break;

    case 'origin-validation':
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = [
            'https://bezmidar.de',
            'https://www.bezmidar.de',
            'http://localhost:5173',
            'http://localhost:4173'
        ];
        $diagnostics['test_details'] = [
            'description' => 'Origin validation test',
            'origin_received' => $origin,
            'origin_allowed' => in_array($origin, $allowedOrigins),
            'allowed_origins' => $allowedOrigins,
            'origin_header_sent' => $origin ?: '*'
        ];
        break;

    case 'post-data':
        $postData = json_decode(file_get_contents('php://input'), true);
        $diagnostics['test_details'] = [
            'description' => 'POST data test',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not-provided',
            'post_data_received' => $postData,
            'raw_input_length' => strlen(file_get_contents('php://input'))
        ];
        break;

    default:
        $diagnostics['test_details'] = [
            'description' => 'Basic connectivity test',
            'message' => 'CORS is working correctly'
        ];
}

// Add endpoint scan results
$diagnostics['endpoint_scan'] = scanCorsEndpoints();

echo json_encode($diagnostics, JSON_PRETTY_PRINT);

/**
 * Scan all API endpoints for CORS configuration
 */
function scanCorsEndpoints() {
    $apiDir = __DIR__ . '/..';
    $endpoints = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($apiDir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $path = $file->getPathname();
            $relativePath = str_replace($apiDir, '', $path);

            // Skip test files
            if (strpos($relativePath, '/test/') !== false) {
                continue;
            }

            $content = file_get_contents($path);

            $hasCorsInclude = strpos($content, "require_once __DIR__ . '/cors.php") !== false ||
                             strpos($content, "require_once __DIR__ . '/../cors.php") !== false ||
                             strpos($content, "require_once __DIR__ . '/../../cors.php") !== false;

            $hasManualCors = preg_match('/header\s*\(\s*["\']Access-Control/i', $content);
            $hasOptionsHandler = strpos($content, "REQUEST_METHOD'] === 'OPTIONS") !== false;

            if ($hasCorsInclude || $hasManualCors) {
                $endpoints[] = [
                    'file' => $relativePath,
                    'has_cors_include' => $hasCorsInclude,
                    'has_manual_cors' => $hasManualCors,
                    'has_options_handler' => $hasOptionsHandler,
                    'issue' => ($hasCorsInclude && $hasManualCors) ? 'Duplicate CORS headers (include + manual)' : null
                ];
            }
        }
    }

    return $endpoints;
}
