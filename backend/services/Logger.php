<?php
namespace App\Services;

class Logger
{
    private static function write(string $level, string $message, array $context = []): void
    {
        $logFile = __DIR__ . '/../error_app.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context) : '';
        $line = sprintf("[%s] [%s] %s%s\n", $timestamp, strtoupper($level), $message, $contextStr);
        // @ suppress in case of permission issues; logging should not break the app
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }
}
