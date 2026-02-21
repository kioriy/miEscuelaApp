<template>
  <ion-page>
    <ion-content class="ion-padding-bottom">
      <div class="p-8 lg:p-12 w-full max-w-[1400px] mx-auto min-h-full flex flex-col bg-gray-50">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
          <div>
            <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">Panel de Súper Administrador</h1>
            <p class="text-gray-500 font-medium">Supervisión global y acceso rápido a la gestión masiva de datos.</p>
          </div>
          <div class="flex items-center gap-3 shrink-0">
            <button class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center gap-2 transition-all">
              <ion-icon :icon="downloadOutline" class="text-lg"></ion-icon>
              Exportar Reporte
            </button>
            <button class="bg-brand-blue text-white font-bold py-2.5 px-5 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 flex items-center gap-2 transition-all">
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
                  <span v-if="loading">...</span>
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
                  <span v-if="loading">...</span>
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
                  <span v-if="loading">...</span>
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

        <!-- Carga Masiva (Reusable block shape) -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mb-8 mt-auto flex-grow h-full flex flex-col">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-brand-blue shrink-0">
              <ion-icon :icon="push" class="text-2xl"></ion-icon>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 tracking-tight">Carga Masiva de Alumnos</h3>
              <p class="text-gray-500 text-sm font-medium">Importar datos desde archivo (CSV, XLSX, JSON)</p>
            </div>
          </div>

          <div class="flex-grow border-2 border-dashed border-gray-200 rounded-2xl p-10 flex flex-col items-center justify-center text-center hover:border-brand-blue hover:bg-blue-50/10 transition-colors cursor-pointer group">
            <div class="flex gap-10 mb-8 text-gray-400 group-hover:text-brand-blue transition-colors">
              <div class="flex flex-col items-center gap-3">
                  <ion-icon :icon="documentText" class="text-[40px]"></ion-icon>
                  <span class="text-[11px] font-bold uppercase tracking-widest text-gray-500">CSV</span>
              </div>
              <div class="flex flex-col items-center gap-3">
                  <ion-icon :icon="grid" class="text-[40px]"></ion-icon>
                  <span class="text-[11px] font-bold uppercase tracking-widest text-gray-500">XLSX</span>
              </div>
              <div class="flex flex-col items-center gap-3">
                  <!-- brackets icon custom -->
                  <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24"><path d="M14.6 16.6l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4m-5.2 0L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4z"/></svg>
                  <span class="text-[11px] font-bold uppercase tracking-widest text-gray-500">JSON</span>
              </div>
            </div>
            <h4 class="text-base font-bold text-gray-900 mb-2">Arrastra tus archivos aquí o haz clic para subir</h4>
            <p class="text-[13px] text-gray-400 font-medium mb-8">Tamaño máximo de archivo: 10MB</p>
            <button class="bg-white border border-gray-200 px-6 py-2.5 rounded-xl text-[13px] font-bold shadow-sm text-gray-700 hover:bg-gray-50 transition-colors">
              Seleccionar Archivo
            </button>
          </div>
        </div>
        
        <!-- Footer Global App in Admin Area -->
        <div class="text-center text-[11px] font-semibold text-gray-400 mt-4 pb-2 tracking-wide">
          © 2026 SchoolTrack Systems. Todos los derechos reservados. Versión 2.1.0
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  downloadOutline, addOutline, trendingUpOutline, business, 
  push, documentText, grid
} from 'ionicons/icons';
import api from '@/services/api';

const loading = ref(true);
const stats = ref({
  schools: 0,
  students: 0,
  users: 0,
  kiosks: 0,
  systemHealth: 0
});

const fetchDashboardStats = async () => {
  try {
    const res = await api.get('/admin/stats');
    if (res.data.success) {
      stats.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching dashboard stats', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardStats();
});
</script>
