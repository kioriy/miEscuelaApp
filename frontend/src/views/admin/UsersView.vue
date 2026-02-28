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
            <input type="text" placeholder="Buscar por nombre o email..." class="pl-10 pr-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-blue focus:ring-1 focus:ring-brand-blue text-[13px] font-medium text-gray-700 w-full transition-colors" />
        </div>
        
        <div class="flex items-center gap-3 shrink-0">
          <div class="relative">
             <select class="appearance-none bg-gray-50/50 border border-gray-200 text-gray-700 text-[13px] font-bold rounded-xl py-2.5 pl-4 pr-10 focus:outline-none focus:ring-1 focus:ring-brand-blue focus:border-brand-blue cursor-pointer">
                <option>Todos los Roles</option>
             </select>
             <ion-icon :icon="chevronDown" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
          </div>
          
          <div class="relative">
             <select class="appearance-none bg-gray-50/50 border border-gray-200 text-gray-700 text-[13px] font-bold rounded-xl py-2.5 pl-4 pr-10 focus:outline-none focus:ring-1 focus:ring-brand-blue focus:border-brand-blue cursor-pointer">
                <option>Todas las Escuelas</option>
             </select>
             <ion-icon :icon="chevronDown" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
          </div>

          <button class="w-10 h-10 flex items-center justify-center border border-gray-200 bg-white rounded-xl text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-colors">
            <ion-icon :icon="filter" class="text-base"></ion-icon>
          </button>
        </div>
      </div>

      <!-- Table Scroll -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
          <thead>
            <tr class="bg-gray-50/50 text-[10px] font-black tracking-widest text-gray-500 uppercase border-b border-gray-100">
              <th class="p-4 pl-6">Nombre</th>
              <th class="p-4">Email</th>
              <th class="p-4">Escuela Asignada</th>
              <th class="p-4">Rol</th>
              <th class="p-4">Estatus</th>
              <th class="p-4 pr-6 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-if="loadingUsers">
              <td colspan="6" class="p-8 text-center text-gray-400 font-medium">Cargando usuarios...</td>
            </tr>
            <tr v-else-if="users.length === 0">
              <td colspan="6" class="p-8 text-center text-gray-400 font-medium">No hay usuarios registrados.</td>
            </tr>
            <tr v-else v-for="user in users" :key="user.id" class="border-b border-gray-50 hover:bg-gray-50/80 transition-colors">
              <td class="p-4 pl-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-brand-blue font-black border border-orange-200 shadow-sm overflow-hidden shrink-0">
                    <img v-if="getUserPhoto(user)" :src="getUserPhoto(user)" class="w-full h-full object-cover" />
                    <ion-icon v-else :icon="personOutline" class="text-xl text-gray-400"></ion-icon>
                  </div>
                  <div>
                    <p class="font-bold text-gray-900 leading-tight">{{ user.name }}</p>
                  </div>
                </div>
              </td>
              <td class="p-4 text-brand-blue font-medium text-[13px]">
                {{ user.email }}
              </td>
              <td class="p-4 font-bold text-gray-900 text-[13px]">
                {{ user.school ? user.school.name : 'Agencia Central' }}
              </td>
              <td class="p-4">
                <span v-if="user.role === 'super_admin'" class="bg-gray-100 border border-gray-200 text-gray-600 text-[11px] px-2.5 py-1 rounded-full font-bold">Súper Admin</span>
                <span v-else-if="user.role === 'director'" class="bg-indigo-50 border border-indigo-100 text-indigo-600 text-[11px] px-2.5 py-1 rounded-full font-bold">Director</span>
                <span v-else class="bg-brand-blue/10 border border-brand-blue/20 text-brand-blue text-[11px] px-2.5 py-1 rounded-full font-bold">{{ user.role }}</span>
              </td>
              <td class="p-4">
                <span class="bg-green-100/50 border border-green-200 text-green-700 text-[11px] px-2.5 py-1 rounded-full font-bold">Activo</span>
              </td>
              <td class="p-4 pr-6">
                <div class="flex items-center justify-end gap-2 text-gray-400">
                  <button @click="$router.push(`/admin/users/${user.id}`)" class="hover:text-brand-blue flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-blue-50 transition-colors text-[12px] font-semibold">
                    <ion-icon :icon="eyeOutline" class="text-sm"></ion-icon>
                    <span>Ver</span>
                  </button>
                  <button @click="$router.push(`/admin/users/${user.id}/edit`)" class="hover:text-gray-900 flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors text-[12px] font-semibold">
                    <ion-icon :icon="createOutline" class="text-sm"></ion-icon>
                    <span>Editar</span>
                  </button>
                  <button class="hover:text-red-500 flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-red-50 transition-colors text-[12px] font-semibold">
                    <ion-icon :icon="trashOutline" class="text-sm"></ion-icon>
                    <span>Eliminar</span>
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

    <!-- Carga Masiva (Reusable block shape) -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col mb-4">
      <div class="flex items-center gap-4 mb-4">
        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-brand-blue shrink-0">
          <ion-icon :icon="push" class="text-lg"></ion-icon>
        </div>
        <div>
          <h3 class="text-lg font-bold text-gray-900 tracking-tight">Carga Masiva de Alumnos</h3>
          <p class="text-gray-500 text-[13px] font-medium">Importar datos desde archivo (CSV, XLSX, JSON)</p>
        </div>
      </div>

      <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 flex flex-col items-center justify-center text-center hover:border-brand-blue hover:bg-blue-50/10 transition-colors cursor-pointer group mt-4">
        <div class="flex gap-8 mb-6 text-gray-400 group-hover:text-brand-blue transition-colors">
           <div class="flex flex-col items-center gap-2">
              <ion-icon :icon="documentText" class="text-3xl"></ion-icon>
              <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">CSV</span>
           </div>
           <div class="flex flex-col items-center gap-2">
              <ion-icon :icon="grid" class="text-3xl"></ion-icon>
              <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">XLSX</span>
           </div>
           <div class="flex flex-col items-center gap-2">
              <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M14.6 16.6l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4m-5.2 0L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4z"/></svg>
              <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">JSON</span>
           </div>
        </div>
        <h4 class="text-sm font-bold text-gray-900 mb-1">Arrastra tus archivos aquí o haz clic para subir</h4>
        <p class="text-xs text-gray-400 font-medium">Tamaño máximo de archivo: 10MB</p>
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
  createOutline, trashOutline, eyeOutline, push, documentText, grid, refreshOutline, personOutline, business
} from 'ionicons/icons';
import api from '@/services/api';
import { storage } from '@/services/storage';

const loadingStats = ref(true);
const loadingUsers = ref(true);
const activeSchoolName = ref('');

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
      : `http://localhost:8000/storage/${user.profile_photo_path}`;
  }
  return null;
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

const fetchUsers = async (forceRefresh = false) => {
  if (forceRefresh) loadingUsers.value = true;
  try {
    const res = await api.get('/admin/users');
    if (res.data.success) {
      users.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching users', error);
  } finally {
    loadingUsers.value = false;
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
  fetchUsers();
});
</script>
