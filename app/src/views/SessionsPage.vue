<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Sessioni</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Diario cronologico</p>
        <h1 class="hero-title">Sessioni di campagna</h1>
        <p class="hero-subtitle">Crea sessioni, riepiloghi, data nel mondo e note master. Le note giocatore possono essere collegate qui.</p>
      </section>

      <section class="section-block">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">{{ form.id ? 'Modifica sessione' : 'Nuova sessione' }}</p>
            <p class="entity-meta">Se non specifichi il numero, viene assegnato il progressivo successivo.</p>

            <ion-input v-model="form.session_number" label="Numero sessione" label-placement="stacked" fill="outline" type="number" />
            <ion-input v-model="form.title" label="Titolo" label-placement="stacked" fill="outline" />
            <ion-input v-model="form.real_date" label="Data reale" label-placement="stacked" fill="outline" type="date" />
            <ion-input v-model="form.world_date" label="Data nel mondo" label-placement="stacked" fill="outline" />
            <ion-textarea v-model="form.summary" label="Riassunto pubblico" label-placement="stacked" fill="outline" :auto-grow="true" />
            <ion-textarea v-model="form.master_notes" label="Note master" label-placement="stacked" fill="outline" :auto-grow="true" />

            <ion-select v-model="form.status" label="Stato" label-placement="stacked" fill="outline">
              <ion-select-option value="draft">Bozza</ion-select-option>
              <ion-select-option value="published">Pubblicata</ion-select-option>
              <ion-select-option value="archived">Archiviata</ion-select-option>
            </ion-select>

            <ion-select v-model="form.visibility" label="Visibilità" label-placement="stacked" fill="outline">
              <ion-select-option value="party">Party</ion-select-option>
              <ion-select-option value="master">Solo master</ion-select-option>
              <ion-select-option value="private">Privata</ion-select-option>
              <ion-select-option value="custom">Personalizzata</ion-select-option>
            </ion-select>

            <p class="auth-error" v-if="error">{{ error }}</p>
            <p class="auth-success" v-if="message">{{ message }}</p>

            <div class="action-row compact-actions">
              <ion-button class="action-button" expand="block" :disabled="loading || !form.title" @click="saveSession">
                {{ form.id ? 'Salva sessione' : 'Crea sessione' }}
              </ion-button>
              <ion-button class="action-button" expand="block" fill="outline" :disabled="loading" @click="resetForm">Annulla</ion-button>
            </div>
          </div>
        </article>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="sessions.length">
          <article class="fantasy-card entity-card" v-for="session in sessions" :key="session.id">
            <div>
              <p class="entity-name">#{{ session.session_number }} · {{ session.title }}</p>
              <p class="entity-meta">{{ dateLabel(session) }}<span v-if="session.world_date"> · {{ session.world_date }}</span> · {{ statusLabel(session.status) }} · {{ visibilityLabel(session.visibility) }}</p>
              <p class="entity-meta" v-if="session.summary">{{ session.summary }}</p>
              <p class="entity-meta" v-else>Nessun riassunto pubblico.</p>
              <div class="badge-row">
                <span class="fantasy-badge">{{ session.player_notes_count || 0 }} note</span>
                <span class="fantasy-badge" v-if="session.master_notes">Note master</span>
              </div>
              <div class="badge-row">
                <ion-button size="small" fill="outline" :disabled="loading" @click="editSession(session)">Modifica</ion-button>
                <ion-button size="small" fill="outline" color="danger" :disabled="loading" @click="deleteSession(session.id)">Elimina se vuota</ion-button>
              </div>
            </div>
          </article>
        </div>

        <article class="fantasy-card entity-card" v-else>
          <div>
            <p class="entity-name">Nessuna sessione</p>
            <p class="entity-meta">Crea la prima sessione per iniziare la cronologia della campagna.</p>
          </div>
        </article>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonHeader, IonInput, IonPage, IonSelect, IonSelectOption, IonTextarea, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet, apiPost } from '../services/api';
import type { Session } from '../types/domain';

type SessionsPayload = {
  sessions: Session[];
  latest_session: Session | null;
};

const router = useRouter();
const sessions = ref<Session[]>([]);
const loading = ref(false);
const error = ref('');
const message = ref('');

const form = reactive({
  id: 0,
  session_number: 0,
  title: '',
  real_date: '',
  world_date: '',
  summary: '',
  master_notes: '',
  status: 'draft',
  visibility: 'party'
});

function applyState(data?: SessionsPayload) {
  sessions.value = data?.sessions || [];
}

async function loadSessions() {
  const response = await apiGet<SessionsPayload>('sessions/list');
  if (!response.ok) {
    router.replace('/login');
    return;
  }
  applyState(response.data);
}

async function runSessionAction(route: string, payload: unknown, success: string) {
  loading.value = true;
  error.value = '';
  message.value = '';
  try {
    const response = await apiPost<SessionsPayload>(route, payload);
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
    session_number: Number(form.session_number) || 0,
    title: form.title,
    real_date: form.real_date,
    world_date: form.world_date,
    summary: form.summary,
    master_notes: form.master_notes,
    status: form.status,
    visibility: form.visibility
  };
}

async function saveSession() {
  if (form.id) {
    await runSessionAction('sessions/update', payload(), 'Sessione aggiornata.');
  } else {
    await runSessionAction('sessions/create', payload(), 'Sessione creata.');
  }

  if (!error.value) {
    resetForm();
  }
}

function editSession(session: Session) {
  form.id = session.id;
  form.session_number = Number(session.session_number) || 0;
  form.title = session.title;
  form.real_date = session.real_date || '';
  form.world_date = session.world_date || '';
  form.summary = session.summary || '';
  form.master_notes = session.master_notes || '';
  form.status = session.status || 'draft';
  form.visibility = session.visibility || 'party';
  error.value = '';
  message.value = '';
}

async function deleteSession(id: number) {
  await runSessionAction('sessions/delete', { id }, 'Sessione eliminata.');
  if (form.id === id) {
    resetForm();
  }
}

function resetForm() {
  form.id = 0;
  form.session_number = 0;
  form.title = '';
  form.real_date = '';
  form.world_date = '';
  form.summary = '';
  form.master_notes = '';
  form.status = 'draft';
  form.visibility = 'party';
}

function dateLabel(session: Session) {
  if (!session.real_date) return 'Data reale non indicata';
  return new Date(`${session.real_date}T00:00:00`).toLocaleDateString('it-IT');
}

function statusLabel(status: string) {
  const labels: Record<string, string> = { draft: 'Bozza', published: 'Pubblicata', archived: 'Archiviata' };
  return labels[status] || status;
}

function visibilityLabel(visibility: string) {
  const labels: Record<string, string> = { party: 'Party', master: 'Solo master', private: 'Privata', custom: 'Personalizzata' };
  return labels[visibility] || visibility;
}

onMounted(loadSessions);
</script>
