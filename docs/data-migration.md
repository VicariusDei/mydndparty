# Migrazione dati legacy MyDndParty

## Obiettivo

Portare i dati del vecchio progetto PHP dentro il nuovo schema `mdp_*`, mantenendo il legacy solo come sorgente dati temporanea.

Il nuovo schema è definito in:

```text
database/schema.sql
```

La migrazione usa lo script protetto:

```text
api/tools/migrate-legacy.php
```

## Prerequisiti

Nel database devono coesistere:

1. le nuove tabelle `mdp_*`, create importando `database/schema.sql`;
2. le tabelle legacy del vecchio dump: `utenti`, `gruppi`, `compagnia`, `inventario`, `tipoMonete`, `monete`, `combattimento`, `dadoIniziativa`, `round`, `effetti`.

Lo script lavora in modalità non distruttiva: non elimina né modifica le tabelle legacy.

## Token di sicurezza

Nel file non versionato:

```text
api/config/config.php
```

aggiungere una chiave temporanea:

```php
'migration_token' => 'scegliere-un-token-lungo-casuale',
```

Il token non va committato.

## Esecuzione dry-run

Aprire da browser:

```text
https://www.friabili.it/mydndparty/api/tools/migrate-legacy.php?token=TOKEN
```

Questa modalità non scrive dati. Restituisce un JSON con:

- tabelle legacy trovate;
- righe candidate alla migrazione;
- avvisi su tabelle mancanti o relazioni non determinabili.

## Esecuzione reale

Dopo aver verificato il dry-run:

```text
https://www.friabili.it/mydndparty/api/tools/migrate-legacy.php?token=TOKEN&execute=1
```

La migrazione avviene in transazione. In caso di errore viene eseguito rollback.

## Mappatura dati

Lo script popola:

- `utenti` → `mdp_users`;
- `gruppi` → `mdp_campaigns`;
- `compagnia` → `mdp_party_members`;
- `inventario` → `mdp_inventory_items`;
- `tipoMonete` → `mdp_coin_types`;
- `monete` → `mdp_wallets`;
- `combattimento` → `mdp_encounters`;
- `dadoIniziativa` oppure `round` → `mdp_combatants`;
- `effetti` → `mdp_effects`.

Le relazioni legacy/nuovo vengono registrate in:

```text
mdp_legacy_migration_map
```

## Password utenti

Le password vengono migrate solo se il valore legacy è già un hash moderno compatibile con `password_verify`, ad esempio bcrypt o argon2.

Se il legacy usa MD5, SHA1 o hash custom, la password viene lasciata `NULL`: l'utente dovrà usare il recupero password o Google login.

## Dopo la migrazione

Verificare i conteggi:

```sql
SELECT COUNT(*) FROM mdp_users;
SELECT COUNT(*) FROM mdp_campaigns;
SELECT COUNT(*) FROM mdp_party_members;
SELECT COUNT(*) FROM mdp_inventory_items;
SELECT COUNT(*) FROM mdp_wallets;
SELECT COUNT(*) FROM mdp_encounters;
SELECT COUNT(*) FROM mdp_combatants;
SELECT COUNT(*) FROM mdp_effects;
```

Dopo il controllo, rimuovere dal server:

```text
api/tools/migrate-legacy.php
```

oppure rimuovere `migration_token` da `api/config/config.php` per disabilitare lo script.