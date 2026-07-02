# Setup autenticazione MyDndParty

## Funzioni incluse

- registrazione con email/username/password;
- login con email o username;
- logout;
- sessione PHP sicura;
- remember-me tramite cookie `HttpOnly` e token persistente su `mdp_remember_tokens`;
- recupero password tramite token monouso su `mdp_password_reset_tokens`;
- accesso Google via OpenID Connect server-side.

## Database

Per una installazione nuova importare:

```text
database/schema.sql
```

Se il database era gia' stato creato prima del modulo auth, importare:

```text
database/migrations/002_auth.sql
```

## Configurazione server

Aggiornare manualmente il file non versionato:

```text
api/config/config.php
```

Campi applicativi consigliati:

```php
'app_url' => 'https://www.friabili.it/mydndparty',
'session_secure' => true,
'cookie_path' => '/mydndparty/',
'allow_demo_auth' => false,
```

Configurazione email:

```php
'mail' => [
    'from_email' => 'noreply@friabili.it',
    'from_name' => 'MyDndParty',
],
```

Configurazione Google:

```php
'google' => [
    'client_id' => 'VALORE_DA_GOOGLE_CLOUD',
    'client_key' => 'VALORE_PRIVATO_DA_GOOGLE_CLOUD',
    'redirect_uri' => 'https://www.friabili.it/mydndparty/api/index.php?route=auth/google/callback',
],
```

## Redirect URI Google

Nel client OAuth Google va registrato esattamente questo redirect URI:

```text
https://www.friabili.it/mydndparty/api/index.php?route=auth/google/callback
```

## Rotte API auth

```text
/api/index.php?route=auth/me
/api/index.php?route=auth/register
/api/index.php?route=auth/login
/api/index.php?route=auth/logout
/api/index.php?route=auth/password/forgot
/api/index.php?route=auth/password/reset
/api/index.php?route=auth/google/start
/api/index.php?route=auth/google/callback
```

## Rotte frontend

```text
/login
/register
/forgot-password
/reset-password?token=...
```

## Note operative

`api/config/config.php` non viene caricato dal deploy GitHub Actions e va mantenuto direttamente sul server Aruba.

Il reset password usa `mail()` PHP. Se Aruba applica restrizioni SMTP o mittente, va adeguata la configurazione email del dominio.
