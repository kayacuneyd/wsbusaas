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
        $this->ensureOrderPaymentLinkColumn();
    }

    private function ensureOrderPaymentLinkColumn(): void
    {
        if (!$this->conn) {
            return;
        }

        try {
            $stmt = $this->conn->query("SHOW COLUMNS FROM orders LIKE 'payment_link'");
            if ($stmt->rowCount() === 0) {
                $this->conn->exec("ALTER TABLE orders ADD COLUMN payment_link TEXT AFTER payment_reference");
            }
        } catch (\Exception $e) {
            // If schema change fails, we let the calling flow handle DB errors
        }
    }

    private function ensurePackagesTable(): void
    {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS packages (
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
    }

    private function getPaymentBaseUrl(?string $packageSlug = null): ?string
    {
        $slug = $packageSlug ?: 'starter';

        try {
            $this->ensurePackagesTable();

            $queryPackage = "SELECT payment_link FROM packages WHERE slug = :slug AND is_active = 1 LIMIT 1";
            $stmtPackage = $this->conn->prepare($queryPackage);
            $stmtPackage->bindParam(':slug', $slug);
            $stmtPackage->execute();
            $packagePaymentLink = $stmtPackage->fetchColumn();

            if ($packagePaymentLink) {
                return $packagePaymentLink;
            }
        } catch (\Exception $e) {
            // packages table might not exist yet, fall back to settings
        }

        $querySettings = "SELECT value FROM settings WHERE `key` = 'payment_url' LIMIT 1";
        $stmtSettings = $this->conn->prepare($querySettings);
        $stmtSettings->execute();
        $baseUrl = $stmtSettings->fetchColumn();

        return $baseUrl ?: null;
    }

    private function buildPaymentUrl(?string $baseUrl, string $orderId): ?string
    {
        if (!$baseUrl) {
            return null;
        }

        $separator = (strpos($baseUrl, '?') !== false) ? '&' : '?';
        return rtrim($baseUrl) . $separator . 'order_id=' . urlencode($orderId);
    }

    public function ensurePaymentLinkForOrder(string $orderId, ?string $packageType = null): ?string
    {
        $order = $this->getOrder($orderId);
        if ($order && !empty($order['payment_link'])) {
            return $order['payment_link'];
        }

        $packageSlug = $packageType;
        if (!$packageSlug && is_array($order) && isset($order['package_type'])) {
            $packageSlug = $order['package_type'];
        }

        $paymentLink = $this->buildPaymentUrl(
            $this->getPaymentBaseUrl($packageSlug ?? 'starter'),
            $orderId
        );

        if (!$paymentLink) {
            return null;
        }

        $query = "UPDATE " . $this->table . " SET payment_link = :payment_link WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':payment_link', $paymentLink);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        return $paymentLink;
    }

    public function createOrder($data)
    {
        $orderId = 'WB' . date('YmdHis') . rand(100, 999);
        $packageType = $data['package_type'] ?? 'starter';
        $status = self::DEFAULT_STATUS;
        $statusMessage = self::STATUS_MESSAGES[$status]['en'];
        $statusUpdatedBy = 'system';
        $paymentLink = $this->buildPaymentUrl($this->getPaymentBaseUrl($packageType), $orderId);

        if (!$paymentLink) {
            throw new \RuntimeException('Payment URL is not configured. Please set it in Admin > Packages or Settings.');
        }

        $query = "INSERT INTO " . $this->table . " 
                  (order_id, user_id, customer_email, customer_name, domain_name, package_type, order_status, status_message, payment_link, status_updated_at, status_updated_by) 
                  VALUES (:order_id, :user_id, :email, :name, :domain, :package, :status, :status_message, :payment_link, NOW(), :status_updated_by)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':email', $data['customer_email']);
        $stmt->bindParam(':name', $data['customer_name']);
        $stmt->bindParam(':domain', $data['domain_name']);
        $stmt->bindParam(':package', $packageType);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':status_message', $statusMessage);
        $stmt->bindParam(':payment_link', $paymentLink);
        $stmt->bindParam(':status_updated_by', $statusUpdatedBy);

        if ($stmt->execute()) {
            $this->logOrder($orderId, 'info', 'Sipariş oluşturuldu');
            $this->recordStatusHistory($orderId, $status, $statusMessage, $statusUpdatedBy);
            return [
                'order_id' => $orderId,
                'payment_link' => $paymentLink,
            ];
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
            if (empty($order['payment_link'])) {
                $order['payment_link'] = $this->ensurePaymentLinkForOrder($orderId, $order['package_type'] ?? null);
            }
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

            // Send email notification if status is 'processing'
            if ($status === 'processing') {
                require_once __DIR__ . '/EmailService.php';
                $emailService = new EmailService();
                $order = $this->getOrder($orderId);
                if ($order) {
                    $emailService->sendOrderProcessingEmail(
                        $order['customer_email'],
                        $order['customer_name'],
                        $order['domain_name'],
                        $order['package_type'],
                        $orderId
                    );
                    $this->logOrder($orderId, 'info', 'İşlem bildirimi e-postaları gönderildi.');
                }
            }

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
