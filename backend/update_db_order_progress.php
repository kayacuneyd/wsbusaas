<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$db = $_ENV['DB_NAME'] ?? 'website_builder';

$progressStatuses = [
    'pending_confirmation',
    'payment_received',
    'processing',
    'completed',
    'cancelled',
    'failed'
];

$defaultMessages = [
    'pending_confirmation' => "Your transaction is pending confirmation. We're waiting for it to be reflected in our system.",
    'payment_received' => 'Payment received and confirmed via Ruul.io.',
    'processing' => 'Order is being processed by the team.',
    'completed' => 'Order completed successfully.',
    'cancelled' => 'Order was cancelled.',
    'failed' => 'Order could not be completed.'
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Updating order progress schema...\n";

    $enumList = "'" . implode("','", $progressStatuses) . "'";

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id VARCHAR(50) NOT NULL,
        status ENUM($enumList) NOT NULL,
        note TEXT,
        changed_by VARCHAR(100) DEFAULT 'system',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    ensureColumn($pdo, 'orders', 'status_message', "ALTER TABLE orders ADD COLUMN status_message TEXT AFTER order_status");
    ensureColumn($pdo, 'orders', 'status_updated_at', "ALTER TABLE orders ADD COLUMN status_updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER status_message");
    ensureColumn($pdo, 'orders', 'status_updated_by', "ALTER TABLE orders ADD COLUMN status_updated_by VARCHAR(100) DEFAULT 'system' AFTER status_updated_at");

    $pdo->exec("ALTER TABLE orders MODIFY order_status ENUM($enumList) DEFAULT 'pending_confirmation'");

    $pdo->exec("UPDATE orders SET order_status = 'pending_confirmation' WHERE order_status = 'created'");
    $pdo->exec("UPDATE orders SET order_status = 'processing' WHERE order_status IN ('domain_purchased','hosting_setup','template_deployed')");

    $stmtOrders = $pdo->query("SELECT order_id, order_status, status_message, status_updated_at, status_updated_by, updated_at, created_at FROM orders");
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $order) {
        $status = $order['order_status'];

        if (empty($order['status_message'])) {
            $message = $defaultMessages[$status] ?? null;
            if ($message) {
                $updateStmt = $pdo->prepare("UPDATE orders SET status_message = :message WHERE order_id = :order_id");
                $updateStmt->execute([
                    ':message' => $message,
                    ':order_id' => $order['order_id']
                ]);
            }
        }

        if (empty($order['status_updated_at'])) {
            $timestamp = $order['updated_at'] ?? $order['created_at'];
            $updateStmt = $pdo->prepare("UPDATE orders SET status_updated_at = :timestamp WHERE order_id = :order_id");
            $updateStmt->execute([
                ':timestamp' => $timestamp,
                ':order_id' => $order['order_id']
            ]);
        }

        $historyCountStmt = $pdo->prepare("SELECT COUNT(*) as total FROM order_status_history WHERE order_id = :order_id");
        $historyCountStmt->execute([':order_id' => $order['order_id']]);
        $historyCount = (int) $historyCountStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($historyCount === 0) {
            $note = $order['status_message'] ?? ($defaultMessages[$status] ?? null);
            $createdAt = $order['status_updated_at'] ?? ($order['updated_at'] ?? $order['created_at']);

            $insertHistory = $pdo->prepare("INSERT INTO order_status_history (order_id, status, note, changed_by, created_at) VALUES (:order_id, :status, :note, :changed_by, :created_at)");
            $insertHistory->execute([
                ':order_id' => $order['order_id'],
                ':status' => $status,
                ':note' => $note,
                ':changed_by' => $order['status_updated_by'] ?? 'system',
                ':created_at' => $createdAt
            ]);
        }
    }

    echo "Order progress schema update completed.\n";
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}

function ensureColumn(PDO $pdo, string $table, string $column, string $alterSql): void
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE :column");
    $stmt->execute([':column' => $column]);
    if ($stmt->rowCount() === 0) {
        $pdo->exec($alterSql);
    }
}
