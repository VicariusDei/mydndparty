<?php

final class AuthController
{
    public function __construct(
        private AuthRepository $users,
        private array $config
    ) {
    }

    public function me(): void
    {
        $userId = Auth::userId($this->config);
        $user = $this->users->findUserById($userId);

        if (!$user) {
            Response::error('Utente non trovato', 404);
        }

        Response::ok([
            'user' => $this->publicUser($user),
        ]);
    }

    public function register(): void
    {
        $body = Request::jsonBody();
        $username = trim((string)($body['username'] ?? ''));
        $email = strtolower(trim((string)($body['email'] ?? '')));
        $password = (string)($body['password'] ?? '');
        $remember = !empty($body['remember']);

        if ($username === '' || $email === '' || $password === '') {
            Response::error('Username, email e password sono obbligatori', 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Email non valida', 422);
        }
        if (strlen($password) < 8) {
            Response::error('La password deve contenere almeno 8 caratteri', 422);
        }
        if ($this->users->findUserByLogin($username) || $this->users->findUserByEmail($email)) {
            Response::error('Username o email gia\' registrati', 409);
        }

        $userId = $this->users->createPasswordUser($username, $email, $password);
        $user = $this->users->findUserById($userId);
        $this->loginUser((int)$user['id'], $remember);

        Response::ok([
            'user' => $this->publicUser($user),
        ]);
    }

    public function login(): void
    {
        $body = Request::jsonBody();
        $login = trim((string)($body['login'] ?? ''));
        $password = (string)($body['password'] ?? '');
        $remember = !empty($body['remember']);

        if ($login === '' || $password === '') {
            Response::error('Login e password sono obbligatori', 422);
        }

        $user = $this->users->findUserByLogin($login);
        if (!$user || empty($user['password_hash']) || !password_verify($password, (string)$user['password_hash'])) {
            Response::error('Credenziali non valide', 401);
        }
        if ((int)$user['is_active'] !== 1) {
            Response::error('Profilo non attivo', 403);
        }

        $this->loginUser((int)$user['id'], $remember);
        $user = $this->users->findUserById((int)$user['id']);

        Response::ok([
            'user' => $this->publicUser($user),
        ]);
    }

    public function logout(): void
    {
        if (!empty($_COOKIE['mdp_remember'])) {
            [$selector] = explode(':', (string)$_COOKIE['mdp_remember'], 2) + ['', ''];
            if ($selector !== '') {
                $this->users->deleteRememberToken($selector);
            }
        }

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->clearRememberCookie();

        Response::ok();
    }

    public function forgotPassword(): void
    {
        $body = Request::jsonBody();
        $email = strtolower(trim((string)($body['email'] ?? '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Email non valida', 422);
        }

        $user = $this->users->findUserByEmail($email);
        if ($user) {
            $token = Security::token(32);
            $this->users->createResetToken((int)$user['id'], $token);
            $url = Security::appUrl($this->config) . '/reset-password?token=' . urlencode($token);
            $bodyText = "Hai richiesto il recupero password per MyDndParty.\n\nApri questo link entro 60 minuti:\n" . $url . "\n\nSe non hai richiesto tu il reset, ignora questa email.";
            Mailer::send($this->config, $email, 'Recupero password MyDndParty', $bodyText);
        }

        Response::ok([
            'message' => 'Se l\'email e\' registrata, riceverai un link di recupero.',
        ]);
    }

    public function resetPassword(): void
    {
        $body = Request::jsonBody();
        $token = trim((string)($body['token'] ?? ''));
        $password = (string)($body['password'] ?? '');

        if ($token === '' || strlen($password) < 8) {
            Response::error('Token non valido o password troppo breve', 422);
        }

        $row = $this->users->userByResetToken($token);
        if (!$row) {
            Response::error('Token scaduto o non valido', 422);
        }

        $this->users->resetPassword((int)$row['reset_id'], (int)$row['id'], $password);
        $this->users->deleteUserRememberTokens((int)$row['id']);

        Response::ok([
            'message' => 'Password aggiornata. Ora puoi accedere.',
        ]);
    }

    public function loginUser(int $userId, bool $remember): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $this->users->touchLogin($userId);

        if ($remember) {
            $selector = Security::token(9);
            $token = Security::token(32);
            $this->users->createRememberToken(
                $userId,
                $selector,
                $token,
                substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                (string)($_SERVER['REMOTE_ADDR'] ?? '')
            );
            $this->setRememberCookie($selector . ':' . $token);
        }
    }

    private function publicUser(array $user): array
    {
        return [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'display_name' => $user['display_name'] ?: $user['username'],
            'avatar_url' => $user['avatar_url'] ?? null,
            'is_admin' => (int)$user['is_admin'] === 1,
        ];
    }

    private function setRememberCookie(string $value): void
    {
        setcookie('mdp_remember', $value, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => Security::cookiePath($this->config),
            'secure' => Security::isSecureCookie($this->config),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function clearRememberCookie(): void
    {
        setcookie('mdp_remember', '', [
            'expires' => time() - 3600,
            'path' => Security::cookiePath($this->config),
            'secure' => Security::isSecureCookie($this->config),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
