# API MyDndParty

Backend PHP 8 senza dipendenze esterne, progettato per hosting Aruba basic senza SSH.

## Avvio rapido

1. Copiare `api/config/config.example.php` in `api/config/config.php`.
2. Inserire credenziali database reali nel file `config.php`.
3. Caricare la cartella `api/` via FTP/FTPS.
4. Testare `/api/index.php?route=health`.

## Rotte iniziali

- `health`
- `demo/dashboard`

## Regola progettuale

Ogni modulo deve restituire JSON e non HTML. Il frontend Ionic decide come presentare i dati.
