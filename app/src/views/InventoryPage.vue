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
        <p class="hero-subtitle">Oggetti identificati, quantità, valore e note recuperati dal database.</p>
      </section>

      <section class="section-block" v-if="wallet.length">
        <div class="entity-list">
          <article class="fantasy-card entity-card" v-for="coin in wallet" :key="coin.id">
            <div>
              <p class="entity-name">{{ coin.name }} · {{ coin.code }}</p>
              <p class="entity-meta">Disponibili {{ coin.quantity }} · deposito {{ coin.deposit_quantity }} · valore {{ coin.gold_value }} mo</p>
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
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonContent, IonHeader, IonPage, IonTitle, IonToolbar } from '@ionic/vue';
import { apiGet } from '../services/api';
import type { InventoryItem, WalletRow } from '../types/domain';

type InventoryPayload = {
  inventory_items: InventoryItem[];
  wallet: WalletRow[];
};

const router = useRouter();
const items = ref<InventoryItem[]>([]);
const wallet = ref<WalletRow[]>([]);

function isIdentified(item: InventoryItem) {
  return item.is_identified === true || Number(item.is_identified) === 1;
}

onMounted(async () => {
  const response = await apiGet<InventoryPayload>('inventory/list');
  if (!response.ok) {
    router.replace('/login');
    return;
  }

  items.value = response.data?.inventory_items || [];
  wallet.value = response.data?.wallet || [];
});
</script>
