<?php
// backend/update_db_schema.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

header('Content-Type: application/json');

try {
    $database = new \App\Config\Database();
    $conn = $database->getConnection();

    $log = [];

    // 1. Update 'orders' table
    $stmt = $conn->query("SHOW COLUMNS FROM orders LIKE 'status_message'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE orders 
                ADD COLUMN status_message TEXT AFTER order_status,
                ADD COLUMN status_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status_message,
                ADD COLUMN status_updated_by VARCHAR(100) DEFAULT 'system' AFTER status_updated_at";
        $conn->exec($sql);
        $log[] = "Added columns to 'orders' table.";
    } else {
        $log[] = "'orders' table already has new columns.";
    }

    // 2. Update ENUM for order_status
    // Note: Changing ENUMs can be tricky, using a broad ALTER to be safe
    $sql = "ALTER TABLE orders MODIFY COLUMN order_status 
            ENUM('pending_confirmation', 'payment_received', 'processing', 'completed', 'cancelled', 'failed') 
            DEFAULT 'pending_confirmation'";
    $conn->exec($sql);
    $log[] = "Updated order_status ENUM.";

    // 3. Create 'order_status_history' table
    $sql = "CREATE TABLE IF NOT EXISTS order_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id VARCHAR(50) NOT NULL,
        status ENUM('pending_confirmation', 'payment_received', 'processing', 'completed', 'cancelled', 'failed') NOT NULL,
        note TEXT,
        changed_by VARCHAR(100) DEFAULT 'system',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    $log[] = "Ensured 'order_status_history' table exists.";

    echo json_encode(['success' => true, 'log' => $log]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
