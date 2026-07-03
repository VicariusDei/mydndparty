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
        <p class="hero-subtitle">Gestisci personaggi, giocatori, classe, stirpe, motto e bonus iniziativa.</p>
      </section>

      <section class="section-block">
        <article class="fantasy-card form-card">
          <p class="entity-name">{{ form.id ? 'Modifica personaggio' : 'Nuovo personaggio' }}</p>
          <p class="entity-meta">I dati sono reali. Classi e stirpi restano testuali finché non verranno introdotte tabelle dedicate.</p>

          <div class="form-grid">
            <div class="form-field">
              <label>Nome personaggio</label>
              <ion-input v-model="form.character_name" class="clean-input" fill="outline" />
            </div>

            <div class="form-field">
              <label>Giocatore</label>
              <ion-input v-model="form.player_name" class="clean-input" fill="outline" />
            </div>

            <div class="form-field">
              <label>Classe</label>
              <ion-input v-model="form.class_name" class="clean-input" fill="outline" />
            </div>

            <div class="form-field">
              <label>Razza / stirpe</label>
              <ion-input v-model="form.ancestry_name" class="clean-input" fill="outline" />
            </div>

            <div class="form-field">
              <label>Bonus iniziativa</label>
              <ion-input v-model="form.initiative_bonus" class="clean-input" fill="outline" type="number" />
            </div>

            <div class="form-field">
              <label>Motto / nota breve</label>
              <ion-input v-model="form.motto" class="clean-input" fill="outline" />
            </div>
          </div>

          <p class="auth-error" v-if="error">{{ error }}</p>
          <p class="auth-success" v-if="message">{{ message }}</p>

          <div class="form-actions">
            <ion-button class="action-button" expand="block" :disabled="loading || !form.character_name || !form.player_name" @click="saveMember">
              {{ form.id ? 'Salva personaggio' : 'Crea personaggio' }}
            </ion-button>
            <ion-button class="action-button" expand="block" fill="outline" :disabled="loading" @click="resetForm">Annulla</ion-button>
          </div>
        </article>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="members.length > 0">
          <article class="fantasy-card list-card" v-for="member in members" :key="member.id">
            <div>
              <p class="list-title">{{ member.character_name }}</p>
              <p class="list-meta">{{ member.player_name }}</p>
              <p class="list-meta">{{ member.class_name || 'Classe non definita' }}<span v-if="member.ancestry_name"> · {{ member.ancestry_name }}</span></p>
              <div class="badge-row">
                <span class="fantasy-badge">Ini {{ signed(member.initiative_bonus) }}</span>
                <span class="fantasy-badge" v-if="member.motto">{{ member.motto }}</span>
              </div>
              <div class="badge-row">
                <ion-button size="small" fill="outline" :disabled="loading" @click="editMember(member)">Modifica</ion-button>
                <ion-button size="small" fill="outline" color="danger" :disabled="loading" @click="deleteMember(member.id)">Elimina se libero</ion-button>
              </div>
            </div>
          </article>
        </div>

        <article class="fantasy-card list-card" v-else>
          <div>
            <p class="list-title">Nessun personaggio</p>
            <p class="list-meta">Crea il primo membro del party.</p>
          </div>
        </article>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonHeader, IonInput, IonPage, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet, apiPost } from '../services/api';
import type { PartyMember } from '../types/domain';

type PartyPayload = {
  party_members: PartyMember[];
};

const router = useRouter();
const members = ref<PartyMember[]>([]);
const loading = ref(false);
const error = ref('');
const message = ref('');

const form = reactive({
  id: 0,
  player_name: '',
  character_name: '',
  class_name: '',
  ancestry_name: '',
  motto: '',
  initiative_bonus: 0
});

function applyState(data?: PartyPayload) {
  members.value = data?.party_members || [];
}

async function loadParty() {
  const response = await apiGet<PartyPayload>('party/list');
  if (!response.ok) {
    router.replace('/login');
    return;
  }
  applyState(response.data);
}

async function runPartyAction(route: string, payload: unknown, success: string) {
  loading.value = true;
  error.value = '';
  message.value = '';
  try {
    const response = await apiPost<PartyPayload>(route, payload);
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

function payload() {
  return {
    id: form.id,
    player_name: form.player_name,
    character_name: form.character_name,
    class_name: form.class_name,
    ancestry_name: form.ancestry_name,
    motto: form.motto,
    initiative_bonus: Number(form.initiative_bonus) || 0
  };
}

async function saveMember() {
  if (form.id) {
    await runPartyAction('party/update', payload(), 'Personaggio aggiornato.');
  } else {
    await runPartyAction('party/create', payload(), 'Personaggio creato.');
  }

  if (!error.value) {
    resetForm();
  }
}

function editMember(member: PartyMember) {
  form.id = member.id;
  form.player_name = member.player_name;
  form.character_name = member.character_name;
  form.class_name = member.class_name || '';
  form.ancestry_name = member.ancestry_name || '';
  form.motto = member.motto || '';
  form.initiative_bonus = Number(member.initiative_bonus) || 0;
  error.value = '';
  message.value = '';
}

async function deleteMember(id: number) {
  await runPartyAction('party/delete', { id }, 'Personaggio eliminato.');
  if (form.id === id) {
    resetForm();
  }
}

function resetForm() {
  form.id = 0;
  form.player_name = '';
  form.character_name = '';
  form.class_name = '';
  form.ancestry_name = '';
  form.motto = '';
  form.initiative_bonus = 0;
}

function signed(value: number) {
  const numeric = Number(value) || 0;
  return numeric >= 0 ? `+${numeric}` : `${numeric}`;
}

onMounted(loadParty);
</script>
