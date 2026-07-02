# Deploy su Aruba basic

## Flusso previsto

1. Sviluppare localmente il frontend in `app/`.
2. Eseguire la build locale.
3. Caricare via FTP/FTPS il contenuto generato dal frontend nella root web oppure nella cartella indicata dal piano hosting.
4. Caricare la cartella `api/` nella stessa root web.
5. Creare manualmente `api/config/config.php` partendo da `api/config/config.example.php`.
6. Importare `database/schema.sql` tramite phpMyAdmin.

## Comandi locali frontend

```bash
cd app
npm install
npm run dev
npm run build
```

## Struttura web consigliata su Aruba

```text
/index.html
/assets/
/api/index.php
/api/config/config.php
/api/core/
/api/modules/
```

## File da non caricare pubblicamente da Git

`api/config/config.php` contiene credenziali reali e non deve essere versionato.

## Prima pubblicazione

Per la prima pubblicazione e' sufficiente caricare:

- build frontend generata localmente;
- cartella `api/`;
- file `api/config/config.php` compilato direttamente sul server;
- database creato da `database/schema.sql`.
