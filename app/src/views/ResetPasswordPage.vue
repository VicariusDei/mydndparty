<template>
  <ion-page>
    <ion-content fullscreen class="app-page auth-page">
      <section class="hero-card auth-card">
        <p class="hero-eyebrow">Nuova password</p>
        <h1 class="hero-title">Completa il reset</h1>
        <p class="hero-subtitle">Scegli una nuova password di almeno 8 caratteri.</p>

        <form class="auth-form" @submit.prevent="submitReset">
          <ion-input v-model="password" label="Nuova password" label-placement="stacked" fill="outline" type="password" autocomplete="new-password" />

          <p class="auth-error" v-if="error">{{ error }}</p>
          <p class="auth-success" v-if="message">{{ message }}</p>

          <ion-button class="action-button" expand="block" type="submit" :disabled="loading || !token">
            {{ loading ? 'Aggiornamento...' : 'Aggiorna password' }}
          </ion-button>
        </form>

        <div class="auth-links">
          <router-link to="/login">Torna al login</router-link>
        </div>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import { IonButton, IonContent, IonInput, IonPage } from '@ionic/vue';
import { resetPassword } from '../services/auth';

const route = useRoute();
const token = computed(() => String(route.query.token || ''));
const password = ref('');
const loading = ref(false);
const error = ref('');
const message = ref('');

async function submitReset() {
  error.value = '';
  message.value = '';
  loading.value = true;

  try {
    const response = await resetPassword({ token: token.value, password: password.value });

    if (!response.ok) {
      error.value = response.error || 'Reset non riuscito';
      return;
    }

    message.value = response.data?.message || 'Password aggiornata.';
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Errore imprevisto durante il reset password.';
  } finally {
    loading.value = false;
  }
}
</script>
