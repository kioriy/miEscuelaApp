import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import MonitorView from '../views/MonitorView.vue';
import MonitorActivateView from '../views/MonitorActivateView.vue';
import AdminLayout from '../views/admin/AdminLayout.vue';
import DashboardView from '../views/admin/DashboardView.vue';
import SchoolsView from '../views/admin/SchoolsView.vue';
import SchoolDetailView from '../views/admin/SchoolDetailView.vue';
import SchoolFormView from '../views/admin/SchoolFormView.vue';
import UsersView from '../views/admin/UsersView.vue';
import UserDetailView from '../views/admin/UserDetailView.vue';
import UserFormView from '../views/admin/UserFormView.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginView
  },
  {
    path: '/monitor',
    name: 'Monitor',
    component: MonitorView
  },
  {
    path: '/monitor/activate',
    name: 'MonitorActivate',
    component: MonitorActivateView
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/admin/dashboard'
      },
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: DashboardView
      },
      {
        path: 'schools',
        name: 'AdminSchools',
        component: SchoolsView
      },
      {
        path: 'schools/create',
        name: 'SchoolForm',
        component: SchoolFormView
      },
      {
        path: 'schools/:id',
        name: 'SchoolDetail',
        component: SchoolDetailView
      },
      {
        path: 'schools/:id/edit',
        name: 'SchoolEdit',
        component: SchoolFormView
      },
      {
        path: 'users',
        name: 'AdminUsers',
        component: UsersView
      },
      {
        path: 'users/create',
        name: 'UserForm',
        component: UserFormView
      },
      {
        path: 'users/:id',
        name: 'UserDetail',
        component: UserDetailView
      },
      {
        path: 'users/:id/edit',
        name: 'UserEdit',
        component: UserFormView
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// === Global Route Guard ===
router.beforeEach((to, from, next) => {
  // 1. Bypass (Pause) Guards if we are in Localhost development
  const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

  if (isLocalhost) {
    console.warn('⚠️ [Router Guard] Bypassed for localhost development.');
    return next();
  }

  // 2. Add real authentication logic here for production
  // Example: const isAuthenticated = checkAuth();
  // const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  // if (requiresAuth && !isAuthenticated) {
  //   next('/login');
  // } else {
  //   next();
  // }

  next(); // Temporary pass-through until real auth is plugged in
});

export default router
