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
        <p class="hero-subtitle">Gestione sessioni e stream con note collegate.</p>
      </section>

      <section class="section-block">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">{{ form.id ? 'Modifica sessione' : 'Nuova sessione' }}</p>
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
              <ion-button class="action-button" expand="block" :disabled="loading || !form.title" @click="saveSession">{{ form.id ? 'Salva sessione' : 'Crea sessione' }}</ion-button>
              <ion-button class="action-button" expand="block" fill="outline" :disabled="loading" @click="resetForm">Annulla</ion-button>
            </div>
          </div>
        </article>
      </section>

      <section class="section-block" v-if="selectedSession">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">Stream #{{ selectedSession.session_number }} · {{ selectedSession.title }}</p>
            <p class="entity-meta">{{ dateLabel(selectedSession) }}<span v-if="selectedSession.world_date"> · {{ selectedSession.world_date }}</span></p>
            <div class="badge-row">
              <span class="fantasy-badge">{{ statusLabel(selectedSession.status) }}</span>
              <span class="fantasy-badge">{{ visibilityLabel(selectedSession.visibility) }}</span>
              <span class="fantasy-badge">{{ selectedNotes.length }} note</span>
            </div>
          </div>
        </article>

        <div class="entity-list">
          <article class="fantasy-card entity-card" v-if="selectedSession.summary">
            <div><p class="entity-name">Riassunto</p><p class="entity-meta note-content">{{ selectedSession.summary }}</p></div>
          </article>
          <article class="fantasy-card entity-card" v-if="selectedSession.master_notes">
            <div><p class="entity-name">Note master</p><p class="entity-meta note-content">{{ selectedSession.master_notes }}</p></div>
          </article>
          <article class="fantasy-card entity-card" v-for="note in selectedNotes" :key="note.id">
            <div>
              <p class="entity-name">{{ note.title || labelForType(note.note_type) }}</p>
              <p class="entity-meta">{{ labelForType(note.note_type) }} · {{ labelForScope(note.share_scope) }} · {{ authorLabel(note) }} · {{ formatDateTime(note.created_at) }}</p>
              <p class="entity-meta note-content">{{ note.content }}</p>
              <div class="badge-row">
                <span class="fantasy-badge" v-if="note.master_flag !== 'none'">{{ labelForFlag(note.master_flag) }}</span>
                <span class="fantasy-badge" v-if="note.corrected_at">Corretta</span>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="sessions.length">
          <article class="fantasy-card entity-card" v-for="session in sessions" :key="session.id">
            <div>
              <p class="entity-name">#{{ session.session_number }} · {{ session.title }}</p>
              <p class="entity-meta">{{ dateLabel(session) }}<span v-if="session.world_date"> · {{ session.world_date }}</span> · {{ statusLabel(session.status) }}</p>
              <p class="entity-meta" v-if="session.summary">{{ session.summary }}</p>
              <p class="entity-meta" v-else>Nessun riassunto pubblico.</p>
              <div class="badge-row"><span class="fantasy-badge">{{ session.player_notes_count || 0 }} note</span><span class="fantasy-badge" v-if="session.master_notes">Note master</span></div>
              <div class="badge-row">
                <ion-button size="small" fill="outline" :disabled="loading" @click="openStream(session.id)">Stream</ion-button>
                <ion-button size="small" fill="outline" :disabled="loading" @click="editSession(session)">Modifica</ion-button>
                <ion-button size="small" fill="outline" color="danger" :disabled="loading" @click="deleteSession(session.id)">Elimina se vuota</ion-button>
              </div>
            </div>
          </article>
        </div>
        <article class="fantasy-card entity-card" v-else><div><p class="entity-name">Nessuna sessione</p><p class="entity-meta">Crea la prima sessione.</p></div></article>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonHeader, IonInput, IonPage, IonSelect, IonSelectOption, IonTextarea, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet, apiPost } from '../services/api';
import type { PlayerNote, Session } from '../types/domain';

type SessionsPayload = { sessions: Session[]; latest_session: Session | null };
type NotesPayload = { player_notes: PlayerNote[] };

const router = useRouter();
const sessions = ref<Session[]>([]);
const notes = ref<PlayerNote[]>([]);
const selectedSessionId = ref(0);
const loading = ref(false);
const error = ref('');
const message = ref('');

const form = reactive({ id: 0, session_number: 0, title: '', real_date: '', world_date: '', summary: '', master_notes: '', status: 'draft', visibility: 'party' });
const selectedSession = computed(() => sessions.value.find((row) => row.id === selectedSessionId.value) || null);
const selectedNotes = computed(() => notes.value.filter((note) => note.session_id === selectedSessionId.value));

function applyState(data?: SessionsPayload) {
  sessions.value = data?.sessions || [];
  if (!selectedSessionId.value && data?.latest_session) selectedSessionId.value = data.latest_session.id;
  if (!selectedSessionId.value && sessions.value.length) selectedSessionId.value = sessions.value[0].id;
}

async function loadSessions() {
  const response = await apiGet<SessionsPayload>('sessions/list');
  if (!response.ok) { router.replace('/login'); return; }
  applyState(response.data);
}

async function loadNotes() {
  const response = await apiGet<NotesPayload>('player-notes/list');
  if (response.ok) notes.value = response.data?.player_notes || [];
}

async function runSessionAction(route: string, data: unknown, success: string) {
  loading.value = true; error.value = ''; message.value = '';
  try {
    const response = await apiPost<SessionsPayload>(route, data);
    if (!response.ok) { error.value = response.error || 'Operazione non riuscita'; return; }
    applyState(response.data); await loadNotes(); message.value = success;
  } finally { loading.value = false; }
}

function payload() { return { ...form, session_number: Number(form.session_number) || 0 }; }
async function saveSession() { await runSessionAction(form.id ? 'sessions/update' : 'sessions/create', payload(), form.id ? 'Sessione aggiornata.' : 'Sessione creata.'); if (!error.value) resetForm(); }
function openStream(id: number) { selectedSessionId.value = id; }
function editSession(session: Session) { Object.assign(form, { id: session.id, session_number: Number(session.session_number) || 0, title: session.title, real_date: session.real_date || '', world_date: session.world_date || '', summary: session.summary || '', master_notes: session.master_notes || '', status: session.status || 'draft', visibility: session.visibility || 'party' }); selectedSessionId.value = session.id; error.value = ''; message.value = ''; }
async function deleteSession(id: number) { await runSessionAction('sessions/delete', { id }, 'Sessione eliminata.'); if (form.id === id) resetForm(); if (selectedSessionId.value === id) selectedSessionId.value = sessions.value[0]?.id || 0; }
function resetForm() { Object.assign(form, { id: 0, session_number: 0, title: '', real_date: '', world_date: '', summary: '', master_notes: '', status: 'draft', visibility: 'party' }); }
function dateLabel(session: Session) { return session.real_date ? new Date(`${session.real_date}T00:00:00`).toLocaleDateString('it-IT') : 'Data reale non indicata'; }
function statusLabel(status: string) { return ({ draft: 'Bozza', published: 'Pubblicata', archived: 'Archiviata' } as Record<string, string>)[status] || status; }
function visibilityLabel(value: string) { return ({ party: 'Party', master: 'Solo master', private: 'Privata', custom: 'Personalizzata' } as Record<string, string>)[value] || value; }
function labelForType(type: string) { return ({ note: 'Nota', npc: 'PNG', place: 'Luogo', quest: 'Quest', loot: 'Loot', question: 'Domanda', rules: 'Regole', idea: 'Idea', scene: 'Scena', decision: 'Decisione' } as Record<string, string>)[type] || 'Nota'; }
function labelForScope(scope: string) { return ({ party: 'Tutto il party', private: 'Privata', restricted: 'Ristretta', master: 'Solo master', public_readonly: 'Pubblica sola lettura' } as Record<string, string>)[scope] || scope; }
function labelForFlag(flag: string) { return ({ needs_review: 'Da rivedere', verified: 'Verificata', spoiler: 'Spoiler', incorrect: 'Errata' } as Record<string, string>)[flag] || flag; }
function authorLabel(note: PlayerNote) { return note.author_character_name || note.author_display_name || note.author_username || note.author_label || 'Autore non indicato'; }
function formatDateTime(value: string) { return value ? new Date(value.replace(' ', 'T')).toLocaleString('it-IT', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }) : ''; }

onMounted(async () => { await Promise.all([loadSessions(), loadNotes()]); });
</script>
