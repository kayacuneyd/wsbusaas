<?php

declare(strict_types=1);

class Payment
{
    public static function initiate(PDO $db, int $orderId, float $amount): string
    {
        $link = 'https://ruul.space/payment/' . bin2hex(random_bytes(8));
        $db->prepare('INSERT INTO payments (order_id, ruul_payment_id, amount, status) VALUES (?, ?, ?, ?)')->execute([
            $orderId,
            null,
            $amount,
            'initiated',
        ]);
        $db->prepare('UPDATE orders SET ruul_payment_link=? WHERE id=?')->execute([$link, $orderId]);
        return $link;
    }

    public static function markPaid(PDO $db, int $orderId, array $payload): void
    {
        $db->prepare('UPDATE payments SET ruul_payment_id=?, status=?, payload=? WHERE order_id=?')->execute([
            $payload['payment_id'] ?? null,
            'paid',
            json_encode($payload),
            $orderId,
        ]);
        $db->prepare('UPDATE orders SET status=? WHERE id=?')->execute(['paid', $orderId]);
    }
}
