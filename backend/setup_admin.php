<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env if exists (for local), otherwise rely on environment or config defaults
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

try {
    $database = new App\Config\Database();
    $conn = $database->getConnection();

    echo "Checking admin_users table...\n";

    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    // Insert default admin
    // Password: admin123
    $passHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    $stmt = $conn->prepare("INSERT IGNORE INTO admin_users (username, email, password_hash) VALUES ('admin', 'admin@example.com', :pass)");
    $stmt->bindParam(':pass', $passHash);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Admin user created.\n";
    } else {
        echo "Admin user already exists.\n";
    }

    echo "Setup complete.\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
