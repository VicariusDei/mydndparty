# API MyDndParty

Backend PHP 8 senza dipendenze esterne, progettato per hosting Aruba basic senza SSH.

## Avvio rapido

1. Copiare `api/config/config.example.php` in `api/config/config.php`.
2. Inserire credenziali database reali nel file `config.php`.
3. Importare `database/schema.sql`. Se il DB esiste gia', importare `database/migrations/002_auth.sql`.
4. Caricare la cartella `api/` via FTP/FTPS.
5. Testare `/api/index.php?route=health`.

## Rotte auth

- `auth/me`
- `auth/register`
- `auth/login`
- `auth/logout`
- `auth/password/forgot`
- `auth/password/reset`
- `auth/google/start`
- `auth/google/callback`

## Rotte dominio iniziali

- `campaigns/list`
- `campaigns/active`
- `campaigns/create`
- `party/list`
- `party/create`

## Configurazione

Il file reale `api/config/config.php` deve contenere credenziali database, URL pubblico dell'app, opzioni cookie/sessione, mittente email e parametri OAuth Google. Vedi `docs/auth-setup.md`.

## Regola progettuale

Ogni modulo deve restituire JSON e non HTML. Il frontend Ionic decide come presentare i dati.
