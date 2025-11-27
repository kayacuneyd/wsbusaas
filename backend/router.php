<?php
// router.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing files directly
if (file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// Handle API routes
if (strpos($uri, '/api/') === 0) {
    // Remove /api/ prefix
    $path = substr($uri, 5);

    // Check if it maps to a PHP file
    $file = __DIR__ . '/api/' . $path . '.php';

    // Handle parameterized routes like /api/orders/WB123
    if (!file_exists($file)) {
        // Try to match /api/orders/WB123 -> api/orders.php with route param
        $parts = explode('/', $path);
        if (count($parts) > 1) {
            $resource = $parts[0]; // orders
            $file = __DIR__ . '/api/' . $resource . '.php';
            if (file_exists($file)) {
                // Pass the full route to the script via $_GET['route'] to mimic .htaccess
                $_GET['route'] = $path;
                require $file;
                return;
            }
        }
    } else {
        require $file;
        return;
    }
}

// Default 404
http_response_code(404);
echo json_encode(['error' => 'Not found']);
