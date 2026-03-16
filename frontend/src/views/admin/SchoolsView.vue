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
        <p class="text-gray-500 font-medium mb-1.5">Gestión integral de las instituciones educativas registradas en el sistema.</p>
        <p v-if="activeSchoolName" class="text-[15px] font-black text-brand-blue flex items-center gap-1.5 mt-1"><ion-icon :icon="business"></ion-icon> {{ activeSchoolName }}</p>
      </div>
          <div class="flex items-center gap-3 shrink-0">
            <button @click="loadingStats = true; fetchDashboardStats(); fetchSchools(true);" class="bg-white border border-gray-200 text-gray-700 font-bold w-10 h-10 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center justify-center transition-all">
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
      <!-- Stat 1: Escuelas -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Escuelas Registradas</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-gray-900 leading-none tracking-tight">
              <span v-if="loadingStats">...</span>
              <span v-else>{{ stats.schools }}</span>
            </span>
            <span class="text-[11px] font-bold text-green-700 bg-green-100/50 border border-green-200 px-2 py-0.5 rounded-full flex items-center gap-1">
               <ion-icon :icon="trendingUpOutline"></ion-icon> Total
            </span>
          </div>
        </div>
        <div class="text-blue-50">
           <ion-icon :icon="business" class="text-[64px]"></ion-icon>
        </div>
      </div>
      <!-- Stat 2: Estudiantes -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Total Alumnos</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-gray-900 leading-none tracking-tight">
              <span v-if="loadingStats">...</span>
              <span v-else>{{ stats.students }}</span>
            </span>
            <span class="text-[11px] font-bold text-blue-700 bg-blue-100/50 border border-blue-200 px-2 py-0.5 rounded-full flex items-center gap-1">
               <ion-icon :icon="peopleOutline"></ion-icon> Registros
            </span>
          </div>
        </div>
        <div class="text-indigo-50">
           <!-- SVG Cap -->
           <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2.08-1.13L23 9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72l5 2.73 5-2.73v3.72z"/></svg>
        </div>
      </div>
      <!-- Stat 3: Personal/Tutor -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between transition-transform hover:-translate-y-1">
        <div>
          <h3 class="text-[13px] font-bold text-gray-500 mb-2">Total Personal</h3>
          <div class="flex items-baseline gap-3">
            <span class="text-[40px] font-black text-gray-900 leading-none tracking-tight">
              <span v-if="loadingStats">...</span>
              <span v-else>{{ stats.users }}</span>
            </span>
            <span class="text-[11px] font-bold text-purple-700 bg-purple-100/50 border border-purple-200 px-2 py-0.5 rounded-full flex items-center gap-1">
               <ion-icon :icon="briefcaseOutline"></ion-icon> Cuentas
            </span>
          </div>
        </div>
        <div class="text-purple-50">
           <ion-icon :icon="briefcaseOutline" class="text-[64px]"></ion-icon>
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
            <input type="text" v-model="searchQuery" placeholder="Buscar por nombre o ciudad..." class="pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-blue focus:ring-1 focus:ring-brand-blue text-[13px] font-medium text-gray-700 w-full md:w-72 transition-colors" />
          </div>
          <div class="relative items-center gap-2 flex shrink-0">
            <select v-model="statusFilter" class="appearance-none border border-gray-200 bg-white px-4 py-2.5 pr-10 rounded-xl text-[13px] font-bold text-gray-600 hover:bg-gray-50 cursor-pointer focus:outline-none focus:ring-1 focus:ring-brand-blue">
              <option value="all">Filtro: Todas</option>
              <option value="active">Solo Activas</option>
              <option value="inactive">Solo Inactivas</option>
            </select>
            <ion-icon :icon="filterOutline" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
          </div>
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
              <th class="p-4">Alumnos / Personal</th>
              <th class="p-4">Estatus</th>
              <th class="p-4 pr-6 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-if="loadingSchools">
              <td colspan="6" class="p-8 text-center text-gray-400 font-medium">Cargando escuelas...</td>
            </tr>
            <tr v-else-if="filteredSchools.length === 0">
              <td colspan="6" class="p-8 text-center text-gray-400 font-medium">No se encontraron escuelas con esos filtros.</td>
            </tr>
            <tr v-else v-for="school in filteredSchools" :key="school.id" class="border-b border-gray-50 hover:bg-gray-50/80 transition-colors">
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
              <td class="p-4">
                <p class="font-black text-gray-900"><ion-icon :icon="peopleOutline" class="mr-1"></ion-icon>{{ school.students_count || 0 }}</p>
                <p class="text-[11px] font-bold text-gray-500"><ion-icon :icon="briefcaseOutline" class="mr-1"></ion-icon>{{ school.users_count || 0 }}</p>
              </td>
              <td class="p-4">
                <span class="bg-green-100/50 border border-green-200 text-green-700 text-[11px] px-2.5 py-1 rounded-full font-bold flex items-center w-max gap-1.5">
                  <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Activa
                </span>
              </td>
              <td class="p-4 pr-6">
                <div class="flex items-center justify-end gap-4 text-gray-400">
                  <button @click="$router.push(`/admin/schools/${school.id}`)" class="hover:text-brand-blue flex items-center gap-1 font-bold text-[13px] transition-colors"><ion-icon :icon="eyeOutline"></ion-icon> Ver</button>
                  <button @click="$router.push(`/admin/schools/${school.id}/edit`)" class="hover:text-gray-900 flex items-center gap-1 font-bold text-[13px] transition-colors"><ion-icon :icon="createOutline"></ion-icon> Editar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t border-gray-100 flex items-center justify-between">
        <span class="text-[13px] text-gray-500 font-medium">Mostrando <strong class="text-gray-900 font-black">{{ filteredSchools.length }}</strong> de <strong class="text-gray-900 font-black">{{ schools.length }}</strong> escuelas</span>
        <div class="flex items-center gap-2">
           <button class="px-3 py-1.5 border border-gray-200 bg-gray-50/50 rounded-lg text-gray-400 font-semibold cursor-not-allowed text-[13px]">Anterior</button>
           <button class="px-3 py-1.5 border border-gray-200 bg-white shadow-sm rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors text-[13px]">Siguiente</button>
        </div>
      </div>
    </div>

    <!-- Carga Masiva -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col mb-4">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
          <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-brand-blue shrink-0">
            <ion-icon :icon="push" class="text-lg"></ion-icon>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Carga Masiva de Alumnos</h3>
            <p class="text-gray-500 text-[13px] font-medium">Importar datos desde archivo (CSV, XLSX, JSON)</p>
          </div>
        </div>
        
        <!-- School Selector for Import (Autocomplete/Predictivo) -->
        <div class="relative w-full md:w-80" @click.stop>
           <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Escuela de Destino (Búsqueda)</label>
           
           <div class="relative">
             <ion-icon :icon="searchOutline" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-base"></ion-icon>
             <input 
               type="text" 
               v-model="importSchoolSearch"
               @focus="isImportSchoolDropdownOpen = true"
               @blur="handleImportSchoolBlur"
               placeholder="Escribe el nombre de la escuela..." 
               class="pl-10 pr-4 py-2.5 w-full bg-gray-50 border border-gray-200 rounded-xl text-[13px] font-bold text-gray-700 outline-none focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-blue-100 transition-all placeholder:font-medium placeholder:text-gray-400"
             />
             <ion-icon :icon="chevronDown" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm transition-transform duration-200" :class="{'rotate-180 text-brand-blue': isImportSchoolDropdownOpen}"></ion-icon>
           </div>
           
           <!-- Dropdown Menú Predictivo -->
           <div v-show="isImportSchoolDropdownOpen" class="absolute z-50 w-full mt-1.5 bg-white border border-gray-100 rounded-xl shadow-xl shadow-blue-900/5 max-h-[300px] overflow-y-auto animate-fade-in-down origin-top custom-scrollbar">
             <div v-if="filteredImportSchools.length === 0" class="p-4 text-[13px] font-medium text-gray-400 text-center">No se encontraron escuelas que coincidan.</div>
             <div 
               v-else 
               v-for="s in filteredImportSchools" 
               :key="s.id" 
               @mousedown.prevent="selectImportSchool(s)"
               class="px-4 py-3 cursor-pointer text-[13px] font-bold text-gray-700 transition-colors border-b border-gray-50/50 last:border-0 hover:bg-blue-50/80 hover:text-brand-blue flex items-center justify-between group"
               :class="{'bg-blue-50 text-brand-blue': importSchoolId === s.id}"
             >
               <span class="truncate pr-4">{{ s.name }}</span>
               <div v-if="importSchoolId === s.id" class="w-2 h-2 rounded-full bg-brand-blue shrink-0"></div>
             </div>
           </div>
        </div>
      </div>

      <div 
        @click="triggerFileUpload"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
        :class="{'border-brand-blue bg-blue-50/20': isDragging, 'border-gray-200': !isDragging}"
        class="border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center text-center hover:border-brand-blue hover:bg-blue-50/10 transition-all cursor-pointer group mt-2"
      >
        <input id="bulkUploadInput" type="file" ref="fileInput" @change="handleFileSelect" class="hidden" accept=".csv,.xlsx,.json,.txt" />
        
        <div v-if="!isUploading" class="flex flex-col items-center">
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
          <h4 class="text-sm font-bold text-gray-900 mb-1">Arrastra tu archivo aquí o haz clic para subir</h4>
          <p class="text-xs text-gray-400 font-medium">matricula, nombre, apellidos, nivel, grado, grupo, turno, email_tutor, email_tutor_2</p>
          <p class="text-xs text-gray-400 font-medium">Tamaño máximo: 10MB</p>
        </div>

        <div v-else class="flex flex-col items-center py-4">
          <div class="w-12 h-12 border-4 border-blue-100 border-t-brand-blue rounded-full animate-spin mb-4"></div>
          <p class="text-sm font-bold text-gray-700">Procesando archivo...</p>
          <p class="text-xs text-gray-400 mt-1">Esto puede tomar unos segundos.</p>
        </div>
      </div>

      <!-- Import Summary -->
      <div v-if="importResults" class="mt-6 p-4 bg-gray-50 rounded-2xl border border-gray-100">
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-sm font-bold text-gray-900">Resultado de Importación</h4>
          <button @click="importResults = null" class="text-gray-400 hover:text-gray-600 font-bold text-xs">Cerrar</button>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="bg-white p-3 rounded-xl border border-gray-100 text-center">
            <p class="text-lg font-black text-gray-900">{{ importResults.total }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase">Total</p>
          </div>
          <div class="bg-white p-3 rounded-xl border border-gray-100 text-center">
            <p class="text-lg font-black text-emerald-600">{{ importResults.imported }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase">Importados</p>
          </div>
          <div class="bg-white p-3 rounded-xl border border-gray-100 text-center">
            <p class="text-lg font-black text-amber-500">{{ importResults.skipped }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase">Omitidos</p>
          </div>
          <div class="bg-white p-3 rounded-xl border border-gray-100 text-center">
            <p class="text-lg font-black text-red-500">{{ importResults.errors.length }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase">Errores</p>
          </div>
        </div>
        <div v-if="importResults.errors.length > 0" class="mt-4 max-h-32 overflow-y-auto bg-red-50/50 rounded-lg p-3">
          <p class="text-[11px] font-bold text-red-700 mb-1">Detalle de errores:</p>
          <ul class="text-[10px] text-red-600 space-y-1">
            <li v-for="(err, idx) in importResults.errors" :key="idx">• {{ err }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent } from '@ionic/vue';
import { 
  addOutline, trendingUpOutline, business, push, documentText, grid,
  listOutline, searchOutline, filterOutline, locationOutline, eyeOutline, 
  createOutline, documentTextOutline, navigateCircleOutline, refreshOutline,
  chevronDown, peopleOutline, briefcaseOutline
} from 'ionicons/icons';
import api from '@/services/api';
import { storage } from '@/services/storage';

const loadingSchools = ref(true);
const loadingStats = ref(true);

const stats = ref({
  schools: 0,
  students: 0,
  users: 0,
  kiosks: 0,
  systemHealth: 0
});
const activeSchoolName = ref('');

const schools = ref<any[]>([]);
const searchQuery = ref('');
const statusFilter = ref('all');

// Bulk Import State
const importSchoolId = ref('');
const importSchoolSearch = ref('');
const isImportSchoolDropdownOpen = ref(false);

const isDragging = ref(false);
const isUploading = ref(false);
const importResults = ref<any>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const filteredImportSchools = computed(() => {
  if (!importSchoolSearch.value) return schools.value;
  const q = importSchoolSearch.value.toLowerCase().trim();
  return schools.value.filter(s => String(s.name || '').toLowerCase().includes(q));
});

const selectImportSchool = (school: any) => {
  importSchoolId.value = school.id;
  importSchoolSearch.value = school.name;
  isImportSchoolDropdownOpen.value = false;
};

const handleImportSchoolBlur = () => {
  setTimeout(() => {
    isImportSchoolDropdownOpen.value = false;
    // Si hay un ID seleccionado, restauramos su nombre al hacer blur (por si escribió algo pero no seleccionó nada)
    const selected = schools.value.find(s => s.id === importSchoolId.value);
    if (selected) {
      importSchoolSearch.value = selected.name;
    } else {
      // Si no hay ID válido seleccionado, borra la caja de búsqueda
      importSchoolSearch.value = '';
    }
  }, 150);
};

const triggerFileUpload = () => {
  if (fileInput.value) {
    fileInput.value.click();
  }
};

const filteredSchools = computed(() => {
  let result = schools.value;

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(s => 
      String(s.name || '').toLowerCase().includes(q) || 
      String(s.address || '').toLowerCase().includes(q) || 
      String(s.cct || '').toLowerCase().includes(q)
    );
  }

  if (statusFilter.value !== 'all') {
    result = result.filter(s => {
      const isActive = s.is_active === 1 || s.is_active === true;
      if (statusFilter.value === 'active') return isActive;
      if (statusFilter.value === 'inactive') return !isActive;
      return true;
    });
  }

  return result;
});

const fetchDashboardStats = async () => {
  try {
    const res = await api.get('/admin/stats?global=true');
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

const handleFileSelect = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (file) prepareUpload(file);
};

const handleDrop = (event: DragEvent) => {
  isDragging.value = false;
  const file = event.dataTransfer?.files[0];
  if (file) prepareUpload(file);
};

const prepareUpload = (file: File) => {
  if (!importSchoolId.value) {
    alert('Por favor selecciona una escuela de destino antes de subir el archivo.');
    return;
  }
  
  const ext = file.name.split('.').pop()?.toLowerCase();
  if (!['csv', 'xlsx', 'json', 'txt'].includes(ext || '')) {
    alert('Formato de archivo no soportado. Usa CSV, XLSX o JSON.');
    return;
  }

  uploadFile(file);
};

const uploadFile = async (file: File) => {
  isUploading.value = true;
  importResults.value = null;

  const formData = new FormData();
  formData.append('file', file);

  try {
    const res = await api.post(`/admin/schools/${importSchoolId.value}/students/import`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    if (res.data.success) {
      importResults.value = res.data.data;
      // Refresh school data to update counts
      fetchSchools();
    }
  } catch (error: any) {
    console.error('Error importing students', error);
    alert(error.response?.data?.message || 'Error al importar los estudiantes.');
  } finally {
    isUploading.value = false;
    if (fileInput.value) fileInput.value.value = '';
  }
};

onMounted(async () => {
  const currentId = await storage.get('current_school_id');
  const schools = await storage.get('user_schools');
  if (currentId && schools) {
    const active = schools.find((s: any) => s.id === currentId);
    if (active) activeSchoolName.value = active.name;
  }
  fetchDashboardStats();
  fetchSchools();
});
</script>
