<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="p-6 lg:p-10 w-full max-w-[1600px] mx-auto min-h-full flex flex-col bg-gray-50 font-sans">
        
        <!-- Super Admin View (Existing) -->
        <div v-if="isAdmin">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
            <div>
              <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">Panel Administrativo</h1>
              <p class="text-gray-500 font-medium tracking-wide mb-2">Gestión global del sistema miEscuelaApp</p>
              <p v-if="activeSchoolName" class="text-[15px] font-black text-brand-blue flex items-center gap-1.5 mt-1"><ion-icon :icon="business"></ion-icon> {{ activeSchoolName }}</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
              <div>
                <p class="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-1">Escuelas</p>
                <h3 class="text-3xl font-black text-gray-900">{{ stats.schools }}</h3>
              </div>
              <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-brand-blue">
                <ion-icon :icon="business" class="text-2xl"></ion-icon>
              </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
              <div>
                <p class="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-1">Alumnos</p>
                <h3 class="text-3xl font-black text-gray-900">{{ stats.students }}</h3>
              </div>
              <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                <ion-icon :icon="school" class="text-2xl"></ion-icon>
              </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
              <div>
                <p class="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-1">Estatus Sistema</p>
                <h3 class="text-3xl font-black text-green-600">{{ stats.systemHealth }}%</h3>
              </div>
              <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                <ion-icon :icon="trendingUpOutline" class="text-2xl"></ion-icon>
              </div>
            </div>
          </div>
        </div>

        <!-- Teacher Dashboard -->
        <TeacherDashboard v-else-if="isTeacher" />

        <!-- Director Dashboard (New Premium Design) -->
        <div v-else class="space-y-8 animate-fade-in">
          
          <!-- Header Hero -->
          <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
              <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none mb-3">Bienvenido, Director {{ directorLastName }}</h1>
              <p class="text-gray-500 font-bold tracking-wide flex items-center gap-2 mb-2">
                {{ formattedDate }} <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Resumen del día escolar
              </p>
              <p v-if="activeSchoolName" class="text-[15px] font-black text-brand-blue flex items-center gap-1.5 mt-1"><ion-icon :icon="business"></ion-icon> {{ activeSchoolName }}</p>
            </div>
            <div class="flex items-center gap-3">
              <button @click="fetchDashboardStats()" class="bg-white border border-gray-200 text-gray-700 font-bold w-12 h-12 rounded-xl text-lg shadow-sm hover:bg-gray-50 flex items-center justify-center transition-all" title="Actualizar datos">
                <ion-icon :icon="refreshOutline"></ion-icon>
              </button>
              <button class="bg-white text-gray-700 font-bold px-5 py-3 rounded-xl border border-gray-200 shadow-sm hover:bg-gray-50 transition-all flex items-center gap-2">
                <ion-icon :icon="megaphoneOutline" class="text-xl"></ion-icon>
                Difundir Mensaje
              </button>
              <button class="bg-brand-blue text-white font-black px-6 py-3 rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-all flex items-center gap-2">
                <ion-icon :icon="documentTextOutline" class="text-xl"></ion-icon>
                Generar Reporte
              </button>
            </div>
          </div>

          <!-- Top Stats Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1: Matrícula -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-all">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Matrícula Total</p>
                  <h3 class="text-4xl font-black text-gray-900 tracking-tighter">
                    <span v-if="loading">...</span>
                    <span v-else>{{ formatNumber(directorStats.totalStudents) }}</span>
                  </h3>
                </div>
                <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-brand-blue group-hover:scale-110 transition-transform">
                  <ion-icon :icon="peopleOutline" class="text-2xl"></ion-icon>
                </div>
              </div>
              <div class="flex items-center gap-1.5 text-green-600 font-black text-[13px]">
                <ion-icon :icon="trendingUpOutline"></ion-icon>
                <span>2% <span class="text-gray-400 font-bold">vs ayer</span></span>
              </div>
            </div>

            <!-- Card 2: Asistencia -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md transition-all">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Asistencia Hoy</p>
                  <h3 class="text-4xl font-black text-gray-900 tracking-tighter">
                    <span v-if="loading">...</span>
                    <span v-else>{{ directorStats.attendanceRate }}%</span>
                  </h3>
                </div>
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600">
                  <ion-icon :icon="checkmarkCircleOutline" class="text-2xl"></ion-icon>
                </div>
              </div>
              <div class="flex items-center gap-1.5 text-green-600 font-black text-[13px]">
                <ion-icon :icon="trendingUpOutline"></ion-icon>
                <span>1.5% <span class="text-gray-400 font-bold">vs ayer</span></span>
              </div>
            </div>

            <!-- Card 3: Ausencias -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md transition-all">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Ausencias</p>
                  <h3 class="text-4xl font-black text-gray-900 tracking-tighter">
                    <span v-if="loading">...</span>
                    <span v-else>{{ directorStats.absentCount }}</span>
                  </h3>
                </div>
                <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500">
                  <ion-icon :icon="banOutline" class="text-2xl"></ion-icon>
                </div>
              </div>
              <div class="flex items-center gap-1.5 text-green-600 font-black text-[13px]">
                <ion-icon :icon="trendingUpOutline" class="rotate-180"></ion-icon>
                <span>{{ directorStats.absentCount > 0 ? 'Sin cambios' : 'Excelente' }}</span>
              </div>
            </div>

            <!-- New Card 4: Salidas Pendientes -->
            <div @click="showUnclosedModal = true" class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md transition-all cursor-pointer border-l-4 border-l-amber-400">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Salidas Pendientes</p>
                  <h3 class="text-4xl font-black text-gray-900 tracking-tighter">
                    <span v-if="loading">...</span>
                    <span v-else>{{ directorStats.unclosedCount }}</span>
                  </h3>
                </div>
                <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 group-hover:rotate-12 transition-transform">
                  <ion-icon :icon="logOutOutline" class="text-2xl"></ion-icon>
                </div>
              </div>
              <div class="flex items-center gap-1.5 text-amber-600 font-black text-[13px]">
                <span>Alumnos aún en plantel</span>
              </div>
            </div>

            <!-- Card 5: Maestros -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md transition-all">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Maestros</p>
                  <h3 class="text-4xl font-black text-gray-900 tracking-tighter">
                    <span v-if="loading">...</span>
                    <span v-else>{{ directorStats.staffPresent }}/{{ directorStats.totalStaff }}</span>
                  </h3>
                </div>
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                  <ion-icon :icon="personAddOutline" class="text-2xl"></ion-icon>
                </div>
              </div>
              <div class="flex items-center gap-1.5 text-green-600 font-black text-[13px]">
                 <span>{{ directorStats.staffPresent }} Presentes</span>
              </div>
            </div>
          </div>

          <!-- Middle Row: Attendance by Group and Entry Summary -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Attendance by Grade & Group -->
            <div class="lg:col-span-2 bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 flex flex-col h-full">
              <div class="flex justify-between items-center mb-8">
                <div>
                  <h3 class="text-xl font-black text-gray-900 tracking-tight">Asistencia por Grado y Grupo</h3>
                  <p class="text-gray-400 text-sm font-bold">Estado detallado por sección</p>
                </div>
                <button class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-50 transition-all">
                  <ion-icon :icon="ellipsisVerticalOutline"></ion-icon>
                </button>
              </div>

              <div class="space-y-6 flex-grow">
                <div v-for="group in directorStats.groupStats" :key="group.grade + group.group_letter" class="flex flex-col gap-2">
                  <div class="flex justify-between items-end">
                    <div>
                      <span class="text-[15px] font-black text-gray-900">{{ group.grade }}º {{ group.group_letter }}</span>
                      <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ group.present }}/{{ group.total }} Estudiantes</p>
                    </div>
                    <span :class="group.percentage >= 100 ? 'text-green-600' : 'text-gray-500'" class="text-[11px] font-black uppercase tracking-widest">
                      {{ group.percentage >= 100 ? 'Completo' : group.percentage + '% Asistencia' }}
                    </span>
                  </div>
                  <div class="w-full h-3 bg-gray-50 rounded-full overflow-hidden border border-gray-100">
                    <div 
                      class="h-full transition-all duration-1000 ease-out rounded-full"
                      :class="group.percentage >= 100 ? 'bg-green-500 shadow-sm shadow-green-200' : 'bg-brand-blue shadow-sm shadow-blue-200'"
                      :style="{ width: group.percentage + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Entry Summary Visualization -->
            <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 flex flex-col items-center">
              <div class="w-full mb-8">
                <h3 class="text-xl font-black text-gray-900 tracking-tight">Resumen de Entrada</h3>
              </div>
              
              <div class="relative w-full aspect-square max-w-[280px] flex items-center justify-center mb-8">
                <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                  <circle cx="18" cy="18" r="15.5" fill="none" stroke="#f3f4f6" stroke-width="3"></circle>
                  
                  <!-- On Time Segment -->
                  <circle 
                    cx="18" cy="18" r="15.5" fill="none" 
                    stroke="#2563eb" stroke-width="3" 
                    stroke-dasharray="97.4 100"
                    stroke-dashoffset="0"
                    stroke-linecap="round"
                    class="transition-all duration-1000"
                  ></circle>

                  <!-- Late Segment -->
                  <circle 
                    cx="18" cy="18" r="15.5" fill="none" 
                    stroke="#fbbf24" stroke-width="3" 
                    stroke-dasharray="4.4 100"
                    stroke-dashoffset="-92"
                    stroke-linecap="round"
                    class="transition-all duration-1000"
                  ></circle>

                  <!-- Absent Segment -->
                  <circle 
                    cx="18" cy="18" r="15.5" fill="none" 
                    stroke="#ef4444" stroke-width="3" 
                    stroke-dasharray="3.6 100"
                    stroke-dashoffset="-96.4"
                    stroke-linecap="round"
                    class="transition-all duration-1000"
                  ></circle>
                </svg>
                
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span class="text-5xl font-black text-gray-900 tracking-tighter">{{ formatNumber(directorStats.totalStudents) }}</span>
                  <span class="text-[12px] font-bold text-gray-400 uppercase tracking-widest mt-1">Alumnos</span>
                </div>
              </div>

              <!-- Legend -->
              <div class="w-full space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-brand-blue"></div>
                    <span class="text-[13px] font-bold text-gray-600">A tiempo</span>
                  </div>
                  <span class="text-[13px] font-black text-gray-900">{{ formatNumber(directorStats.entrySummary.onTime) }} (92%)</span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                    <span class="text-[13px] font-bold text-gray-600">Tarde</span>
                  </div>
                  <span class="text-[13px] font-black text-gray-900">{{ directorStats.entrySummary.late }} (4.4%)</span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span class="text-[13px] font-bold text-gray-600">Ausente</span>
                  </div>
                  <span class="text-[13px] font-black text-gray-900">{{ directorStats.entrySummary.absent }} (3.6%)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Row: Staff Status -->
          <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8">
            <div class="flex items-center justify-between mb-8">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-brand-blue">
                   <ion-icon :icon="peopleOutline" class="text-lg"></ion-icon>
                </div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">Estado del Personal</h3>
              </div>
              <div class="flex items-center gap-4 text-[11px] font-black uppercase tracking-widest">
                <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-green-500"></div> Presente</div>
                <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-gray-300"></div> Ausente</div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <div v-for="person in directorStats.staff" :key="person.id" class="p-4 rounded-3xl border border-gray-50 bg-gray-50/30 flex items-center gap-4 group hover:bg-white hover:shadow-md transition-all cursor-pointer">
                <div class="relative">
                  <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white shadow-sm flex items-center justify-center bg-orange-100">
                    <img v-if="person.avatar_url" :src="person.avatar_url" class="w-full h-full object-cover">
                    <ion-icon v-else :icon="personOutline" class="text-2xl text-gray-500"></ion-icon>
                  </div>
                  <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white" :class="person.status === 'present' ? 'bg-green-500' : 'bg-gray-300'"></div>
                </div>
                <div>
                  <h4 class="text-sm font-black text-gray-900 leading-none mb-1">{{ person.name }}</h4>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cuerpo Docente</p>
                </div>
                <div class="ml-auto text-right">
                   <span class="text-[11px] font-black text-green-600 bg-white px-2 py-1 rounded-lg border border-gray-50">{{ person.time }}</span>
                </div>
              </div>
            </div>

            <div class="mt-10 pt-6 border-t border-gray-50 text-center">
               <a href="#" class="text-brand-blue font-black text-sm uppercase tracking-widest hover:underline">Ver lista completa de personal</a>
            </div>
          </div>
        </div>

        <!-- Footer Global App in Admin Area -->
        <div class="text-center text-[11px] font-semibold text-gray-400 mt-12 pb-4 tracking-wide uppercase">
          © 2026 miEscuelaApp. Todos los derechos reservados. Versión 1.0.0
        </div>
      </div>

      <!-- Custom Modal for Unclosed Records (Integrated) -->
      <div v-if="showUnclosedModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-opacity">
        <div class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl overflow-hidden flex flex-col max-h-[80vh] animate-fade-in">
          <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0">
            <div>
              <h2 class="text-xl font-black text-gray-900 leading-none mb-1">Salidas Pendientes</h2>
              <p class="text-[12px] font-bold text-gray-400 uppercase tracking-widest">Alumnos que no han marcado salida hoy</p>
            </div>
            <button @click="showUnclosedModal = false" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-all">
              <ion-icon :icon="closeOutline" class="text-xl"></ion-icon>
            </button>
          </div>

          <div class="flex-grow overflow-y-auto p-6 space-y-4 custom-scrollbar">
            <div v-if="loadingUnclosed" class="flex flex-col items-center justify-center py-12">
              <div class="w-10 h-10 border-4 border-brand-blue border-t-transparent rounded-full animate-spin mb-4"></div>
              <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Cargando lista...</p>
            </div>
            
            <div v-else-if="unclosedStudents.length === 0" class="text-center py-12">
              <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center text-green-500 mx-auto mb-4">
                 <ion-icon :icon="checkmarkCircleOutline" class="text-4xl"></ion-icon>
              </div>
              <h4 class="text-lg font-black text-gray-900 mb-1">¡Todo en orden!</h4>
              <p class="text-gray-400 font-medium px-8 text-sm">No hay alumnos con salidas pendientes registradas para hoy.</p>
            </div>

            <div v-else v-for="record in unclosedStudents" :key="record.id" class="p-4 bg-gray-50 rounded-2xl flex items-center gap-4 border border-gray-100 hover:border-amber-200 hover:bg-amber-50/10 transition-all">
              <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-brand-blue font-black overflow-hidden shadow-sm">
                 <img v-if="record.student.photo_url" :src="record.student.photo_url" class="w-full h-full object-cover">
                 <span v-else>{{ record.student.first_name[0] }}{{ record.student.last_name[0] }}</span>
              </div>
              <div class="flex-grow">
                <h4 class="text-sm font-black text-gray-900 leading-none mb-1">{{ record.student.first_name }} {{ record.student.last_name }}</h4>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">{{ record.student.classroom?.grade }}º {{ record.student.classroom?.group_letter }} • ID: {{ record.student.enrollment_code }}</p>
              </div>
              <div class="text-right">
                 <div class="flex items-center gap-1 text-brand-blue mb-0.5">
                    <ion-icon :icon="timeOutline"></ion-icon>
                    <span class="text-xs font-black">{{ new Date(record.scanned_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                 </div>
                 <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Entrada</p>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-50 flex justify-end bg-gray-50/50">
            <button @click="showUnclosedModal = false" class="bg-brand-blue text-white font-black px-8 py-3 rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-all">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent } from '@ionic/vue';
import TeacherDashboard from '@/components/admin/TeacherDashboard.vue';
import { 
  trendingUpOutline, business, school, megaphoneOutline, documentTextOutline, peopleOutline, 
  checkmarkCircleOutline, banOutline, personAddOutline, ellipsisVerticalOutline, personOutline,
  logOutOutline, closeOutline, timeOutline, refreshOutline
} from 'ionicons/icons';
import api from '@/services/api';
import { storage } from '@/services/storage';

const loading = ref(true);
const isAdmin = ref(false);
const isTeacher = ref(false);
const schoolName = ref('');
const activeSchoolName = ref('');
const currentUser = ref<any>(null);

const showUnclosedModal = ref(false);
const unclosedStudents = ref<any[]>([]);
const loadingUnclosed = ref(false);

const stats = ref({
  schools: 0,
  students: 0,
  users: 0,
  kiosks: 0,
  systemHealth: 0
});

// Director stats state
const directorStats = ref({
  totalStudents: 0,
  attendanceToday: 0,
  attendanceRate: 0,
  attendanceTrend: '+0',
  absentCount: 0,
  unclosedCount: 0,
  staffPresent: 0,
  totalStaff: 0,
  entrySummary: {
    onTime: 0,
    late: 0,
    absent: 0
  },
  groupStats: [] as any[],
  staff: [] as any[]
});

const formattedDate = computed(() => {
  return new Date().toLocaleDateString('es-ES', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
});

const directorLastName = computed(() => {
  if (!currentUser.value?.name) return 'Martínez';
  const parts = currentUser.value.name.split(' ');
  return parts[parts.length - 1];
});

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('es-MX').format(num);
};

const fetchDashboardStats = async () => {
  loading.value = true;
  try {
    const now = new Date();
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const startOfTodayISO = todayStart.toISOString();

    const endpoint = isAdmin.value ? '/admin/stats' : '/admin/director/stats';
    const res = await api.get(endpoint, {
      params: { local_start: startOfTodayISO }
    });
    
    if (res.data.success) {
      if (isAdmin.value) {
        stats.value = res.data.data;
      } else {
        directorStats.value = res.data.data;
      }
    }
  } catch (error) {
    console.error('Error fetching dashboard stats', error);
  } finally {
    loading.value = false;
  }
};

const handleRefresh = async (event: any) => {
  await fetchDashboardStats();
  event.target.complete();
};

const fetchUnclosedStudents = async () => {
  loadingUnclosed.value = true;
  try {
    const now = new Date();
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const startOfTodayISO = todayStart.toISOString();

    const res = await api.get('/admin/reports/unclosed', {
      params: { local_start: startOfTodayISO }
    });
    if (res.data.success) {
      unclosedStudents.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching unclosed students', error);
  } finally {
    loadingUnclosed.value = false;
  }
};

const watchUnclosedModal = (val: boolean) => {
  if (val) fetchUnclosedStudents();
};

watch(showUnclosedModal, watchUnclosedModal);

onMounted(async () => {
  const user = await storage.get('auth_user');
  if (user) {
    currentUser.value = user;
    isAdmin.value = user.role === 'super_admin';
    isTeacher.value = user.role === 'teacher';
    schoolName.value = user.school?.name || '';
  }

  const currentId = await storage.get('current_school_id');
  const schools = await storage.get('user_schools');
  if (currentId && schools) {
    const active = schools.find((s: any) => s.id === currentId);
    if (active) activeSchoolName.value = active.name;
  }
  
  fetchDashboardStats();
});
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Custom donut transition */
svg circle {
  transition: stroke-dasharray 1s ease-in-out, stroke-dashoffset 1s ease-in-out;
}

/* Modal Styles for pending exits */
.unclosed-modal-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 10px;
}
</style>
