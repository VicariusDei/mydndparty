<template>
  <ion-page>
    <ion-content fullscreen class="app-page auth-page">
      <section class="hero-card auth-card">
        <p class="hero-eyebrow">Recupero</p>
        <h1 class="hero-title">Reset password</h1>
        <p class="hero-subtitle">Inserisci l'email: se e' registrata riceverai un link valido 60 minuti.</p>

        <form class="auth-form" @submit.prevent="submitForgot">
          <ion-input v-model="email" label="Email" label-placement="stacked" fill="outline" type="email" autocomplete="email" />

          <p class="auth-error" v-if="error">{{ error }}</p>
          <p class="auth-success" v-if="message">{{ message }}</p>

          <ion-button class="action-button" expand="block" type="submit" :disabled="loading">
            {{ loading ? 'Invio...' : 'Invia link' }}
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
import { ref } from 'vue';
import { IonButton, IonContent, IonInput, IonPage } from '@ionic/vue';
import { forgotPassword } from '../services/auth';

const email = ref('');
const loading = ref(false);
const error = ref('');
const message = ref('');

async function submitForgot() {
  error.value = '';
  message.value = '';
  loading.value = true;

  try {
    const response = await forgotPassword({ email: email.value });

    if (!response.ok) {
      error.value = response.error || 'Invio non riuscito';
      return;
    }

    message.value = response.data?.message || 'Controlla la tua email.';
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Errore imprevisto durante il recupero password.';
  } finally {
    loading.value = false;
  }
}
</script>
