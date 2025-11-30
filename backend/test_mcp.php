<?php
require_once __DIR__ . '/config/config.php';

// MCP Server URL from config
$mcpUrl = $_ENV['MCP_SERVER_URL'] ?? 'https://www.bezmidar.de/api/mcp';

echo "Testing connection to MCP Server at: $mcpUrl\n";

$payload = json_encode([
    'tool' => 'check_domain_availability',
    'params' => [
        'domain' => 'test-connection-' . time() . '.com'
    ]
]);

$ch = curl_init($mcpUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

echo "HTTP Status Code: $httpCode\n";
echo "Response:\n$response\n";

if ($httpCode === 200) {
    $json = json_decode($response, true);
    if ($json && isset($json['success']) && $json['success'] === true) {
        echo "✅ SUCCESS! Backend successfully talked to Vercel MCP.\n";
    } else {
        echo "⚠️ Connected, but MCP returned error.\n";
    }
} else {
    echo "❌ FAILED. HTTP Code $httpCode indicates a problem.\n";
}
