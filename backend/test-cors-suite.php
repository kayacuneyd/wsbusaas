#!/usr/bin/env php
<?php
/**
 * Automated CORS Test Suite
 * CLI tool for testing CORS configuration across all endpoints
 *
 * Usage: php test-cors-suite.php [--verbose] [--output=json|text]
 */

// Configuration
$config = [
    'base_url' => 'http://localhost:8000/api',
    'origins' => [
        'http://localhost:5173',
        'http://localhost:4173',
        'https://bezmidar.de',
        'https://www.bezmidar.de',
        'https://evil-site.com' // Should fail
    ],
    'verbose' => in_array('--verbose', $argv),
    'output' => 'text'
];

// Parse output format
foreach ($argv as $arg) {
    if (strpos($arg, '--output=') === 0) {
        $config['output'] = substr($arg, 9);
    }
}

class CorsTestSuite {
    private $config;
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Run all tests
     */
    public function runAll() {
        $this->log("🚀 Starting CORS Test Suite\n", 'info');
        $this->log("Base URL: {$this->config['base_url']}\n\n", 'info');

        // Test categories
        $this->testBasicConnectivity();
        $this->testPreflightRequests();
        $this->testOriginValidation();
        $this->testHttpMethods();
        $this->testCustomHeaders();
        $this->testCredentials();
        $this->testExistingEndpoints();

        $this->printSummary();
    }

    /**
     * Test 1: Basic connectivity
     */
    private function testBasicConnectivity() {
        $this->log("📡 Test Category: Basic Connectivity\n", 'category');

        $result = $this->makeRequest('/test/cors-diagnostics.php?test=basic', [
            'method' => 'GET',
            'origin' => $this->config['origins'][0]
        ]);

        $this->assertTest(
            'Basic GET request',
            $result['success'] &&
            isset($result['headers']['access-control-allow-origin']),
            $result
        );
    }

    /**
     * Test 2: Preflight requests
     */
    private function testPreflightRequests() {
        $this->log("\n🔍 Test Category: Preflight (OPTIONS) Requests\n", 'category');

        foreach ($this->config['origins'] as $origin) {
            $result = $this->makeRequest('/test/cors-diagnostics.php?test=preflight', [
                'method' => 'OPTIONS',
                'origin' => $origin,
                'headers' => [
                    'Access-Control-Request-Method: POST',
                    'Access-Control-Request-Headers: Content-Type, Authorization'
                ]
            ]);

            $this->assertTest(
                "OPTIONS request from {$origin}",
                $result['status'] === 204 || $result['status'] === 200,
                $result
            );
        }
    }

    /**
     * Test 3: Origin validation
     */
    private function testOriginValidation() {
        $this->log("\n🌐 Test Category: Origin Validation\n", 'category');

        foreach ($this->config['origins'] as $origin) {
            // Use real API endpoint instead of test endpoint for origin validation
            $result = $this->makeRequest('/packages', [
                'method' => 'GET',
                'origin' => $origin
            ]);

            $allowedOrigins = [
                'http://localhost:5173',
                'http://localhost:4173',
                'https://bezmidar.de',
                'https://www.bezmidar.de'
            ];

            $shouldBeAllowed = in_array($origin, $allowedOrigins);

            // Check if origin is allowed by verifying CORS header matches the origin or is empty (blocked)
            $corsHeader = $result['headers']['access-control-allow-origin'] ?? '';
            $isAllowed = !empty($corsHeader) && ($corsHeader === $origin || $corsHeader === '*');

            $this->assertTest(
                "Origin {$origin} " . ($shouldBeAllowed ? 'should be allowed' : 'should be blocked'),
                $shouldBeAllowed === $isAllowed,
                $result
            );
        }
    }

    /**
     * Test 4: HTTP methods
     */
    private function testHttpMethods() {
        $this->log("\n🔧 Test Category: HTTP Methods\n", 'category');

        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        $origin = $this->config['origins'][0];

        foreach ($methods as $method) {
            $result = $this->makeRequest('/test/cors-diagnostics.php?test=methods', [
                'method' => $method,
                'origin' => $origin
            ]);

            $this->assertTest(
                "{$method} request",
                $result['success'] && isset($result['headers']['access-control-allow-origin']),
                $result
            );
        }
    }

    /**
     * Test 5: Custom headers
     */
    private function testCustomHeaders() {
        $this->log("\n📋 Test Category: Custom Headers\n", 'category');

        $result = $this->makeRequest('/test/cors-diagnostics.php?test=custom-headers', [
            'method' => 'GET',
            'origin' => $this->config['origins'][0],
            'headers' => [
                'X-Test-Header: CustomValue',
                'X-Custom-Header: TestValue'
            ]
        ]);

        $this->assertTest(
            'Custom headers accepted',
            $result['success'] && isset($result['headers']['access-control-allow-origin']),
            $result
        );
    }

    /**
     * Test 6: Credentials
     */
    private function testCredentials() {
        $this->log("\n🔐 Test Category: Credentials\n", 'category');

        $result = $this->makeRequest('/test/cors-endpoints.php?endpoint=with-cookies', [
            'method' => 'GET',
            'origin' => $this->config['origins'][0],
            'credentials' => true
        ]);

        $this->assertTest(
            'Credentials included',
            $result['success'] &&
            isset($result['headers']['access-control-allow-credentials']) &&
            $result['headers']['access-control-allow-credentials'] === 'true',
            $result
        );
    }

    /**
     * Test 7: Existing endpoints
     */
    private function testExistingEndpoints() {
        $this->log("\n🎯 Test Category: Existing API Endpoints\n", 'category');

        $endpoints = [
            ['path' => '/packages', 'method' => 'GET'],
            ['path' => '/check-domain', 'method' => 'POST', 'body' => ['domain' => 'test', 'tld' => 'com']],
            ['path' => '/contact', 'method' => 'POST', 'body' => ['name' => 'Test', 'email' => 'test@test.com', 'message' => 'Test']]
        ];

        foreach ($endpoints as $endpoint) {
            $result = $this->makeRequest($endpoint['path'], [
                'method' => $endpoint['method'],
                'origin' => $this->config['origins'][0],
                'body' => $endpoint['body'] ?? null
            ]);

            $this->assertTest(
                "{$endpoint['method']} {$endpoint['path']}",
                isset($result['headers']['access-control-allow-origin']),
                $result
            );
        }
    }

    /**
     * Make HTTP request
     */
    private function makeRequest($path, $options = []) {
        $url = $this->config['base_url'] . $path;
        $method = $options['method'] ?? 'GET';
        $origin = $options['origin'] ?? '';
        $headers = $options['headers'] ?? [];
        $body = $options['body'] ?? null;

        // Build curl request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Set origin header
        if ($origin) {
            $headers[] = "Origin: {$origin}";
        }

        // Set content type for POST/PUT
        if ($body && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        // Set credentials
        if (!empty($options['credentials'])) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, '');
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Execute request
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);

        // Parse response
        $headerSize = $info['header_size'];
        $headerString = substr($response, 0, $headerSize);
        $bodyString = substr($response, $headerSize);

        // Parse headers
        $responseHeaders = [];
        foreach (explode("\r\n", $headerString) as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $responseHeaders[strtolower(trim($key))] = trim($value);
            }
        }

        $bodyData = json_decode($bodyString, true);

        return [
            'success' => !$error && $info['http_code'] < 400,
            'status' => $info['http_code'],
            'headers' => $responseHeaders,
            'body' => $bodyData,
            'error' => $error
        ];
    }

    /**
     * Assert test result
     */
    private function assertTest($name, $condition, $details = []) {
        $this->totalTests++;

        if ($condition) {
            $this->passedTests++;
            $this->log("  ✓ {$name}", 'pass');
        } else {
            $this->failedTests++;
            $this->log("  ✗ {$name}", 'fail');

            if ($this->config['verbose']) {
                $this->log("    Details: " . json_encode($details, JSON_PRETTY_PRINT), 'detail');
            }
        }

        $this->results[] = [
            'test' => $name,
            'passed' => $condition,
            'details' => $details
        ];

        $this->log("\n", 'info');
    }

    /**
     * Print summary
     */
    private function printSummary() {
        if ($this->config['output'] === 'json') {
            echo json_encode([
                'total' => $this->totalTests,
                'passed' => $this->passedTests,
                'failed' => $this->failedTests,
                'success_rate' => ($this->passedTests / $this->totalTests) * 100,
                'results' => $this->results
            ], JSON_PRETTY_PRINT);
            return;
        }

        $this->log("\n" . str_repeat('=', 60) . "\n", 'info');
        $this->log("📊 Test Summary\n", 'category');
        $this->log(str_repeat('=', 60) . "\n\n", 'info');

        $this->log("Total Tests:  {$this->totalTests}\n", 'info');
        $this->log("Passed:       {$this->passedTests}", 'pass');
        $this->log("\n");
        $this->log("Failed:       {$this->failedTests}", 'fail');
        $this->log("\n");

        $successRate = ($this->passedTests / $this->totalTests) * 100;
        $this->log("Success Rate: " . number_format($successRate, 1) . "%\n", 'info');

        if ($this->failedTests === 0) {
            $this->log("\n🎉 All tests passed!\n", 'pass');
        } else {
            $this->log("\n⚠️  Some tests failed. Run with --verbose for details.\n", 'fail');
        }
    }

    /**
     * Log message with color
     */
    private function log($message, $type = 'info') {
        $colors = [
            'info' => "\033[0m",
            'pass' => "\033[32m",
            'fail' => "\033[31m",
            'category' => "\033[1;34m",
            'detail' => "\033[33m"
        ];

        $reset = "\033[0m";
        $color = $colors[$type] ?? $colors['info'];

        echo $color . $message . $reset;
    }
}

// Run tests
$suite = new CorsTestSuite($config);
$suite->runAll();
