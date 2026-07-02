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
        <h1 class="hero-title">{{ campaignTitle }}</h1>
        <p class="hero-subtitle">{{ campaignSubtitle }}</p>
      </section>

      <section class="stat-grid">
        <article class="fantasy-card stat-card">
          <p class="stat-label">Personaggi</p>
          <p class="stat-value">{{ partyMembers.length }}</p>
        </article>
        <article class="fantasy-card stat-card">
          <p class="stat-label">Round</p>
          <p class="stat-value">0</p>
        </article>
        <article class="fantasy-card stat-card">
          <p class="stat-label">Oro totale</p>
          <p class="stat-value">0</p>
        </article>
        <article class="fantasy-card stat-card">
          <p class="stat-label">Effetti</p>
          <p class="stat-value">0</p>
        </article>
      </section>

      <section class="section-block">
        <h2 class="section-title">Party</h2>
        <div class="entity-list">
          <article class="fantasy-card entity-card" v-for="member in partyMembers" :key="member.id">
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
      </section>

      <section class="action-row">
        <ion-button class="action-button" expand="block" router-link="/tabs/party">Gestisci party</ion-button>
        <ion-button class="action-button" expand="block" fill="outline" router-link="/tabs/combat">Vai al combat</ion-button>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet } from '../services/api';
import type { Campaign, PartyMember } from '../types/domain';

type ActiveCampaignPayload = {
  campaign: Campaign | null;
};

type PartyPayload = {
  party_members: PartyMember[];
};

const router = useRouter();
const campaign = ref<Campaign | null>(null);
const partyMembers = ref<PartyMember[]>([]);

const campaignTitle = computed(() => campaign.value?.name || 'Nessuna campagna attiva');
const campaignSubtitle = computed(() => campaign.value?.notes || 'Crea o attiva una campagna per iniziare la sessione.');

onMounted(async () => {
  const campaignResponse = await apiGet<ActiveCampaignPayload>('campaigns/active');
  if (!campaignResponse.ok) {
    router.replace('/login');
    return;
  }

  if (campaignResponse.data?.campaign) {
    campaign.value = campaignResponse.data.campaign;
  }

  const partyResponse = await apiGet<PartyPayload>('party/list');
  if (!partyResponse.ok) {
    router.replace('/login');
    return;
  }

  if (partyResponse.data?.party_members) {
    partyMembers.value = partyResponse.data.party_members;
  }
});
</script>
