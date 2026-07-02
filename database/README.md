# Database nuova app

Questa cartella contiene lo schema della nuova versione di MyDndParty.

## File principali

- `schema.sql`: schema iniziale normalizzato, derivato dalle meccaniche legacy ma non dal dump grezzo.

## Convenzione nomi

Tutte le tabelle nuove usano il prefisso `mdp_` per evitare collisioni con tabelle esistenti sullo stesso database Aruba.

## Linea guida

Il vecchio dump resta in `legacy/database/schema_sanitized.sql` solo per analisi.

Lo schema nuovo usa nomi espliciti e prefissati:

- `mdp_users`
- `mdp_campaigns`
- `mdp_party_members`
- `mdp_inventory_items`
- `mdp_wallets`
- `mdp_coin_types`
- `mdp_encounters`
- `mdp_combatants`
- `mdp_effects`
