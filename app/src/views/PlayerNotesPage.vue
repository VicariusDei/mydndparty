<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Note</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Memoria di sessione</p>
        <h1 class="hero-title">Note giocatori</h1>
        <p class="hero-subtitle">Appunti subito visibili, collegabili a una sessione e correggibili dal master.</p>
      </section>

      <section class="section-block">
        <article class="fantasy-card form-card">
          <p class="entity-name">{{ form.id ? 'Correggi nota' : 'Nuova nota rapida' }}</p>
          <p class="entity-meta">Le note entrano subito nella cronologia secondo la visibilità scelta.</p>

          <div class="form-grid">
            <div class="form-field">
              <label>Sessione</label>
              <ion-select v-model="form.session_id" class="clean-input" fill="outline">
                <ion-select-option :value="0">Nessuna sessione specifica</ion-select-option>
                <ion-select-option v-for="session in sessions" :key="session.id" :value="session.id">
                  #{{ session.session_number }} · {{ session.title }}
                </ion-select-option>
              </ion-select>
            </div>

            <div class="form-field">
              <label>Titolo opzionale</label>
              <ion-input v-model="form.title" class="clean-input" fill="outline" />
            </div>

            <div class="form-field is-full">
              <label>Nota</label>
              <ion-textarea v-model="form.content" class="clean-input" fill="outline" :auto-grow="true" />
            </div>

            <div class="form-field">
              <label>Tipo</label>
              <ion-select v-model="form.note_type" class="clean-input" fill="outline">
                <ion-select-option value="note">Nota</ion-select-option>
                <ion-select-option value="npc">PNG</ion-select-option>
                <ion-select-option value="place">Luogo</ion-select-option>
                <ion-select-option value="quest">Quest</ion-select-option>
                <ion-select-option value="loot">Loot</ion-select-option>
                <ion-select-option value="question">Domanda</ion-select-option>
                <ion-select-option value="rules">Regole</ion-select-option>
                <ion-select-option value="idea">Idea</ion-select-option>
                <ion-select-option value="scene">Scena</ion-select-option>
                <ion-select-option value="decision">Decisione</ion-select-option>
              </ion-select>
            </div>

            <div class="form-field">
              <label>Visibilità</label>
              <ion-select v-model="form.share_scope" class="clean-input" fill="outline">
                <ion-select-option value="party">Tutto il party</ion-select-option>
                <ion-select-option value="private">Privata autore + master</ion-select-option>
                <ion-select-option value="restricted">Ristretta</ion-select-option>
                <ion-select-option value="master">Solo master</ion-select-option>
              </ion-select>
            </div>

            <div class="form-field is-full" v-if="form.share_scope === 'restricted'">
              <label>Destinatari</label>
              <ion-select v-model="form.recipient_party_member_ids" class="clean-input" fill="outline" multiple>
                <ion-select-option v-for="member in partyMembers" :key="member.id" :value="member.id">
                  {{ member.character_name }} · {{ member.player_name }}
                </ion-select-option>
              </ion-select>
            </div>

            <div class="form-field">
              <label>Scritta come</label>
              <ion-select v-model="form.author_party_member_id" class="clean-input" fill="outline">
                <ion-select-option :value="0">Utente / master</ion-select-option>
                <ion-select-option v-for="member in partyMembers" :key="member.id" :value="member.id">
                  {{ member.character_name }} · {{ member.player_name }}
                </ion-select-option>
              </ion-select>
            </div>

            <div class="form-field">
              <label>Flag master</label>
              <ion-select v-model="form.master_flag" class="clean-input" fill="outline">
                <ion-select-option value="none">Nessuno</ion-select-option>
                <ion-select-option value="needs_review">Da rivedere</ion-select-option>
                <ion-select-option value="verified">Verificata</ion-select-option>
                <ion-select-option value="spoiler">Spoiler</ion-select-option>
                <ion-select-option value="incorrect">Errata</ion-select-option>
              </ion-select>
            </div>
          </div>

          <p class="auth-error" v-if="error">{{ error }}</p>
          <p class="auth-success" v-if="message">{{ message }}</p>

          <div class="form-actions">
            <ion-button class="action-button" expand="block" :disabled="loading || !form.content" @click="saveNote">
              {{ form.id ? 'Salva correzione' : 'Pubblica nota' }}
            </ion-button>
            <ion-button class="action-button" expand="block" fill="outline" :disabled="loading" @click="resetForm">Annulla</ion-button>
          </div>
        </article>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="notes.length">
          <article class="fantasy-card list-card" v-for="note in notes" :key="note.id">
            <div>
              <p class="list-title">{{ note.title || labelForType(note.note_type) }}</p>
              <p class="list-meta">{{ labelForType(note.note_type) }} · {{ labelForScope(note.share_scope) }} · {{ authorLabel(note) }} · {{ formatDate(note.created_at) }}</p>
              <p class="list-meta" v-if="note.session_title">Sessione #{{ note.session_number }} · {{ note.session_title }}</p>
              <p class="list-meta note-content">{{ note.content }}</p>

              <div class="badge-row">
                <span class="fantasy-badge">{{ note.status }}</span>
                <span class="fantasy-badge" v-if="note.master_flag !== 'none'">{{ labelForFlag(note.master_flag) }}</span>
                <span class="fantasy-badge" v-if="note.recipients?.length">{{ recipientLabel(note) }}</span>
                <span class="fantasy-badge" v-if="note.corrected_at">Corretta</span>
              </div>

              <div class="badge-row">
                <ion-button size="small" fill="outline" :disabled="loading" @click="editNote(note)">Correggi</ion-button>
                <ion-button size="small" fill="outline" color="danger" :disabled="loading" @click="deleteNote(note.id)">Nascondi</ion-button>
              </div>
            </div>
          </article>
        </div>

        <article class="fantasy-card list-card" v-else>
          <div>
            <p class="list-title">Nessuna nota</p>
            <p class="list-meta">Inserisci la prima nota della campagna o della prossima sessione.</p>
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
import type { PartyMember, PlayerNote, Session } from '../types/domain';

type PlayerNotesPayload = {
  player_notes: PlayerNote[];
};

type PartyPayload = {
  party_members: PartyMember[];
};

type SessionsPayload = {
  sessions: Session[];
  latest_session: Session | null;
};

const router = useRouter();
const notes = ref<PlayerNote[]>([]);
const partyMembers = ref<PartyMember[]>([]);
const sessions = ref<Session[]>([]);
const loading = ref(false);
const error = ref('');
const message = ref('');

const form = reactive({
  id: 0,
  session_id: 0,
  title: '',
  content: '',
  note_type: 'note',
  share_scope: 'party',
  recipient_party_member_ids: [] as number[],
  author_party_member_id: 0,
  master_flag: 'none'
});

function applyNotes(data?: PlayerNotesPayload) {
  notes.value = data?.player_notes || [];
}

async function loadNotes() {
  const response = await apiGet<PlayerNotesPayload>('player-notes/list');
  if (!response.ok) {
    router.replace('/login');
    return;
  }
  applyNotes(response.data);
}

async function loadParty() {
  const response = await apiGet<PartyPayload>('party/list');
  if (response.ok) {
    partyMembers.value = response.data?.party_members || [];
  }
}

async function loadSessions() {
  const response = await apiGet<SessionsPayload>('sessions/list');
  if (response.ok) {
    sessions.value = response.data?.sessions || [];
    if (!form.session_id && response.data?.latest_session) {
      form.session_id = response.data.latest_session.id;
    }
  }
}

async function runNoteAction(route: string, payload: unknown, success: string) {
  loading.value = true;
  error.value = '';
  message.value = '';
  try {
    const response = await apiPost<PlayerNotesPayload>(route, payload);
    if (!response.ok) {
      error.value = response.error || 'Operazione non riuscita';
      return;
    }
    applyNotes(response.data);
    message.value = success;
  } finally {
    loading.value = false;
  }
}

function payload() {
  return {
    id: form.id,
    session_id: Number(form.session_id) || 0,
    title: form.title,
    content: form.content,
    note_type: form.note_type,
    share_scope: form.share_scope,
    recipient_party_member_ids: form.share_scope === 'restricted' ? form.recipient_party_member_ids : [],
    author_party_member_id: Number(form.author_party_member_id) || 0,
    master_flag: form.master_flag,
    status: form.id ? 'corrected' : 'visible'
  };
}

async function saveNote() {
  if (form.id) {
    await runNoteAction('player-notes/update', payload(), 'Nota corretta.');
  } else {
    await runNoteAction('player-notes/create', payload(), 'Nota pubblicata.');
  }

  if (!error.value) {
    resetForm();
  }
}

function editNote(note: PlayerNote) {
  form.id = note.id;
  form.session_id = note.session_id || 0;
  form.title = note.title || '';
  form.content = note.content;
  form.note_type = note.note_type || 'note';
  form.share_scope = note.share_scope || 'party';
  form.author_party_member_id = note.author_party_member_id || 0;
  form.master_flag = note.master_flag || 'none';
  form.recipient_party_member_ids = (note.recipients || [])
    .map((recipient) => recipient.recipient_party_member_id || 0)
    .filter((id) => id > 0);
  error.value = '';
  message.value = '';
}

async function deleteNote(id: number) {
  await runNoteAction('player-notes/delete', { id }, 'Nota nascosta dalla cronologia attiva.');
  if (form.id === id) {
    resetForm();
  }
}

function resetForm() {
  const latest = sessions.value[0];
  form.id = 0;
  form.session_id = latest?.id || 0;
  form.title = '';
  form.content = '';
  form.note_type = 'note';
  form.share_scope = 'party';
  form.recipient_party_member_ids = [];
  form.author_party_member_id = 0;
  form.master_flag = 'none';
}

function labelForType(type: string) {
  const labels: Record<string, string> = {
    note: 'Nota', npc: 'PNG', place: 'Luogo', quest: 'Quest', loot: 'Loot', question: 'Domanda', rules: 'Regole', idea: 'Idea', scene: 'Scena', decision: 'Decisione'
  };
  return labels[type] || 'Nota';
}

function labelForScope(scope: string) {
  const labels: Record<string, string> = {
    party: 'Tutto il party', private: 'Privata', restricted: 'Ristretta', master: 'Solo master', public_readonly: 'Pubblica sola lettura'
  };
  return labels[scope] || scope;
}

function labelForFlag(flag: string) {
  const labels: Record<string, string> = {
    needs_review: 'Da rivedere', verified: 'Verificata', spoiler: 'Spoiler', incorrect: 'Errata'
  };
  return labels[flag] || flag;
}

function authorLabel(note: PlayerNote) {
  return note.author_character_name || note.author_display_name || note.author_username || note.author_label || 'Autore non indicato';
}

function recipientLabel(note: PlayerNote) {
  return `Destinatari: ${(note.recipients || []).map((recipient) => recipient.character_name || recipient.display_name || recipient.username).filter(Boolean).join(', ')}`;
}

function formatDate(value: string) {
  if (!value) return '';
  return new Date(value.replace(' ', 'T')).toLocaleString('it-IT', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
}

onMounted(async () => {
  await Promise.all([loadNotes(), loadParty(), loadSessions()]);
});
</script>
