<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain');

echo "Server Root Diagnosis\n";
echo "=====================\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "\nFile Checks:\n";
echo "api/seo.php: " . (file_exists(__DIR__ . '/api/seo.php') ? 'FOUND' : 'MISSING') . "\n";
echo "api/admin/orders.php: " . (file_exists(__DIR__ . '/api/admin/orders.php') ? 'FOUND' : 'MISSING') . "\n";
echo "config/config.php: " . (file_exists(__DIR__ . '/config/config.php') ? 'FOUND' : 'MISSING') . "\n";
