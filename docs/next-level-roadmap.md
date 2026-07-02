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
- Portafoglio da `mdp_wallets` e `mdp_coin_types`.
- Encounter e iniziativa da `mdp_encounters` e `mdp_combatants`.
- Dashboard summary tramite `dashboard/summary`.

### Presente ma non ancora attivo

- Messaggi.
- Richieste amicizia.
- Ricerca globale.
- Dado rapido.
- Nuovo round / avanza turno.
- Gestione completa effetti.
- Diario campagna strutturato.
- Impostazioni da `cfgSistema`, `cfgLingua`, `cfgUtenti`.

## Migrazione legacy: priorità

### 1. Combattimento / iniziativa

Origine legacy:

- `carica_combattimento.php`
- `combattimento`
- `round`
- `effetti`
- `dadoIniziativa`
- `compagnia`

Obiettivo nuova app:

- elenco encounter per campagna;
- creazione encounter;
- aggiunta PG dal party;
- aggiunta avversari manuali;
- ordinamento iniziativa;
- gestione combattenti lenti;
- nuovo round;
- avanzamento turno;
- applicazione/rimozione effetti;
- decremento automatico effetti temporanei.

### 2. Inventario e monete

Origine legacy:

- `carica_dati.php`
- `carica_monete.php`
- `inventario`
- `monete`
- `tipoMonete`

Obiettivo nuova app:

- CRUD oggetti;
- identificato/non identificato;
- categorie;
- assegnazione a party o personaggio;
- modifica quantità;
- deposito/prelievo monete;
- calcolo valore totale in oro;
- calcolo peso.

### 3. Campagne / diario

Origine legacy:

- `carica_gruppi.php`
- `gruppi`

Obiettivo nuova app:

- selezione campagna attiva;
- diario campagna;
- note master;
- storico eventi reali.

### 4. Social minimo

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

## Prossimo sviluppo consigliato

Implementare il modulo combattimento completo perché è il cuore del vecchio progetto e ha già dati migrati.

Primo micro-rilascio:

1. API `combat/create`;
2. API `combat/add-party-member`;
3. API `combat/add-combatant`;
4. API `combat/next-turn`;
5. API `combat/new-round`;
6. API `combat/effect/add`;
7. API `combat/effect/remove`;
8. aggiornamento `CombatPage.vue` con controlli reali.
