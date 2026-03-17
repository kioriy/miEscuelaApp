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

            <router-link v-if="isTeacher || isDirector" to="/admin/messaging" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/messaging') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="chatbubbleOutline" class="text-xl"></ion-icon>
              Mensajes
            </router-link>
            
            <router-link v-if="isAdmin" to="/admin/schools" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/schools') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="business" class="text-xl"></ion-icon>
              Escuelas
            </router-link>

            <router-link v-if="isAdmin" to="/admin/users" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/users') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="people" class="text-xl"></ion-icon>
              Usuarios
            </router-link>

            <router-link v-if="isAdmin" to="/admin/kioscos" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/kioscos') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="storefrontOutline" class="text-xl"></ion-icon>
              Kioscos
            </router-link>

            <router-link v-if="currentProfile === 'super_admin' || currentProfile === 'director'" to="/admin/teachers" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/teachers') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="people" class="text-xl"></ion-icon>
              Profesores
            </router-link>

            <router-link v-if="currentProfile !== 'teacher'" to="/admin/students" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/students') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="school" class="text-xl"></ion-icon>
              Estudiantes
            </router-link>

            <router-link v-if="isDirector" to="/admin/reports" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/reports') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="barChart" class="text-xl"></ion-icon>
              Reportes
            </router-link>


            <router-link v-if="isAdmin || isDirector" to="/admin/sync-kiosk" @click="closeMobileMenu" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-semibold" active-class="bg-blue-50 text-brand-blue shadow-sm" :class="$route.path.includes('/sync-kiosk') ? 'bg-blue-50 text-brand-blue shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
              <ion-icon :icon="time" class="text-xl"></ion-icon>
              Sincronizar Kiosco
            </router-link>

          </nav>

          <!-- User Profile & Logout -->
          <div class="px-4 py-6 border-t border-gray-100 mt-auto bg-white">
            <ProfileSwitcher variant="sidebar" />
          </div>
        </div>
      </aside>

      <!-- Main Content Area -->
      <!-- Add pt-16 on mobile to account for fixed header, enable overflow-y-auto -->
      <main class="flex-1 overflow-y-auto w-full h-full relative pt-16 lg:pt-0 bg-gray-50 flex flex-col">
        
        <!-- Teacher Portal Specific Header (Visible only for teachers on Desktop) -->
        <div v-if="currentProfile === 'teacher'" class="hidden lg:flex items-center justify-between px-10 py-4 bg-white border-b border-gray-100 shrink-0">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-[#EBF4FF] rounded-2xl flex items-center justify-center text-brand-blue shadow-[0_4px_12px_rgba(59,130,246,0.15)]">
              <ion-icon :icon="school" class="text-2xl"></ion-icon>
            </div>
            <div>
              <h1 class="text-[22px] font-black text-gray-900 tracking-tight leading-none mb-1">Portal Docente</h1>
              <p class="text-[13px] text-gray-500 font-semibold tracking-wide">miEscuelaApp</p>
            </div>
          </div>

          <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 bg-gray-50 px-4 py-2.5 rounded-xl border border-gray-100">
               <ion-icon :icon="calendar" class="text-gray-400 text-lg"></ion-icon>
               <span class="text-[14px] font-semibold text-gray-600">{{ formattedDate }}</span>
            </div>
            
            <div class="h-8 w-px bg-gray-200"></div>

            <ProfileSwitcher variant="sidebar" />
          </div>
        </div>

        <ion-router-outlet class="flex-1" />
      </main>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { IonPage, IonIcon, IonRouterOutlet } from '@ionic/vue';
import { 
  school, grid, business, people, barChart, settings, time, menuOutline, closeOutline, personOutline, notifications, calendar, storefrontOutline, chevronDownOutline, chatbubbleOutline
} from 'ionicons/icons';
import { useRouter } from 'vue-router';
import { storage } from '@/services/storage';
import SchoolSwitcher from '@/components/SchoolSwitcher.vue';
import ProfileSwitcher from '@/components/ProfileSwitcher.vue';

const router = useRouter();

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
};

const currentProfile = ref('user');

onMounted(async () => {
  const profile = await storage.get('current_profile');
  if (profile) currentProfile.value = profile;
});

const isAdmin = computed(() => currentProfile.value === 'super_admin');
const isTeacher = computed(() => currentProfile.value === 'teacher');
const isDirector = computed(() => currentProfile.value === 'director');

const formattedDate = computed(() => {
  const options: Intl.DateTimeFormatOptions = { weekday: 'long', day: 'numeric', month: 'long' };
  const dateStr = new Date().toLocaleDateString('es-ES', options);
  return dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
});

const logout = async () => {
  closeMobileMenu();
  await storage.remove('auth_token');
  await storage.remove('auth_user');
  router.push('/login');
};
</script>
