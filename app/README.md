# App frontend

Frontend Ionic Vue + TypeScript per la nuova versione di MyDndParty.

## Comandi locali

```bash
npm install
npm run dev
npm run build
```

La build viene generata in `../dist` per facilitare il deploy FTP su Aruba.

## Template iniziale

Il template include:

- tab bar mobile-first;
- dashboard campagna;
- pagina party/personaggi;
- pagina inventario;
- pagina combattimento;
- pagina strumenti aggiuntivi;
- tema dark fantasy in `src/theme/`.

## API

Le chiamate API partono da `src/services/api.ts` e puntano a `/api/index.php`.
