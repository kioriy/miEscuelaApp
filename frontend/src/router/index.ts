import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import MonitorView from '../views/MonitorView.vue';
import MonitorActivateView from '../views/MonitorActivateView.vue';
import AdminLayout from '../views/admin/AdminLayout.vue';
import DashboardView from '../views/admin/DashboardView.vue';
import SchoolsView from '../views/admin/SchoolsView.vue';
import KioscosView from '../views/admin/KioscosView.vue';
import SchoolDetailView from '../views/admin/SchoolDetailView.vue';
import SchoolFormView from '../views/admin/SchoolFormView.vue';
import UsersView from '../views/admin/UsersView.vue';
import UserDetailView from '../views/admin/UserDetailView.vue';
import UserFormView from '../views/admin/UserFormView.vue';
import StudentsView from '../views/admin/StudentsView.vue';
import StudentDetailView from '../views/admin/StudentDetailView.vue';
import StudentFormView from '../views/admin/StudentFormView.vue';
import TimeSyncView from '../views/admin/TimeSyncView.vue';
import InstitutionalMessagingView from '../views/admin/InstitutionalMessagingView.vue';
import { storage, storageReady } from '@/services/storage';

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
    path: '/privacy',
    name: 'Privacy',
    component: () => import('../views/PrivacyView.vue')
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
    path: '/parent/dashboard',
    name: 'ParentDashboard',
    component: () => import('../views/parent/ParentDashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/parent/authorized-persons/create',
    name: 'AuthorizedPersonCreate',
    component: () => import('../views/parent/AuthorizedPersonFormView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/parent/history',
    name: 'ParentHistory',
    component: () => import('../views/parent/ParentHistoryView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/parent/messages',
    name: 'ParentMessages',
    component: () => import('../views/parent/ParentMessagesView.vue'),
    meta: { requiresAuth: true }
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
        path: 'teacher/dashboard',
        name: 'TeacherDashboard',
        component: DashboardView
      },
      {
        path: 'teacher/attendance/:classroomId',
        name: 'TeacherAttendance',
        component: () => import('../views/admin/TeacherAttendanceView.vue')
      },
      {
        path: 'messaging',
        name: 'InstitutionalMessaging',
        component: InstitutionalMessagingView
      },
      {
        path: 'teacher/messaging',
        name: 'TeacherMessaging',
        component: InstitutionalMessagingView
      },
      {
        path: 'schools',
        name: 'AdminSchools',
        component: SchoolsView
      },
      {
        path: 'kioscos',
        name: 'AdminKioscos',
        component: KioscosView
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
      },
      {
        path: 'students',
        name: 'AdminStudents',
        component: StudentsView
      },
      {
        path: 'students/:id',
        name: 'StudentDetail',
        component: StudentDetailView
      },
      {
        path: 'students/create',
        name: 'StudentCreate',
        component: StudentFormView
      },
      {
        path: 'students/:id/edit',
        name: 'StudentEdit',
        component: StudentFormView
      },
      {
        path: 'teachers',
        name: 'AdminTeachers',
        component: () => import('../views/admin/TeachersView.vue')
      },
      {
        path: 'teachers/:id/detail',
        name: 'TeacherDetail',
        component: () => import('../views/admin/TeacherDetailView.vue')
      },
      {
        path: 'teachers/create',
        name: 'TeacherCreate',
        component: () => import('../views/admin/TeacherFormView.vue')
      },
      {
        path: 'teachers/:id/edit',
        name: 'TeacherEdit',
        component: () => import('../views/admin/TeacherFormView.vue')
      },
      {
        path: 'sync-kiosk',
        name: 'TimeSync',
        component: TimeSyncView
      },
      {
        path: 'staff-status',
        name: 'StaffStatus',
        component: () => import('../views/admin/StaffStatusView.vue')
      },
      {
        path: 'reports',
        name: 'AdminReports',
        component: () => import('../views/admin/ReportsView.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// === Global Route Guard ===
router.beforeEach(async (to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  // Asegurarse de que el storage esté inicializado
  await storageReady;

  // Intentar obtener el token de storage
  const token = await storage.get('auth_token');
  const isAuthenticated = !!token;

  if (requiresAuth && !isAuthenticated) {
    console.warn('🔒 [Router Guard] Acceso denegado: Se requiere autenticación.');
    return next('/login');
  }

  // Si ya está autenticado e intenta ir al login, mandarlo al dashboard
  if (to.path === '/login' && isAuthenticated) {
    const userStr = await storage.get('auth_user');
    let role = 'guest';
    if (userStr) {
      try { role = (typeof userStr === 'string' ? JSON.parse(userStr) : userStr).role; } catch (e) { }
    }

    if (role === 'parent') {
      return next('/parent/dashboard');
    } else if (role === 'super_admin' || role === 'director' || role === 'teacher') {
      return next('/admin/dashboard');
    } else {
      return next('/monitor');
    }
  }

  next();
});

export default router
