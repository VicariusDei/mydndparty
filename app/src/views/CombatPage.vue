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
        <h1 class="hero-title">{{ encounter?.name || 'Nessun encounter attivo' }}</h1>
        <p class="hero-subtitle">Ordine di iniziativa, lenti ed effetti temporanei da database.</p>
      </section>

      <section class="action-row">
        <ion-button class="action-button" expand="block" disabled>Nuovo round</ion-button>
        <ion-button class="action-button" expand="block" fill="outline" disabled>Avanza turno</ion-button>
      </section>

      <section class="section-block" v-if="encounters.length > 1">
        <div class="entity-list">
          <article class="fantasy-card entity-card" v-for="row in encounters" :key="row.id">
            <div>
              <p class="entity-name">{{ row.name }}</p>
              <p class="entity-meta">Round {{ row.current_round }} · {{ row.combatants_count || 0 }} combattenti</p>
            </div>
          </article>
        </div>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="combatants.length">
          <article
            class="fantasy-card entity-card"
            :class="{ 'turn-card': index === 0 }"
            v-for="(combatant, index) in combatants"
            :key="combatant.id"
          >
            <div>
              <p class="entity-name">{{ combatant.name }}</p>
              <p class="entity-meta">{{ combatantLabel(combatant) }} · iniziativa {{ combatant.initiative }} · bonus {{ combatant.initiative_bonus }}</p>
              <div class="badge-row">
                <span class="fantasy-badge" v-if="isSlow(combatant)">Lento</span>
                <span class="fantasy-badge" v-if="combatant.party_member_id">PG</span>
                <span class="fantasy-badge" v-for="effect in combatant.effects || []" :key="effect.id">
                  {{ effect.name }}<span v-if="!isPermanent(effect)"> · {{ effect.remaining_rounds }}</span>
                </span>
              </div>
            </div>
            <strong class="stat-value">{{ combatant.initiative }}</strong>
          </article>
        </div>

        <article class="fantasy-card entity-card" v-else>
          <div>
            <p class="entity-name">Nessun combattente</p>
            <p class="entity-meta">Non ci sono combattenti reali associati all'encounter attivo.</p>
          </div>
        </article>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet } from '../services/api';
import type { CombatEffect, Combatant, Encounter } from '../types/domain';

type CombatPayload = {
  encounter: Encounter | null;
  combatants: Combatant[];
  encounters: Encounter[];
};

const router = useRouter();
const encounter = ref<Encounter | null>(null);
const combatants = ref<Combatant[]>([]);
const encounters = ref<Encounter[]>([]);

function isSlow(combatant: Combatant) {
  return combatant.is_slow === true || Number(combatant.is_slow) === 1;
}

function isPermanent(effect: CombatEffect) {
  return effect.is_permanent === true || Number(effect.is_permanent) === 1;
}

function combatantLabel(combatant: Combatant) {
  if (combatant.character_name) {
    return `${combatant.character_name}${combatant.player_name ? ' · ' + combatant.player_name : ''}`;
  }

  return combatant.type || 'Combattente';
}

onMounted(async () => {
  const response = await apiGet<CombatPayload>('combat/active');
  if (!response.ok) {
    router.replace('/login');
    return;
  }

  encounter.value = response.data?.encounter || null;
  combatants.value = response.data?.combatants || [];
  encounters.value = response.data?.encounters || [];
});
</script>
