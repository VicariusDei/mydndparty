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
        <p class="hero-subtitle">Round {{ encounter?.current_round || 0 }} · ordine iniziativa, lenti ed effetti temporanei.</p>
      </section>

      <section class="section-block">
        <div class="entity-list">
          <article class="fantasy-card entity-card">
            <div>
              <p class="entity-name">Nuovo combattimento</p>
              <p class="entity-meta">Crea un encounter attivo per la campagna.</p>
              <ion-input v-model="newEncounterName" label="Nome encounter" label-placement="stacked" fill="outline" />
              <ion-button class="action-button" expand="block" :disabled="loading" @click="createEncounter">Crea encounter</ion-button>
            </div>
          </article>

          <article class="fantasy-card entity-card" v-if="encounter">
            <div>
              <p class="entity-name">Azioni round</p>
              <p class="entity-meta">Avanza il turno o apre un nuovo round decrementando gli effetti temporanei.</p>
              <div class="action-row compact-actions">
                <ion-button class="action-button" expand="block" :disabled="loading || combatants.length === 0" @click="nextTurn">Avanza turno</ion-button>
                <ion-button class="action-button" expand="block" fill="outline" :disabled="loading" @click="newRound">Nuovo round</ion-button>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section class="section-block" v-if="encounter">
        <div class="entity-list">
          <article class="fantasy-card entity-card">
            <div>
              <p class="entity-name">Aggiungi PG dal party</p>
              <p class="entity-meta">Porta un personaggio reale nell'iniziativa.</p>
              <ion-select v-model="partyForm.party_member_id" label="Personaggio" label-placement="stacked" fill="outline">
                <ion-select-option v-for="member in partyMembers" :key="member.id" :value="member.id">
                  {{ member.character_name }} · {{ member.player_name }}
                </ion-select-option>
              </ion-select>
              <ion-input v-model="partyForm.initiative" label="Iniziativa" label-placement="stacked" fill="outline" type="number" />
              <ion-button class="action-button" expand="block" :disabled="loading || !partyForm.party_member_id" @click="addPartyMember">Aggiungi PG</ion-button>
            </div>
          </article>

          <article class="fantasy-card entity-card">
            <div>
              <p class="entity-name">Aggiungi avversario / PNG</p>
              <p class="entity-meta">Replica la generazione manuale avversari del legacy.</p>
              <ion-input v-model="enemyForm.name" label="Nome" label-placement="stacked" fill="outline" />
              <ion-select v-model="enemyForm.type" label="Tipo" label-placement="stacked" fill="outline">
                <ion-select-option value="enemy">Avversario</ion-select-option>
                <ion-select-option value="npc">PNG</ion-select-option>
              </ion-select>
              <ion-input v-model="enemyForm.initiative" label="Iniziativa" label-placement="stacked" fill="outline" type="number" />
              <ion-input v-model="enemyForm.initiative_bonus" label="Bonus iniziativa" label-placement="stacked" fill="outline" type="number" />
              <label class="auth-check"><ion-checkbox v-model="enemyForm.is_slow" /><span>Lento</span></label>
              <ion-button class="action-button" expand="block" :disabled="loading || !enemyForm.name" @click="addCombatant">Aggiungi combattente</ion-button>
            </div>
          </article>

          <article class="fantasy-card entity-card">
            <div>
              <p class="entity-name">Applica effetto</p>
              <p class="entity-meta">Gli effetti temporanei scalano a ogni nuovo round.</p>
              <ion-select v-model="effectForm.combatant_id" label="Combattente" label-placement="stacked" fill="outline">
                <ion-select-option v-for="combatant in combatants" :key="combatant.id" :value="combatant.id">
                  {{ combatant.name }}
                </ion-select-option>
              </ion-select>
              <ion-input v-model="effectForm.name" label="Effetto" label-placement="stacked" fill="outline" />
              <ion-input v-model="effectForm.remaining_rounds" label="Round rimanenti" label-placement="stacked" fill="outline" type="number" />
              <label class="auth-check"><ion-checkbox v-model="effectForm.is_permanent" /><span>Permanente</span></label>
              <ion-button class="action-button" expand="block" :disabled="loading || !effectForm.combatant_id || !effectForm.name" @click="addEffect">Applica effetto</ion-button>
            </div>
          </article>
        </div>
      </section>

      <section class="section-block" v-if="message || error">
        <article class="fantasy-card entity-card">
          <p class="auth-success" v-if="message">{{ message }}</p>
          <p class="auth-error" v-if="error">{{ error }}</p>
        </article>
      </section>

      <section class="section-block" v-if="encounters.length > 1">
        <div class="entity-list">
          <article class="fantasy-card entity-card" v-for="row in encounters" :key="row.id">
            <div>
              <p class="entity-name">{{ row.name }}</p>
              <p class="entity-meta">Round {{ row.current_round }} · {{ row.combatants_count || 0 }} combattenti</p>
              <ion-button v-if="!isActive(row)" class="action-button" expand="block" fill="outline" :disabled="loading" @click="activateEncounter(row.id)">Rendi attivo</ion-button>
            </div>
          </article>
        </div>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="combatants.length">
          <article
            class="fantasy-card entity-card"
            :class="{ 'turn-card': isCurrentTurn(combatant, index) }"
            v-for="(combatant, index) in combatants"
            :key="combatant.id"
          >
            <div>
              <p class="entity-name">{{ combatant.name }}</p>
              <p class="entity-meta">{{ combatantLabel(combatant) }} · iniziativa {{ combatant.initiative }} · bonus {{ combatant.initiative_bonus }}</p>
              <div class="badge-row">
                <span class="fantasy-badge" v-if="isCurrentTurn(combatant, index)">Turno corrente</span>
                <span class="fantasy-badge" v-if="hasActed(combatant)">Ha agito</span>
                <span class="fantasy-badge" v-if="isSlow(combatant)">Lento</span>
                <span class="fantasy-badge" v-if="combatant.party_member_id">PG</span>
              </div>
              <div class="badge-row" v-if="combatant.effects?.length">
                <span class="fantasy-badge" v-for="effect in combatant.effects" :key="effect.id">
                  {{ effect.name }}<span v-if="!isPermanent(effect)"> · {{ effect.remaining_rounds }}</span>
                  <button class="inline-remove" type="button" @click="removeEffect(effect.id)">×</button>
                </span>
              </div>
            </div>
            <strong class="stat-value">{{ combatant.initiative }}</strong>
          </article>
        </div>

        <article class="fantasy-card entity-card" v-else>
          <div>
            <p class="entity-name">Nessun combattente</p>
            <p class="entity-meta">Crea un encounter o aggiungi personaggi/avversari.</p>
          </div>
        </article>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonCheckbox, IonContent, IonHeader, IonInput, IonPage, IonSelect, IonSelectOption, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet, apiPost } from '../services/api';
import type { CombatEffect, Combatant, Encounter, PartyMember } from '../types/domain';

type CombatPayload = {
  encounter: Encounter | null;
  combatants: Combatant[];
  encounters: Encounter[];
  turn_complete?: boolean;
};

type PartyPayload = {
  party_members: PartyMember[];
};

const router = useRouter();
const encounter = ref<Encounter | null>(null);
const combatants = ref<Combatant[]>([]);
const encounters = ref<Encounter[]>([]);
const partyMembers = ref<PartyMember[]>([]);
const loading = ref(false);
const error = ref('');
const message = ref('');
const newEncounterName = ref('');

const partyForm = reactive({
  party_member_id: null as number | null,
  initiative: 0
});

const enemyForm = reactive({
  name: '',
  type: 'enemy',
  initiative: 0,
  initiative_bonus: 0,
  is_slow: false
});

const effectForm = reactive({
  combatant_id: null as number | null,
  name: '',
  remaining_rounds: 1,
  is_permanent: false
});

function applyState(data?: CombatPayload) {
  encounter.value = data?.encounter || null;
  combatants.value = data?.combatants || [];
  encounters.value = data?.encounters || [];
}

async function loadCombat() {
  const response = await apiGet<CombatPayload>('combat/active');
  if (!response.ok) {
    router.replace('/login');
    return;
  }
  applyState(response.data);
}

async function loadParty() {
  const response = await apiGet<PartyPayload>('party/list');
  if (response.ok) {
    partyMembers.value = response.data?.party_members || [];
  }
}

async function runAction(route: string, payload: unknown, success: string) {
  loading.value = true;
  error.value = '';
  message.value = '';
  try {
    const response = await apiPost<CombatPayload>(route, payload);
    if (!response.ok) {
      error.value = response.error || 'Operazione non riuscita';
      return;
    }
    applyState(response.data);
    message.value = success;
  } finally {
    loading.value = false;
  }
}

async function createEncounter() {
  await runAction('combat/create', { name: newEncounterName.value }, 'Encounter creato.');
  newEncounterName.value = '';
}

async function activateEncounter(id: number) {
  await runAction('combat/activate', { encounter_id: id }, 'Encounter attivato.');
}

async function addPartyMember() {
  await runAction('combat/add-party-member', { ...partyForm }, 'Personaggio aggiunto all iniziativa.');
  partyForm.party_member_id = null;
  partyForm.initiative = 0;
}

async function addCombatant() {
  await runAction('combat/add-combatant', { ...enemyForm }, 'Combattente aggiunto.');
  enemyForm.name = '';
  enemyForm.initiative = 0;
  enemyForm.initiative_bonus = 0;
  enemyForm.is_slow = false;
}

async function nextTurn() {
  await runAction('combat/next-turn', {}, 'Turno avanzato.');
}

async function newRound() {
  await runAction('combat/new-round', {}, 'Nuovo round avviato.');
}

async function addEffect() {
  await runAction('combat/effect/add', { ...effectForm }, 'Effetto applicato.');
  effectForm.name = '';
  effectForm.remaining_rounds = 1;
  effectForm.is_permanent = false;
}

async function removeEffect(effectId: number) {
  await runAction('combat/effect/remove', { effect_id: effectId }, 'Effetto rimosso.');
}

function isActive(row: Encounter) {
  return row.is_active === true || Number(row.is_active) === 1;
}

function isSlow(combatant: Combatant) {
  return combatant.is_slow === true || Number(combatant.is_slow) === 1;
}

function hasActed(combatant: Combatant) {
  return combatant.has_acted === true || Number(combatant.has_acted) === 1;
}

function isCurrentTurn(combatant: Combatant, index: number) {
  return index === 0 && !hasActed(combatant);
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
  await Promise.all([loadCombat(), loadParty()]);
});
</script>
