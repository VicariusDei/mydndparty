<template>
  <ion-page>
    <ion-content fullscreen class="app-page auth-page">
      <section class="hero-card auth-card">
        <p class="hero-eyebrow">Accesso</p>
        <h1 class="hero-title">Entra nel party</h1>
        <p class="hero-subtitle">Accedi con email, username o account Google.</p>

        <form class="auth-form" @submit.prevent="submitLogin">
          <ion-input v-model="form.login" label="Email o username" label-placement="stacked" fill="outline" autocomplete="username" />
          <ion-input v-model="form.password" label="Password" label-placement="stacked" fill="outline" type="password" autocomplete="current-password" />

          <label class="auth-check">
            <ion-checkbox v-model="form.remember" />
            <span>Resta collegato su questo dispositivo</span>
          </label>

          <p class="auth-error" v-if="error">{{ error }}</p>
          <p class="auth-success" v-if="routeError">Accesso Google non completato: {{ routeError }}</p>

          <ion-button class="action-button" expand="block" type="submit" :disabled="loading">
            {{ loading ? 'Accesso...' : 'Accedi' }}
          </ion-button>

          <ion-button class="action-button" expand="block" fill="outline" type="button" @click="loginWithGoogle">
            Continua con Google
          </ion-button>
        </form>

        <div class="auth-links">
          <router-link to="/forgot-password">Password dimenticata?</router-link>
          <router-link to="/register">Crea account</router-link>
        </div>
      </section>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonButton, IonCheckbox, IonContent, IonInput, IonPage } from '@ionic/vue';
import { googleLoginUrl, login } from '../services/auth';

const router = useRouter();
const route = useRoute();
const loading = ref(false);
const error = ref('');
const routeError = computed(() => (route.query.error ? String(route.query.error) : ''));

const form = reactive({
  login: '',
  password: '',
  remember: true
});

async function submitLogin() {
  error.value = '';
  loading.value = true;

  try {
    const response = await login({ ...form });

    if (!response.ok) {
      error.value = response.error || 'Accesso non riuscito';
      return;
    }

    await router.replace('/tabs/dashboard');
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Errore imprevisto durante il login.';
  } finally {
    loading.value = false;
  }
}

function loginWithGoogle() {
  window.location.href = googleLoginUrl();
}
</script>
