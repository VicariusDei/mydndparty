# Analisi iniziale progetto legacy MyDndParty

## Moduli applicativi emersi

Il vecchio progetto è composto da pagine PHP procedurali, CSS separati e un file JavaScript principale. Le logiche sono concentrate in endpoint `carica_*` richiamati dal front-end.

### Autenticazione

File principali:

- `mydndparty.php`
- `process_login.php`
- `registrazione_nospam.php`
- `recupero_password.php`
- `reset_password.php`
- `auth.php`

Meccaniche recuperabili:

- login tramite username o email;
- sessione `loginuser`;
- registrazione con validazione via token;
- recupero password con token temporaneo;
- distinzione admin su tabella `utenti`.

Criticità da non ereditare:

- query SQL concatenate in alcuni punti;
- token generati con `uniqid()`;
- routing frammentato;
- mancanza di CSRF;
- autorizzazioni distribuite nei singoli file.

### Gruppi / campagne

File principale: `carica_gruppi.php`.

Meccaniche recuperabili:

- creazione gruppo;
- modifica diario/appunti;
- gruppo attivo per utente;
- eliminazione gruppo;
- vista elenco gruppi dell'utente.

Entità DB correlate:

- `gruppi`
- `cfgUtenti`
- `utenti`

### Compagnia / personaggi

File principale: `carica_compagnia.php`.

Meccaniche recuperabili:

- inserimento personaggio;
- modifica personaggio;
- cancellazione personaggio;
- associazione a gruppo;
- classe, razza, motto, bonus iniziativa.

Entità DB correlate:

- `compagnia`
- `classi`
- `razze`
- `gruppi`

### Inventario

File principale: `carica_dati.php`.

Meccaniche recuperabili:

- oggetti per utente;
- quantità;
- valore;
- categoria;
- identificato/non identificato;
- note.

Entità DB correlate:

- `inventario`

### Monete

File principale: `carica_monete.php`.

Meccaniche recuperabili:

- aggiungi/togli monete;
- deposito/prelievo;
- conversione in oro;
- calcolo peso;
- riepilogo per tipo moneta.

Entità DB correlate:

- `monete`
- `tipoMonete`

### Combattimento / iniziativa

File principale: `carica_combattimento.php`.

Meccaniche recuperabili:

- creazione nuovo combattimento per gruppo attivo;
- generazione avversari;
- inizializzazione round;
- avanzamento iniziativa;
- nuovo round;
- gestione combattenti lenti;
- applicazione/eliminazione effetti;
- decremento effetti temporanei.

Entità DB correlate:

- `combattimento`
- `round`
- `effetti`
- `dadoIniziativa`
- `compagnia`

## Tabelle individuate nel dump

- `cfgLingua`
- `cfgSistema`
- `cfgUtenti`
- `classi`
- `combattimento`
- `compagnia`
- `dadoIniziativa`
- `effetti`
- `gruppi`
- `inventario`
- `log`
- `logSeverity`
- `monete`
- `razze`
- `resetPassword`
- `round`
- `tipoMonete`
- `utenti`

## Linea guida per la riscrittura

Il legacy va trattato come reference funzionale, non come base tecnica. La nuova app dovrebbe separare chiaramente:

- autenticazione;
- dominio D&D/campagna;
- persistenza dati;
- API interne;
- front-end;
- sicurezza;
- migrazioni DB;
- documentazione.

Primo obiettivo consigliato: ridisegnare il modello dati intorno a `users`, `campaigns`, `party_members`, `inventory_items`, `wallets`, `encounters`, `combatants`, `rounds`, `conditions/effects`.
