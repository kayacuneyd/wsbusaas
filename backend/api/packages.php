<?php
// Set headers immediately to avoid CORS issues on error
header('Content-Type: application/json');
$allowedOrigin = $_SERVER['HTTP_ORIGIN'] ?? '*';
// Allow any origin for now to debug, or restrict to specific domains
header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/Database.php';

    $database = new App\Config\Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Ensure packages table exists (noop if already created)
    $conn->exec("CREATE TABLE IF NOT EXISTS packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        price DECIMAL(10,2),
        payment_link TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        display_order INT DEFAULT 0,
        category VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $query = "SELECT id, name, slug, description, price, category, payment_link, is_active, display_order 
              FROM packages 
              WHERE is_active = 1 
              ORDER BY display_order ASC, id ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'packages' => $packages]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
