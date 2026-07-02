# Roadmap funzionale MyDndParty

## Principio guida

La dashboard e i menu non devono mostrare dati fittizi. Ogni sezione deve rispettare una di queste condizioni:

1. legge dati reali dal database tramite API;
2. mostra uno stato vuoto reale;
3. dichiara esplicitamente che il modulo non è ancora attivo.

## Stato attuale

### Attivo su dati reali

- Login e sessione utente.
- Campagna attiva.
- Party / personaggi da `mdp_party_members`.
- Inventario da `mdp_inventory_items`.
- CRUD oggetti tramite `inventory/create`, `inventory/update`, `inventory/delete`.
- Portafoglio da `mdp_wallets` e `mdp_coin_types`.
- Aggiornamento monete tramite `inventory/wallet/adjust` e `inventory/wallet/update`.
- Encounter e iniziativa da `mdp_encounters` e `mdp_combatants`.
- Azioni combattimento tramite `combat/create`, `combat/activate`, `combat/add-party-member`, `combat/add-combatant`, `combat/next-turn`, `combat/new-round`, `combat/effect/add`, `combat/effect/remove`.
- Dashboard summary tramite `dashboard/summary`.

### Presente ma non ancora attivo

- Messaggi.
- Richieste amicizia.
- Ricerca globale.
- Dado rapido.
- Diario campagna strutturato.
- Impostazioni da `cfgSistema`, `cfgLingua`, `cfgUtenti`.

## Migrazione legacy completata parzialmente

### Combattimento / iniziativa

Origine legacy:

- `carica_combattimento.php`
- `combattimento`
- `round`
- `effetti`
- `dadoIniziativa`
- `compagnia`

Portato nella nuova app:

- elenco encounter per campagna;
- creazione encounter;
- attivazione encounter;
- aggiunta PG dal party;
- aggiunta avversari manuali;
- ordinamento iniziativa;
- gestione combattenti lenti;
- nuovo round;
- avanzamento turno;
- applicazione/rimozione effetti;
- decremento automatico effetti temporanei.

### Inventario e monete

Origine legacy:

- `carica_dati.php`
- `carica_monete.php`
- `inventario`
- `monete`
- `tipoMonete`

Portato nella nuova app:

- CRUD oggetti;
- identificato/non identificato;
- categorie;
- assegnazione a party o personaggio;
- modifica quantità;
- modifica valore;
- note oggetto;
- deposito/prelievo monete tramite delta;
- aggiornamento diretto quantità/deposito via API.

Da rifinire:

- calcolo valore totale in oro;
- calcolo peso monete;
- azioni massive su monete;
- filtri e ricerca inventario.

## Prossime priorità

### 1. Campagne / diario

Origine legacy:

- `carica_gruppi.php`
- `gruppi`

Obiettivo nuova app:

- selezione campagna attiva;
- modifica diario campagna;
- note master;
- storico eventi reali.

### 2. Social minimo

Nuove tabelle da progettare:

- `mdp_friend_requests`
- `mdp_friendships`
- `mdp_messages`
- `mdp_notifications`

Obiettivo nuova app:

- richieste amicizia reali;
- messaggi reali;
- notifiche dashboard reali;
- nessun badge hardcoded.
