<?php
namespace App\Services;

class WhoisService
{
    private $servers = [
        'de' => 'whois.denic.de',
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'eu' => 'whois.eu',
    ];

    public function isAvailable($domain, $tld)
    {
        if (!isset($this->servers[$tld])) {
            throw new \Exception("TLD not supported");
        }

        $server = $this->servers[$tld];
        $fullDomain = $domain . '.' . $tld;

        // Special handling for .de (DENIC requires -T dn,ace)
        $query = $fullDomain . "\r\n";
        if ($tld === 'de') {
            $query = "-T dn,ace " . $fullDomain . "\r\n";
        }

        $output = $this->queryWhois($server, $query);

        return $this->parseOutput($output, $tld);
    }

    private function queryWhois($server, $query)
    {
        $fp = fsockopen($server, 43, $errno, $errstr, 10);
        if (!$fp) {
            throw new \Exception("WHOIS connection failed: $errstr");
        }

        fputs($fp, $query);
        $response = "";
        while (!feof($fp)) {
            $response .= fgets($fp, 128);
        }
        fclose($fp);

        return $response;
    }

    private function parseOutput($output, $tld)
    {
        $output = strtolower($output);

        switch ($tld) {
            case 'de':
                // DENIC: "Status: free" means available
                // "Status: connect" means taken
                // But DENIC output is complex. simpler check:
                // If it contains "status: free", it's free.
                // If it contains "the domain has not been registered", it's free.
                return strpos($output, 'status: free') !== false ||
                    strpos($output, 'the domain has not been registered') !== false;

            case 'com':
            case 'net':
                // Verisign: "No match for" means available
                return strpos($output, 'no match for') !== false;

            case 'org':
                // PIR: "NOT FOUND" means available
                return strpos($output, 'not found') !== false;

            case 'eu':
                // EURid: "Status: AVAILABLE"
                return strpos($output, 'status: available') !== false;

            default:
                return false;
        }
    }
}
