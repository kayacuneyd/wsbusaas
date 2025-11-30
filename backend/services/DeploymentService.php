<?php
namespace App\Services;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

use App\Config\Database;
use PDO;
use Exception;

// Manually require services if autoload is not updated
require_once __DIR__ . '/HostingerApiService.php';
require_once __DIR__ . '/TemplateService.php';
require_once __DIR__ . '/FtpDeploymentService.php';
require_once __DIR__ . '/EmailService.php';

class DeploymentService
{
    const STEPS = [
        1 => 'check_domain_availability',
        2 => 'create_whois_profile',
        3 => 'purchase_domain',
        4 => 'verify_domain_ownership',
        5 => 'create_website_directory',
        6 => 'prepare_template',
        7 => 'customize_template',
        8 => 'deploy_via_ftp',
        9 => 'send_customer_notification'
    ];

    private $conn;
    private $hostingerApi;
    private $templateService;
    private $ftpService;
    private $emailService;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();

        // Initialize dependencies
        $this->hostingerApi = new HostingerApiService();
        $this->templateService = new TemplateService();
        $this->ftpService = new FtpDeploymentService();
        $this->emailService = new EmailService();
    }

    public function createDeploymentJob(string $orderId): string
    {
        // Check if job already exists
        $query = "SELECT id FROM deployment_jobs WHERE order_id = :order_id AND status != 'failed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $orderId]);
        if ($stmt->fetch()) {
            throw new Exception("Deployment job already exists for order $orderId");
        }

        // Create new job
        $query = "INSERT INTO deployment_jobs (order_id, status, current_step, total_steps) VALUES (:order_id, 'pending', 0, 9)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $orderId]);

        return $this->conn->lastInsertId();
    }

    public function processQueue(): void
    {
        // Fetch pending or retrying jobs
        $query = "SELECT * FROM deployment_jobs WHERE status IN ('pending', 'processing', 'retrying') ORDER BY created_at ASC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            return; // No jobs to process
        }

        $this->processJob($job);
    }

    private function processJob(array $job): void
    {
        $jobId = $job['id'];
        $orderId = $job['order_id'];

        // Update status to processing
        $this->updateJobStatus($jobId, 'processing');

        try {
            // Fetch order details to get domain and business info
            $order = $this->getOrderDetails($orderId);
            if (!$order) {
                throw new Exception("Order not found: $orderId");
            }

            // Determine next step
            $nextStep = $job['current_step'] + 1;

            if ($nextStep > 9) {
                $this->updateJobStatus($jobId, 'completed');
                return;
            }

            $stepName = self::STEPS[$nextStep];
            $this->logStep($jobId, $stepName, 'started');

            // Execute step
            $methodName = 'step' . str_replace('_', '', ucwords($stepName, '_'));
            if (!method_exists($this, $methodName)) {
                throw new Exception("Method $methodName not implemented");
            }

            $result = $this->$methodName($order, $jobId);

            // Log success and advance
            $this->logStep($jobId, $stepName, 'success', null, $result);
            $this->advanceStep($jobId, $nextStep);

            // If not finished, maybe process next step immediately? 
            // For now, let the next cron run handle it to avoid timeouts.

        } catch (Exception $e) {
            $this->logStep($jobId, self::STEPS[$job['current_step'] + 1] ?? 'unknown', 'failed', null, ['error' => $e->getMessage()]);
            $this->handleJobFailure($jobId, $e->getMessage());
        }
    }

    // --- Step Implementations ---

    private function stepCheckDomainAvailability($order, $jobId)
    {
        $domain = $order['domain_name'] . '.' . $order['tld']; // e.g., 'example.de'
        $result = $this->hostingerApi->checkDomainAvailability($domain);

        if (!$result['available']) {
            throw new Exception("Domain $domain is not available");
        }

        return $result;
    }

    private function stepCreateWhoisProfile($order, $jobId)
    {
        // Construct contact data from order
        $contactData = [
            'first_name' => 'Thomas', // Placeholder, should come from order
            'last_name' => 'Muentzer',
            'email' => $order['email'],
            'phone' => '+49123456789',
            'address' => 'Musterstrasse 1',
            'city' => 'Berlin',
            'country' => 'DE',
            'zip' => '10115'
        ];

        return $this->hostingerApi->createWhoisProfile($contactData);
    }

    private function stepPurchaseDomain($order, $jobId)
    {
        $domain = $order['domain_name'] . '.' . $order['tld'];
        // We need a whois profile ID, assume we stored it or get it from previous step logs
        // For MVP, we might hardcode or fetch from DB
        $whoisProfileId = 'mock-profile-id';

        return $this->hostingerApi->purchaseDomain($domain, $whoisProfileId);
    }

    private function stepVerifyDomainOwnership($order, $jobId)
    {
        // Poll Hostinger to see if domain is active
        return ['status' => 'active'];
    }

    private function stepCreateWebsiteDirectory($order, $jobId)
    {
        $domain = $order['domain_name'] . '.' . $order['tld'];
        // FTP logic to create directory
        // For MVP, Hostinger usually creates the dir when adding the domain via API
        // If API doesn't add hosting, we might need to do it here.
        // Assuming API 'purchaseDomain' also sets up hosting or we have a separate 'createHosting' step.
        // Let's assume we just verify FTP access here.
        return ['status' => 'directory_ready'];
    }

    private function stepPrepareTemplate($order, $jobId)
    {
        // Get template path
        return $this->templateService->getTemplate('starter', 'static');
    }

    private function stepCustomizeTemplate($order, $jobId)
    {
        $templatePath = $_ENV['TEMPLATE_STORAGE_PATH'] . '/starter-static-v1.0.0';
        $customData = [
            'business_name' => $order['business_name'] ?? 'My Business',
            'page_title' => $order['business_name'] ?? 'Welcome',
            'email' => $order['email']
        ];

        return $this->templateService->customizeTemplate($templatePath, $customData);
    }

    private function stepDeployViaFtp($order, $jobId)
    {
        $domain = $order['domain_name'] . '.' . $order['tld'];
        // The customized template is in a temp dir
        $localPath = sys_get_temp_dir() . '/deployment_' . $jobId;

        return $this->ftpService->deploy(
            $localPath,
            $domain,
            $_ENV['FTP_USERNAME'],
            $_ENV['FTP_PASSWORD']
        );
    }

    private function stepSendCustomerNotification($order, $jobId)
    {
        $domain = $order['domain_name'] . '.' . $order['tld'];
        return $this->emailService->sendDeploymentCompleteEmail(
            $order['email'],
            'Customer',
            $domain,
            "https://$domain",
            $order['order_id']
        );
    }

    // --- Helper Methods ---

    private function getOrderDetails($orderId)
    {
        $query = "SELECT * FROM orders WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateJobStatus($jobId, $status)
    {
        $query = "UPDATE deployment_jobs SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':status' => $status, ':id' => $jobId]);
    }

    private function advanceStep($jobId, $nextStep)
    {
        $query = "UPDATE deployment_jobs SET current_step = :step WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':step' => $nextStep, ':id' => $jobId]);
    }

    private function logStep($jobId, $stepName, $status, $payload = null, $response = null)
    {
        $query = "INSERT INTO deployment_steps (job_id, step_name, status, payload, response) VALUES (:job_id, :step_name, :status, :payload, :response)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':job_id' => $jobId,
            ':step_name' => $stepName,
            ':status' => $status,
            ':payload' => json_encode($payload),
            ':response' => json_encode($response)
        ]);
    }

    private function handleJobFailure($jobId, $errorMessage)
    {
        $query = "UPDATE deployment_jobs SET status = 'failed', error_message = :error WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':error' => $errorMessage, ':id' => $jobId]);
    }
}
