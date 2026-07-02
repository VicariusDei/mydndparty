<template>
  <ion-page>
    <ion-tabs>
      <ion-router-outlet />
      <ion-tab-bar slot="bottom">
        <ion-tab-button tab="dashboard" :href="tabHref('/tabs/dashboard')">
          <ion-icon :icon="homeOutline" />
          <ion-label>Dashboard</ion-label>
        </ion-tab-button>
        <ion-tab-button tab="party" :href="tabHref('/tabs/party')">
          <ion-icon :icon="peopleOutline" />
          <ion-label>Party</ion-label>
        </ion-tab-button>
        <ion-tab-button tab="inventory" :href="tabHref('/tabs/inventory')">
          <ion-icon :icon="cubeOutline" />
          <ion-label>Inventario</ion-label>
        </ion-tab-button>
        <ion-tab-button tab="combat" :href="tabHref('/tabs/combat')">
          <ion-icon :icon="flashOutline" />
          <ion-label>Combattimento</ion-label>
        </ion-tab-button>
        <ion-tab-button tab="more" :href="tabHref('/tabs/more')">
          <ion-icon :icon="ellipsisHorizontalCircleOutline" />
          <ion-label>Altro</ion-label>
        </ion-tab-button>
      </ion-tab-bar>
    </ion-tabs>
  </ion-page>
</template>

<script setup lang="ts">
import {
  IonIcon,
  IonLabel,
  IonPage,
  IonRouterOutlet,
  IonTabBar,
  IonTabButton,
  IonTabs
} from '@ionic/vue';
import {
  cubeOutline,
  ellipsisHorizontalCircleOutline,
  flashOutline,
  homeOutline,
  peopleOutline
} from 'ionicons/icons';

const appBase = import.meta.env.BASE_URL.replace(/\/$/, '');

function tabHref(path: string) {
  return `${appBase}${path}`;
}
</script>
