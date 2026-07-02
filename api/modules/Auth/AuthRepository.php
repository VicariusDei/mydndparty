<?php

final class AuthRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, email, display_name, avatar_url, google_id, password_hash, is_active, is_admin, email_verified_at, last_login_at FROM mdp_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findUserByLogin(string $login): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, email, display_name, avatar_url, google_id, password_hash, is_active, is_admin, email_verified_at, last_login_at FROM mdp_users WHERE username = :login OR email = :login LIMIT 1');
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, email, display_name, avatar_url, google_id, password_hash, is_active, is_admin, email_verified_at, last_login_at FROM mdp_users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findUserByGoogleId(string $googleId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, email, display_name, avatar_url, google_id, password_hash, is_active, is_admin, email_verified_at, last_login_at FROM mdp_users WHERE google_id = :google_id LIMIT 1');
        $stmt->execute(['google_id' => $googleId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function createPasswordUser(string $username, string $email, string $plainPassword): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_users (username, email, password_hash, display_name, is_active, email_verified_at)
             VALUES (:username, :email, :password_hash, :display_name, 1, NOW())'
        );
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'display_name' => $username,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function upsertGoogleUser(array $profile): array
    {
        $googleId = (string)$profile['sub'];
        $email = strtolower((string)$profile['email']);
        $displayName = (string)($profile['name'] ?? $email);
        $avatarUrl = (string)($profile['picture'] ?? '');

        $user = $this->findUserByGoogleId($googleId);
        if (!$user) {
            $user = $this->findUserByEmail($email);
        }

        if ($user) {
            $stmt = $this->pdo->prepare(
                'UPDATE mdp_users
                 SET google_id = :google_id,
                     display_name = :display_name,
                     avatar_url = :avatar_url,
                     is_active = 1,
                     email_verified_at = COALESCE(email_verified_at, NOW()),
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'google_id' => $googleId,
                'display_name' => $displayName,
                'avatar_url' => $avatarUrl ?: null,
                'id' => $user['id'],
            ]);
            return $this->findUserById((int)$user['id']);
        }

        $username = $this->uniqueUsernameFromEmail($email);
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_users (username, email, display_name, avatar_url, google_id, is_active, email_verified_at)
             VALUES (:username, :email, :display_name, :avatar_url, :google_id, 1, NOW())'
        );
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'display_name' => $displayName,
            'avatar_url' => $avatarUrl ?: null,
            'google_id' => $googleId,
        ]);

        return $this->findUserById((int)$this->pdo->lastInsertId());
    }

    public function touchLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE mdp_users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public function createRememberToken(int $userId, string $selector, string $token, ?string $userAgent, ?string $ip): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_remember_tokens (user_id, selector, token_hash, expires_at, user_agent, ip_address)
             VALUES (:user_id, :selector, :token_hash, DATE_ADD(NOW(), INTERVAL 30 DAY), :user_agent, :ip_address)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => Security::hashToken($token),
            'user_agent' => $userAgent,
            'ip_address' => $ip,
        ]);
    }

    public function userByRememberToken(string $selector, string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT rt.id AS token_id, rt.token_hash, u.id, u.username, u.email, u.display_name, u.avatar_url, u.google_id, u.is_active, u.is_admin
             FROM mdp_remember_tokens rt
             JOIN mdp_users u ON u.id = rt.user_id
             WHERE rt.selector = :selector AND rt.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['selector' => $selector]);
        $row = $stmt->fetch();

        if (!$row || !hash_equals((string)$row['token_hash'], Security::hashToken($token))) {
            return null;
        }

        $this->markRememberUsed((int)$row['token_id']);
        unset($row['token_id'], $row['token_hash']);
        return $row;
    }

    public function deleteRememberToken(string $selector): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM mdp_remember_tokens WHERE selector = :selector');
        $stmt->execute(['selector' => $selector]);
    }

    public function deleteUserRememberTokens(int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM mdp_remember_tokens WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }

    public function createResetToken(int $userId, string $token): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_password_reset_tokens (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 60 MINUTE))'
        );
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => Security::hashToken($token),
        ]);
    }

    public function userByResetToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT prt.id AS reset_id, u.id, u.username, u.email, u.display_name
             FROM mdp_password_reset_tokens prt
             JOIN mdp_users u ON u.id = prt.user_id
             WHERE prt.token_hash = :token_hash AND prt.expires_at > NOW() AND prt.used_at IS NULL
             ORDER BY prt.id DESC
             LIMIT 1'
        );
        $stmt->execute(['token_hash' => Security::hashToken($token)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function resetPassword(int $resetId, int $userId, string $plainPassword): void
    {
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare('UPDATE mdp_users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);

        $stmt = $this->pdo->prepare('UPDATE mdp_password_reset_tokens SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $resetId]);
        $this->pdo->commit();
    }

    private function markRememberUsed(int $tokenId): void
    {
        $stmt = $this->pdo->prepare('UPDATE mdp_remember_tokens SET last_used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $tokenId]);
    }

    private function uniqueUsernameFromEmail(string $email): string
    {
        $base = preg_replace('/[^a-z0-9_]+/i', '_', explode('@', $email)[0]) ?: 'user';
        $candidate = strtolower(trim($base, '_')) ?: 'user';
        $suffix = 0;

        while ($this->usernameExists($candidate . ($suffix > 0 ? '_' . $suffix : ''))) {
            $suffix++;
        }

        return $candidate . ($suffix > 0 ? '_' . $suffix : '');
    }

    private function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mdp_users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        return (bool)$stmt->fetch();
    }
}
