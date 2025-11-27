<?php
namespace App\Services;

use App\Config\Database;
use PDO;

class OrderService
{
    public $conn;
    private $table = 'orders';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createOrder($data)
    {
        $orderId = 'WB' . date('YmdHis') . rand(100, 999);

        $query = "INSERT INTO " . $this->table . " 
                  (order_id, user_id, customer_email, customer_name, domain_name, package_type, order_status) 
                  VALUES (:order_id, :user_id, :email, :name, :domain, :package, 'created')";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':email', $data['customer_email']);
        $stmt->bindParam(':name', $data['customer_name']);
        $stmt->bindParam(':domain', $data['domain_name']);
        $stmt->bindParam(':package', $data['package_type']);

        if ($stmt->execute()) {
            $this->logOrder($orderId, 'info', 'Sipariş oluşturuldu');
            return $orderId;
        }

        return false;
    }

    public function getOrder($orderId)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE order_id = :order_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function logOrder($orderId, $type, $message)
    {
        $query = "INSERT INTO order_logs (order_id, log_type, message) VALUES (:order_id, :type, :message)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        $stmt->execute();
    }
}
