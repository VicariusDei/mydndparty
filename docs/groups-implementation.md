# Gruppi di gioco - stato implementazione

## Stato

Prima fase implementata.

Il sistema ora espone il concetto di gruppo di gioco separato da campagna, master e giocatore.

## API

Rotte aggiunte:

```text
groups/list
groups/create
groups/members
groups/member/add
groups/user/find
```

## Frontend

Pagina aggiunta:

```text
/tabs/groups
```

Voce aggiunta al menu laterale hamburger:

```text
Gruppi
```

## Funzioni disponibili

- creare un gruppo;
- diventare automaticamente owner del gruppo creato;
- vedere i gruppi di cui si è membro attivo;
- selezionare un gruppo;
- vedere i membri del gruppo;
- aggiungere un membro tramite username;
- assegnare ruolo gruppo `member` o `admin`.

## Regole implementate

Un utente può vedere solo i gruppi di cui è membro attivo.

Solo `owner` e `admin` del gruppo possono aggiungere membri.

Quando un gruppo viene creato, il creatore viene inserito automaticamente in `mdp_game_group_members` con ruolo `owner`.

## Limiti attuali

- Non esiste ancora UI per creare una campagna dentro un gruppo.
- Non esiste ancora UI per assegnare partecipanti a una campagna con ruolo `master`, `co_master`, `player`, `viewer`.
- Non c'è ancora gestione di inviti pendenti: l'aggiunta tramite username rende subito il membro attivo.
- Non c'è ancora rimozione o sospensione membri.
- Non c'è ancora filtro permessi globale sulle sezioni esistenti basato su `mdp_campaign_participants`.

## Prossimo step tecnico

Implementare Campagne nel gruppo:

- creare campagna partendo da un gruppo;
- collegare la campagna a `mdp_game_group_campaigns`;
- inserire automaticamente il creatore in `mdp_campaign_participants` come `master`;
- aggiungere giocatori dal gruppo alla campagna;
- assegnare ruolo campagna;
- opzionalmente collegare il giocatore a un personaggio.
