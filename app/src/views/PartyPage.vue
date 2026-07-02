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
        <div class="entity-list" v-if="members.length > 0">
          <article class="fantasy-card entity-card" v-for="member in members" :key="member.id">
            <div>
              <p class="entity-name">{{ member.character_name }}</p>
              <p class="entity-meta">{{ member.player_name }} · {{ member.class_name || 'Classe non definita' }} {{ member.ancestry_name || '' }}</p>
              <div class="badge-row">
                <span class="fantasy-badge">Ini +{{ member.initiative_bonus }}</span>
                <span class="fantasy-badge" v-if="member.motto">{{ member.motto }}</span>
              </div>
            </div>
          </article>
        </div>

        <article class="fantasy-card entity-card" v-else>
          <div>
            <p class="entity-name">Nessun personaggio</p>
            <p class="entity-meta">Crea il primo membro del party.</p>
          </div>
        </article>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet } from '../services/api';
import type { PartyMember } from '../types/domain';

type PartyPayload = {
  party_members: PartyMember[];
};

const router = useRouter();
const members = ref<PartyMember[]>([]);

onMounted(async () => {
  const response = await apiGet<PartyPayload>('party/list');
  if (!response.ok) {
    router.replace('/login');
    return;
  }

  if (response.data?.party_members) {
    members.value = response.data.party_members;
  }
});
</script>
