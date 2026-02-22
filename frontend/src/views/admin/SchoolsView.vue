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
        <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">Administración de Escuelas</h1>
        <p class="text-gray-500 font-medium">Gestión integral de las instituciones educativas registradas en el sistema.</p>
      </div>
          <div class="flex items-center gap-3 shrink-0">
            <button @click="fetchSchools(true)" class="bg-white border border-gray-200 text-gray-700 font-bold w-10 h-10 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center justify-center transition-all">
              <ion-icon :icon="refreshOutline" class="text-lg"></ion-icon>
            </button>
            <button class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center gap-2 transition-all">
              <ion-icon :icon="documentTextOutline" class="text-lg"></ion-icon>
              Carga Masiva de Alumnos
            </button>
        <button @click="$router.push('/admin/schools/create')" class="bg-brand-blue text-white font-bold py-2.5 px-5 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 flex items-center gap-2 transition-all">
          <ion-icon :icon="addOutline" class="text-lg"></ion-icon>
          Agregar Nueva Escuela
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
      <!-- Stat 1 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Escuelas Registradas</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-gray-900 leading-none tracking-tight">
              <span v-if="loadingStats">...</span>
              <span v-else>{{ stats.schools }}</span>
            </span>
            <span class="text-[11px] font-bold text-green-700 bg-green-100/50 border border-green-200 px-2 py-0.5 rounded-full flex items-center gap-1">
               <ion-icon :icon="trendingUpOutline"></ion-icon> Total activo
            </span>
          </div>
        </div>
        <div class="text-blue-50">
           <ion-icon :icon="business" class="text-[64px]"></ion-icon>
        </div>
      </div>
      <!-- Stat 2 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Estudiantes/Usuarios (Total DB)</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-gray-900 leading-none tracking-tight">
              <span v-if="loadingStats">...</span>
              <span v-else>{{ stats.students + stats.users }}</span>
            </span>
            <span class="text-[11px] font-bold text-green-700 bg-green-100/50 border border-green-200 px-2 py-0.5 rounded-full flex items-center gap-1">
               <ion-icon :icon="trendingUpOutline"></ion-icon> Activos
            </span>
          </div>
        </div>
        <div class="text-blue-50">
           <!-- SVG Cap -->
           <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2.08-1.13L23 9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72l5 2.73 5-2.73v3.72z"/></svg>
        </div>
      </div>
      <!-- Stat 3 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Salud del Sistema (Kioscos)</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-gray-900 leading-none tracking-tight">
              <span v-if="loadingStats">...</span>
              <span v-else>{{ stats.systemHealth }}%</span>
            </span>
            <span class="text-[11px] font-bold text-green-700 flex items-center gap-1.5">
               <div class="w-2 h-2 rounded-full bg-green-500 ring-2 ring-green-100"></div> {{ stats.kiosks }} Operativos
            </span>
          </div>
        </div>
        <div class="text-blue-50 flex flex-col gap-1">
           <div class="w-10 h-5 bg-current rounded pl-1 pt-1"><div class="w-1.5 h-1.5 bg-white rounded-full"></div></div>
           <div class="w-10 h-5 bg-current rounded pl-1 pt-1"><div class="w-1.5 h-1.5 bg-white rounded-full"></div></div>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-10">
      
      <!-- Toolbar -->
      <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
           <div class="w-8 h-8 bg-blue-50 text-brand-blue flex items-center justify-center rounded-lg">
             <ion-icon :icon="listOutline" class="text-lg"></ion-icon>
           </div>
           <h3 class="text-lg font-black text-gray-900">Gestión Escolar</h3>
        </div>
        
        <div class="flex items-center gap-3 w-full md:w-auto">
          <div class="relative flex-grow md:flex-grow-0">
            <ion-icon :icon="searchOutline" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></ion-icon>
            <input type="text" placeholder="Buscar por nombre o ciudad..." class="pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-blue focus:ring-1 focus:ring-brand-blue text-[13px] font-medium text-gray-700 w-full md:w-72 transition-colors" />
          </div>
          <button class="flex items-center gap-2 border border-gray-200 bg-white px-4 py-2.5 rounded-xl text-[13px] font-bold text-gray-600 hover:bg-gray-50 shrink-0">
            <ion-icon :icon="filterOutline" class="text-base"></ion-icon>
            Filtrar
          </button>
        </div>
      </div>

      <!-- Table Scroll -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
          <thead>
            <tr class="bg-gray-50/50 text-[10px] font-black tracking-widest text-gray-500 uppercase border-b border-gray-100">
              <th class="p-4 pl-6">Nombre de la Escuela</th>
              <th class="p-4">Ubicación</th>
              <th class="p-4">Contacto Admin</th>
              <th class="p-4">Estudiantes</th>
              <th class="p-4">Estatus</th>
              <th class="p-4 pr-6 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-if="loadingSchools">
              <td colspan="6" class="p-8 text-center text-gray-400 font-medium">Cargando escuelas...</td>
            </tr>
            <tr v-else-if="schools.length === 0">
              <td colspan="6" class="p-8 text-center text-gray-400 font-medium">No hay escuelas registradas.</td>
            </tr>
            <tr v-else v-for="school in schools" :key="school.id" class="border-b border-gray-50 hover:bg-gray-50/80 transition-colors">
              <td class="p-4 pl-6">
                <div class="flex items-center gap-4">
                  <div v-if="school.logo_path" class="w-10 h-10 rounded-full bg-white border border-gray-200 overflow-hidden shrink-0">
                    <img :src="school.logo_path.startsWith('http') ? school.logo_path : 'http://localhost:8000/storage/' + school.logo_path" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-10 h-10 rounded-full bg-blue-50 text-brand-blue font-black flex items-center justify-center shrink-0 border border-blue-100">
                    {{ school.name ? school.name.substring(0, 2).toUpperCase() : 'SC' }}
                  </div>
                  <div>
                    <p class="font-bold text-gray-900 leading-tight">{{ school.name }}</p>
                    <p class="text-[11px] font-medium text-gray-400">ID: sch_{{ school.id }}</p>
                  </div>
                </div>
              </td>
              <td class="p-4 text-gray-600 font-medium">
                <div class="flex items-center gap-2 max-w-[200px]">
                   <ion-icon :icon="locationOutline" class="text-gray-400 text-base shrink-0"></ion-icon> 
                   <span class="truncate">{{ school.address || 'Sin dirección' }}</span>
                   <a v-if="school.address && school.address !== 'Por Definir'" :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(school.address)" target="_blank" class="w-6 h-6 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center hover:bg-blue-100 transition-colors shrink-0" title="Abrir en Google Maps">
                     <ion-icon :icon="navigateCircleOutline" class="text-lg"></ion-icon>
                   </a>
                </div>
              </td>
              <td class="p-4">
                <p class="font-bold text-gray-900 leading-tight">{{ school.contact_phone || 'N/A' }}</p>
                <p class="text-xs font-medium text-gray-500">Kioscos: {{ school.kiosks_count }}</p>
              </td>
              <td class="p-4 font-black text-gray-900">{{ school.students_count || 0 }}</td>
              <td class="p-4">
                <span class="bg-green-100/50 border border-green-200 text-green-700 text-[11px] px-2.5 py-1 rounded-full font-bold flex items-center w-max gap-1.5">
                  <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Activa
                </span>
              </td>
              <td class="p-4 pr-6">
                <div class="flex items-center justify-end gap-4 text-gray-400">
                  <button class="hover:text-brand-blue flex items-center gap-1 font-bold text-[13px] transition-colors"><ion-icon :icon="eyeOutline"></ion-icon> Ver</button>
                  <button @click="$router.push(`/admin/schools/${school.id}/edit`)" class="hover:text-gray-900 flex items-center gap-1 font-bold text-[13px] transition-colors"><ion-icon :icon="createOutline"></ion-icon> Editar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t border-gray-100 flex items-center justify-between">
        <span class="text-[13px] text-gray-500 font-medium">Mostrando <strong class="text-gray-900 font-black">{{ schools.length }}</strong> de <strong class="text-gray-900 font-black">{{ schools.length }}</strong> escuelas</span>
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
import { ref, onMounted } from 'vue';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent } from '@ionic/vue';
import { 
  addOutline, trendingUpOutline, business, push, documentText, grid,
  listOutline, searchOutline, filterOutline, locationOutline, eyeOutline, 
  createOutline, documentTextOutline, navigateCircleOutline, refreshOutline
} from 'ionicons/icons';
import api from '@/services/api';

const loadingStats = ref(true);
const loadingSchools = ref(true);

const stats = ref({
  schools: 0,
  students: 0,
  users: 0,
  kiosks: 0,
  systemHealth: 0
});

const schools = ref<any[]>([]);

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

const fetchSchools = async (forceRefresh = false) => {
  if (forceRefresh) loadingSchools.value = true;
  try {
    const res = await api.get('/admin/schools');
    if (res.data.success) {
      schools.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching schools', error);
  } finally {
    loadingSchools.value = false;
  }
};

const handleRefresh = async (event: any) => {
  await Promise.all([
    fetchDashboardStats(),
    fetchSchools(true)
  ]);
  event.target.complete();
};

onMounted(() => {
  fetchDashboardStats();
  fetchSchools();
});
</script>
