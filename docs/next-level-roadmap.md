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
- Elenco campagne tramite `campaigns/list`.
- Creazione campagna tramite `campaigns/create`.
- Modifica nome e diario tramite `campaigns/update`.
- Attivazione campagna tramite `campaigns/activate`.
- Eliminazione sicura campagna vuota tramite `campaigns/delete`.
- Party / personaggi da `mdp_party_members`.
- CRUD personaggi tramite `party/create`, `party/update`, `party/delete`.
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
- Diario campagna strutturato per eventi separati.
- Tabelle dedicate classi/razze.
- Impostazioni da `cfgSistema`, `cfgLingua`, `cfgUtenti`.

## Migrazione legacy portata

### Party / personaggi

Origine legacy:

- `carica_compagnia.php`
- `compagnia`
- `classi`
- `razze`

Portato nella nuova app:

- elenco personaggi per campagna attiva;
- creazione personaggio;
- modifica personaggio;
- eliminazione solo se il personaggio non è collegato a inventario o combattimenti;
- gestione nome giocatore;
- gestione nome personaggio;
- classe testuale;
- razza/stirpe testuale;
- motto/nota breve;
- bonus iniziativa.

Da rifinire:

- tabelle dedicate classi e razze;
- selezione classe/razza da lookup reale;
- associazione collaborativa utente/personaggio;
- campi scheda avanzati.

### Campagne / diario

Origine legacy:

- `carica_gruppi.php`
- `gruppi`

Portato nella nuova app:

- elenco campagne;
- creazione campagna;
- attivazione campagna;
- modifica nome campagna;
- modifica note/diario master;
- eliminazione solo se la campagna è vuota, per evitare perdita involontaria di party, inventario, monete o combattimenti.

Da rifinire:

- storico eventi strutturato;
- separazione note master / diario pubblico;
- permessi collaborativi su campagna.

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

### 1. Lookup classi / razze

Origine legacy:

- `classi`
- `razze`

Obiettivo nuova app:

- creare tabelle `mdp_classes` e `mdp_ancestries`;
- migrare classi e razze legacy;
- collegare i personaggi a lookup reali;
- mantenere fallback testuale per dati sporchi o personalizzati.

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
