<?php
/**
 * CORS Test Endpoints
 * Multiple endpoints to test different CORS scenarios
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';

use App\Services\JwtService;

// Set CORS headers
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Vary: Origin');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

$endpoint = $_GET['endpoint'] ?? 'simple';
$method = $_SERVER['REQUEST_METHOD'];

$response = [
    'success' => true,
    'endpoint' => $endpoint,
    'method' => $method,
    'timestamp' => date('Y-m-d H:i:s')
];

switch ($endpoint) {
    case 'simple':
        // Simple GET request
        $response['message'] = 'Simple CORS request successful';
        break;

    case 'with-auth':
        // Test with Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            http_response_code(401);
            $response['success'] = false;
            $response['error'] = 'Authorization header missing';
        } else {
            try {
                $token = str_replace('Bearer ', '', $authHeader);
                $payload = JwtService::verify($token);
                $response['message'] = 'Authenticated request successful';
                $response['user'] = $payload;
            } catch (Exception $e) {
                http_response_code(401);
                $response['success'] = false;
                $response['error'] = 'Invalid token: ' . $e->getMessage();
            }
        }
        break;

    case 'with-cookies':
        // Test with cookies
        setcookie('test_cookie', 'test_value', [
            'expires' => time() + 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ]);

        $response['message'] = 'Cookie set successfully';
        $response['cookies_received'] = $_COOKIE;
        break;

    case 'post-json':
        // Test POST with JSON
        if ($method !== 'POST') {
            http_response_code(405);
            $response['success'] = false;
            $response['error'] = 'Method not allowed. Use POST.';
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            $response['message'] = 'JSON data received';
            $response['data_received'] = $data;
        }
        break;

    case 'database':
        // Test database connection with CORS
        try {
            $database = new App\Config\Database();
            $conn = $database->getConnection();

            if (!$conn) {
                throw new Exception('Database connection failed');
            }

            $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['message'] = 'Database query successful';
            $response['user_count'] = $result['count'];
        } catch (Exception $e) {
            http_response_code(500);
            $response['success'] = false;
            $response['error'] = 'Database error: ' . $e->getMessage();
        }
        break;

    case 'slow':
        // Test with artificial delay
        sleep(2);
        $response['message'] = 'Slow request completed after 2 seconds';
        break;

    case 'error':
        // Test error handling with CORS
        http_response_code(500);
        $response['success'] = false;
        $response['error'] = 'Intentional error for testing';
        break;

    case 'redirect':
        // Test redirect with CORS
        header('Location: /api/test/cors-endpoints.php?endpoint=simple');
        exit(0);

    case 'large-payload':
        // Test with large response
        $response['message'] = 'Large payload test';
        $response['large_data'] = array_fill(0, 1000, [
            'id' => rand(1, 1000),
            'name' => 'Test Item',
            'description' => str_repeat('Lorem ipsum dolor sit amet. ', 10)
        ]);
        break;

    case 'custom-headers':
        // Test custom response headers
        header('X-Custom-Header: CustomValue');
        header('X-Test-Header: TestValue');
        header('Access-Control-Expose-Headers: X-Custom-Header, X-Test-Header');

        $response['message'] = 'Custom headers sent';
        $response['custom_headers'] = [
            'X-Custom-Header' => 'CustomValue',
            'X-Test-Header' => 'TestValue'
        ];
        break;

    default:
        http_response_code(404);
        $response['success'] = false;
        $response['error'] = 'Unknown endpoint';
        $response['available_endpoints'] = [
            'simple', 'with-auth', 'with-cookies', 'post-json',
            'database', 'slow', 'error', 'redirect', 'large-payload', 'custom-headers'
        ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
