<?php

final class Mailer
{
    public static function send(array $config, string $to, string $subject, string $body): bool
    {
        $fromEmail = (string)($config['mail']['from_email'] ?? 'noreply@example.test');
        $fromName = (string)($config['mail']['from_name'] ?? 'MyDndParty');

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'From: ' . self::sanitizeHeader($fromName) . ' <' . self::sanitizeHeader($fromEmail) . '>';
        $headers[] = 'Reply-To: ' . self::sanitizeHeader($fromEmail);

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    private static function sanitizeHeader(string $value): string
    {
        return str_replace(["\r", "\n"], '', $value);
    }
}
