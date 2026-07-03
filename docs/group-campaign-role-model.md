# Modello gruppi, campagne e ruoli contestuali

## Principio

MyDndParty non deve separare rigidamente master e giocatori.

Un account è neutro.

Lo stesso utente può essere:

- master di una campagna;
- giocatore in un'altra campagna;
- co-master in una terza;
- semplice membro di un gruppo di gioco.

Il ruolo non appartiene all'utente in senso globale. Appartiene al contesto.

## Gerarchia funzionale

```text
Account utente
  -> Gruppo di gioco
      -> Campagna
          -> Partecipanti campagna
              -> eventuale personaggio
```

## Account

Tabella esistente:

```text
mdp_users
```

Rappresenta una persona reale iscritta al portale.

Non è master o player in assoluto.

## Gruppo di gioco

Nuova tabella:

```text
mdp_game_groups
```

Rappresenta il gruppo sociale stabile: per esempio il gruppo del martedì sera, la compagnia storica, il tavolo online, ecc.

Un utente crea o entra in un gruppo tramite username/invito.

## Membri del gruppo

Nuova tabella:

```text
mdp_game_group_members
```

Dice quali account fanno parte di un gruppo.

Ruoli nel gruppo:

- `owner`: chi ha creato/amministra il gruppo;
- `admin`: può aiutare nella gestione;
- `member`: membro normale.

Questi ruoli sono amministrativi del gruppo, non ruoli narrativi.

## Campagne dentro il gruppo

Nuova tabella:

```text
mdp_game_group_campaigns
```

Collega una campagna a un gruppo di gioco.

La campagna resta in `mdp_campaigns`, ma il suo contesto sociale è il gruppo.

Un membro del gruppo può aprire una campagna. In quella campagna diventa master.

## Partecipanti della campagna

Nuova tabella:

```text
mdp_campaign_participants
```

Dice chi partecipa a una specifica campagna e con quale ruolo.

Ruoli campagna:

- `master`;
- `co_master`;
- `player`;
- `viewer`.

Esempio:

```text
Utente Marco
- nel gruppo: member
- nella campagna A: master
- nella campagna B: player
```

Questo risolve il vincolo master/player globale.

## Personaggi

Tabella esistente:

```text
mdp_party_members
```

Rappresenta personaggi, PNG o membri del party.

Un partecipante campagna può essere collegato a un personaggio tramite:

```text
mdp_campaign_participants.party_member_id
```

Il collegamento è opzionale.

Serve perché un utente può partecipare senza avere ancora un personaggio, oppure può fare il master senza personaggio.

## Materiali campagna

Nuova tabella:

```text
mdp_campaign_assets
```

Il master può caricare o collegare materiali:

- audio;
- video;
- immagini;
- PDF;
- documenti;
- link esterni;
- altro.

Ogni asset può essere collegato:

- alla campagna;
- opzionalmente a una sessione.

Ha visibilità propria:

- party;
- master;
- private;
- restricted;
- public_readonly.

## Calendario sessioni

Tabella esistente:

```text
mdp_sessions
```

contiene già la sessione narrativa.

Nuova tabella:

```text
mdp_session_calendar
```

contiene gli aspetti calendario:

- data/ora inizio;
- data/ora fine;
- timezone;
- luogo;
- link posizione;
- stato: planned, confirmed, cancelled, played.

## Abbinamento automatico note/sessione

Il campo resta:

```text
mdp_player_notes.session_id
```

È un attributo della nota, non un vincolo funzionale pesante.

Regola UI suggerita:

1. Se oggi esiste una sessione calendarizzata per la campagna, la nota viene proposta su quella sessione.
2. Se non esiste una sessione oggi, viene proposta l'ultima sessione precedente.
3. L'utente può cambiare manualmente sessione o lasciare la nota non collegata.
4. La nota deve poter essere salvata anche senza `session_id`.

Questo evita di bloccare il flusso durante la partita.

## Effetto sui permessi

La visibilità reale dovrà usare:

- membership gruppo;
- partecipazione campagna;
- ruolo campagna;
- destinatari ristretti delle note/thread/materiali.

Prima di questa struttura, il sistema salvava la visibilità ma non poteva applicarla pienamente.

## Prossimi step tecnici

1. API gruppi:
   - creare gruppo;
   - cercare utente per username;
   - aggiungere membro;
   - elenco gruppi dell'utente.

2. API campagna nel gruppo:
   - creare campagna dentro un gruppo;
   - aggiungere partecipanti;
   - assegnare ruolo;
   - collegare partecipante a personaggio.

3. Permessi:
   - `master` può gestire campagna, sessioni, asset e partecipanti;
   - `player` può inserire note, leggere contenuti party, partecipare a off-sessione;
   - `viewer` sola lettura;
   - `co_master` quasi master.

4. Materiali:
   - upload file;
   - gestione link esterni;
   - collegamento asset a sessione.

5. Calendarizzazione:
   - data/ora sessione;
   - stato sessione;
   - auto-suggerimento sessione nelle note.
