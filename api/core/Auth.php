<?php

final class Auth
{
    public static function userId(array $config): int
    {
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        if (!empty($config['allow_demo_auth']) && ($config['app_env'] ?? '') === 'local') {
            return (int) ($config['demo_user_id'] ?? 1);
        }

        Response::error('Autenticazione richiesta', 401);
    }
}
