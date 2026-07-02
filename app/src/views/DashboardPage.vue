<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>MyDndParty</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Campagna attiva</p>
        <h1 class="hero-title">Le Ombre di Vhalor</h1>
        <p class="hero-subtitle">Sessione pronta. Il party e' al limite del Bosco Cavo, con tre effetti ancora attivi.</p>
      </section>

      <section class="stat-grid">
        <article class="fantasy-card stat-card">
          <p class="stat-label">Personaggi</p>
          <p class="stat-value">5</p>
        </article>
        <article class="fantasy-card stat-card">
          <p class="stat-label">Round</p>
          <p class="stat-value">3</p>
        </article>
        <article class="fantasy-card stat-card">
          <p class="stat-label">Oro totale</p>
          <p class="stat-value">248</p>
        </article>
        <article class="fantasy-card stat-card">
          <p class="stat-label">Effetti</p>
          <p class="stat-value">3</p>
        </article>
      </section>

      <section class="section-block">
        <h2 class="section-title">Turno corrente</h2>
        <article class="fantasy-card entity-card turn-card">
          <div>
            <p class="entity-name">Mirael</p>
            <p class="entity-meta">Ranger mezzelfa · iniziativa 21</p>
            <div class="badge-row">
              <span class="fantasy-badge">Benedetto</span>
              <span class="fantasy-badge">Velocizzato</span>
            </div>
          </div>
        </article>
      </section>

      <section class="action-row">
        <ion-button class="action-button" expand="block">Nuovo round</ion-button>
        <ion-button class="action-button" expand="block" fill="outline">Avanza turno</ion-button>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { IonButton, IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';
</script>
