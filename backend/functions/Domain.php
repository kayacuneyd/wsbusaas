<?php

declare(strict_types=1);

class Domain
{
    public static function validate(string $domain): void
    {
        if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $domain)) {
            throw new InvalidArgumentException('Invalid domain format');
        }
    }

    public static function check(string $domain): array
    {
        self::validate($domain);
        $types = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'SOA', 'TXT'];
        $records = [];
        foreach ($types as $type) {
            $flag = constant('DNS_' . $type);
            $records[$type] = dns_get_record($domain, $flag) ?: [];
        }
        $http = self::checkHttp($domain);
        $whois = self::whoisLookup($domain);
        $available = empty($records['A']) && !$http['reachable'] && $whois['registered'] === false;
        return [
            'domain' => $domain,
            'available' => $available,
            'records' => $records,
            'http' => $http,
            'whois' => $whois,
        ];
    }

    private static function checkHttp(string $domain): array
    {
        $ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 3, 'ignore_errors' => true]]);
        $resp = @file_get_contents("https://{$domain}", false, $ctx);
        $code = 0;
        if (isset($http_response_header)) {
            preg_match('#HTTP/\\d\\.\\d\\s+(\\d+)#', $http_response_header[0], $m);
            $code = (int)($m[1] ?? 0);
        }
        return [
            'reachable' => $code > 0 && $code < 500,
            'status' => $code,
        ];
    }

    private static function whoisLookup(string $domain): array
    {
        $server = 'whois.verisign-grs.com';
        $fp = @fsockopen($server, 43, $errno, $errstr, 5);
        if (!$fp) {
            return ['registered' => false, 'raw' => null];
        }
        fwrite($fp, $domain . "\r\n");
        $out = '';
        while (!feof($fp)) {
            $out .= fgets($fp, 128);
        }
        fclose($fp);
        $registered = stripos($out, 'No match') === false && stripos($out, 'NOT FOUND') === false;
        return ['registered' => $registered, 'raw' => $out];
    }
}
