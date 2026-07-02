<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Inventario</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Tesori e oggetti</p>
        <h1 class="hero-title">Inventario vivo</h1>
        <p class="hero-subtitle">Oggetti identificati, quantita', valore e note rapide.</p>
      </section>

      <section class="section-block">
        <div class="entity-list">
          <article class="fantasy-card entity-card" v-for="item in items" :key="item.name">
            <div>
              <p class="entity-name">{{ item.name }}</p>
              <p class="entity-meta">{{ item.category }} · qta {{ item.quantity }} · {{ item.value }} mo</p>
              <div class="badge-row">
                <span class="fantasy-badge">{{ item.identified ? 'Identificato' : 'Da identificare' }}</span>
                <span class="fantasy-badge">{{ item.owner }}</span>
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

const items = [
  { name: 'Amuleto del Corvo', category: 'Magico', quantity: 1, value: 120, identified: false, owner: 'Party' },
  { name: 'Pozione cura ferite', category: 'Consumabile', quantity: 3, value: 50, identified: true, owner: 'Mirael' },
  { name: 'Mappa bruciata', category: 'Indizio', quantity: 1, value: 0, identified: true, owner: 'Party' }
];
</script>
