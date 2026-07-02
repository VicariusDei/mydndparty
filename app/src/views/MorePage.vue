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
        <p class="hero-subtitle">Accesso rapido a campagne, diario, impostazioni, monete e reference.</p>
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
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';

const entries = [
  { title: 'Diario campagna', description: 'Appunti, scoperte, PNG e luoghi.' },
  { title: 'Monete', description: 'Deposito, prelievo, peso e valore in oro.' },
  { title: 'Impostazioni', description: 'Dado iniziativa, lingua e preferenze.' },
  { title: 'Reference legacy', description: 'Funzioni recuperate dal vecchio progetto PHP.' }
];
</script>
