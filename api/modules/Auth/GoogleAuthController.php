<?php

final class GoogleAuthController
{
    private const AUTH_ENDPOINT = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    private const USERINFO_ENDPOINT = 'https://openidconnect.googleapis.com/v1/userinfo';

    public function __construct(
        private AuthRepository $users,
        private AuthController $auth,
        private array $config
    ) {
    }

    public function start(): void
    {
        $google = $this->googleConfig();
        if ($google['client_id'] === '' || $google['client_key'] === '' || $google['redirect_uri'] === '') {
            Response::error('Google OAuth non configurato', 500);
        }

        $state = Security::token(24);
        $nonce = Security::token(24);
        $_SESSION['google_oauth_state'] = $state;
        $_SESSION['google_oauth_nonce'] = $nonce;

        $query = http_build_query([
            'client_id' => $google['client_id'],
            'redirect_uri' => $google['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'nonce' => $nonce,
            'prompt' => 'select_account',
        ]);

        header('Location: ' . self::AUTH_ENDPOINT . '?' . $query, true, 302);
        exit;
    }

    public function callback(): void
    {
        $state = (string)($_GET['state'] ?? '');
        $code = (string)($_GET['code'] ?? '');
        $expectedState = (string)($_SESSION['google_oauth_state'] ?? '');

        unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_nonce']);

        if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
            $this->redirectWithError('google_state');
        }
        if ($code === '') {
            $this->redirectWithError('google_code');
        }

        $google = $this->googleConfig();
        $tokenResponse = HttpClient::postForm(self::TOKEN_ENDPOINT, [
            'code' => $code,
            'client_id' => $google['client_id'],
            'client_secret' => $google['client_key'],
            'redirect_uri' => $google['redirect_uri'],
            'grant_type' => 'authorization_code',
        ]);

        if ($tokenResponse['status'] < 200 || $tokenResponse['status'] >= 300 || empty($tokenResponse['json']['access_token'])) {
            $this->redirectWithError('google_token');
        }

        $accessToken = (string)$tokenResponse['json']['access_token'];
        $profileResponse = HttpClient::getJson(self::USERINFO_ENDPOINT, [
            'Authorization: Bearer ' . $accessToken,
        ]);

        if ($profileResponse['status'] < 200 || $profileResponse['status'] >= 300 || empty($profileResponse['json']['sub']) || empty($profileResponse['json']['email'])) {
            $this->redirectWithError('google_profile');
        }

        $profile = $profileResponse['json'];
        if (isset($profile['email_verified']) && !$profile['email_verified']) {
            $this->redirectWithError('google_email_not_verified');
        }

        $user = $this->users->upsertGoogleUser($profile);
        $this->auth->loginUser((int)$user['id'], true);

        header('Location: ' . Security::appUrl($this->config) . '/tabs/dashboard', true, 302);
        exit;
    }

    private function googleConfig(): array
    {
        $google = $this->config['google'] ?? [];
        return [
            'client_id' => (string)($google['client_id'] ?? ''),
            'client_key' => (string)($google['client_key'] ?? ($google['client_secret'] ?? '')),
            'redirect_uri' => (string)($google['redirect_uri'] ?? ''),
        ];
    }

    private function redirectWithError(string $code): void
    {
        header('Location: ' . Security::appUrl($this->config) . '/login?error=' . urlencode($code), true, 302);
        exit;
    }
}
