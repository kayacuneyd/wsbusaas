<?php

declare(strict_types=1);

class Order
{
    public static function create(PDO $db, array $payload): int
    {
        $stmt = $db->prepare('INSERT INTO orders (user_email, theme_id, total_amount, domain, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $payload['email'],
            $payload['theme_id'],
            $payload['amount'],
            $payload['domain'],
            'pending',
        ]);
        $orderId = (int)$db->lastInsertId();
        $db->prepare('INSERT INTO theme_configs (order_id, primary_color, secondary_color, font, logo_path, sections_json) VALUES (?, ?, ?, ?, ?, ?)')->execute([
            $orderId,
            $payload['primary_color'] ?? '#3b82f6',
            $payload['secondary_color'] ?? '#f97316',
            $payload['font'] ?? 'Inter',
            null,
            json_encode($payload['sections'] ?? []),
        ]);
        $db->prepare('INSERT INTO domains (order_id, domain, availability, checked_at) VALUES (?, ?, ?, NOW())')->execute([
            $orderId,
            $payload['domain'],
            'unknown',
        ]);
        return $orderId;
    }

    public static function get(PDO $db, int $orderId): ?array
    {
        $stmt = $db->prepare('SELECT o.*, tc.primary_color, tc.secondary_color, tc.font, tc.logo_path, tc.sections_json FROM orders o LEFT JOIN theme_configs tc ON tc.order_id = o.id WHERE o.id = ?');
        $stmt->execute([$orderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateThemeConfig(PDO $db, int $orderId, array $data): void
    {
        $db->prepare('UPDATE theme_configs SET primary_color=?, secondary_color=?, font=?, logo_path=?, sections_json=? WHERE order_id=?')->execute([
            $data['primary_color'],
            $data['secondary_color'],
            $data['font'],
            $data['logo_path'] ?? null,
            json_encode($data['sections'] ?? []),
            $orderId,
        ]);
    }
}
