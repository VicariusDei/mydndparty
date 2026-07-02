<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Combattimento</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Encounter attivo</p>
        <h1 class="hero-title">Iniziativa</h1>
        <p class="hero-subtitle">Ordine di turno, avversari, lenti ed effetti temporanei.</p>
      </section>

      <section class="action-row">
        <ion-button class="action-button" expand="block">Nuovo round</ion-button>
        <ion-button class="action-button" expand="block" fill="outline">Avanza turno</ion-button>
      </section>

      <section class="section-block">
        <div class="entity-list">
          <article
            class="fantasy-card entity-card"
            :class="{ 'turn-card': combatant.active }"
            v-for="combatant in combatants"
            :key="combatant.name"
          >
            <div>
              <p class="entity-name">{{ combatant.name }}</p>
              <p class="entity-meta">{{ combatant.type }} · iniziativa {{ combatant.initiative }}</p>
              <div class="badge-row">
                <span class="fantasy-badge" v-if="combatant.slow">Lento</span>
                <span class="fantasy-badge" v-for="effect in combatant.effects" :key="effect">{{ effect }}</span>
              </div>
            </div>
            <strong class="stat-value">{{ combatant.initiative }}</strong>
          </article>
        </div>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { IonButton, IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';

const combatants = [
  { name: 'Mirael', type: 'PG', initiative: 21, slow: false, active: true, effects: ['Benedetto', 'Velocizzato'] },
  { name: 'Nym', type: 'PG', initiative: 18, slow: false, active: false, effects: [] },
  { name: 'Branco di ghoul', type: 'Avversario', initiative: 14, slow: false, active: false, effects: ['Intralciato'] },
  { name: 'Thoran', type: 'PG', initiative: 0, slow: true, active: false, effects: [] }
];
</script>
