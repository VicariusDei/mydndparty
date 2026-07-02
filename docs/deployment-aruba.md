# Deploy su Aruba basic

## Deploy automatico GitHub Actions

Il repository contiene il workflow:

```text
.github/workflows/deploy.yml
```

Il deploy parte automaticamente a ogni push su `main` e puo' essere avviato manualmente da GitHub Actions tramite `workflow_dispatch`.

## Environment GitHub richiesto

Environment:

```text
mydndparty
```

Variabili/segreti richiesti:

```text
FTP_SERVER=ftp.friabili.it
FTP_TARGET_DIR=www.friabili.it/mydndparty/
FTP_USERNAME=<utente FTP>
FTP_PASSWORD=<password FTP>
```

Consigliato:

- `FTP_PASSWORD` come secret;
- `FTP_USERNAME` come secret oppure variable;
- `FTP_SERVER` e `FTP_TARGET_DIR` come environment variables.

## Cosa pubblica il workflow

Il workflow:

1. installa le dipendenze frontend in `app/`;
2. compila Ionic/Vite con base `/mydndparty/`;
3. crea `deploy-package/`;
4. copia dentro il frontend compilato;
5. copia dentro `api/`;
6. esclude `api/config/config.php`;
7. genera `.htaccess` per fallback SPA;
8. pubblica via FTP in `FTP_TARGET_DIR`.

## Config server manuale

Il file seguente non viene pubblicato dal workflow:

```text
api/config/config.php
```

Va creato direttamente sul server Aruba partendo da:

```text
api/config/config.example.php
```

## Database

Importare tramite phpMyAdmin:

```text
database/schema.sql
```

Per test e demo importare anche:

```text
database/seeds/demo.sql
```

## Comandi locali frontend

```bash
cd app
npm install
npm run dev
npm run build
```

## Struttura web su Aruba dopo deploy

```text
/mydndparty/index.html
/mydndparty/assets/
/mydndparty/api/index.php
/mydndparty/api/config/config.php
/mydndparty/api/core/
/mydndparty/api/modules/
```

## Note

Il workflow usa FTP standard. Se Aruba richiede FTPS, modificare lo step `Deploy via FTP` aggiungendo il protocollo supportato dall'azione FTP.
