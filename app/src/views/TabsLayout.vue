<template>
  <ion-page>
    <ion-tabs>
      <ion-router-outlet />
      <ion-tab-bar slot="bottom">
        <ion-tab-button tab="dashboard" href="/tabs/dashboard">
          <ion-icon :icon="homeOutline" />
          <ion-label>Campagna</ion-label>
        </ion-tab-button>
        <ion-tab-button tab="party" href="/tabs/party">
          <ion-icon :icon="peopleOutline" />
          <ion-label>Party</ion-label>
        </ion-tab-button>
        <ion-tab-button tab="inventory" href="/tabs/inventory">
          <ion-icon :icon="cubeOutline" />
          <ion-label>Inventario</ion-label>
        </ion-tab-button>
        <ion-tab-button tab="combat" href="/tabs/combat">
          <ion-icon :icon="flashOutline" />
          <ion-label>Combatti</ion-label>
        </ion-tab-button>
        <ion-tab-button tab="more" href="/tabs/more">
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
</script>
