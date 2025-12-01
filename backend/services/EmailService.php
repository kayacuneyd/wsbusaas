<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['SMTP_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USER'];
        $this->mailer->Password = $_ENV['SMTP_PASS'];
        $this->mailer->Port = $_ENV['SMTP_PORT'] ?? 587;

        if ($this->mailer->Port == 465) {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        // Default sender
        $this->mailer->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']);
    }

    public function sendDeploymentCompleteEmail(
        string $email,
        string $name,
        string $domain,
        string $websiteUrl,
        string $orderId
    ): bool {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Your Website is Ready! - $domain";

            // Simple HTML body for MVP
            $body = "
                <h1>Congratulations, $name!</h1>
                <p>Your website <strong>$domain</strong> has been successfully deployed.</p>
                <p>You can view it here: <a href='$websiteUrl'>$websiteUrl</a></p>
                <p>Order ID: $orderId</p>
                <br>
                <p>Best regards,<br>Bezmidar Team</p>
            ";

            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            // Log error
            error_log("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function sendDeploymentFailedEmail(
        string $email,
        string $name,
        string $domain,
        string $errorMessage,
        string $orderId
    ): bool {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Deployment Issue - $domain";

            $body = "
                <h1>Hello $name,</h1>
                <p>We encountered an issue while deploying your website <strong>$domain</strong>.</p>
                <p><strong>Error:</strong> $errorMessage</p>
                <p>Our team has been notified and will resolve this shortly.</p>
                <p>Order ID: $orderId</p>
                <br>
                <p>Best regards,<br>Bezmidar Team</p>
            ";

            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
    public function sendOrderProcessingEmail(
        string $email,
        string $name,
        string $domain,
        string $package,
        string $orderId
    ): bool {
        try {
            // 1. Send to Customer
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Siparişiniz İşleme Alındı - $domain";

            $bodyCustomer = "
                <h1>Merhaba $name,</h1>
                <p><strong>$domain</strong> alan adı için verdiğiniz sipariş (ID: $orderId) ödeme onayından geçti ve işleme alındı.</p>
                <p><strong>Sipariş Detayları:</strong></p>
                <ul>
                    <li><strong>Paket:</strong> $package</li>
                    <li><strong>Domain:</strong> $domain</li>
                    <li><strong>Durum:</strong> İşleniyor (Kurulum Hazırlığı)</li>
                </ul>
                <p>Ekibimiz kurulum işlemlerini başlattı. Tamamlandığında size tekrar bilgi vereceğiz.</p>
                <br>
                <p>Saygılarımızla,<br>Bezmidar Ekibi</p>
            ";

            $this->mailer->Body = $bodyCustomer;
            $this->mailer->AltBody = strip_tags($bodyCustomer);
            $this->mailer->send();

            // 2. Send to Admin (Notification)
            $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@bezmidar.de'; // Fallback or env
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($adminEmail, 'Admin');
            $this->mailer->Subject = "[Admin] Sipariş İşleme Alındı - $domain";

            $bodyAdmin = "
                <h1>Sipariş İşleme Alındı - Admin Bildirimi</h1>
                <p><strong>$name</strong> ($email) adlı müşteriye aşağıdaki bilgilendirme e-postası gönderilmiştir:</p>
                <div style='border: 1px solid #ccc; padding: 15px; margin: 10px 0; background-color: #f9f9f9;'>
                    $bodyCustomer
                </div>
                <hr>
                <p><strong>Sistem Bilgisi:</strong></p>
                <ul>
                    <li><strong>Sipariş ID:</strong> $orderId</li>
                    <li><strong>İşlem Zamanı:</strong> " . date('Y-m-d H:i:s') . "</li>
                </ul>
            ";

            $this->mailer->Body = $bodyAdmin;
            $this->mailer->AltBody = strip_tags($bodyAdmin);
            $this->mailer->send();

            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}
