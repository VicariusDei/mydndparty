import { createRouter, createWebHistory } from '@ionic/vue-router';
import TabsLayout from '../views/TabsLayout.vue';

const routes = [
  {
    path: '/',
    redirect: '/tabs/dashboard'
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
