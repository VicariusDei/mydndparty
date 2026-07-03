<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar><ion-title>Gruppi</ion-title></ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Portale sociale</p>
        <h1 class="hero-title">Gruppi di gioco</h1>
        <p class="hero-subtitle">Gruppi, campagne e ruoli contestuali.</p>
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

      <section class="section-block">
        <div class="entity-list" v-if="groups.length">
          <article class="fantasy-card entity-card" v-for="group in groups" :key="group.id">
            <div>
              <p class="entity-name">{{ group.name }}</p>
              <p class="entity-meta">{{ group.description || 'Nessuna descrizione.' }}</p>
              <div class="badge-row">
                <span class="fantasy-badge">{{ groupRoleLabel(group.my_role || 'member') }}</span>
                <span class="fantasy-badge">{{ group.members_count || 0 }} membri</span>
                <span class="fantasy-badge">{{ group.campaigns_count || 0 }} campagne</span>
              </div>
              <ion-button size="small" fill="outline" :disabled="loading" @click="selectGroup(group)">Gestisci gruppo</ion-button>
            </div>
          </article>
        </div>
        <article class="fantasy-card entity-card" v-else><div><p class="entity-name">Nessun gruppo</p><p class="entity-meta">Crea il primo gruppo di gioco.</p></div></article>
      </section>

      <section class="section-block" v-if="selectedGroup">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">{{ selectedGroup.name }}</p>
            <p class="entity-meta">Membri e campagne del gruppo selezionato.</p>
          </div>
        </article>

        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">Aggiungi membro</p>
            <ion-input v-model="memberForm.username" label="Username" label-placement="stacked" fill="outline" />
            <ion-select v-model="memberForm.role" label="Ruolo nel gruppo" label-placement="stacked" fill="outline">
              <ion-select-option value="member">Membro</ion-select-option>
              <ion-select-option value="admin">Admin gruppo</ion-select-option>
            </ion-select>
            <ion-button class="action-button" expand="block" :disabled="loading || !memberForm.username" @click="addMember">Aggiungi membro</ion-button>
          </div>
        </article>

        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">Nuova campagna nel gruppo</p>
            <ion-input v-model="campaignForm.name" label="Nome campagna" label-placement="stacked" fill="outline" />
            <ion-textarea v-model="campaignForm.notes" label="Note campagna" label-placement="stacked" fill="outline" :auto-grow="true" />
            <ion-button class="action-button" expand="block" :disabled="loading || !campaignForm.name" @click="createCampaign">Crea campagna</ion-button>
          </div>
        </article>

        <div class="entity-list" v-if="members.length">
          <article class="fantasy-card entity-card" v-for="member in members" :key="member.id">
            <div>
              <p class="entity-name">{{ member.display_name || member.username }}</p>
              <p class="entity-meta">@{{ member.username }} · {{ groupRoleLabel(member.role) }} · {{ member.status }}</p>
            </div>
          </article>
        </div>

        <div class="entity-list" v-if="campaigns.length">
          <article class="fantasy-card entity-card" v-for="campaign in campaigns" :key="campaign.id">
            <div>
              <p class="entity-name">{{ campaign.name }}</p>
              <p class="entity-meta">Master iniziale: {{ campaign.owner_display_name || campaign.owner_username }} · {{ campaign.participants_count || 0 }} partecipanti</p>
              <p class="entity-meta" v-if="campaign.notes">{{ campaign.notes }}</p>
              <div class="badge-row">
                <ion-button size="small" fill="outline" :disabled="loading" @click="selectCampaign(campaign)">Partecipanti</ion-button>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section class="section-block" v-if="selectedCampaign">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">Partecipanti · {{ selectedCampaign.name }}</p>
            <ion-select v-model="participantForm.user_id" label="Membro del gruppo" label-placement="stacked" fill="outline">
              <ion-select-option v-for="member in members" :key="member.user_id" :value="member.user_id">{{ member.display_name || member.username }}</ion-select-option>
            </ion-select>
            <ion-select v-model="participantForm.role" label="Ruolo in campagna" label-placement="stacked" fill="outline">
              <ion-select-option value="player">Giocatore</ion-select-option>
              <ion-select-option value="master">Master</ion-select-option>
              <ion-select-option value="co_master">Co-master</ion-select-option>
              <ion-select-option value="viewer">Spettatore</ion-select-option>
            </ion-select>
            <ion-button class="action-button" expand="block" :disabled="loading || !participantForm.user_id" @click="addParticipant">Aggiungi alla campagna</ion-button>
          </div>
        </article>

        <div class="entity-list" v-if="participants.length">
          <article class="fantasy-card entity-card" v-for="participant in participants" :key="participant.id">
            <div>
              <p class="entity-name">{{ participant.display_name || participant.username }}</p>
              <p class="entity-meta">@{{ participant.username }} · {{ campaignRoleLabel(participant.role) }} · {{ participant.status }}</p>
              <p class="entity-meta" v-if="participant.character_name">PG: {{ participant.character_name }}</p>
            </div>
          </article>
        </div>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonHeader, IonInput, IonPage, IonSelect, IonSelectOption, IonTextarea, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet, apiPost } from '../services/api';
import type { CampaignParticipant, GameGroup, GameGroupMember, GroupCampaign } from '../types/domain';

type GroupsPayload = { groups: GameGroup[] };
type MembersPayload = { members: GameGroupMember[] };
type CampaignsPayload = { campaigns: GroupCampaign[]; members?: GameGroupMember[] };
type ParticipantsPayload = { participants: CampaignParticipant[]; campaigns?: GroupCampaign[] };

const router = useRouter();
const groups = ref<GameGroup[]>([]);
const members = ref<GameGroupMember[]>([]);
const campaigns = ref<GroupCampaign[]>([]);
const participants = ref<CampaignParticipant[]>([]);
const selectedGroup = ref<GameGroup | null>(null);
const selectedCampaign = ref<GroupCampaign | null>(null);
const loading = ref(false);
const error = ref('');
const message = ref('');

const groupForm = reactive({ name: '', description: '' });
const memberForm = reactive({ username: '', role: 'member' });
const campaignForm = reactive({ name: '', notes: '' });
const participantForm = reactive({ user_id: 0, role: 'player' });

async function loadGroups() {
  const response = await apiGet<GroupsPayload>('groups/list');
  if (!response.ok) { router.replace('/login'); return; }
  groups.value = response.data?.groups || [];
}

async function loadMembers(groupId: number) {
  const response = await apiGet<MembersPayload>('groups/members', { group_id: groupId });
  if (response.ok) members.value = response.data?.members || [];
}

async function loadCampaigns(groupId: number) {
  const response = await apiGet<CampaignsPayload>('group-campaigns/list', { group_id: groupId });
  if (response.ok) campaigns.value = response.data?.campaigns || [];
}

async function loadParticipants(campaignId: number) {
  const response = await apiGet<ParticipantsPayload>('group-campaigns/participants', { campaign_id: campaignId });
  if (response.ok) participants.value = response.data?.participants || [];
}

async function createGroup() {
  await run(async () => {
    const response = await apiPost<GroupsPayload>('groups/create', groupForm);
    if (!response.ok) { error.value = response.error || 'Creazione non riuscita'; return; }
    groups.value = response.data?.groups || [];
    groupForm.name = ''; groupForm.description = ''; message.value = 'Gruppo creato.';
  });
}

async function addMember() {
  if (!selectedGroup.value) return;
  await run(async () => {
    const response = await apiPost<MembersPayload & GroupsPayload>('groups/member/add', { group_id: selectedGroup.value?.id, username: memberForm.username, role: memberForm.role });
    if (!response.ok) { error.value = response.error || 'Inserimento non riuscito'; return; }
    groups.value = response.data?.groups || []; members.value = response.data?.members || [];
    memberForm.username = ''; memberForm.role = 'member'; message.value = 'Membro aggiunto.';
  });
}

async function createCampaign() {
  if (!selectedGroup.value) return;
  await run(async () => {
    const response = await apiPost<CampaignsPayload>('group-campaigns/create', { group_id: selectedGroup.value?.id, name: campaignForm.name, notes: campaignForm.notes });
    if (!response.ok) { error.value = response.error || 'Creazione campagna non riuscita'; return; }
    campaigns.value = response.data?.campaigns || [];
    if (response.data?.members) members.value = response.data.members;
    campaignForm.name = ''; campaignForm.notes = ''; message.value = 'Campagna creata nel gruppo.';
    await loadGroups();
  });
}

async function addParticipant() {
  if (!selectedCampaign.value) return;
  await run(async () => {
    const response = await apiPost<ParticipantsPayload>('group-campaigns/participant/add', { campaign_id: selectedCampaign.value?.id, user_id: participantForm.user_id, role: participantForm.role });
    if (!response.ok) { error.value = response.error || 'Aggiunta partecipante non riuscita'; return; }
    participants.value = response.data?.participants || [];
    if (response.data?.campaigns) campaigns.value = response.data.campaigns;
    participantForm.user_id = 0; participantForm.role = 'player'; message.value = 'Partecipante aggiunto.';
  });
}

async function selectGroup(group: GameGroup) {
  selectedGroup.value = group; selectedCampaign.value = null; members.value = []; campaigns.value = []; participants.value = [];
  await Promise.all([loadMembers(group.id), loadCampaigns(group.id)]);
}

async function selectCampaign(campaign: GroupCampaign) {
  selectedCampaign.value = campaign; participants.value = []; await loadParticipants(campaign.id);
}

async function run(action: () => Promise<void>) { loading.value = true; error.value = ''; message.value = ''; try { await action(); } finally { loading.value = false; } }
function groupRoleLabel(role: string) { return ({ owner: 'Owner', admin: 'Admin', member: 'Membro' } as Record<string, string>)[role] || role; }
function campaignRoleLabel(role: string) { return ({ master: 'Master', co_master: 'Co-master', player: 'Giocatore', viewer: 'Spettatore' } as Record<string, string>)[role] || role; }

onMounted(loadGroups);
</script>
