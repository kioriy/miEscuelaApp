<template>
  <ion-page>
    <div class="flex h-screen bg-gray-50 font-sans text-gray-900 overflow-hidden w-full relative">
      
      <!-- Mobile Header & Hamburger -->
      <div class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-100 flex items-center justify-between px-4 z-40 shadow-sm">
        <div class="flex items-center gap-2">
           <div class="w-8 h-8 bg-brand-blue rounded-lg flex items-center justify-center text-white shadow-md shadow-blue-500/20">
              <ion-icon :icon="school" class="text-sm"></ion-icon>
           </div>
            <h2 class="text-lg font-black text-gray-900 tracking-tight leading-none italic uppercase">miEscuelaApp</h2>
        </div>
        <button @click="toggleMobileMenu" class="p-2 -mr-2 text-gray-600 hover:text-brand-blue transition-colors focus:outline-none">
          <ion-icon :icon="menuOutline" class="text-3xl"></ion-icon>
        </button>
      </div>

      <!-- Backdrop for Mobile Sidebar -->
      <div 
        v-if="isMobileMenuOpen" 
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden transition-opacity"
        @click="closeMobileMenu"
      ></div>

      <!-- Sidebar -->
      <aside 
        :class="[
          'w-64 bg-white border-r border-gray-100 flex flex-col justify-between shrink-0 z-50 shadow-sm fixed inset-y-0 left-0 lg:relative lg:flex transition-transform duration-300 ease-in-out transform',
          isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
      >
        <div class="flex flex-col h-full bg-white relative">
          <!-- Logo Header (Sidebar) -->
          <div class="px-6 py-8 flex items-center justify-between gap-3 border-b border-gray-50 mb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-brand-blue rounded-xl flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                <ion-icon :icon="school" class="text-xl"></ion-icon>
              </div>
              <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight leading-none italic uppercase">miEscuelaApp</h2>
                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Sistema Escolar</span>
              </div>
            </div>
            <!-- Close button only on Mobile inside Sidebar -->
            <button @click="closeMobileMenu" class="lg:hidden text-gray-400 hover:text-gray-900 p-1">
               <ion-icon :icon="closeOutline" class="text-2xl"></ion-icon>
            </button>
          </div>

          <SchoolSwitcher />

          <!-- Navigation -->
          <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
            <router-link to="/admin/dashboard" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/dashboard') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="grid" class="text-xl"></ion-icon>
              Panel Principal
            </router-link>
            
            <router-link v-if="isAdmin" to="/admin/schools" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/schools') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="business" class="text-xl"></ion-icon>
              Escuelas
            </router-link>

            <router-link v-if="isAdmin" to="/admin/users" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/users') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="people" class="text-xl"></ion-icon>
              Usuarios
            </router-link>

            <router-link v-if="!isAdmin" to="/admin/teachers" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/teachers') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="people" class="text-xl"></ion-icon>
              Profesores
            </router-link>

            <router-link v-if="!isAdmin" to="/admin/students" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/students') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="school" class="text-xl"></ion-icon>
              Estudiantes
            </router-link>

            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold text-gray-500 hover:bg-gray-50 hover:text-gray-900">
              <ion-icon :icon="barChart" class="text-xl"></ion-icon>
              Reportes
            </a>

            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold text-gray-500 hover:bg-gray-50 hover:text-gray-900">
              <ion-icon :icon="settings" class="text-xl"></ion-icon>
              Configuración
            </a>

            <router-link v-if="isAdmin || userProfile.role === 'director'" to="/admin/sync-kiosk" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/sync-kiosk') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="time" class="text-xl"></ion-icon>
              Sincronizar Kiosco
            </router-link>

            <!-- Accesos Rápidos Section -->
            <div class="pt-8 pb-2 px-4">
              <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Accesos Rápidos</span>
            </div>

            <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-all group">
              <div class="flex items-center gap-3 font-semibold text-sm">
                <ion-icon :icon="notifications" class="text-xl text-gray-400 group-hover:text-amber-500"></ion-icon>
                Alertas
              </div>
              <span class="bg-red-50 text-red-600 text-[10px] font-black px-1.5 py-0.5 rounded-md">3</span>
            </a>

            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-all group">
              <ion-icon :icon="calendar" class="text-xl text-gray-400 group-hover:text-brand-blue"></ion-icon>
              <span class="font-semibold text-sm">Calendario</span>
            </a>
          </nav>

          <!-- User Profile & Logout -->
          <div class="px-4 py-6 border-t border-gray-100 mt-auto bg-white">
            <div class="flex items-center gap-3 px-2 mb-6">
              <div class="w-10 h-10 rounded-full bg-orange-100 overflow-hidden shrink-0 border border-gray-200 shadow-sm flex items-center justify-center">
                <img v-if="userProfile.photo" :src="userProfile.photo" :alt="userProfile.name" class="w-full h-full object-cover">
                <ion-icon v-else :icon="personOutline" class="text-xl text-gray-500"></ion-icon>
              </div>
              <div class="overflow-hidden">
                <p class="text-[13px] font-bold text-gray-900 truncate tracking-tight">{{ userProfile.name }}</p>
                <p class="text-[10px] text-brand-blue font-black uppercase tracking-tighter">{{ userRoleLabel }}</p>
                <p class="text-[10px] text-gray-400 font-medium truncate mt-0.5">{{ userProfile.email }}</p>
              </div>
            </div>
            <button @click="logout" class="w-full flex items-center justify-center gap-2 bg-red-50/50 border border-red-100 text-red-600 hover:bg-red-50 hover:border-red-200 transition-all py-3 rounded-xl font-bold text-sm shadow-sm">
              <ion-icon :icon="logOutOutline" class="text-lg"></ion-icon>
              Cerrar Sesión
            </button>
          </div>
        </div>
      </aside>

      <!-- Main Content Area -->
      <!-- Add pt-16 on mobile to account for fixed header, enable overflow-y-auto -->
      <main class="flex-1 overflow-y-auto w-full h-full relative pt-16 lg:pt-0 bg-gray-50">
        <ion-router-outlet />
      </main>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { IonPage, IonIcon, IonRouterOutlet } from '@ionic/vue';
import { 
  school, grid, business, people, barChart, settings, time, logOutOutline, menuOutline, closeOutline, personOutline, notifications, calendar
} from 'ionicons/icons';
import { useRouter } from 'vue-router';
import { storage } from '@/services/storage';
import SchoolSwitcher from '@/components/SchoolSwitcher.vue';

const router = useRouter();

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
};

const userProfile = ref({
  name: 'Cargando...',
  role: 'user',
  email: '',
  photo: ''
});

onMounted(async () => {
  const user = await storage.get('auth_user');
  if (user) {
    userProfile.value.name = user.name || 'Usuario';
    userProfile.value.role = user.role || 'user';
    userProfile.value.email = user.email || '';
    
    // Prioridad: 1. Google Avatar (avatar_url), 2. Local Photo (profile_photo_path)
    if (user.avatar_url) {
      userProfile.value.photo = user.avatar_url;
    } else if (user.profile_photo_path) {
      userProfile.value.photo = user.profile_photo_path.startsWith('http') 
        ? user.profile_photo_path 
        : `http://localhost:8000/storage/${user.profile_photo_path}`;
    }
  }
});

const isAdmin = computed(() => userProfile.value.role === 'super_admin');

const userRoleLabel = computed(() => {
  switch(userProfile.value.role) {
    case 'super_admin': return 'Súper Admin / Sistemas';
    case 'director': return 'Director General';
    case 'teacher': return 'Profesor(a)';
    case 'parent': return 'Padre / Tutor';
    default: return 'Usuario Registrado';
  }
});

const logout = async () => {
  closeMobileMenu();
  await storage.remove('auth_token');
  await storage.remove('auth_user');
  router.push('/login');
};
</script>
