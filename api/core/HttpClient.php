<?php

final class HttpClient
{
    public static function postForm(string $url, array $data): array
    {
        $payload = http_build_query($data);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                CURLOPT_TIMEOUT => 15,
            ]);
            $body = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $payload,
                    'timeout' => 15,
                ],
            ]);
            $body = file_get_contents($url, false, $context);
            $status = self::statusFromHeaders($http_response_header ?? []);
        }

        return [
            'status' => $status,
            'json' => json_decode((string)$body, true),
            'raw' => $body,
        ];
    }

    public static function getJson(string $url, array $headers = []): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 15,
            ]);
            $body = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers),
                    'timeout' => 15,
                ],
            ]);
            $body = file_get_contents($url, false, $context);
            $status = self::statusFromHeaders($http_response_header ?? []);
        }

        return [
            'status' => $status,
            'json' => json_decode((string)$body, true),
            'raw' => $body,
        ];
    }

    private static function statusFromHeaders(array $headers): int
    {
        $first = $headers[0] ?? '';
        if (preg_match('/\s(\d{3})\s/', $first, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }
}
