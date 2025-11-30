<?php
namespace App\Services;

class EmailService
{
    public function sendDeploymentCompleteEmail(
        string $email,
        string $name,
        string $domain,
        string $websiteUrl,
        string $orderId
    ): bool {
        // TODO: Implement
        return false;
    }

    public function sendDeploymentFailedEmail(
        string $email,
        string $name,
        string $domain,
        string $errorMessage,
        string $orderId
    ): bool {
        // TODO: Implement
        return false;
    }
}
