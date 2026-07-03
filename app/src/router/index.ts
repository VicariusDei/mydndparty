import { createRouter, createWebHistory } from '@ionic/vue-router';
import TabsLayout from '../views/TabsLayout.vue';

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    component: () => import('../views/LoginPage.vue')
  },
  {
    path: '/register',
    component: () => import('../views/RegisterPage.vue')
  },
  {
    path: '/forgot-password',
    component: () => import('../views/ForgotPasswordPage.vue')
  },
  {
    path: '/reset-password',
    component: () => import('../views/ResetPasswordPage.vue')
  },
  {
    path: '/tabs/',
    component: TabsLayout,
    children: [
      {
        path: '',
        redirect: '/tabs/dashboard'
      },
      {
        path: 'dashboard',
        component: () => import('../views/DashboardPage.vue')
      },
      {
        path: 'notes',
        component: () => import('../views/PlayerNotesPage.vue')
      },
      {
        path: 'campaigns',
        component: () => import('../views/CampaignsPage.vue')
      },
      {
        path: 'party',
        component: () => import('../views/PartyPage.vue')
      },
      {
        path: 'inventory',
        component: () => import('../views/InventoryPage.vue')
      },
      {
        path: 'combat',
        component: () => import('../views/CombatPage.vue')
      },
      {
        path: 'more',
        component: () => import('../views/MorePage.vue')
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

export default router;
