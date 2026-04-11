<template>
  <ion-page>
    <ion-content class="ion-padding-bottom">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>
      <div class="p-8 lg:p-12 w-full max-w-[1400px] mx-auto min-h-full flex flex-col bg-gray-50">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
      <div>
        <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">Administración de Usuarios del Sistema</h1>
        <p class="text-gray-500 font-medium mb-1.5">Gestiona el personal y los niveles de acceso en todas las instituciones.</p>
        <p v-if="activeSchoolName" class="text-[15px] font-black text-brand-blue flex items-center gap-1.5 mt-1"><ion-icon :icon="business"></ion-icon> {{ activeSchoolName }}</p>
      </div>
          <div class="flex items-center gap-3 shrink-0">
            <button @click="fetchUsers(true)" class="bg-white border border-gray-200 text-gray-700 font-bold w-10 h-10 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center justify-center transition-all">
              <ion-icon :icon="refreshOutline" class="text-lg"></ion-icon>
            </button>
            <button class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center gap-2 transition-all">
              <ion-icon :icon="downloadOutline" class="text-lg"></ion-icon>
              Exportar Lista
            </button>
        <button @click="$router.push('/admin/users/create')" class="bg-brand-blue text-white font-bold py-2.5 px-5 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 flex items-center gap-2 transition-all">
          <ion-icon :icon="personAddOutline" class="text-lg"></ion-icon>
          Invitar Usuario
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
      <!-- Usuarios Totales -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Usuarios Totales</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-gray-900 leading-none tracking-tight">
              <span v-if="loadingUsers">...</span>
              <span v-else>{{ users.length }}</span>
            </span>
            <span class="text-[11px] font-bold text-green-700 bg-green-100/50 border border-green-200 px-2 py-0.5 rounded-full flex items-center gap-1">
               Registrados
            </span>
          </div>
        </div>
      </div>
      <!-- Directores -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Directores</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-indigo-600 leading-none tracking-tight">
              <span v-if="loadingUsers">...</span>
              <span v-else>{{ directorsCount }}</span>
            </span>
            <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-full flex items-center gap-1">
               Activos
            </span>
          </div>
        </div>
      </div>
      <!-- Maestros -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Maestros</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-brand-blue leading-none tracking-tight">
              <span v-if="loadingUsers">...</span>
              <span v-else>{{ teachersCount }}</span>
            </span>
            <span class="text-[11px] font-bold text-brand-blue bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full flex items-center gap-1">
               Activos
            </span>
          </div>
        </div>
      </div>
      <!-- Padres / Tutores -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Padres / Tutores</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-emerald-600 leading-none tracking-tight">
              <span v-if="loadingUsers">...</span>
              <span v-else>{{ parentsCount }}</span>
            </span>
            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1">
               Registrados
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-10">
      
      <!-- Toolbar -->
      <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row items-center gap-4">
        <div class="flex-grow w-full relative">
            <ion-icon :icon="searchOutline" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></ion-icon>
            <input type="text" v-model="searchQuery" @input="debouncedSearch" placeholder="Buscar por nombre o email..." class="pl-10 pr-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-blue focus:ring-1 focus:ring-brand-blue text-[13px] font-medium text-gray-700 w-full transition-colors" />
        </div>
        
        <div class="flex items-center gap-3 shrink-0">
          <div class="relative">
             <select v-model="filterRole" @change="fetchUsers(true)" class="appearance-none bg-gray-50/50 border border-gray-200 text-gray-700 text-[13px] font-bold rounded-xl py-2.5 pl-4 pr-10 focus:outline-none focus:ring-1 focus:ring-brand-blue focus:border-brand-blue cursor-pointer">
                <option value="">Todos los Roles</option>
                <option value="super_admin">Súper Admin</option>
                <option value="director">Director</option>
                <option value="teacher">Maestro</option>
                <option value="parent">Padre / Tutor</option>
             </select>
             <ion-icon :icon="chevronDown" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
          </div>
          
          <div class="relative">
             <select v-model="filterSchoolId" @change="fetchUsers(true)" class="appearance-none bg-gray-50/50 border border-gray-200 text-gray-700 text-[13px] font-bold rounded-xl py-2.5 pl-4 pr-10 focus:outline-none focus:ring-1 focus:ring-brand-blue focus:border-brand-blue cursor-pointer">
                <option value="">Todas las Escuelas</option>
                <option v-for="s in schoolsList" :key="s.id" :value="s.id">{{ s.name }}</option>
             </select>
             <ion-icon :icon="chevronDown" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
          </div>

          <button @click="clearFilters" title="Limpiar filtros" class="w-10 h-10 flex items-center justify-center border border-gray-200 bg-white rounded-xl text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-colors" :class="hasActiveFilters ? 'border-brand-blue text-brand-blue bg-blue-50' : ''">
            <ion-icon :icon="filter" class="text-base"></ion-icon>
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-hidden">
        <table class="w-full text-left border-collapse table-fixed">
          <thead>
            <tr class="bg-gray-50/50 text-[10px] font-black tracking-widest text-gray-500 uppercase border-b border-gray-100">
              <th class="p-3 pl-5" style="width: 22%">Nombre</th>
              <th class="p-3" style="width: 22%">Email</th>
              <th class="p-3" style="width: 20%">Escuela</th>
              <th class="p-3" style="width: 10%">Rol</th>
              <th class="p-3 pr-5 text-right" style="width: 26%">Acciones</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-if="loadingUsers">
              <td colspan="5" class="p-8 text-center text-gray-400 font-medium">Cargando usuarios...</td>
            </tr>
            <tr v-else-if="users.length === 0">
              <td colspan="5" class="p-8 text-center text-gray-400 font-medium">No hay usuarios registrados.</td>
            </tr>
            <tr v-else v-for="user in users" :key="user.id" class="border-b border-gray-50 hover:bg-gray-50/80 transition-colors">
              <td class="p-3 pl-5">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center font-black border border-orange-200 shadow-sm overflow-hidden shrink-0">
                    <img v-if="getUserPhoto(user)" :src="getUserPhoto(user)" class="w-full h-full object-cover" />
                    <ion-icon v-else :icon="personOutline" class="text-base text-gray-400"></ion-icon>
                  </div>
                  <p class="font-bold text-gray-900 text-[13px] leading-tight truncate">{{ user.name }}</p>
                </div>
              </td>
              <td class="p-3">
                <p class="text-brand-blue font-medium text-[12px] truncate">{{ user.email }}</p>
              </td>
              <td class="p-3">
                <p class="font-semibold text-gray-700 text-[12px] truncate leading-snug" :title="getAssignedSchools(user)">{{ getAssignedSchools(user) }}</p>
              </td>
              <td class="p-3">
                <span v-if="user.role === 'super_admin'" class="bg-gray-100 border border-gray-200 text-gray-600 text-[10px] px-2 py-0.5 rounded-full font-bold whitespace-nowrap">Admin</span>
                <span v-else-if="user.role === 'director'" class="bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] px-2 py-0.5 rounded-full font-bold whitespace-nowrap">Director</span>
                <span v-else-if="user.role === 'teacher'" class="bg-brand-blue/10 border border-brand-blue/20 text-brand-blue text-[10px] px-2 py-0.5 rounded-full font-bold whitespace-nowrap">Maestro</span>
                <span v-else-if="user.role === 'parent'" class="bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] px-2 py-0.5 rounded-full font-bold whitespace-nowrap">Padre</span>
                <span v-else class="bg-brand-blue/10 border border-brand-blue/20 text-brand-blue text-[10px] px-2 py-0.5 rounded-full font-bold whitespace-nowrap">{{ user.role }}</span>
              </td>
              <td class="p-3 pr-5">
                <div class="flex items-center justify-end gap-1.5">
                  <button v-if="user.role !== 'super_admin'" @click="impersonateUser(user)" :disabled="impersonatingId === user.id" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" :title="impersonatingId === user.id ? 'Ingresando...' : 'Suplantar usuario'">
                    <ion-icon :icon="enterOutline" class="text-base"></ion-icon>
                  </button>
                  <button @click="$router.push(`/admin/users/${user.id}`)" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-brand-blue hover:bg-blue-50 transition-colors" title="Ver detalle">
                    <ion-icon :icon="eyeOutline" class="text-base"></ion-icon>
                  </button>
                  <button @click="$router.push(`/admin/users/${user.id}/edit`)" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-900 hover:bg-gray-100 transition-colors" title="Editar">
                    <ion-icon :icon="createOutline" class="text-base"></ion-icon>
                  </button>
                  <button class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="Eliminar">
                    <ion-icon :icon="trashOutline" class="text-base"></ion-icon>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t border-gray-100 flex items-center justify-between">
        <span class="text-[13px] text-gray-500 font-medium">Mostrando <strong class="text-gray-900 font-black">{{ users.length }}</strong> de <strong class="text-gray-900 font-black">{{ users.length }}</strong> usuarios</span>
        <div class="flex items-center gap-2">
           <button class="px-3 py-1.5 border border-gray-200 bg-gray-50/50 rounded-lg text-gray-400 font-semibold cursor-not-allowed text-[13px]">Anterior</button>
           <button class="px-3 py-1.5 border border-gray-200 bg-white shadow-sm rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors text-[13px]">Siguiente</button>
        </div>
      </div>
    </div>


  </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent } from '@ionic/vue';
import { 
  downloadOutline, personAddOutline, searchOutline, chevronDown, filter,
  createOutline, trashOutline, eyeOutline, push, documentText, grid, refreshOutline, personOutline, business, enterOutline
} from 'ionicons/icons';
import { toastController } from '@ionic/vue';
import api from '@/services/api';
import { storage } from '@/services/storage';

const loadingStats = ref(true);
const loadingUsers = ref(true);
const activeSchoolName = ref('');

// Filtros reactivos
const searchQuery = ref('');
const filterRole = ref('');
const filterSchoolId = ref('');
const schoolsList = ref<any[]>([]);

let searchTimeout: any = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchUsers(true);
  }, 300);
};

const hasActiveFilters = computed(() => {
  return searchQuery.value !== '' || filterRole.value !== '' || filterSchoolId.value !== '';
});

const clearFilters = () => {
  searchQuery.value = '';
  filterRole.value = '';
  filterSchoolId.value = '';
  fetchUsers(true);
};

const stats = ref({
  schools: 0,
  students: 0,
  users: 0,
  kiosks: 0,
  systemHealth: 0
});

const users = ref<any[]>([]);

// Computed: conteos por rol
const directorsCount = computed(() => users.value.filter(u => u.role === 'director').length);
const teachersCount = computed(() => users.value.filter(u => u.role === 'teacher').length);
const parentsCount = computed(() => users.value.filter(u => u.role === 'parent').length);

const getUserPhoto = (user: any) => {
  if (user.avatar_url) return user.avatar_url;
  if (user.profile_photo_path) {
    return user.profile_photo_path.startsWith('http') 
      ? user.profile_photo_path 
      : `${import.meta.env.VITE_API_URL?.replace('/api', '') || ''}/storage/${user.profile_photo_path}`;
  }
  return null;
};

const getAssignedSchools = (user: any) => {
  const schoolNames: string[] = [];
  if (user.school) schoolNames.push(user.school.name);
  if (user.schools && user.schools.length > 0) {
    user.schools.forEach((s: any) => {
      if (!schoolNames.includes(s.name)) {
        schoolNames.push(s.name);
      }
    });
  }
  return schoolNames.length > 0 ? schoolNames.join(', ') : 'Agencia Central';
};

const fetchDashboardStats = async () => {
  try {
    const res = await api.get('/admin/stats');
    if (res.data.success) {
      stats.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching dashboard stats', error);
  } finally {
    loadingStats.value = false;
  }
};

const fetchSchoolsList = async () => {
  try {
    const res = await api.get('/admin/schools');
    if (res.data.success) {
      schoolsList.value = res.data.data.map((s: any) => ({ id: s.id, name: s.name }));
    }
  } catch (error) {
    console.error('Error fetching schools list', error);
  }
};

const fetchUsers = async (forceRefresh = false) => {
  if (forceRefresh) loadingUsers.value = true;
  try {
    const params: any = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (filterRole.value) params.role = filterRole.value;
    if (filterSchoolId.value) params.school_id = filterSchoolId.value;

    const res = await api.get('/admin/users', { params });
    if (res.data.success) {
      users.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching users', error);
  } finally {
    loadingUsers.value = false;
  }
};

const impersonatingId = ref<number | null>(null);

const impersonateUser = async (user: any) => {
  if (impersonatingId.value) return;
  impersonatingId.value = user.id;
  
  try {
    // Save current admin session before impersonating
    const currentToken = await storage.get('auth_token');
    const currentUser = await storage.get('auth_user');
    const currentSchools = await storage.get('user_schools');
    const currentProfiles = await storage.get('available_profiles');
    const currentSchoolId = await storage.get('current_school_id');
    
    // Call impersonation endpoint
    const res = await api.post(`/admin/impersonate/${user.id}`);
    
    if (res.data.success) {
      // Store admin session for later restoration
      await storage.set('impersonation_admin_token', currentToken);
      await storage.set('impersonation_admin_user', currentUser);
      await storage.set('impersonation_admin_schools', currentSchools);
      await storage.set('impersonation_admin_profiles', currentProfiles);
      await storage.set('impersonation_admin_school_id', currentSchoolId);
      
      // Set the impersonated user's session
      await storage.set('auth_token', res.data.token);
      await storage.set('auth_user', res.data.user);
      await storage.set('user_schools', res.data.schools || []);
      await storage.set('available_profiles', res.data.available_profiles || [res.data.user.role]);
      
      // Set the initial profile for the impersonated user
      const profiles = res.data.available_profiles || [res.data.user.role];
      let initialProfile = res.data.user.role;
      if (profiles.includes('director')) initialProfile = 'director';
      else if (profiles.includes('teacher')) initialProfile = 'teacher';
      else if (profiles.includes('parent')) initialProfile = 'parent';
      
      await storage.set('current_profile', initialProfile);
      
      // Set school context
      if (res.data.schools && res.data.schools.length > 0) {
        await storage.set('current_school_id', res.data.schools[0].id);
      } else if (res.data.user.school_id) {
        await storage.set('current_school_id', res.data.user.school_id);
      }
      
      const toast = await toastController.create({
        message: `Ahora estás viendo como ${res.data.user.name}`,
        duration: 3000,
        color: 'warning',
        position: 'top'
      });
      await toast.present();
      
      // Navigate to the appropriate dashboard with full reload
      if (['super_admin', 'director', 'teacher'].includes(initialProfile)) {
        window.location.href = '/admin/dashboard';
      } else if (initialProfile === 'parent') {
        window.location.href = '/parent/dashboard';
      } else {
        window.location.href = '/admin/dashboard';
      }
    }
  } catch (error: any) {
    console.error('Error impersonating user:', error);
    const msg = error.response?.data?.message || 'Error al suplantar usuario.';
    const toast = await toastController.create({
      message: msg,
      duration: 4000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
    impersonatingId.value = null;
  }
};

const handleRefresh = async (event: any) => {
  await Promise.all([
    fetchDashboardStats(),
    fetchUsers(true)
  ]);
  event.target.complete();
};

onMounted(async () => {
  const currentId = await storage.get('current_school_id');
  const userSchools = await storage.get('user_schools');
  if (currentId && userSchools) {
    const active = userSchools.find((s: any) => s.id === currentId);
    if (active) activeSchoolName.value = active.name;
  }
  fetchDashboardStats();
  fetchSchoolsList();
  fetchUsers();
});
</script>
