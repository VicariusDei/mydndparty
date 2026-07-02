# Database nuova app

Questa cartella contiene lo schema della nuova versione di MyDndParty.

## File principali

- `schema.sql`: schema iniziale normalizzato, derivato dalle meccaniche legacy ma non dal dump grezzo.
- `migrations/002_auth.sql`: upgrade per aggiungere login, remember-me, reset password e Google OAuth a un DB gia' importato.
- `seeds/demo.sql`: dati demo per sviluppo locale e prime prove grafiche/API.

## Ordine import nuova installazione

1. Importare `schema.sql`.
2. Importare `seeds/demo.sql` solo se servono dati demo.

## Ordine import se il DB esiste gia'

1. Importare `migrations/002_auth.sql`.
2. Non reimportare `schema.sql`, altrimenti si rischia conflitto su tabelle esistenti.

## Convenzione nomi

Tutte le tabelle nuove usano il prefisso `mdp_` per evitare collisioni con tabelle esistenti sullo stesso database Aruba.

## Linea guida

Il vecchio dump resta in `legacy/database/schema_sanitized.sql` solo per analisi.

Lo schema nuovo usa nomi espliciti e prefissati:

- `mdp_users`
- `mdp_remember_tokens`
- `mdp_password_reset_tokens`
- `mdp_campaigns`
- `mdp_party_members`
- `mdp_inventory_items`
- `mdp_wallets`
- `mdp_coin_types`
- `mdp_encounters`
- `mdp_combatants`
- `mdp_effects`
