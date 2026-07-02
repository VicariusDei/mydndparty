<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Altro</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Strumenti del master</p>
        <h1 class="hero-title">Controllo sessione</h1>
        <p class="hero-subtitle">Accesso rapido a campagne, diario, impostazioni, monete e account.</p>
      </section>

      <section class="section-block">
        <div class="entity-list">
          <article class="fantasy-card entity-card" v-for="entry in entries" :key="entry.title">
            <div>
              <p class="entity-name">{{ entry.title }}</p>
              <p class="entity-meta">{{ entry.description }}</p>
            </div>
          </article>
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
  { title: 'Diario campagna', description: 'Appunti, scoperte, PNG e luoghi.' },
  { title: 'Monete', description: 'Deposito, prelievo, peso e valore in oro.' },
  { title: 'Impostazioni', description: 'Dado iniziativa, lingua e preferenze.' },
  { title: 'Reference legacy', description: 'Funzioni recuperate dal vecchio progetto PHP.' }
];

async function doLogout() {
  await logout();
  router.replace('/login');
}
</script>
