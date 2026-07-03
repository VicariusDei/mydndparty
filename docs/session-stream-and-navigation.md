# Session stream e navigazione laterale

## Stato

Implementato sviluppo successivo al modulo Sessioni / Note.

## Navigazione

Il bottom menu Ionic è stato rimosso da `TabsLayout.vue`.

La navigazione principale ora usa:

- menu laterale `ion-menu`;
- pulsante hamburger fisso;
- voci router-link compatibili con deploy sotto `/mydndparty/` perché usano le rotte Vue esistenti.

Voci del menu:

- Dashboard;
- Sessioni / Diario;
- Note giocatori;
- Campagne;
- Party;
- Inventario;
- Combattimento;
- Altro.

## Stream cronologico sessione

La pagina `/tabs/sessions` ora include uno stream della sessione selezionata.

Lo stream mostra:

- intestazione sessione;
- stato;
- visibilità;
- data reale;
- data nel mondo;
- numero note collegate;
- riassunto pubblico;
- note master;
- note giocatore collegate alla sessione.

Ogni nota mostra:

- tipo;
- visibilità;
- autore;
- orario;
- contenuto;
- flag master se presente;
- stato correzione se presente.

## Comportamento

La sessione più recente viene selezionata automaticamente.

Ogni card sessione ha un pulsante `Stream` che cambia la sessione visualizzata.

Le note visualizzate nello stream vengono lette dalla rotta `player-notes/list` e filtrate lato frontend in base a `session_id`.

## Limiti

- Lo stream non include ancora eventi timeline da `mdp_timeline_events`.
- La conversione nota -> PNG / luogo / quest / timeline è ancora da implementare.
- Non c'è ancora una pagina dettaglio separata `/tabs/sessions/:id`; per ora lo stream è integrato nella pagina elenco sessioni.

## Prossimo step

Implementare conversione nota in contenuto narrativo:

- nota -> PNG/luogo/fazione/oggetto lore in `mdp_world_entities`;
- nota -> quest in `mdp_quests`;
- nota -> evento timeline in `mdp_timeline_events`;
- aggiornamento `converted_target_type` e `converted_target_id` sulla nota originale.
