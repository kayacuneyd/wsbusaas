<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? 'localhost' ?? '127.0.0.1';
$user = $_ENV['DB_USER'] ?? 'u553245641_websaas';
$pass = $_ENV['DB_PASS'] ?? 'Kayacuneyd1453!';
$db = $_ENV['DB_NAME'] ?? 'u553245641_websaas';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating settings table...\n";

    $sql = "CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(50) PRIMARY KEY,
        `value` TEXT,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);

    // Insert default payment URL if not exists
    $defaultUrl = "https://ruul.space/payment/cs_live_a1pcdSzZ9W0GJwTGV7ybtlRtwiyDxl7mIFaVsCGJ6NZH8Q642veiC4XEyA_secret_fidnandhYHdWcXxpYCc%2FJ2FgY2RwaXEnKSdwbEhqYWAnPydmcHZxamgneCUl?from=%2Fcuneytkaya%2Fproducts%2F13347";

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('payment_url', :value)");
    $stmt->bindParam(':value', $defaultUrl);
    $stmt->execute();

    echo "Settings table created and default value inserted.\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
