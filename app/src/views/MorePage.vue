<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Altro</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Menu operativo</p>
        <h1 class="hero-title">Sezioni reali</h1>
        <p class="hero-subtitle">Le voci inattive restano visibili ma dichiarano lo stato reale del modulo.</p>
      </section>

      <section class="section-block">
        <div class="entity-list">
          <router-link class="fantasy-card entity-card" v-for="entry in entries" :key="entry.title" :to="entry.to">
            <div>
              <p class="entity-name">{{ entry.title }}</p>
              <p class="entity-meta">{{ entry.description }}</p>
            </div>
          </router-link>
        </div>
      </section>

      <section class="section-block">
        <ion-button class="action-button" expand="block" fill="outline" @click="doLogout">Esci</ion-button>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';
import { logout } from '../services/auth';

const router = useRouter();

const entries = [
  { title: 'Dashboard', description: 'Riepilogo reale di campagna, party, inventario e combattimento.', to: '/tabs/dashboard' },
  { title: 'Campagne', description: 'Elenco campagne, attivazione e diario master.', to: '/tabs/campaigns' },
  { title: 'Party', description: 'Personaggi migrati da compagnia e nuovi personaggi.', to: '/tabs/party' },
  { title: 'Inventario', description: 'Oggetti, categorie, quantità, valore e identificazione.', to: '/tabs/inventory' },
  { title: 'Combattimento', description: 'Encounter e iniziativa migrati dalle pagine legacy.', to: '/tabs/combat' },
  { title: 'Messaggi', description: 'Modulo presente ma non ancora attivato: nessun dato fittizio.', to: '/tabs/more' },
  { title: 'Richieste amicizia', description: 'Modulo presente ma non ancora attivato: nessuna richiesta fittizia.', to: '/tabs/more' },
  { title: 'Impostazioni', description: 'Da collegare a cfgSistema, cfgLingua e preferenze utente.', to: '/tabs/more' }
];

async function doLogout() {
  await logout();
  router.replace('/login');
}
</script>
