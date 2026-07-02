# API MyDndParty

Backend PHP 8 senza dipendenze esterne, progettato per hosting Aruba basic senza SSH.

## Avvio rapido

1. Copiare `api/config/config.example.php` in `api/config/config.php`.
2. Inserire credenziali database reali nel file `config.php`.
3. Importare `database/schema.sql` e, per test, `database/seeds/demo.sql`.
4. Caricare la cartella `api/` via FTP/FTPS.
5. Testare `/api/index.php?route=health`.

## Rotte iniziali

- `health`
- `demo/dashboard`
- `campaigns/list`
- `campaigns/active`
- `campaigns/create`
- `party/list`
- `party/create`

## Autenticazione temporanea sviluppo

In ambiente `local`, se `allow_demo_auth` e' attivo, l'API usa `demo_user_id` dal file config. Questo serve solo per testare rapidamente campagne e party prima del modulo login reale.

In produzione questa opzione deve essere disattivata.

## Regola progettuale

Ogni modulo deve restituire JSON e non HTML. Il frontend Ionic decide come presentare i dati.
