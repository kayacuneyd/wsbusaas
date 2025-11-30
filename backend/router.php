<?php
// router.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing files directly
if (file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// Handle API routes
if (strpos($uri, '/api/') === 0) {
    // Remove /api/ prefix and query string
    $path = substr($uri, 5);

    // Remove trailing slash
    $path = rtrim($path, '/');

    // Check if it maps to a PHP file (add .php extension if missing)
    if (substr($path, -4) !== '.php') {
        $file = __DIR__ . '/api/' . $path . '.php';
    } else {
        $file = __DIR__ . '/api/' . $path;
    }

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
                return true;
            }
        }
    }

    if (file_exists($file)) {
        require $file;
        return true;
    }
}

// Default 404
http_response_code(404);
echo json_encode(['error' => 'Not found']);
