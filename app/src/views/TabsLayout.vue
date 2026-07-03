<template>
  <ion-page>
    <ion-menu content-id="main-content" side="start" type="overlay">
      <ion-header>
        <ion-toolbar>
          <ion-title>MyDnDParty</ion-title>
        </ion-toolbar>
      </ion-header>

      <ion-content class="app-side-menu">
        <div class="menu-brand">
          <div class="brand-die">d20</div>
          <div>
            <p class="menu-title">Memoria viva</p>
            <p class="menu-subtitle">Campagna, sessioni, note e party.</p>
          </div>
        </div>

        <nav class="side-nav" aria-label="Menu principale">
          <ion-menu-toggle v-for="item in menuItems" :key="item.label" auto-hide>
            <router-link class="side-nav-item" :to="item.to">
              <ion-icon :icon="item.icon" />
              <span>{{ item.label }}</span>
            </router-link>
          </ion-menu-toggle>
        </nav>
      </ion-content>
    </ion-menu>

    <ion-menu-button class="hamburger-button" auto-hide="false" />
    <ion-router-outlet id="main-content" />
  </ion-page>
</template>

<script setup lang="ts">
import {
  IonContent,
  IonHeader,
  IonIcon,
  IonMenu,
  IonMenuButton,
  IonMenuToggle,
  IonPage,
  IonRouterOutlet,
  IonTitle,
  IonToolbar
} from '@ionic/vue';
import {
  bookOutline,
  cubeOutline,
  documentTextOutline,
  flashOutline,
  homeOutline,
  mapOutline,
  menuOutline,
  peopleOutline,
  settingsOutline
} from 'ionicons/icons';

const menuItems = [
  { label: 'Dashboard', to: '/tabs/dashboard', icon: homeOutline },
  { label: 'Sessioni / Diario', to: '/tabs/sessions', icon: bookOutline },
  { label: 'Note giocatori', to: '/tabs/notes', icon: documentTextOutline },
  { label: 'Campagne', to: '/tabs/campaigns', icon: mapOutline },
  { label: 'Party', to: '/tabs/party', icon: peopleOutline },
  { label: 'Inventario', to: '/tabs/inventory', icon: cubeOutline },
  { label: 'Combattimento', to: '/tabs/combat', icon: flashOutline },
  { label: 'Altro', to: '/tabs/more', icon: settingsOutline }
];

void menuOutline;
</script>

<style scoped>
.hamburger-button {
  position: fixed;
  top: calc(env(safe-area-inset-top) + 10px);
  left: 10px;
  z-index: 10000;
  width: 44px;
  height: 44px;
  border-radius: 14px;
  color: #f5ead2;
  background: rgba(24, 17, 12, 0.82);
  border: 1px solid rgba(245, 212, 142, 0.32);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
}

.app-side-menu {
  --background: radial-gradient(circle at top left, rgba(167, 113, 48, 0.18), transparent 36%), #140f0c;
  color: #f5ead2;
}

.menu-brand {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 22px 18px 14px;
}

.menu-title {
  margin: 0;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.menu-subtitle {
  margin: 4px 0 0;
  color: rgba(245, 234, 210, 0.68);
  font-size: 0.86rem;
}

.side-nav {
  display: grid;
  gap: 8px;
  padding: 10px 12px 24px;
}

.side-nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 48px;
  padding: 12px 14px;
  border: 1px solid rgba(245, 212, 142, 0.16);
  border-radius: 16px;
  color: #f5ead2;
  background: rgba(255, 255, 255, 0.04);
  text-decoration: none;
  font-weight: 700;
}

.side-nav-item.router-link-active {
  border-color: rgba(245, 212, 142, 0.42);
  background: rgba(245, 212, 142, 0.12);
}

.side-nav-item ion-icon {
  font-size: 1.2rem;
  color: #f0c66a;
}
</style>
