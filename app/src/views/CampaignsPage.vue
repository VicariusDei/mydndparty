<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Campagne</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Diario del master</p>
        <h1 class="hero-title">Campagne reali</h1>
        <p class="hero-subtitle">Gestisci elenco campagne, campagna attiva e note/diario recuperate dal vecchio modulo gruppi.</p>
      </section>

      <section class="section-block">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">{{ form.id ? 'Modifica campagna' : 'Nuova campagna' }}</p>
            <p class="entity-meta">Le note sono il diario master iniziale. Più avanti lo separeremo in eventi strutturati.</p>
            <ion-input v-model="form.name" label="Nome campagna" label-placement="stacked" fill="outline" />
            <ion-textarea v-model="form.notes" label="Note / diario" label-placement="stacked" fill="outline" :auto-grow="true" />

            <p class="auth-error" v-if="error">{{ error }}</p>
            <p class="auth-success" v-if="message">{{ message }}</p>

            <div class="action-row compact-actions">
              <ion-button class="action-button" expand="block" :disabled="loading || !form.name" @click="saveCampaign">
                {{ form.id ? 'Salva diario' : 'Crea campagna' }}
              </ion-button>
              <ion-button class="action-button" expand="block" fill="outline" :disabled="loading" @click="resetForm">Annulla</ion-button>
            </div>
          </div>
        </article>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="campaigns.length">
          <article class="fantasy-card entity-card" v-for="campaign in campaigns" :key="campaign.id">
            <div>
              <p class="entity-name">{{ campaign.name }}</p>
              <p class="entity-meta">{{ isActive(campaign) ? 'Campagna attiva' : 'Campagna non attiva' }}</p>
              <p class="entity-meta" v-if="campaign.notes">{{ campaign.notes }}</p>
              <p class="entity-meta" v-else>Nessuna nota reale presente.</p>
              <div class="badge-row">
                <span class="fantasy-badge" v-if="isActive(campaign)">Attiva</span>
                <span class="fantasy-badge" v-if="campaign.updated_at">Aggiornata</span>
              </div>
              <div class="badge-row">
                <ion-button size="small" fill="outline" :disabled="loading" @click="editCampaign(campaign)">Modifica</ion-button>
                <ion-button size="small" fill="outline" :disabled="loading || isActive(campaign)" @click="activateCampaign(campaign.id)">Rendi attiva</ion-button>
                <ion-button size="small" fill="outline" color="danger" :disabled="loading" @click="deleteCampaign(campaign.id)">Elimina se vuota</ion-button>
              </div>
            </div>
          </article>
        </div>

        <article class="fantasy-card entity-card" v-else>
          <div>
            <p class="entity-name">Nessuna campagna</p>
            <p class="entity-meta">Crea la prima campagna per alimentare dashboard, party, inventario e combattimenti.</p>
          </div>
        </article>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonContent, IonHeader, IonInput, IonPage, IonTextarea, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet, apiPost } from '../services/api';
import type { Campaign } from '../types/domain';

type CampaignsPayload = {
  campaigns: Campaign[];
  active_campaign: Campaign | null;
};

const router = useRouter();
const campaigns = ref<Campaign[]>([]);
const activeCampaign = ref<Campaign | null>(null);
const loading = ref(false);
const error = ref('');
const message = ref('');

const form = reactive({
  id: 0,
  name: '',
  notes: ''
});

function applyState(data?: CampaignsPayload) {
  campaigns.value = data?.campaigns || [];
  activeCampaign.value = data?.active_campaign || null;
}

async function loadCampaigns() {
  const response = await apiGet<CampaignsPayload>('campaigns/list');
  if (!response.ok) {
    router.replace('/login');
    return;
  }
  applyState(response.data);
}

async function runCampaignAction(route: string, payload: unknown, success: string) {
  loading.value = true;
  error.value = '';
  message.value = '';
  try {
    const response = await apiPost<CampaignsPayload>(route, payload);
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

async function saveCampaign() {
  const payload = {
    id: form.id,
    name: form.name,
    notes: form.notes
  };

  if (form.id) {
    await runCampaignAction('campaigns/update', payload, 'Campagna aggiornata.');
  } else {
    await runCampaignAction('campaigns/create', payload, 'Campagna creata.');
  }

  if (!error.value) {
    resetForm();
  }
}

function editCampaign(campaign: Campaign) {
  form.id = campaign.id;
  form.name = campaign.name;
  form.notes = campaign.notes || '';
  error.value = '';
  message.value = '';
}

async function activateCampaign(id: number) {
  await runCampaignAction('campaigns/activate', { id }, 'Campagna attivata.');
}

async function deleteCampaign(id: number) {
  await runCampaignAction('campaigns/delete', { id }, 'Campagna eliminata.');
  if (form.id === id) {
    resetForm();
  }
}

function resetForm() {
  form.id = 0;
  form.name = '';
  form.notes = '';
}

function isActive(campaign: Campaign) {
  return campaign.is_active === true || Number(campaign.is_active) === 1 || activeCampaign.value?.id === campaign.id;
}

onMounted(loadCampaigns);
</script>
