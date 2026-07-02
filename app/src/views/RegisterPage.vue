<template>
  <ion-page>
    <ion-content fullscreen class="app-page auth-page">
      <section class="hero-card auth-card">
        <p class="hero-eyebrow">Registrazione</p>
        <h1 class="hero-title">Nuovo avventuriero</h1>
        <p class="hero-subtitle">Crea l'account per gestire campagne, party e sessioni.</p>

        <form class="auth-form" @submit.prevent="submitRegister">
          <ion-input v-model="form.username" label="Username" label-placement="stacked" fill="outline" autocomplete="username" />
          <ion-input v-model="form.email" label="Email" label-placement="stacked" fill="outline" type="email" autocomplete="email" />
          <ion-input v-model="form.password" label="Password" label-placement="stacked" fill="outline" type="password" autocomplete="new-password" />

          <label class="auth-check">
            <ion-checkbox v-model="form.remember" />
            <span>Resta collegato dopo la registrazione</span>
          </label>

          <p class="auth-error" v-if="error">{{ error }}</p>

          <ion-button class="action-button" expand="block" type="submit" :disabled="loading">
            {{ loading ? 'Creazione...' : 'Crea account' }}
          </ion-button>
        </form>

        <div class="auth-links">
          <router-link to="/login">Hai gia' un account?</router-link>
        </div>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonButton, IonCheckbox, IonContent, IonInput, IonPage } from '@ionic/vue';
import { register } from '../services/auth';

const router = useRouter();
const loading = ref(false);
const error = ref('');
const form = reactive({
  username: '',
  email: '',
  password: '',
  remember: true
});

async function submitRegister() {
  error.value = '';
  loading.value = true;

  try {
    const response = await register({ ...form });

    if (!response.ok) {
      error.value = response.error || 'Registrazione non riuscita';
      return;
    }

    await router.replace('/tabs/dashboard');
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Errore imprevisto durante la registrazione.';
  } finally {
    loading.value = false;
  }
}
</script>
