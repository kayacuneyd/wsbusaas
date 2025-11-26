<?php

declare(strict_types=1);

class Deploy
{
    public static function run(PDO $db, int $orderId, array $paths): bool
    {
        $order = Order::get($db, $orderId);
        if (!$order) {
            throw new RuntimeException('Order not found');
        }
        $db->prepare("UPDATE orders SET status='deploying' WHERE id=?")->execute([$orderId]);

        $domain = $order['domain'];
        $logo = $order['logo_path'] ?: $paths['uploads'] . "/order_{$orderId}_logo.png";

        Theme::copyTemplate($order['theme_id'], $domain, $paths);
        Theme::replaceCSS($domain, $order, $paths);
        Theme::injectLogo($domain, $logo, $paths);
        Theme::injectSEO($domain, "{$domain} - {$order['theme_id']}", "Auto site for {$domain}", $paths);
        Theme::generateHtaccess($domain, $paths);

        Mailer::send($order['user_email'], 'Site hazır', "Domain: {$domain}\nDNS: CNAME your.hosting");
        $db->prepare("UPDATE orders SET status='live' WHERE id=?")->execute([$orderId]);
        return true;
    }
}
