# Player Notes - stato implementazione

## Stato

Prima fase implementata.

La funzione permette di inserire note firmate e subito visibili nella campagna attiva.

## API

Rotte aggiunte:

```text
player-notes/list
player-notes/create
player-notes/update
player-notes/delete
```

## Frontend

Pagina aggiunta:

```text
/tabs/notes
```

Accessibile dal bottom menu come voce rapida `Note`.

## Funzioni disponibili

- creazione nota;
- lista cronologica note;
- tipo nota: nota, PNG, luogo, quest, loot, domanda, regole, idea, scena, decisione;
- visibilità: party, privata, ristretta, solo master;
- destinatari per note ristrette;
- autore associabile a un personaggio del party;
- flag master: da rivedere, verificata, spoiler, errata;
- correzione nota con salvataggio revisione;
- cancellazione logica.

## Comportamento

Le note vengono salvate in `mdp_player_notes` con stato `visible`.

Non entrano in moderazione preventiva.

Il master può correggere o nascondere dopo. Ogni correzione salva lo stato precedente in `mdp_player_note_revisions`.

## Limiti della prima versione

- La visibilità viene salvata ma la separazione reale tra utenti/giocatori sarà pienamente efficace dopo la gestione collaboratori campagna.
- Le note non sono ancora collegate a una sessione specifica perché manca la pagina Sessioni.
- La conversione nota -> PNG/luogo/quest/timeline è prevista dal DB ma non ancora implementata in UI.
- QR temporaneo e bot non sono ancora implementati.
- Off-sessione è modellato nel DB ma non ancora esposto in API/UI.

## Prossimo step tecnico

Implementare `mdp_sessions`:

- API sessioni;
- pagina Sessioni / Diario;
- selezione sessione nella nota;
- stream cronologico note + sessioni;
- dashboard con ultima sessione reale.
