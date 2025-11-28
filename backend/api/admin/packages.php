<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/JwtService.php';

use App\Services\JwtService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

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

// Ensure packages table exists
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM packages ORDER BY display_order ASC, id ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'packages' => $packages]);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['name']) || empty($input['slug'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Name and slug are required']);
        exit;
    }
    
    try {
        $query = "INSERT INTO packages (name, slug, description, price, payment_link, is_active, display_order) 
                  VALUES (:name, :slug, :description, :price, :payment_link, :is_active, :display_order)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $input['name']);
        $stmt->bindParam(':slug', $input['slug']);
        $stmt->bindParam(':description', $input['description'] ?? null);
        $stmt->bindParam(':price', $input['price'] ?? null);
        $stmt->bindParam(':payment_link', $input['payment_link'] ?? null);
        $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : true;
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':display_order', $input['display_order'] ?? 0);
        $stmt->execute();
        
        $packageId = $conn->lastInsertId();
        echo json_encode(['success' => true, 'package_id' => $packageId]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Package ID is required']);
        exit;
    }
    
    try {
        $query = "UPDATE packages SET 
                  name = :name, 
                  slug = :slug, 
                  description = :description, 
                  price = :price, 
                  payment_link = :payment_link, 
                  is_active = :is_active, 
                  display_order = :display_order
                  WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $input['name']);
        $stmt->bindParam(':slug', $input['slug']);
        $stmt->bindParam(':description', $input['description'] ?? null);
        $stmt->bindParam(':price', $input['price'] ?? null);
        $stmt->bindParam(':payment_link', $input['payment_link'] ?? null);
        $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : true;
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':display_order', $input['display_order'] ?? 0);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
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

