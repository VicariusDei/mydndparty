# MyDndParty

Riscrittura del vecchio progetto PHP per la gestione di party e campagne D&D.

Il legacy resta disponibile come reference funzionale, ma la nuova app nasce con una struttura separata: frontend mobile-first, API PHP leggere e database MySQL normalizzato.

## Stack scelto

- Frontend: Ionic Vue + TypeScript
- Backend: PHP 8 custom micro-API
- Database: MySQL / MariaDB
- Hosting target: Aruba basic senza SSH
- Deploy: build locale + FTP/FTPS

## Struttura repository

```text
app/          sorgente frontend Ionic Vue
api/          backend PHP JSON API senza dipendenze server
database/     schema nuova app
docs/         architettura, deploy e analisi
legacy/       vecchio progetto solo come riferimento
```

## Avvio frontend locale

```bash
cd app
npm install
npm run dev
```

## Build frontend

```bash
cd app
npm run build
```

La build viene generata in `dist/`, ignorata da Git e caricabile via FTP su Aruba.

## API locale / server

Copiare:

```text
api/config/config.example.php
```

in:

```text
api/config/config.php
```

poi compilare le credenziali del database. Il file reale `config.php` non viene versionato.

## Rotte iniziali API

```text
/api/index.php?route=health
/api/index.php?route=demo/dashboard
```

## Documentazione

- `docs/architecture.md`
- `docs/deployment-aruba.md`
- `docs/legacy-analysis.md`

## Principio di lavoro

Il codice legacy non va evoluto direttamente. Serve a recuperare logiche e meccaniche: campagne, party, inventario, monete, iniziativa, round ed effetti.
