<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';
require_once __DIR__ . '/../../services/OrderService.php';

use App\Services\JwtService;
use App\Services\OrderService;

header('Content-Type: application/json');

function respondJsonError($message, int $code = 500): void
{
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
}

set_exception_handler(function ($e) {
    error_log('packages fatal exception: ' . $e->getMessage());
    respondJsonError('Server error: ' . $e->getMessage(), 500);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('packages fatal error: ' . $error['message']);
        respondJsonError('Server error: ' . $error['message'], 500);
    }
});

// Auth Check
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$decoded = null;

if (!empty($authHeader)) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        $token = $authHeader;
    }
    $decoded = JwtService::decode($token);
}

if (!$decoded || ($decoded['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$database = new App\Config\Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Ensure packages table exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        price DECIMAL(10,2),
        payment_link TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    error_log('packages table create error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

function normalizePackageInput(array $input): array
{
    $clean = [];
    $clean['name'] = $input['name'] ?? null;
    $clean['slug'] = $input['slug'] ?? null;
    $clean['description'] = $input['description'] ?? null;
    $clean['category'] = $input['category'] ?? 'general';

    // Normalize price: allow null or numeric
    $price = $input['price'] ?? null;
    if ($price === '' || $price === null) {
        $clean['price'] = null;
    } elseif (is_numeric($price)) {
        $clean['price'] = (float) $price;
    } else {
        $clean['price'] = null;
    }

    $paymentLink = $input['payment_link'] ?? null;
    $clean['payment_link'] = $paymentLink === '' ? null : $paymentLink;

    $clean['is_active'] = isset($input['is_active']) ? (bool) $input['is_active'] : true;
    $clean['display_order'] = isset($input['display_order']) ? (int) $input['display_order'] : 0;

    return $clean;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM packages ORDER BY display_order ASC, id ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'packages' => $packages]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        respondJsonError('Invalid JSON payload', 400);
        exit;
    }
    $input = normalizePackageInput($input);

    if (empty($input['name']) || empty($input['slug'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Name and slug are required']);
        exit;
    }

    try {
        $query = "INSERT INTO packages (name, slug, description, price, category, payment_link, is_active, display_order) 
                  VALUES (:name, :slug, :description, :price, :category, :payment_link, :is_active, :display_order)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':name', $input['name']);
        $stmt->bindValue(':slug', $input['slug']);
        $stmt->bindValue(':description', $input['description'] ?? null);
        $stmt->bindValue(':price', $input['price'], $input['price'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':category', $input['category']);
        $stmt->bindValue(':payment_link', $input['payment_link'], $input['payment_link'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':is_active', $input['is_active'], PDO::PARAM_BOOL);
        $stmt->bindValue(':display_order', $input['display_order'], PDO::PARAM_INT);
        $stmt->execute();

        $packageId = $conn->lastInsertId();
        echo json_encode(['success' => true, 'package_id' => $packageId]);
    } catch (Exception $e) {
        error_log('packages POST error: ' . $e->getMessage());
        respondJsonError($e->getMessage(), 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $rawInput = json_decode(file_get_contents('php://input'), true);
    if ($rawInput === null) {
        respondJsonError('Invalid JSON payload', 400);
        exit;
    }
    $id = $rawInput['id'] ?? null;
    $input = normalizePackageInput($rawInput);

    if (!$id) {
        respondJsonError('Package ID is required', 400);
        exit;
    }

    try {
        $query = "UPDATE packages SET 
                  name = :name, 
                  slug = :slug, 
                  description = :description, 
                  price = :price, 
                  category = :category,
                  payment_link = :payment_link, 
                  is_active = :is_active, 
                  display_order = :display_order
                  WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $input['name']);
        $stmt->bindValue(':slug', $input['slug']);
        $stmt->bindValue(':description', $input['description'] ?? null);
        $stmt->bindValue(':price', $input['price'], $input['price'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':category', $input['category']);
        $stmt->bindValue(':payment_link', $input['payment_link'], $input['payment_link'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':is_active', $input['is_active'], PDO::PARAM_BOOL);
        $stmt->bindValue(':display_order', $input['display_order'], PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log('packages PUT error: ' . $e->getMessage());
        respondJsonError($e->getMessage(), 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = $input['id'] ?? $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Package ID is required']);
        exit;
    }

    try {
        $query = "DELETE FROM packages WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
