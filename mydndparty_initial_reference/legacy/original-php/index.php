<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Friabili | Progetti digitali personali</title>
  <meta name="description" content="Friabili raccoglie progetti digitali personali dedicati a gioco di ruolo, teatro e organizzazione di serate da tavolo.">
  <style>
    :root {
      --bg: #111111;
      --panel: #181818;
      --panel-soft: #202020;
      --text: #f4f1eb;
      --muted: #b8b1a7;
      --line: rgba(255,255,255,.10);
      --accent: #d7a84f;
      --accent-soft: rgba(215,168,79,.14);
      --radius: 22px;
      --shadow: 0 24px 70px rgba(0,0,0,.35);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background:
        radial-gradient(circle at top left, rgba(215,168,79,.16), transparent 34rem),
        radial-gradient(circle at bottom right, rgba(255,255,255,.06), transparent 30rem),
        var(--bg);
      color: var(--text);
      line-height: 1.5;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    .page {
      width: min(1120px, calc(100% - 32px));
      margin: 0 auto;
      padding: 32px 0 48px;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      padding: 14px 0 54px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
    }

    .brand-mark {
      width: 38px;
      height: 38px;
      border-radius: 13px;
      background: linear-gradient(135deg, var(--accent), #7e5521);
      box-shadow: 0 0 0 6px var(--accent-soft);
    }

    nav {
      display: flex;
      gap: 18px;
      color: var(--muted);
      font-size: 14px;
    }

    nav a:hover {
      color: var(--text);
    }

    .hero {
      padding: 54px 0 72px;
      display: grid;
      grid-template-columns: 1.15fr .85fr;
      gap: 48px;
      align-items: center;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 18px;
      padding: 7px 12px;
      border: 1px solid var(--line);
      border-radius: 999px;
      background: rgba(255,255,255,.04);
      color: var(--muted);
      font-size: 13px;
    }

    .eyebrow::before {
      content: "";
      width: 7px;
      height: 7px;
      border-radius: 999px;
      background: var(--accent);
    }

    h1 {
      margin: 0;
      max-width: 820px;
      font-size: clamp(42px, 7vw, 82px);
      line-height: .95;
      letter-spacing: -.06em;
    }

    .hero p {
      max-width: 680px;
      margin: 28px 0 0;
      color: var(--muted);
      font-size: clamp(17px, 2vw, 21px);
    }

    .hero-panel {
      border: 1px solid var(--line);
      background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.03));
      border-radius: var(--radius);
      padding: 28px;
      box-shadow: var(--shadow);
    }

    .hero-panel strong {
      display: block;
      margin-bottom: 12px;
      font-size: 15px;
      text-transform: uppercase;
      letter-spacing: .12em;
      color: var(--accent);
    }

    .hero-panel p {
      margin: 0;
      font-size: 16px;
      color: var(--muted);
    }

    .section-title {
      display: flex;
      justify-content: space-between;
      align-items: end;
      gap: 24px;
      margin-bottom: 22px;
    }

    .section-title h2 {
      margin: 0;
      font-size: clamp(28px, 4vw, 44px);
      letter-spacing: -.04em;
    }

    .section-title p {
      max-width: 460px;
      margin: 0;
      color: var(--muted);
    }

    .projects {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
    }

    .card {
      min-height: 390px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      border: 1px solid var(--line);
      background:
        linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.025)),
        var(--panel);
      border-radius: var(--radius);
      padding: 26px;
      transition: transform .2s ease, border-color .2s ease, background .2s ease;
    }

    .card:hover {
      transform: translateY(-4px);
      border-color: rgba(215,168,79,.45);
      background:
        linear-gradient(180deg, rgba(215,168,79,.09), rgba(255,255,255,.025)),
        var(--panel);
    }

    .card-kicker {
      margin-bottom: 18px;
      color: var(--accent);
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .12em;
    }

    .card h3 {
      margin: 0 0 14px;
      font-size: 29px;
      letter-spacing: -.03em;
    }

    .card p {
      margin: 0;
      color: var(--muted);
      font-size: 16px;
    }

    .tags {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin: 24px 0;
    }

    .tag {
      padding: 7px 10px;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--muted);
      font-size: 13px;
      background: rgba(255,255,255,.03);
    }

    .button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 0 16px;
      border-radius: 999px;
      background: var(--text);
      color: #111111;
      font-weight: 700;
      font-size: 14px;
    }

    .button:hover {
      background: var(--accent);
    }

    .about {
      margin-top: 72px;
      padding: 34px;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      background: rgba(255,255,255,.04);
      display: grid;
      grid-template-columns: .8fr 1.2fr;
      gap: 32px;
    }

    .about h2 {
      margin: 0;
      font-size: 34px;
      letter-spacing: -.04em;
    }

    .about p {
      margin: 0;
      color: var(--muted);
      font-size: 17px;
    }

    footer {
      display: flex;
      justify-content: space-between;
      gap: 24px;
      padding-top: 38px;
      color: var(--muted);
      font-size: 14px;
    }

    @media (max-width: 900px) {
      header,
      .section-title,
      footer {
        align-items: flex-start;
        flex-direction: column;
      }

      .hero,
      .about {
        grid-template-columns: 1fr;
      }

      .projects {
        grid-template-columns: 1fr;
      }

      .card {
        min-height: auto;
      }

      nav {
        flex-wrap: wrap;
      }
    }
  </style>
</head>
<body>
  <div class="page">
    <header>
      <a class="brand" href="/">
        <span class="brand-mark" aria-hidden="true"></span>
        <span>Friabili</span>
      </a>

      <nav aria-label="Progetti">
        <a href="#progetti">Progetti</a>
        <a href="https://www.friabili.it/teatro/">Teatro</a>
        <a href="https://www.taply.cloud/">TAPLY</a>
      </nav>
    </header>

    <main>
      <section class="hero">
        <div>
          <div class="eyebrow">Laboratorio digitale personale</div>
          <h1>Progetti web per organizzare passioni reali.</h1>
          <p>
            Friabili raccoglie strumenti nati da esigenze concrete: giocare di ruolo,
            gestire percorsi teatrali, organizzare tavoli e serate con altre persone.
          </p>
        </div>

        <aside class="hero-panel">
          <strong>Approccio</strong>
          <p>
            Applicazioni semplici, verticali e operative. Ogni progetto nasce per ridurre
            dispersione, chat, file sparsi e organizzazione manuale.
          </p>
        </aside>
      </section>

      <section id="progetti">
        <div class="section-title">
          <h2>Progetti</h2>
          <p>
            Tre ambienti distinti, un’unica direzione: trasformare attività sociali e creative
            in flussi più chiari, consultabili e condivisi.
          </p>
        </div>

        <div class="projects">
          <article class="card">
            <div>
              <div class="card-kicker">Giochi di ruolo</div>
              <h3>MyDndParty</h3>
              <p>
                Un portale pensato per gruppi, campagne, personaggi e sessioni di gioco di ruolo.
                L’obiettivo è tenere insieme informazioni, organizzazione e materiale utile al tavolo.
              </p>

              <div class="tags">
                <span class="tag">Party</span>
                <span class="tag">Campagne</span>
                <span class="tag">Sessioni</span>
              </div>
            </div>

            <a class="button" href="https://www.friabili.it/mydndparty.php">
              Apri progetto
            </a>
          </article>

          <article class="card">
            <div>
              <div class="card-kicker">Formazione teatrale</div>
              <h3>Teatro Docente</h3>
              <p>
                Un archivio operativo per docenti di teatro: corsi, incontri, esercizi,
                sessioni di lavoro, note e calendario in un unico ambiente digitale.
              </p>

              <div class="tags">
                <span class="tag">Corsi</span>
                <span class="tag">Esercizi</span>
                <span class="tag">PWA</span>
              </div>
            </div>

            <a class="button" href="https://www.friabili.it/teatro/">
              Apri progetto
            </a>
          </article>

          <article class="card">
            <div>
              <div class="card-kicker">Giochi da tavolo</div>
              <h3>TAPLY</h3>
              <p>
                Un social network per organizzare serate di gioco da tavolo:
                collezioni condivise, gruppi, inviti, tavoli aperti e profilo ludico.
              </p>

              <div class="tags">
                <span class="tag">Board game</span>
                <span class="tag">Gruppi</span>
                <span class="tag">Tavoli</span>
              </div>
            </div>

            <a class="button" href="https://www.taply.cloud/">
              Apri progetto
            </a>
          </article>
        </div>
      </section>

      <section class="about">
        <h2>Perché Friabili</h2>
        <p>
          Friabili è uno spazio di sperimentazione personale: progetti piccoli, mirati,
          migliorabili nel tempo, costruiti intorno a comunità, creatività e organizzazione.
          Non una vetrina generica, ma un indice ragionato di strumenti in evoluzione.
        </p>
      </section>
    </main>

    <footer>
      <span>© 2026 Friabili.it</span>
      <span>Progetti personali, strumenti digitali, organizzazione creativa.</span>
    </footer>
  </div>
</body>
</html>