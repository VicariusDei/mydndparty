# Campagne nel gruppo - stato implementazione

## Stato

Prima fase implementata.

Una campagna può ora nascere dentro un gruppo di gioco e avere partecipanti con ruoli contestuali.

## API

Rotte aggiunte:

```text
group-campaigns/list
group-campaigns/create
group-campaigns/participants
group-campaigns/participant/add
```

## Frontend

La pagina esistente è stata estesa:

```text
/tabs/groups
```

Ora permette anche di:

- creare una campagna dentro il gruppo selezionato;
- vedere le campagne del gruppo;
- selezionare una campagna;
- vedere i partecipanti della campagna;
- aggiungere un membro del gruppo alla campagna;
- assegnare ruolo campagna.

## Regole implementate

### Creazione campagna

Qualsiasi membro attivo del gruppo può aprire una campagna nel gruppo.

Quando la campagna viene creata:

1. viene inserita in `mdp_campaigns`;
2. viene collegata al gruppo tramite `mdp_game_group_campaigns`;
3. il creatore viene inserito in `mdp_campaign_participants` come `master`.

### Partecipanti campagna

Solo `master` e `co_master` della campagna possono aggiungere partecipanti.

Un partecipante deve essere già membro attivo del gruppo di gioco.

Ruoli campagna disponibili:

- `master`;
- `co_master`;
- `player`;
- `viewer`.

## Modello risultante

```text
Utente
  -> membro di Gruppo
      -> può aprire Campagna
          -> diventa Master di quella Campagna
          -> aggiunge Partecipanti dal Gruppo
```

Lo stesso utente può essere master in una campagna e giocatore in un'altra.

## Limiti attuali

- La campagna creata nel gruppo non viene ancora resa automaticamente campagna attiva nella dashboard legacy.
- Le vecchie API `campaigns/list`, `campaigns/active` leggono ancora principalmente `owner_user_id`.
- Non c'è ancora collegamento partecipante -> personaggio dalla UI.
- Non c'è ancora rimozione/sospensione partecipanti.
- Non ci sono ancora permessi applicativi profondi su note, sessioni, inventario e asset basati su `mdp_campaign_participants`.

## Prossimo step tecnico

Aggiornare il concetto di campagna attiva:

- l'utente deve vedere anche campagne dove è partecipante, non solo owner;
- deve poter scegliere una campagna attiva tra quelle in cui è `master`, `co_master`, `player` o `viewer`;
- dashboard, note, sessioni, party e inventario devono usare la campagna attiva partecipata;
- i permessi vanno applicati in base al ruolo campagna.

Dopo questo step, implementare:

- collegamento partecipante -> personaggio;
- calendario sessioni;
- materiali campagna.
