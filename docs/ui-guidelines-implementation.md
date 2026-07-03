# UI guidelines - stato applicazione

## Stato

Applicata prima fase di refactoring grafico globale.

L'obiettivo era trasformare l'interfaccia da dark fantasy disordinata a dark fantasy gestionale: atmosfera, ma con form leggibili, spaziature coerenti e componenti riusabili.

## File principali aggiornati

```text
app/src/theme/variables.css
app/src/theme/mdp-design.css
app/src/main.ts
```

È stato aggiunto un layer CSS dedicato importato dopo `app.css`:

```text
app/src/theme/mdp-design.css
```

Questo permette di correggere l'interfaccia senza cancellare il CSS storico già presente.

## Design token introdotti

Sono stati introdotti token `--mdp-*`:

- `--mdp-bg`;
- `--mdp-bg-soft`;
- `--mdp-panel`;
- `--mdp-panel-raised`;
- `--mdp-border`;
- `--mdp-border-strong`;
- `--mdp-gold`;
- `--mdp-gold-soft`;
- `--mdp-parchment`;
- `--mdp-green`;
- `--mdp-red`;
- `--mdp-blue`;
- `--mdp-text`;
- `--mdp-muted`;
- `--mdp-muted-weak`.

I vecchi token `--rpg-*` restano come alias per compatibilità.

## Classi UI standardizzate

- `hero-card`;
- `form-card`;
- `form-grid`;
- `form-field`;
- `form-actions`;
- `list-card`;
- `list-title`;
- `list-meta`;
- `fantasy-badge`;
- `clean-input`.

## Pagine rifattorizzate

Le seguenti pagine sono state aggiornate per usare label esterne e form ordinati:

- `PartyPage.vue`;
- `GroupsPage.vue`;
- `SessionsPage.vue`;
- `PlayerNotesPage.vue`;
- `CampaignsPage.vue`;
- `InventoryPage.vue`;
- `CombatPage.vue`.

## Problema risolto

Prima, i campi Ionic con `label-placement="stacked"` e `fill="outline"` generavano label accavallate ai bordi degli input.

Ora i form principali usano struttura:

```html
<div class="form-field">
  <label>Etichetta</label>
  <ion-input class="clean-input" fill="outline" />
</div>
```

Questo separa semanticamente label e campo ed evita sovrapposizioni.

## Compatibilità

Le funzionalità non sono state cambiate: solo markup e classi CSS.

Le rotte, le API e i payload restano invariati.

## Note tecniche

Il controllo combinato status GitHub sul commit più recente non ha restituito status check disponibili. Non ho quindi una conferma automatica del build in questa risposta.

## Prossimi interventi UI consigliati

1. Rifinire DashboardPage, che contiene ancora una struttura più complessa e precedente.
2. Rimuovere progressivamente classi legacy inutilizzate da `app.css`.
3. Creare componenti Vue riusabili per:
   - `PageHero`;
   - `FormField`;
   - `ListCard`;
   - `EmptyState`;
   - `ActionBar`.
4. Applicare lo stesso sistema ai futuri moduli: materiali, calendario, off-sessione.
