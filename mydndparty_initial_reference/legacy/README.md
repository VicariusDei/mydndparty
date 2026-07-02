# Legacy MyDndParty

Questa cartella contiene il vecchio progetto PHP da usare solo come sorgente di analisi per la riscrittura dell'app.

Non è codice da rilanciare direttamente in produzione. Serve a recuperare logiche, meccaniche, nomi delle entità, flussi utente e modello dati.

## Contenuto

- `original-php/`: file PHP, CSS e JavaScript estratti dallo ZIP originale.
- `database/schema_sanitized.sql`: schema MySQL derivato dal dump, con dati sensibili rimossi.
- `original-php/dbConfig.example.php`: template non operativo per ricordare il file di configurazione mancante.

## Dati esclusi o sanificati

Il dump originale conteneva dati utente, password hash, token di attivazione/reset, log applicativi, gruppi, personaggi, inventario e stato combattimenti. Nel repository pubblico questi dati non sono stati caricati.

## Uso previsto

La nuova app dovrà attingere da qui solo per:

- modello concettuale del party;
- gestione personaggi e compagnia;
- gestione gruppi/campagne;
- inventario;
- monete e deposito;
- iniziativa e combattimento;
- effetti temporanei/permanenti;
- impostazioni utente come dado iniziativa, lingua e sistema.
