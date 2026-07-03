<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Gruppi</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Portale sociale</p>
        <h1 class="hero-title">Gruppi di gioco</h1>
        <p class="hero-subtitle">Crea un gruppo, aggiungi utenti tramite username e prepara le campagne condivise.</p>
      </section>

      <section class="section-block">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">Nuovo gruppo</p>
            <ion-input v-model="groupForm.name" label="Nome gruppo" label-placement="stacked" fill="outline" />
            <ion-textarea v-model="groupForm.description" label="Descrizione" label-placement="stacked" fill="outline" :auto-grow="true" />
            <p class="auth-error" v-if="error">{{ error }}</p>
            <p class="auth-success" v-if="message">{{ message }}</p>
            <ion-button class="action-button" expand="block" :disabled="loading || !groupForm.name" @click="createGroup">Crea gruppo</ion-button>
          </div>
        </article>
      </section>

      <section class="section-block" v-if="selectedGroup">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">Aggiungi membro a {{ selectedGroup.name }}</p>
            <p class="entity-meta">Usa lo username dell'utente iscritto al portale.</p>
            <ion-input v-model="memberForm.username" label="Username" label-placement="stacked" fill="outline" />
            <ion-select v-model="memberForm.role" label="Ruolo nel gruppo" label-placement="stacked" fill="outline">
              <ion-select-option value="member">Membro</ion-select-option>
              <ion-select-option value="admin">Admin gruppo</ion-select-option>
            </ion-select>
            <ion-button class="action-button" expand="block" :disabled="loading || !memberForm.username" @click="addMember">Aggiungi membro</ion-button>
          </div>
        </article>

        <div class="entity-list" v-if="members.length">
          <article class="fantasy-card entity-card" v-for="member in members" :key="member.id">
            <div>
              <p class="entity-name">{{ member.display_name || member.username }}</p>
              <p class="entity-meta">@{{ member.username }} · {{ roleLabel(member.role) }} · {{ member.status }}</p>
            </div>
          </article>
        </div>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="groups.length">
          <article class="fantasy-card entity-card" v-for="group in groups" :key="group.id">
            <div>
              <p class="entity-name">{{ group.name }}</p>
              <p class="entity-meta">{{ group.description || 'Nessuna descrizione.' }}</p>
              <div class="badge-row">
                <span class="fantasy-badge">{{ roleLabel(group.my_role || 'member') }}</span>
                <span class="fantasy-badge">{{ group.members_count || 0 }} membri</span>
                <span class="fantasy-badge">{{ group.campaigns_count || 0 }} campagne</span>
              </div>
              <ion-button size="small" fill="outline" :disabled="loading" @click="selectGroup(group)">Gestisci membri</ion-button>
            </div>
          </article>
        </div>
        <article class="fantasy-card entity-card" v-else>
          <div>
            <p class="entity-name">Nessun gruppo</p>
            <p class="entity-meta">Crea il primo gruppo di gioco e aggiungi i partecipanti.</p>
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
import type { GameGroup, GameGroupMember } from '../types/domain';

type GroupsPayload = { groups: GameGroup[] };
type MembersPayload = { members: GameGroupMember[] };
type AddMemberPayload = GroupsPayload & MembersPayload;

const router = useRouter();
const groups = ref<GameGroup[]>([]);
const members = ref<GameGroupMember[]>([]);
const selectedGroup = ref<GameGroup | null>(null);
const loading = ref(false);
const error = ref('');
const message = ref('');

const groupForm = reactive({ name: '', description: '' });
const memberForm = reactive({ username: '', role: 'member' });

async function loadGroups() {
  const response = await apiGet<GroupsPayload>('groups/list');
  if (!response.ok) { router.replace('/login'); return; }
  groups.value = response.data?.groups || [];
}

async function loadMembers(groupId: number) {
  const response = await apiGet<MembersPayload>('groups/members', { group_id: groupId });
  if (response.ok) members.value = response.data?.members || [];
}

async function createGroup() {
  await run(async () => {
    const response = await apiPost<GroupsPayload>('groups/create', groupForm);
    if (!response.ok) { error.value = response.error || 'Creazione non riuscita'; return; }
    groups.value = response.data?.groups || [];
    groupForm.name = '';
    groupForm.description = '';
    message.value = 'Gruppo creato.';
  });
}

async function addMember() {
  if (!selectedGroup.value) return;
  await run(async () => {
    const response = await apiPost<AddMemberPayload>('groups/member/add', { group_id: selectedGroup.value?.id, username: memberForm.username, role: memberForm.role });
    if (!response.ok) { error.value = response.error || 'Inserimento non riuscito'; return; }
    groups.value = response.data?.groups || [];
    members.value = response.data?.members || [];
    memberForm.username = '';
    memberForm.role = 'member';
    message.value = 'Membro aggiunto.';
  });
}

async function selectGroup(group: GameGroup) {
  selectedGroup.value = group;
  members.value = [];
  await loadMembers(group.id);
}

async function run(action: () => Promise<void>) {
  loading.value = true;
  error.value = '';
  message.value = '';
  try { await action(); } finally { loading.value = false; }
}

function roleLabel(role: string) {
  return ({ owner: 'Owner', admin: 'Admin', member: 'Membro' } as Record<string, string>)[role] || role;
}

onMounted(loadGroups);
</script>
