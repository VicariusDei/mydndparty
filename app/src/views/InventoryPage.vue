<template>
  <ion-page>
    <ion-header translucent>
      <ion-toolbar>
        <ion-title>Inventario</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content fullscreen class="app-page">
      <section class="hero-card">
        <p class="hero-eyebrow">Tesori e oggetti</p>
        <h1 class="hero-title">Inventario reale</h1>
        <p class="hero-subtitle">CRUD oggetti, assegnazione al party/personaggio e gestione monete da database.</p>
      </section>

      <section class="section-block">
        <article class="fantasy-card entity-card">
          <div>
            <p class="entity-name">{{ form.id ? 'Modifica oggetto' : 'Nuovo oggetto' }}</p>
            <p class="entity-meta">Porting delle funzioni legacy di inventario: descrizione, quantità, valore, categoria, identificazione e note.</p>

            <ion-input v-model="form.name" label="Nome oggetto" label-placement="stacked" fill="outline" />
            <ion-input v-model="form.category" label="Categoria" label-placement="stacked" fill="outline" />
            <ion-input v-model="form.quantity" label="Quantità" label-placement="stacked" fill="outline" type="number" />
            <ion-input v-model="form.value_gold" label="Valore in oro" label-placement="stacked" fill="outline" type="number" />

            <ion-select v-model="form.owner_party_member_id" label="Proprietario" label-placement="stacked" fill="outline">
              <ion-select-option :value="0">Party</ion-select-option>
              <ion-select-option v-for="member in partyMembers" :key="member.id" :value="member.id">
                {{ member.character_name }} · {{ member.player_name }}
              </ion-select-option>
            </ion-select>

            <label class="auth-check">
              <ion-checkbox v-model="form.is_identified" />
              <span>Identificato</span>
            </label>

            <ion-input v-model="form.notes" label="Note" label-placement="stacked" fill="outline" />

            <p class="auth-error" v-if="error">{{ error }}</p>
            <p class="auth-success" v-if="message">{{ message }}</p>

            <div class="action-row compact-actions">
              <ion-button class="action-button" expand="block" :disabled="loading || !form.name" @click="saveItem">
                {{ form.id ? 'Salva modifiche' : 'Crea oggetto' }}
              </ion-button>
              <ion-button class="action-button" expand="block" fill="outline" :disabled="loading" @click="resetForm">Annulla</ion-button>
            </div>
          </div>
        </article>
      </section>

      <section class="section-block" v-if="wallet.length">
        <div class="entity-list">
          <article class="fantasy-card entity-card" v-for="coin in wallet" :key="coin.id">
            <div>
              <p class="entity-name">{{ coin.name }} · {{ coin.code }}</p>
              <p class="entity-meta">Disponibili {{ coin.quantity }} · deposito {{ coin.deposit_quantity }} · valore {{ coin.gold_value }} mo</p>
              <div class="badge-row">
                <ion-button size="small" fill="outline" :disabled="loading" @click="adjustWallet(coin.id, 1, 0)">+1 mano</ion-button>
                <ion-button size="small" fill="outline" :disabled="loading" @click="adjustWallet(coin.id, -1, 0)">-1 mano</ion-button>
                <ion-button size="small" fill="outline" :disabled="loading" @click="adjustWallet(coin.id, 0, 1)">+1 deposito</ion-button>
                <ion-button size="small" fill="outline" :disabled="loading" @click="adjustWallet(coin.id, 0, -1)">-1 deposito</ion-button>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section class="section-block">
        <div class="entity-list" v-if="items.length">
          <article class="fantasy-card entity-card" v-for="item in items" :key="item.id">
            <div>
              <p class="entity-name">{{ item.name }}</p>
              <p class="entity-meta">{{ item.category || 'Senza categoria' }} · qta {{ item.quantity }} · {{ item.value_gold }} mo</p>
              <div class="badge-row">
                <span class="fantasy-badge">{{ isIdentified(item) ? 'Identificato' : 'Da identificare' }}</span>
                <span class="fantasy-badge">{{ item.owner_character_name || 'Party' }}</span>
              </div>
              <p class="entity-meta" v-if="item.notes">{{ item.notes }}</p>
              <div class="badge-row">
                <ion-button size="small" fill="outline" :disabled="loading" @click="editItem(item)">Modifica</ion-button>
                <ion-button size="small" fill="outline" color="danger" :disabled="loading" @click="deleteItem(item.id)">Elimina</ion-button>
              </div>
            </div>
          </article>
        </div>

        <article class="fantasy-card entity-card" v-else>
          <div>
            <p class="entity-name">Nessun oggetto</p>
            <p class="entity-meta">L'inventario è vuoto per la campagna attiva.</p>
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
import type { InventoryItem, PartyMember, WalletRow } from '../types/domain';

type InventoryPayload = {
  inventory_items: InventoryItem[];
  wallet: WalletRow[];
};

type PartyPayload = {
  party_members: PartyMember[];
};

const router = useRouter();
const items = ref<InventoryItem[]>([]);
const wallet = ref<WalletRow[]>([]);
const partyMembers = ref<PartyMember[]>([]);
const loading = ref(false);
const error = ref('');
const message = ref('');

const form = reactive({
  id: 0,
  name: '',
  category: '',
  quantity: 1,
  value_gold: 0,
  owner_party_member_id: 0,
  is_identified: false,
  notes: ''
});

function applyState(data?: InventoryPayload) {
  items.value = data?.inventory_items || [];
  wallet.value = data?.wallet || [];
}

async function loadInventory() {
  const response = await apiGet<InventoryPayload>('inventory/list');
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

async function runInventoryAction(route: string, payload: unknown, success: string) {
  loading.value = true;
  error.value = '';
  message.value = '';
  try {
    const response = await apiPost<InventoryPayload>(route, payload);
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

function itemPayload() {
  return {
    id: form.id,
    name: form.name,
    category: form.category,
    quantity: Number(form.quantity) || 1,
    value_gold: Number(form.value_gold) || 0,
    owner_party_member_id: Number(form.owner_party_member_id) || 0,
    is_identified: form.is_identified,
    notes: form.notes
  };
}

async function saveItem() {
  if (form.id) {
    await runInventoryAction('inventory/update', itemPayload(), 'Oggetto aggiornato.');
  } else {
    await runInventoryAction('inventory/create', itemPayload(), 'Oggetto creato.');
  }
  if (!error.value) {
    resetForm();
  }
}

function editItem(item: InventoryItem) {
  form.id = item.id;
  form.name = item.name;
  form.category = item.category || '';
  form.quantity = Number(item.quantity) || 1;
  form.value_gold = Number(item.value_gold) || 0;
  form.owner_party_member_id = item.owner_party_member_id || 0;
  form.is_identified = isIdentified(item);
  form.notes = item.notes || '';
  message.value = '';
  error.value = '';
}

async function deleteItem(id: number) {
  await runInventoryAction('inventory/delete', { id }, 'Oggetto eliminato.');
  if (form.id === id) {
    resetForm();
  }
}

async function adjustWallet(walletId: number, quantityDelta: number, depositDelta: number) {
  await runInventoryAction('inventory/wallet/adjust', { wallet_id: walletId, quantity_delta: quantityDelta, deposit_delta: depositDelta }, 'Monete aggiornate.');
}

function resetForm() {
  form.id = 0;
  form.name = '';
  form.category = '';
  form.quantity = 1;
  form.value_gold = 0;
  form.owner_party_member_id = 0;
  form.is_identified = false;
  form.notes = '';
}

function isIdentified(item: InventoryItem) {
  return item.is_identified === true || Number(item.is_identified) === 1;
}

onMounted(async () => {
  await Promise.all([loadInventory(), loadParty()]);
});
</script>
