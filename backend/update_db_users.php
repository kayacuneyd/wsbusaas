<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Production Defaults (Hostinger)
$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'u553245641_websaas';
$pass = $_ENV['DB_PASS'] ?? 'Kayacuneyd1453!';
$db = $_ENV['DB_NAME'] ?? 'u553245641_websaas';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating users table...\n";

    $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlUsers);
    echo "Users table created.\n";

    echo "Updating orders table...\n";
    // Check if user_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'user_id'");
    if ($stmt->rowCount() == 0) {
        $sqlAlter = "ALTER TABLE orders ADD COLUMN user_id INT NULL AFTER id";
        $pdo->exec($sqlAlter);

        // Add Foreign Key
        $sqlFK = "ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
        $pdo->exec($sqlFK);
        echo "Orders table updated with user_id column.\n";
    } else {
        echo "Orders table already has user_id column.\n";
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
