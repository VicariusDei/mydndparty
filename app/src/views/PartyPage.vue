<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Party</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Compagnia</p>
        <h1 class="hero-title">Eroi e gregari</h1>
        <p class="hero-subtitle">Gestisci personaggi, classi, razze, motto e bonus iniziativa.</p>
      </section>

      <section class="section-block">
        <div class="entity-list">
          <article class="fantasy-card entity-card" v-for="member in members" :key="member.name">
            <div>
              <p class="entity-name">{{ member.name }}</p>
              <p class="entity-meta">{{ member.player }} · {{ member.role }}</p>
              <div class="badge-row">
                <span class="fantasy-badge">Ini +{{ member.initiative }}</span>
                <span class="fantasy-badge">{{ member.group }}</span>
              </div>
            </div>
          </article>
        </div>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';

const members = [
  { name: 'Mirael', player: 'Laura', role: 'Ranger mezzelfa', initiative: 4, group: 'Bosco Cavo' },
  { name: 'Thoran', player: 'Marco', role: 'Paladino nano', initiative: 1, group: 'Bosco Cavo' },
  { name: 'Nym', player: 'Sara', role: 'Ladra halfling', initiative: 5, group: 'Bosco Cavo' }
];
</script>
