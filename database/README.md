# Database nuova app

Questa cartella contiene lo schema della nuova versione di MyDndParty.

## File principali

- `schema.sql`: schema iniziale normalizzato, derivato dalle meccaniche legacy ma non dal dump grezzo.

## Linea guida

Il vecchio dump resta in `legacy/database/schema_sanitized.sql` solo per analisi.

Lo schema nuovo usa nomi piu' espliciti:

- `users`
- `campaigns`
- `party_members`
- `inventory_items`
- `wallets`
- `coin_types`
- `encounters`
- `combatants`
- `effects`
