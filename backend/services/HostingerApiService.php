<?php
namespace App\Services;

class HostingerApiService
{
    private string $mcpEndpoint;

    public function __construct()
    {
        $this->mcpEndpoint = $_ENV['MCP_SERVER_URL'] ?? 'https://wsbusaas.vercel.app/api/mcp';
    }

    public function checkDomainAvailability(string $domain): array
    {
        return $this->callMcp('check_domain_availability', ['domain' => $domain]);
    }

    public function createWhoisProfile(array $contactData): array
    {
        return $this->callMcp('create_whois_profile', $contactData);
    }

    public function purchaseDomain(string $domain, string $whoisProfileId): array
    {
        return $this->callMcp('purchase_domain', [
            'domain' => $domain,
            'whois_profile_id' => $whoisProfileId
        ]);
    }

    public function verifyDomainOwnership(string $domainId): array
    {
        return $this->callMcp('verify_domain', ['domain_id' => $domainId]);
    }

    private function callMcp(string $tool, array $params): array
    {
        $payload = json_encode([
            'tool' => $tool,
            'params' => $params
        ]);

        $ch = curl_init($this->mcpEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("MCP Connection Error: $error");
        }

        if ($httpCode !== 200) {
            throw new \Exception("MCP Error (HTTP $httpCode): $response");
        }

        $json = json_decode($response, true);
        if (!$json || !isset($json['success'])) {
            throw new \Exception("Invalid MCP Response: $response");
        }

        if ($json['success'] === false) {
            throw new \Exception("MCP Tool Error: " . ($json['error'] ?? 'Unknown error'));
        }

        return $json['data'];
    }
}
