<?php

final class Auth
{
    public static function bootstrapRemember(AuthRepository $users): void
    {
        if (!empty($_SESSION['user_id']) || empty($_COOKIE['mdp_remember'])) {
            return;
        }

        $parts = explode(':', (string)$_COOKIE['mdp_remember'], 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return;
        }

        $user = $users->userByRememberToken($parts[0], $parts[1]);
        if ($user && (int)$user['is_active'] === 1) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
        }
    }

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
