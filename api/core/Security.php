<?php

final class Security
{
    public static function token(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function appUrl(array $config): string
    {
        return rtrim((string)($config['app_url'] ?? ''), '/');
    }

    public static function cookiePath(array $config): string
    {
        return (string)($config['cookie_path'] ?? '/');
    }

    public static function isSecureCookie(array $config): bool
    {
        if (isset($config['session_secure'])) {
            return (bool)$config['session_secure'];
        }

        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    }
}
