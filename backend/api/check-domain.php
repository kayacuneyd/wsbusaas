<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/WhoisService.php';

use App\Services\WhoisService;

header('Content-Type: application/json');
$allowedOrigin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$domain = $input['domain'] ?? '';
$tld = $input['tld'] ?? 'de';

if (empty($domain)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Domain is required']);
    exit;
}

// Basic validation
if (!preg_match('/^[a-z0-9-]+$/', $domain)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid domain format']);
    exit;
}

$fullDomain = $domain . '.' . $tld;
$whois = new WhoisService();
$isAvailable = false;
$error = null;

try {
    $isAvailable = $whois->isAvailable($domain, $tld);
} catch (Exception $e) {
    // Fallback or error handling
    $error = $e->getMessage();
    // For MVP, if WHOIS fails, we might want to fail open or closed. 
    // Let's fail closed (saying not available) but log error.
    error_log("WHOIS Error: " . $e->getMessage());
}

$response = [
    'success' => true,
    'available' => $isAvailable,
    'domain' => $fullDomain,
    'message' => $isAvailable ? 'Bu domain müsait!' : 'Bu domain zaten alınmış.'
];

if (!$isAvailable) {
    $response['suggestions'] = [
        $domain . '123.' . $tld,
        'my-' . $domain . '.' . $tld,
        $domain . '.net'
    ];
}

echo json_encode($response);
