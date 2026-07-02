<?php

final class Request
{
    public static function route(): string
    {
        return trim((string)($_GET['route'] ?? 'health'));
    }

    public static function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
