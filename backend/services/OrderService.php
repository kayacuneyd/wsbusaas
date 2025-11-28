<?php
namespace App\Services;

use App\Config\Database;
use PDO;

class OrderService
{
    public $conn;
    private $table = 'orders';

    public const PROGRESS_STATUSES = [
        'pending_confirmation',
        'payment_received',
        'processing',
        'completed',
        'cancelled',
        'failed'
    ];

    private const DEFAULT_STATUS = 'pending_confirmation';

    public const STATUS_MESSAGES = [
        'pending_confirmation' => [
            'en' => "Your transaction is pending confirmation. We're waiting for it to be reflected in our system.",
            'tr' => 'İşleminizin sunucumuza/sistemimize yansıması bekleniyor.'
        ],
        'payment_received' => [
            'en' => 'We have received your payment via Ruul.io and are preparing your order for production.',
            'tr' => 'Ruul.io üzerinden ödemeniz onaylandı, siparişiniz hazırlanıyor.'
        ],
        'processing' => [
            'en' => 'Our team is manually verifying and configuring your service details.',
            'tr' => 'Ekibimiz siparişinizi manuel olarak doğruluyor ve yapılandırıyor.'
        ],
        'completed' => [
            'en' => 'Your service has been delivered. Thank you for working with us!',
            'tr' => 'Hizmetiniz tamamlandı, bizimle çalıştığınız için teşekkür ederiz.'
        ],
        'cancelled' => [
            'en' => 'This order was cancelled. Please contact support if you have questions.',
            'tr' => 'Sipariş iptal edildi. Sorularınız için destek ekibimizle iletişime geçebilirsiniz.'
        ],
        'failed' => [
            'en' => 'We could not complete this order. Please contact support to continue.',
            'tr' => 'Sipariş tamamlanamadı. Devam edebilmek için lütfen destek ekibimizle iletişime geçin.'
        ],
    ];

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createOrder($data)
    {
        $orderId = 'WB' . date('YmdHis') . rand(100, 999);
        $status = self::DEFAULT_STATUS;
        $statusMessage = self::STATUS_MESSAGES[$status]['en'];
        $statusUpdatedBy = 'system';

        $query = "INSERT INTO " . $this->table . " 
                  (order_id, user_id, customer_email, customer_name, domain_name, package_type, order_status, status_message, status_updated_at, status_updated_by) 
                  VALUES (:order_id, :user_id, :email, :name, :domain, :package, :status, :status_message, NOW(), :status_updated_by)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':email', $data['customer_email']);
        $stmt->bindParam(':name', $data['customer_name']);
        $stmt->bindParam(':domain', $data['domain_name']);
        $stmt->bindParam(':package', $data['package_type']);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':status_message', $statusMessage);
        $stmt->bindParam(':status_updated_by', $statusUpdatedBy);

        if ($stmt->execute()) {
            $this->logOrder($orderId, 'info', 'Sipariş oluşturuldu');
            $this->recordStatusHistory($orderId, $status, $statusMessage, $statusUpdatedBy);
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

    public function getOrderWithStatus($orderId)
    {
        $order = $this->getOrder($orderId);
        if ($order) {
            $order['status_history'] = $this->getStatusHistory($orderId);
        }

        return $order;
    }

    public function updateOrderStatus(string $orderId, string $status, ?string $note = null, string $changedBy = 'system')
    {
        if (!self::isValidStatus($status)) {
            throw new \InvalidArgumentException('Invalid order status: ' . $status);
        }

        $message = $note ?: (self::STATUS_MESSAGES[$status]['en'] ?? null);

        $query = "UPDATE " . $this->table . " 
                  SET order_status = :status,
                      status_message = :status_message,
                      status_updated_at = NOW(),
                      status_updated_by = :changed_by,
                      updated_at = NOW()
                  WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':status_message', $message);
        $stmt->bindParam(':changed_by', $changedBy);
        $stmt->bindParam(':order_id', $orderId);

        if ($stmt->execute()) {
            $this->recordStatusHistory($orderId, $status, $message, $changedBy);
            $this->logOrder($orderId, 'info', 'Durum güncellendi: ' . $status);
            return $this->getOrderWithStatus($orderId);
        }

        return false;
    }

    public function getStatusHistory(string $orderId): array
    {
        $query = "SELECT status, note, changed_by, created_at 
                  FROM order_status_history 
                  WHERE order_id = :order_id 
                  ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function recordStatusHistory(string $orderId, string $status, ?string $note, string $changedBy = 'system'): void
    {
        $query = "INSERT INTO order_status_history (order_id, status, note, changed_by) 
                  VALUES (:order_id, :status, :note, :changed_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':note', $note);
        $stmt->bindParam(':changed_by', $changedBy);
        $stmt->execute();
    }

    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::PROGRESS_STATUSES, true);
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
