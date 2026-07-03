# Sessioni / Diario - stato implementazione

## Stato

Prima fase implementata.

Il modulo permette di creare e gestire sessioni di campagna e di collegare le note giocatore a una sessione specifica.

## API

Rotte aggiunte:

```text
sessions/list
sessions/create
sessions/update
sessions/delete
```

## Frontend

Pagina aggiunta:

```text
/tabs/sessions
```

Accessibile da:

- dashboard side menu;
- dashboard hero actions;
- pagina Altro.

## Funzioni disponibili

- elenco sessioni della campagna attiva;
- creazione sessione;
- numero progressivo automatico;
- modifica sessione;
- eliminazione solo se la sessione non ha contenuti collegati;
- data reale;
- data nel mondo;
- riassunto pubblico;
- note master;
- stato: bozza, pubblicata, archiviata;
- visibilità: party, master, privata, custom;
- conteggio note collegate.

## Integrazione Player Notes

La pagina `/tabs/notes` ora permette di scegliere la sessione associata alla nota.

Le note mostrano, quando presente:

```text
Sessione #N · Titolo sessione
```

Se esiste una sessione recente, viene preselezionata automaticamente nella form note.

## Dashboard

La dashboard ora mostra:

- conteggio sessioni;
- ultima sessione reale;
- riassunto ultima sessione se presente;
- link rapido a Sessioni;
- link rapido a Note.

## Limiti della prima versione

- Non esiste ancora una vista unica “stream sessione” con sessione + note + eventi timeline.
- La conversione nota -> PNG / luogo / quest / timeline non è ancora implementata.
- Le note master sono salvate, ma la separazione permessi completa richiederà gestione collaboratori campagna.
- La timeline usa ancora solo la struttura DB, non ha UI.

## Prossimo step tecnico

Implementare lo stream cronologico della sessione:

- dettaglio sessione;
- note collegate alla sessione;
- eventi timeline;
- filtri per visibilità e tipo;
- pulsanti converti nota in PNG/luogo/quest/evento.
