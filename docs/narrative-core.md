# MyDndParty - Narrative Core

## Obiettivo

MyDndParty resta il nome dell'app, ma il dominio funzionale non deve dipendere da un singolo regolamento.

Il centro del sistema diventa la memoria della campagna:

- sessioni;
- diario;
- timeline;
- PNG;
- luoghi;
- fazioni;
- missioni;
- eventi;
- note rapide;
- collegamenti tra elementi narrativi.

Le regole specifiche restano modulari e opzionali.

## Migration

File:

```text
/database/migrations/2026_07_03_narrative_core.sql
```

La migration aggiunge solo nuove tabelle e non modifica dati esistenti.

## Tabelle principali

### `mdp_campaign_settings`

Configura la campagna in modo agnostico:

- regolamento dichiarativo (`ruleset_code`, `ruleset_name`);
- visibilità predefinita;
- consenso alle note rapide dei giocatori;
- moderazione note rapide.

### `mdp_sessions`

Rappresenta una sessione giocata.

Campi chiave:

- numero sessione;
- titolo;
- data reale;
- data nel mondo;
- riassunto;
- note master;
- stato bozza/pubblicata/archiviata;
- visibilità.

### `mdp_world_entities`

Archivio mondo agnostico.

Tipi previsti:

- `npc`;
- `place`;
- `faction`;
- `object`;
- `lore`;
- `event`;
- `creature`;
- `organization`;
- `other`.

Ogni entità ha contenuto pubblico e note segrete master.

### `mdp_quests`

Trame, missioni e fili narrativi.

Stati:

- aperta;
- sospesa;
- completata;
- fallita;
- abbandonata.

### `mdp_timeline_events`

Linea temporale della campagna.

Un evento può essere collegato a una sessione e può rappresentare:

- scoperta;
- battaglia;
- morte;
- viaggio;
- missione;
- relazione;
- lore;
- loot;
- evento custom.

### `mdp_entity_links`

Tabella trasversale per collegare tutto.

Esempi:

- sessione -> PNG;
- sessione -> luogo;
- quest -> fazione;
- personaggio -> oggetto;
- timeline event -> sessione;
- encounter -> luogo.

Non usa foreign key polimorfiche perché i target possono appartenere a tabelle diverse. La coerenza verrà gestita dall'applicazione.

## Input rapido dei giocatori

Il problema da risolvere è: i giocatori devono poter appuntare qualcosa subito, senza trasformare la sessione in uso costante del telefono.

Sono previsti due canali.

### QR temporaneo

Tabelle:

- `mdp_quick_access_tokens`;
- `mdp_quick_notes`.

Flusso consigliato:

1. Il master apre una sessione.
2. L'app genera un QR valido, per esempio, 15 o 30 minuti.
3. Il giocatore scansiona il QR.
4. Si apre una pagina minimale, senza login.
5. Il giocatore inserisce una nota rapida.
6. La nota entra in stato `pending`.
7. Il master la accetta, la rifiuta o la converte in PNG, luogo, quest, loot o evento timeline.

Uso ideale:

- una finestra breve a metà sessione;
- una finestra a fine sessione;
- non accesso permanente.

### Bot Telegram / WhatsApp

Tabelle:

- `mdp_bot_identities`;
- `mdp_quick_notes`.

Il bot non deve scrivere direttamente nel diario definitivo. Deve creare note rapide da revisionare.

Flusso:

1. Il giocatore manda un messaggio al bot.
2. Il bot identifica campagna e giocatore.
3. Il messaggio entra in `mdp_quick_notes` con canale `telegram` o `whatsapp`.
4. Il master converte la nota quando opportuno.

## Strategia consigliata per il tavolo

La modalità più coerente è il QR temporaneo.

Motivi:

- non richiede account;
- non richiede installare bot;
- non incentiva chat continua;
- si controlla con scadenza;
- può essere mostrato solo nei momenti stabiliti dal master;
- funziona bene da telefono.

Il bot è utile in secondo momento per gruppi che vogliono asincronia tra una sessione e l'altra.

## Stati delle note rapide

`mdp_quick_notes.status`:

- `pending`: nota ricevuta, non ancora revisionata;
- `accepted`: nota valida ma non convertita;
- `rejected`: scartata;
- `converted`: trasformata in oggetto narrativo definitivo.

`converted_target_type` permette di sapere in cosa è stata trasformata:

- sessione;
- entity;
- quest;
- timeline_event;
- inventory_item;
- none.

## Campi custom e regolamenti

Per evitare di inseguire tutti i regolamenti, sono state aggiunte:

- `mdp_custom_fields`;
- `mdp_custom_field_values`.

Esempi:

D&D:

- livello;
- classe;
- CA;
- PF;
- iniziativa.

Vampiri:

- clan;
- generazione;
- umanità.

Not the End:

- tratti;
- risorse;
- complicazioni.

Questa struttura permette a ogni campagna di avere campi propri senza introdurre subito plugin regolamento-specifici.

## Roadmap tecnica immediata

1. Importare la migration via phpMyAdmin.
2. Creare API `sessions/*`.
3. Creare pagina `Sessioni / Diario`.
4. Mostrare ultima sessione in dashboard.
5. Creare API `quick-notes/*`.
6. Generare QR temporaneo per sessione.
7. Creare inbox master per convertire le note rapide.
8. Creare archivio mondo: PNG, luoghi, fazioni.
