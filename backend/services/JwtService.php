<?php
namespace App\Services;

class JwtService
{
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data)
    {
        $padding = 4 - (strlen($data) % 4);
        if ($padding < 4) {
            $data .= str_repeat('=', $padding);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function generate(array $payload, int $ttlSeconds = 86400): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $issuedAt = time();
        $payload['iat'] = $payload['iat'] ?? $issuedAt;
        $payload['exp'] = $payload['exp'] ?? ($issuedAt + $ttlSeconds);

        $secret = $_ENV['JWT_SECRET'] ?? 'change-me';

        $segments = [
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($payload))
        ];

        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $token): array|false
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;
        $secret = $_ENV['JWT_SECRET'] ?? 'change-me';

        $signingInput = $headerB64 . '.' . $payloadB64;
        $expectedSignature = self::base64UrlEncode(hash_hmac('sha256', $signingInput, $secret, true));

        if (!hash_equals($expectedSignature, $signatureB64)) {
            return false;
        }

        $payloadJson = self::base64UrlDecode($payloadB64);
        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            return false;
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            return false;
        }

        return $payload;
    }
}
