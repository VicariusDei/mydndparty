<template>
  <ion-page>
    <ion-content fullscreen class="app-page dashboard-page">
      <div class="dashboard-frame">
        <header class="topbar">
          <div class="brand-lockup">
            <div class="brand-die">d20</div>
            <div>
              <h1 class="brand-title">MyDnDParty</h1>
              <p class="brand-subtitle">Memoria viva della campagna.</p>
            </div>
          </div>

          <div class="top-search" aria-label="Ricerca globale">
            <span>⌕</span>
            <span>Ricerca globale non ancora attiva</span>
            <span class="search-key">Prossimo step</span>
          </div>
        </header>

        <nav class="mobile-nav" aria-label="Navigazione rapida">
          <router-link v-for="item in navItems" :key="item.label" class="mobile-nav-item" :class="{ 'is-active': item.active }" :to="item.to">
            {{ item.icon }} {{ item.label }}
          </router-link>
        </nav>

        <div class="dashboard-layout">
          <aside class="sidebar" aria-label="Menu principale">
            <nav class="sidebar-nav">
              <router-link v-for="item in navItems" :key="item.label" :to="item.to" class="sidebar-item" :class="{ 'is-active': item.active }">
                <span class="sidebar-icon">{{ item.icon }}</span>
                <span>{{ item.label }}</span>
                <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
              </router-link>
            </nav>

            <div class="sidebar-lore">
              <div class="campfire-scene">♜ ⚔ 🔥 ⚔ ♜</div>
              <p class="lore-copy">La dashboard mostra solo dati reali o stati vuoti.</p>
            </div>

            <div class="sidebar-status">
              <span>Campagne: <strong>{{ stats.campaigns }}</strong></span>
              <span>Sessioni: <strong>{{ stats.sessions }}</strong></span>
              <span>Note: <strong>{{ stats.messages }}</strong></span>
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
                    <ion-button class="rpg-button rpg-button-primary" expand="block" router-link="/tabs/sessions">Sessioni</ion-button>
                    <ion-button class="rpg-button rpg-button-success" expand="block" router-link="/tabs/notes">Note</ion-button>
                    <ion-button class="rpg-button rpg-button-gold" expand="block" router-link="/tabs/inventory">Inventario</ion-button>
                  </div>
                </div>
              </article>

              <article class="fantasy-panel next-session-panel">
                <div class="panel-header"><h3 class="panel-title">✎ Ultima sessione</h3></div>
                <div class="session-body" v-if="summary?.latest_session">
                  <p class="session-date">Sessione #{{ summary.latest_session.session_number }}</p>
                  <p class="session-title">{{ summary.latest_session.title }}</p>
                  <p class="session-time" v-if="summary.latest_session.real_date">{{ formatDate(summary.latest_session.real_date) }}</p>
                  <p class="session-copy">{{ summary.latest_session.summary || 'Nessun riassunto pubblico ancora presente.' }}</p>
                  <ion-button class="rpg-button rpg-button-primary" expand="block" router-link="/tabs/sessions">Apri diario →</ion-button>
                </div>
                <div class="session-body" v-else>
                  <p class="session-title">Nessuna sessione registrata</p>
                  <p class="session-copy">Crea la prima sessione per iniziare la cronologia della campagna.</p>
                  <ion-button class="rpg-button rpg-button-primary" expand="block" router-link="/tabs/sessions">Crea sessione</ion-button>
                </div>
              </article>

              <article class="fantasy-panel calendar-panel">
                <div class="panel-header">
                  <h3 class="panel-title">▣ Stato dati</h3>
                  <span class="panel-link">Reale</span>
                </div>
                <div class="event-list">
                  <div v-for="stat in statRows" :key="stat.label" class="event-row">
                    <div class="event-date">{{ stat.value }}</div>
                    <div>
                      <p class="event-title">{{ stat.label }}</p>
                      <p class="event-meta">{{ stat.meta }}</p>
                    </div>
                  </div>
                </div>
              </article>
            </section>

            <section class="content-grid" aria-label="Dashboard operativa">
              <article class="fantasy-panel party-panel">
                <div class="panel-header">
                  <h3 class="panel-title">⚔ Personaggi del party</h3>
                  <span class="panel-kicker">{{ stats.party_members }} PG</span>
                </div>
                <div v-if="partyMembers.length" class="character-grid">
                  <div v-for="member in partyMembers" :key="member.id" class="character-card">
                    <div class="avatar-mark">{{ memberInitials(member.character_name) }}</div>
                    <p class="entity-name">{{ member.character_name }}</p>
                    <p class="entity-meta">{{ member.player_name }}</p>
                    <p class="entity-meta">{{ member.class_name || 'Classe non definita' }} · {{ member.ancestry_name || 'Origine ignota' }}</p>
                  </div>
                </div>
                <p v-else class="entity-meta">Nessun personaggio reale nella campagna attiva.</p>
              </article>

              <article class="fantasy-panel notes-panel">
                <div class="panel-header"><h3 class="panel-title">✎ Note campagna</h3></div>
                <div class="note-body">
                  <p>{{ summary?.campaign?.notes || 'Nessuna nota reale presente per la campagna attiva.' }}</p>
                  <ion-button class="rpg-button rpg-button-primary" expand="block" router-link="/tabs/notes">Aggiungi nota</ion-button>
                </div>
              </article>

              <article class="fantasy-panel log-panel">
                <div class="panel-header">
                  <h3 class="panel-title">♜ Combattenti</h3>
                  <span class="panel-link">{{ stats.combatants }}</span>
                </div>
                <div class="log-list" v-if="combatants.length">
                  <div v-for="combatant in combatants" :key="combatant.id" class="log-row">
                    <span class="log-date">{{ combatant.initiative }}</span>
                    <p class="log-copy">{{ combatant.name }} · {{ combatant.type }}</p>
                  </div>
                </div>
                <p v-else class="entity-meta">Nessun combattente presente nell'encounter attivo.</p>
              </article>

              <article class="fantasy-panel loot-panel">
                <div class="panel-header"><h3 class="panel-title">Loot recente</h3></div>
                <div class="loot-list" v-if="recentInventory.length">
                  <div v-for="item in recentInventory" :key="item.id" class="loot-row">
                    <div class="loot-icon">◈</div>
                    <div>
                      <p class="loot-name">{{ item.name }}</p>
                      <p class="loot-meta">{{ item.category || 'Senza categoria' }} · qta {{ item.quantity }} · {{ item.value_gold }} mo</p>
                    </div>
                  </div>
                  <ion-button class="rpg-button rpg-button-gold" expand="block" router-link="/tabs/inventory">Vedi inventario</ion-button>
                </div>
                <p v-else class="entity-meta">Nessun oggetto reale in inventario.</p>
              </article>
            </section>

            <footer class="rpg-footer">
              <span>Campagna: <strong>{{ campaignTitle }}</strong></span>
              <span>Sessioni: <strong>{{ stats.sessions }}</strong></span>
              <span>Dati: reali</span>
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
import type { Combatant, DashboardStats, DashboardSummary, InventoryItem, PartyMember } from '../types/domain';

const router = useRouter();
const summary = ref<DashboardSummary | null>(null);

const emptyStats: DashboardStats = {
  campaigns: 0,
  sessions: 0,
  party_members: 0,
  inventory_items: 0,
  wallet_rows: 0,
  encounters: 0,
  combatants: 0,
  messages: 0,
  friend_requests: 0
};

const navItems = computed(() => [
  { label: 'Dashboard', icon: '⌂', to: '/tabs/dashboard', active: true },
  { label: 'Sessioni', icon: '✎', to: '/tabs/sessions', active: false, badge: stats.value.sessions || undefined },
  { label: 'Note', icon: '✉', to: '/tabs/notes', active: false },
  { label: 'Campagne', icon: '▣', to: '/tabs/campaigns', active: false, badge: stats.value.campaigns || undefined },
  { label: 'Party', icon: '⚔', to: '/tabs/party', active: false, badge: stats.value.party_members || undefined },
  { label: 'Inventario', icon: '◈', to: '/tabs/inventory', active: false, badge: stats.value.inventory_items || undefined },
  { label: 'Combattimento', icon: '♜', to: '/tabs/combat', active: false, badge: stats.value.combatants || undefined }
]);

const stats = computed(() => summary.value?.stats || emptyStats);
const partyMembers = computed<PartyMember[]>(() => summary.value?.party_members || []);
const recentInventory = computed<InventoryItem[]>(() => summary.value?.recent_inventory || []);
const combatants = computed<Combatant[]>(() => summary.value?.combatants || []);
const campaignTitle = computed(() => summary.value?.campaign?.name || 'Nessuna campagna attiva');
const campaignSubtitle = computed(() => summary.value?.campaign?.notes || 'Crea o attiva una campagna per alimentare la dashboard.');

const statRows = computed(() => [
  { label: 'Sessioni', value: stats.value.sessions, meta: 'Sessioni registrate nel diario' },
  { label: 'Personaggi', value: stats.value.party_members, meta: 'Membri del party nella campagna attiva' },
  { label: 'Inventario', value: stats.value.inventory_items, meta: 'Oggetti migrati o creati' },
  { label: 'Encounter', value: stats.value.encounters, meta: 'Combattimenti disponibili' }
]);

function memberInitials(name: string) {
  return name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
}

function formatDate(value: string) {
  return new Date(`${value}T00:00:00`).toLocaleDateString('it-IT');
}

onMounted(async () => {
  const response = await apiGet<DashboardSummary>('dashboard/summary');
  if (!response.ok) {
    router.replace('/login');
    return;
  }

  summary.value = response.data || null;
});
</script>
