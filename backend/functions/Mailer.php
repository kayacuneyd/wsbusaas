<?php

declare(strict_types=1);

class Mailer
{
    public static function send(string $to, string $subject, string $body): void
    {
        global $config;
        if ($config['mail']['transport'] === 'log') {
            logMessage('INFO', 'Mail log', compact('to', 'subject', 'body'));
            return;
        }
        $headers = [
            'From: ' . $config['mail']['from'],
            'Content-Type: text/plain; charset=utf-8',
        ];
        mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
