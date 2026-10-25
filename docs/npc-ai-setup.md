# PNG vocali: attivazione e utilizzo

## Attivazione sul server

1. Eseguire prima le migrazioni esistenti `2026_07_03_groups_campaign_roles_assets.sql` e `2026_07_03_narrative_core.sql`, se non già applicate.
2. Importare con phpMyAdmin `database/migrations/2026_10_25_npc_ai.sql`. La migrazione aggiunge quattro tabelle e non modifica i dati esistenti.
3. Nel file **già presente sul server** `/mydndparty/api/config/config.php`, aggiungere all'array restituito questa voce, mantenendo tutte le altre impostazioni:

```php
'npc_ai' => [
    'api_key' => 'INSERISCI_QUI_LA_CHIAVE',
    'text_model' => 'gpt-4.1-mini',
    'transcription_model' => 'gpt-transcribe',
    'speech_model' => 'gpt-4o-mini-tts',
],
```

In alternativa la chiave può essere definita nella variabile server `OPENAI_API_KEY`. Non inserirla nel frontend, nel repository o nella chat. Il deploy esclude il file config.php reale. Richiesti PHP 8+, PDO MySQL, cURL e fileinfo; `upload_max_filesize` almeno 10M, `post_max_size` superiore a 10M. HTTPS necessario per il microfono. Ogni registrazione dura al massimo 60 secondi. Il servizio IA richiede un account API con credito e accesso ai modelli configurati; i consumi sono separati dall'abbonamento ChatGPT.

4. Pubblicare il codice tramite il normale workflow GitHub dopo l'importazione SQL. Le migrazioni non sono eseguite dal deploy.
5. Accedere e aprire **Altro → PNG · Regia IA**. Il proprietario della campagna e i partecipanti attivi master/co-master possono gestire il modulo.

## Primo utilizzo

Selezionare campagna e creare un PNG con personalità, conoscenze autorizzate, memoria iniziale e voce. È possibile collegarlo a un PNG dell'archivio narrativo: il collegamento non importa descrizioni o note segrete. Il contenuto autorizzato deve essere inserito esplicitamente.

Salvare, poi aprire una sessione. Sul tablet aprire `/mydndparty/npc-terminal` e inserire il codice del master, oppure usare il collegamento generato. Il codice scade dopo 12 ore; il comando **Chiudi e revoca terminale** lo disabilita immediatamente. Il terminale non richiede di condividere le credenziali del master.

Il master imposta la scena percepibile dal PNG. **Aggiorna scena / Riprendi** salva scena e modalità di approvazione, annullando le operazioni e le bozze precedenti. **Pausa e interrompi** blocca nuovi turni, invalida le risposte in preparazione e ferma il terminale al successivo aggiornamento (polling ogni 1,5 secondi). Le richieste al fornitore già partite possono terminare ed essere fatturate; il loro risultato non viene pubblicato.

Il terminale propone domande scritte o registrazioni. **Parla → Termina registrazione** trascrive la domanda, che rimane modificabile prima di **Invia**. Microfono e riproduzione non operano insieme. Il pulsante **Attiva voce** abilita le risposte vocali; se il browser blocca la riproduzione automatica, premere **Riascolta ultima battuta**. Il testo resta sempre disponibile.

Con approvazione attiva le risposte compaiono soltanto nella regia e sono modificabili. Le proposte su evento richiedono sempre approvazione. Le battute manuali vengono pubblicate direttamente; richiedono la chiave soltanto per la sintesi vocale.

**Proponi memoria** genera una bozza privata dalla conversazione della sessione. Correggerla, copiarla nella memoria e premere **Salva PNG**. Non avviene alcun aggiornamento automatico dei fatti. La cronologia completa rimane separata e la regia può riprendere le sessioni dal selettore.

## Dati e limiti della prima versione

Le conoscenze e i ricordi sono separati per PNG e campagna; le note master delle campagne/sessioni non sono lette né inviate al modello. Il contesto comprende configurazione autorizzata, scena e ultimi 20 messaggi pubblicati della sessione. La cronologia visualizzata mostra gli ultimi 100 messaggi; lo storico completo resta nel database. Un turno di generazione per sessione, con lease di 90 secondi e timeout fornitore di 60 secondi. Nessun ascolto continuo, intervento autonomo ambientale o streaming audio.

`mdp_npc_usage` conserva operazione, esito e metadati d'uso restituiti dal fornitore; non calcola un costo monetario e non conserva chiavi o audio. Non è ancora presente un pannello statistiche dei consumi. Le registrazioni vengono usate per la trascrizione senza archiviarle nell'app. Le risposte vocali sono sintetizzate su richiesta e riascoltarle comporta una nuova richiesta.

## Verifica

- `cd app && npm ci && npm run build -- --base=/mydndparty/`
- `php -l api/modules/Npc/NpcController.php`
- `php api/tests/npc-access.php` (richiede PDO SQLite; fixture in memoria, nessun accesso al database reale).

Il test copre isolamento campagne, ruoli, codice temporaneo, visibilità delle bozze, approvazione, pausa, chiave mancante e revoca. La fixture SQLite traduce le funzioni temporali MySQL: non sostituisce una prova di importazione su MySQL/MariaDB. I test non sono distribuiti sul server.

Prima dell'utilizzo al tavolo occorre provare con chiave reale conversazione, registrazione, sintesi, permessi microfono e riproduzione su tablet Android e iPad.

Documentazione API usata: https://developers.openai.com/api/docs/guides/speech-to-text e https://developers.openai.com/api/docs/guides/text-to-speech. I nomi dei modelli rimangono configurabili sul server.
