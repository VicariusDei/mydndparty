# Architettura nuova app

## Obiettivo

Riscrivere MyDndParty partendo dal legacy solo come reference funzionale.

La nuova app viene progettata per hosting Aruba basic senza SSH, con priorita' alla qualita' grafica e alla semplicita' di deploy.

## Stack scelto

- Frontend: Ionic Vue + TypeScript
- Backend: PHP 8 custom micro-API
- Database: MySQL
- Deploy: build locale + FTP/FTPS

## Struttura repository

```text
app/          sorgente frontend Ionic Vue
api/          backend PHP JSON API compatibile hosting basic
database/     schema, migrations e seed
docs/         documentazione progettuale
legacy/       progetto storico solo come riferimento
```

## Principio tecnico

Il frontend e' il prodotto principale e gestisce esperienza, navigazione, layout e componenti.

Il backend espone solo API JSON, valida le richieste, applica i permessi e legge/scrive su MySQL.

## Moduli funzionali

- Auth
- Campagne
- Party / personaggi
- Inventario
- Monete
- Combattimento
- Round
- Effetti
- Impostazioni

## Vincoli Aruba basic

- nessun comando SSH sul server
- nessun composer install sul server
- nessun npm install sul server
- nessuna migration runtime obbligatoria
- config PHP manuale non versionata
- import SQL via phpMyAdmin
