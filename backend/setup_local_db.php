<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$db = $_ENV['DB_NAME'] ?? 'website_builder';

try {
    echo "Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating database '$db' if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    $pdo->exec("USE `$db`");

    echo "Importing schema...\n";
    $sql = file_get_contents(__DIR__ . '/database.sql');

    // Split by semicolon to execute multiple statements
    // This is a naive split, but works for our simple schema
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }

    echo "Database setup completed successfully!\n";
    echo "Admin user: admin / admin123\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
