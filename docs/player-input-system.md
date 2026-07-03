# Sistema input giocatori e gioco off-sessione

## Premessa

Il problema non è solo tecnico. Al tavolo l'app deve raccogliere memoria senza spostare il centro dell'attenzione sui telefoni.

Quindi MyDndParty deve supportare più modalità di input, ma con una regola di prodotto chiara:

- durante la sessione: input rapido, breve, contestuale;
- dopo la sessione: rifinitura, commenti, gioco asincrono;
- il diario deve restare cronologico e correggibile.

## Cambio rispetto alla prima ipotesi

Le note dei giocatori non devono entrare sempre in moderazione preventiva.

Nuova regola:

- una nota del giocatore è visibile subito secondo la visibilità scelta;
- il master può correggerla, nasconderla, convertirla o marcarla dopo;
- ogni correzione conserva revisione/storico.

Per questo sono state aggiunte nuove tabelle dedicate:

- `mdp_player_notes`;
- `mdp_player_note_recipients`;
- `mdp_player_note_revisions`.

Le vecchie `mdp_quick_notes` restano utili per input anonimo o QR senza login, ma non sono più il canale principale delle note firmate dai giocatori.

## Visibilità

Ogni nota o thread usa `share_scope`:

- `party`: visibile a tutto il party;
- `private`: visibile solo all'autore e al master;
- `restricted`: visibile solo a destinatari scelti;
- `master`: visibile solo al master;
- `public_readonly`: visibile a chi ha link pubblico/read-only, in futuro.

Per `restricted` si usano tabelle destinatari:

- `mdp_player_note_recipients`;
- `mdp_offsession_thread_recipients`.

I destinatari possono essere:

- utenti registrati;
- personaggi del party.

Questo permette scene private anche quando non tutti i giocatori hanno ancora un account strutturato.

## Note durante la sessione

Tabella principale:

- `mdp_player_notes`.

Esempi di nota:

- appunto generico;
- PNG incontrato;
- luogo;
- quest;
- loot;
- domanda regole;
- decisione del party;
- idea;
- scena.

La nota viene ordinata cronologicamente con `created_at` e può essere collegata a una sessione tramite `session_id`.

### Stati nota

- `visible`: visibile subito;
- `hidden`: nascosta dal master;
- `corrected`: corretta dal master o dall'autore;
- `converted`: trasformata in elemento definitivo;
- `deleted`: eliminata logicamente.

### Flag master

- `none`;
- `needs_review`;
- `verified`;
- `spoiler`;
- `incorrect`.

Il master non deve bloccare il flusso. Deve poter intervenire dopo.

## Gioco off-sessione

Tabelle:

- `mdp_offsession_threads`;
- `mdp_offsession_thread_recipients`;
- `mdp_offsession_messages`;
- `mdp_offsession_message_revisions`.

Obiettivo: sostituire WhatsApp per tutto ciò che riguarda la campagna.

Tipi thread:

- `roleplay`: scene interpretative asincrone;
- `planning`: piani del party;
- `recap`: riepiloghi;
- `rules`: dubbi regole;
- `loot`: distribuzione tesori;
- `quest`: discussione su missioni;
- `logistics`: organizzazione date/orari;
- `private_scene`: scena privata;
- `other`.

Ogni thread può essere collegato a una sessione, così il flusso resta cronologicamente coerente.

## Opzioni di input

### 1. Web app completa

Uso:

- giocatore autenticato;
- dashboard personale;
- note, thread, personaggio, diario.

Pro:

- controllo pieno;
- permessi robusti;
- cronologia pulita;
- nessuna dipendenza esterna.

Contro:

- richiede login;
- più attrito durante la sessione.

Uso consigliato:

- fuori sessione;
- preparazione;
- consultazione;
- gioco asincrono.

### 2. Quick capture da QR temporaneo

Uso:

- il master genera un QR per la sessione;
- il link scade dopo 10/15/30 minuti;
- il giocatore inserisce nota senza login oppure con identificazione leggera.

Pro:

- attrito minimo;
- ottimo al tavolo;
- non richiede installazioni;
- il master può decidere quando aprire la finestra input.

Contro:

- identificazione più debole se senza login;
- da usare con token a scadenza e limite usi.

Uso consigliato:

- fine scena;
- pausa;
- fine sessione;
- raccolta rapida loot/decisioni/domande.

### 3. Modalità “kiosk master”

Uso:

- un solo dispositivo aperto, gestito dal master o da un segretario di sessione;
- i giocatori dettano o passano brevi note;
- chi scrive seleziona autore e visibilità.

Pro:

- niente telefoni al tavolo;
- controllo massimo;
- ideale se il gruppo vuole restare analogico.

Contro:

- carica una persona del lavoro di scrittura;
- meno spontaneo.

Uso consigliato:

- gruppi che non vogliono smartphone durante il gioco;
- sessioni dense;
- campagne investigative.

### 4. Bot Telegram

Uso:

- ogni giocatore collega Telegram al proprio utente/personaggio;
- invia note o messaggi al bot;
- il sistema salva su MyDndParty.

Pro:

- veloce;
- familiare;
- ottimo fuori sessione;
- buono per messaggi asincroni.

Contro:

- può incentivare chat parallela;
- richiede gestione bot/webhook;
- dipendenza esterna.

Uso consigliato:

- off-sessione;
- promemoria;
- domande tra sessioni;
- scene private asincrone.

### 5. Bot WhatsApp

Uso simile a Telegram.

Pro:

- tutti lo hanno già.

Contro:

- integrazione più onerosa;
- API meno comode;
- rischio di tornare mentalmente a “chat WhatsApp”.

Uso consigliato:

- solo se Telegram viene escluso dal gruppo.

### 6. Email-in

Uso:

- indirizzo tipo campagna+token@mydndparty;
- il giocatore manda una mail;
- il sistema importa come nota o thread.

Pro:

- asincrono;
- buono per recap lunghi;
- nessun bot.

Contro:

- lento;
- poco adatto al tavolo;
- parsing allegati complesso.

Uso consigliato:

- recap post-sessione;
- background lunghi;
- downtime narrativo.

### 7. Voice memo controllato

Uso:

- il giocatore registra una nota vocale breve;
- il master o un modulo futuro la trascrive;
- entra come nota.

Pro:

- molto rapido;
- meno digitazione al tavolo.

Contro:

- trascrizione da implementare;
- audio potenzialmente rumoroso;
- privacy.

Uso consigliato:

- non come primo sviluppo;
- utile in futuro per accessibilità.

### 8. Session scribe

Ruolo esplicito assegnato a rotazione.

Uso:

- a ogni sessione un giocatore è “cronista”;
- prende note direttamente in MyDndParty;
- gli altri aggiungono correzioni rapide.

Pro:

- socialmente sostenibile;
- ottima qualità del diario;
- riduce rumore digitale.

Contro:

- richiede disciplina;
- può pesare a qualcuno.

Uso consigliato:

- come pratica di gruppo, non solo funzione software.

## Strategia consigliata

Implementare in quest'ordine:

### Fase 1 — Note giocatore web

- pagina minimale “Aggiungi nota”;
- scelta sessione;
- scelta visibilità;
- destinatari se restricted;
- visibile subito;
- correzione master con storico.

### Fase 2 — Diario sessione

- stream cronologico sessione;
- note giocatori integrate;
- filtro per visibilità;
- master flag;
- conversione nota -> PNG/luogo/quest/timeline.

### Fase 3 — Off-sessione interno

- thread campagna;
- messaggi cronologici;
- visibilità party/private/restricted;
- collegamento a sessione o quest.

### Fase 4 — QR temporaneo

- apertura finestra input breve;
- link senza login o login leggero;
- token a scadenza;
- nota subito visibile se il token è trusted, oppure marcata `needs_review`.

### Fase 5 — Bot Telegram

- bridge asincrono;
- non sostituisce l'app;
- salva in note o thread in base a comando/canale.

## Regola di tavolo consigliata

Per evitare abuso del telefono:

- durante le scene: telefoni giù;
- a fine scena o pausa: 2 minuti di “log phase”;
- chi ha una nota la inserisce;
- il master può aprire un QR temporaneo;
- a fine sessione il cronista o il master pubblica il recap.

Questa regola rispetta il gioco al tavolo ma impedisce la perdita di informazioni.
