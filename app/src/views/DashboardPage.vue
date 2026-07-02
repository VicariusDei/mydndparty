<template>
  <ion-page>
    <ion-content fullscreen class="app-page dashboard-page">
      <div class="dashboard-frame">
        <header class="topbar">
          <div class="brand-lockup">
            <div class="brand-die">d20</div>
            <div>
              <h1 class="brand-title">MyDnDParty</h1>
              <p class="brand-subtitle">Il tuo mondo. Le tue storie. Il vostro destino.</p>
            </div>
          </div>

          <div class="top-search" aria-label="Ricerca globale">
            <span>⌕</span>
            <span>Cerca campagne, personaggi, giocatori...</span>
            <span class="search-key">CTRL + K</span>
          </div>

          <div class="top-actions">
            <div class="icon-square is-optional" aria-label="Notifiche">♟<span class="status-badge">3</span></div>
            <div class="icon-square is-optional" aria-label="Messaggi">✉<span class="status-badge">2</span></div>
            <div class="profile-chip">
              <div class="profile-avatar">DM</div>
              <div>
                <p class="profile-name">Arconte87</p>
                <p class="profile-role">Dungeon Master</p>
              </div>
            </div>
          </div>
        </header>

        <nav class="mobile-nav" aria-label="Navigazione rapida">
          <span
            v-for="item in navItems"
            :key="item.label"
            class="mobile-nav-item"
            :class="{ 'is-active': item.active }"
          >
            {{ item.icon }} {{ item.label }}
          </span>
        </nav>

        <div class="dashboard-layout">
          <aside class="sidebar" aria-label="Menu principale">
            <nav class="sidebar-nav">
              <a
                v-for="item in navItems"
                :key="item.label"
                href="#"
                class="sidebar-item"
                :class="{ 'is-active': item.active }"
              >
                <span class="sidebar-icon">{{ item.icon }}</span>
                <span>{{ item.label }}</span>
                <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
              </a>
            </nav>

            <div class="sidebar-lore">
              <div class="campfire-scene">♜ ⚔ 🔥 ⚔ ♜</div>
              <p class="lore-copy">
                “Se il narratore tace,<br />
                lascia che siano i dadi<br />
                a scrivere il destino.”<br />
                &gt; _
              </p>
            </div>

            <div class="sidebar-status">
              <span>Modalità: <strong>Dungeon Master</strong></span>
              <span>Server: <strong>MyDnDParty Online</strong></span>
              <span>Regole: <strong>Personalizzate</strong></span>
            </div>
          </aside>

          <main class="dashboard-main">
            <section class="hero-grid" aria-label="Riepilogo campagna">
              <article class="fantasy-panel campaign-panel">
                <div class="campaign-art" aria-hidden="true"></div>
                <div class="campaign-copy">
                  <p class="hero-eyebrow">Campagna attiva</p>
                  <h2 class="hero-title">{{ campaignTitle }}</h2>
                  <p class="hero-subtitle">{{ campaignSubtitle }}</p>
                  <div class="hero-actions">
                    <ion-button class="rpg-button rpg-button-primary" expand="block" router-link="/tabs/dashboard">Continua</ion-button>
                    <ion-button class="rpg-button rpg-button-success" expand="block" router-link="/tabs/combat">Crea sessione</ion-button>
                    <ion-button class="rpg-button rpg-button-gold" expand="block" router-link="/tabs/party">Invita giocatori</ion-button>
                  </div>
                </div>
              </article>

              <article class="fantasy-panel next-session-panel">
                <div class="panel-header">
                  <h3 class="panel-title">⌛ Prossima sessione</h3>
                </div>
                <div class="session-body">
                  <p class="session-date">Sabato 25 Maggio</p>
                  <p class="session-time">20:30 - 00:30</p>
                  <p class="session-title">Episodio 7: Il Forte Dimenticato</p>
                  <p class="session-copy">I PG esplorano le rovine alla ricerca del Cristallo Nero.</p>
                  <ion-button class="rpg-button rpg-button-primary" expand="block" fill="solid" router-link="/tabs/combat">Vedi dettagli →</ion-button>
                </div>
              </article>

              <article class="fantasy-panel calendar-panel">
                <div class="panel-header">
                  <h3 class="panel-title">▣ Calendario eventi</h3>
                  <span class="panel-link">Vedi tutto</span>
                </div>
                <div class="event-list">
                  <div v-for="event in events" :key="event.date + event.title" class="event-row">
                    <div class="event-date" v-html="event.date"></div>
                    <div>
                      <p class="event-title">{{ event.title }}</p>
                      <p class="event-meta">{{ event.meta }}</p>
                    </div>
                    <span class="event-time">{{ event.time }}</span>
                  </div>
                </div>
              </article>
            </section>

            <section class="content-grid" aria-label="Dashboard operativa">
              <article class="fantasy-panel party-panel">
                <div class="panel-header">
                  <h3 class="panel-title">⚔ Personaggi del party</h3>
                  <span class="panel-kicker">{{ visiblePartyMembers.length }} PG</span>
                </div>
                <div class="character-grid">
                  <div v-for="member in visiblePartyMembers" :key="member.id" class="character-card">
                    <div class="avatar-mark">{{ memberInitials(member) }}</div>
                    <p class="entity-name">{{ member.character_name }}</p>
                    <p class="entity-meta">{{ member.ancestry_name || 'Origine ignota' }}</p>
                    <p class="entity-meta">{{ member.class_name || 'Classe non definita' }} · Liv. 6</p>
                    <div class="hp-row">
                      <span>♥</span>
                      <span class="hp-bar"><span class="hp-fill" :style="{ width: memberHp(member) + '%' }"></span></span>
                      <span>{{ memberHp(member) }}/100</span>
                    </div>
                  </div>
                </div>
              </article>

              <article class="fantasy-panel missions-panel">
                <div class="panel-header">
                  <h3 class="panel-title">▰ Missioni attive</h3>
                </div>
                <div class="missions-list">
                  <div v-for="mission in missions" :key="mission.title" class="mission-row">
                    <div class="mission-icon">{{ mission.icon }}</div>
                    <div>
                      <p class="mission-title">{{ mission.title }} <span class="mission-count">{{ mission.step }}</span></p>
                      <p class="mission-copy">{{ mission.copy }}</p>
                      <div class="mission-progress"><span :style="{ width: mission.progress + '%' }"></span></div>
                    </div>
                  </div>
                </div>
              </article>

              <article class="fantasy-panel online-panel">
                <div class="panel-header">
                  <h3 class="panel-title">● Party online</h3>
                  <span class="online-count">{{ onlinePlayers.length }} online</span>
                </div>
                <div class="online-list">
                  <div v-for="player in onlinePlayers" :key="player.id" class="online-row">
                    <div class="online-avatar">{{ memberInitials(player) }}</div>
                    <div>
                      <p class="entity-name">{{ player.player_name }}</p>
                      <p class="online-status">Online</p>
                    </div>
                    <span class="crown">♛</span>
                  </div>
                  <ion-button class="rpg-button rpg-button-gold" expand="block" router-link="/tabs/party">Invita giocatori</ion-button>
                </div>
              </article>

              <article class="fantasy-panel dice-panel">
                <div class="panel-header">
                  <h3 class="panel-title">◇ Dado rapido</h3>
                </div>
                <div class="dice-body">
                  <div class="dice-select"><span>Seleziona dado</span><strong>d20</strong></div>
                  <div class="dice-result">17</div>
                  <p class="dice-quote">“La fortuna favorisce chi osa.”</p>
                  <ion-button class="rpg-button rpg-button-primary" expand="block">Tira il dado</ion-button>
                </div>
              </article>

              <article class="fantasy-panel log-panel">
                <div class="panel-header">
                  <h3 class="panel-title">♜ Log di campagna - ultime voci</h3>
                  <span class="panel-link">Vedi tutto</span>
                </div>
                <div class="log-list">
                  <div v-for="entry in campaignLog" :key="entry.date + entry.copy" class="log-row">
                    <span class="log-date">{{ entry.date }}</span>
                    <p class="log-copy">{{ entry.copy }}</p>
                  </div>
                </div>
              </article>

              <article class="fantasy-panel progress-panel">
                <div class="panel-header">
                  <h3 class="panel-title">⚑ Progresso campagna</h3>
                </div>
                <div class="progress-body">
                  <div class="progress-nodes">
                    <span v-for="node in progressNodes" :key="node.icon" class="progress-node" :class="{ 'is-done': node.done }">{{ node.icon }}</span>
                  </div>
                  <p class="progress-label">Atto II - Le terre di confine</p>
                  <div class="campaign-progress-line">
                    <div class="campaign-progress"><span style="width: 64%"></span></div>
                    <span class="progress-percent">64%</span>
                  </div>
                  <p class="next-objective">Prossimo obiettivo: Il Forte Dimenticato</p>
                </div>
              </article>

              <article class="fantasy-panel notes-panel">
                <div class="panel-header">
                  <h3 class="panel-title">✎ Note del master</h3>
                </div>
                <div class="note-body">
                  <p>Ricorda di far emergere il conflitto tra i clan nani e l'importanza del Cristallo Nero.</p>
                  <ul>
                    <li>Introdurre l'antagonista nel prossimo incontro.</li>
                    <li>Preparare mappa del Forte.</li>
                    <li>NPC: Arak Tor, il Traditore.</li>
                  </ul>
                </div>
              </article>

              <article class="fantasy-panel loot-panel">
                <div class="panel-header">
                  <h3 class="panel-title">Loot recente</h3>
                </div>
                <div class="loot-list">
                  <div v-for="item in loot" :key="item.name" class="loot-row">
                    <div class="loot-icon">{{ item.icon }}</div>
                    <div>
                      <p class="loot-name">{{ item.name }}</p>
                      <p class="loot-meta">{{ item.meta }}</p>
                    </div>
                  </div>
                  <ion-button class="rpg-button rpg-button-gold" expand="block" router-link="/tabs/inventory">Vedi inventario</ion-button>
                </div>
              </article>
            </section>

            <footer class="rpg-footer">
              <span>Modalità: <strong>Dungeon Master</strong></span>
              <span>Server: <span class="online">MyDnDParty Online</span></span>
              <span>v1.0.0 &gt; _</span>
            </footer>
          </main>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonPage } from '@ionic/vue';
import { apiGet } from '../services/api';
import type { Campaign, PartyMember } from '../types/domain';

type ActiveCampaignPayload = {
  campaign: Campaign | null;
};

type PartyPayload = {
  party_members: PartyMember[];
};

const router = useRouter();
const campaign = ref<Campaign | null>(null);
const partyMembers = ref<PartyMember[]>([]);

const demoPartyMembers: PartyMember[] = [
  {
    id: -1,
    campaign_id: 0,
    user_id: 0,
    player_name: 'Thalion89',
    character_name: 'Thalion',
    class_name: 'Guerriero',
    ancestry_name: 'Umano',
    motto: 'Non arretrare',
    initiative_bonus: 2
  },
  {
    id: -2,
    campaign_id: 0,
    user_id: 0,
    player_name: 'Lyra_Azure',
    character_name: 'Lyra',
    class_name: 'Maga',
    ancestry_name: 'Elfa',
    motto: 'Luce purificante',
    initiative_bonus: 4
  },
  {
    id: -3,
    campaign_id: 0,
    user_id: 0,
    player_name: 'Bromgar_Stone',
    character_name: 'Bromgar',
    class_name: 'Chierico',
    ancestry_name: 'Nano',
    motto: 'Per la montagna',
    initiative_bonus: 1
  },
  {
    id: -4,
    campaign_id: 0,
    user_id: 0,
    player_name: 'ZyraSilva',
    character_name: 'Zyra',
    class_name: 'Ranger',
    ancestry_name: 'Mezzelfa',
    motto: 'Nel silenzio',
    initiative_bonus: 5
  }
];

const navItems = [
  { label: 'Dashboard', icon: '⌂', active: true },
  { label: 'Campagne', icon: '▣' },
  { label: 'Personaggi', icon: '♙' },
  { label: 'Sessioni', icon: '◴' },
  { label: 'Calendario', icon: '▦' },
  { label: 'Party', icon: '⚔' },
  { label: 'Inventario', icon: '◈' },
  { label: 'Missioni', icon: '⚑' },
  { label: 'Messaggi', icon: '✉', badge: 5 },
  { label: 'Impostazioni', icon: '⚙' }
];

const events = [
  { date: '25<br>MAG', title: 'Sessione di gioco', meta: 'Il Forte Dimenticato', time: '20:30' },
  { date: '01<br>GIU', title: 'Sessione di gioco', meta: 'Le Catacombe di Velmora', time: '21:00' },
  { date: '08<br>GIU', title: 'Evento speciale', meta: 'La Fiera delle Meraviglie', time: '15:00' },
  { date: '15<br>GIU', title: 'Sessione di gioco', meta: 'La Cripta dei Sussurri', time: '20:30' }
];

const missions = [
  {
    icon: '♦',
    title: 'Il Cristallo Nero',
    copy: 'Recupera il Cristallo Nero dalle rovine del Forte Dimenticato.',
    step: '2/4',
    progress: 50
  },
  {
    icon: '♜',
    title: 'Alleanze Fragili',
    copy: 'Ottieni il supporto dei clan nani delle montagne.',
    step: '1/3',
    progress: 34
  },
  {
    icon: '✹',
    title: 'La Minaccia Crescente',
    copy: 'Indaga sugli attacchi nelle terre di confine.',
    step: '3/5',
    progress: 60
  }
];

const campaignLog = [
  { date: '18/05', copy: 'I PG hanno sconfitto il Guardiano delle Rovine.' },
  { date: '12/05', copy: 'Thalion ha trovato: Spada del Giuramento.' },
  { date: '05/05', copy: 'Lyra ha appreso il rituale: Luce Purificante.' },
  { date: '28/04', copy: 'Il party è entrato nelle Catacombe di Velmora.' }
];

const progressNodes = [
  { icon: '⌂', done: true },
  { icon: '♣', done: true },
  { icon: '♜', done: true },
  { icon: '♦', done: false },
  { icon: '☠', done: false }
];

const loot = [
  { icon: '†', name: 'Spada del Giuramento', meta: 'Non comune' },
  { icon: '♦', name: "Cristallo dell'Ombra", meta: 'Raro' },
  { icon: '◉', name: "Monete d'Oro", meta: '245 mo' }
];

const visiblePartyMembers = computed(() => (partyMembers.value.length ? partyMembers.value : demoPartyMembers).slice(0, 4));
const onlinePlayers = computed(() => visiblePartyMembers.value.slice(0, 4));
const campaignTitle = computed(() => campaign.value?.name || 'Le Terre di Ombra e Rovina');
const campaignSubtitle = computed(
  () => campaign.value?.notes || 'Un’antica minaccia si risveglia dalle rovine perdute. I confini del regno vacillano e gli eroi sono chiamati a forgiare il proprio destino.'
);

function memberInitials(member: PartyMember) {
  return member.character_name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
}

function memberHp(member: PartyMember) {
  const base = Math.abs(member.id * 17 + member.initiative_bonus * 11) % 42;
  return 58 + base;
}

onMounted(async () => {
  try {
    const campaignResponse = await apiGet<ActiveCampaignPayload>('campaigns/active');
    if (!campaignResponse.ok) {
      router.replace('/login');
      return;
    }

    if (campaignResponse.data?.campaign) {
      campaign.value = campaignResponse.data.campaign;
    }

    const partyResponse = await apiGet<PartyPayload>('party/list');
    if (!partyResponse.ok) {
      router.replace('/login');
      return;
    }

    if (partyResponse.data?.party_members) {
      partyMembers.value = partyResponse.data.party_members;
    }
  } catch {
    router.replace('/login');
  }
});
</script>