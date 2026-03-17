<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="p-6 lg:p-10 w-full max-w-[1600px] mx-auto min-h-full flex flex-col bg-gray-50 font-sans">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 animate-fade-in">
          <div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none mb-3">Reportes de Asistencia</h1>
            <p class="text-gray-500 font-bold tracking-wide flex items-center gap-2">
              Estadísticas generales de tu escuela
            </p>
            <p v-if="activeSchoolName" class="text-[15px] font-black text-brand-blue flex items-center gap-1.5 mt-2">
              <ion-icon :icon="businessOutline"></ion-icon> {{ activeSchoolName }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <!-- Date Range Selector -->
            <div class="relative">
              <select v-model="dateRange" class="appearance-none bg-white border border-gray-200 text-gray-700 text-[13px] font-black rounded-xl py-3 pl-10 pr-10 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-brand-blue cursor-pointer shadow-sm">
                <option value="week">Esta Semana</option>
                <option value="month">Este Mes</option>
                <option value="quarter">Este Trimestre</option>
              </select>
              <ion-icon :icon="calendarOutline" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none"></ion-icon>
              <ion-icon :icon="chevronDownOutline" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></ion-icon>
            </div>
            <button class="bg-brand-blue text-white font-black px-6 py-3 rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-all flex items-center gap-2">
              <ion-icon :icon="downloadOutline" class="text-xl"></ion-icon>
              Exportar
            </button>
          </div>
        </div>

        <!-- Summary Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 animate-fade-in">
          <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Matrícula Total</p>
                <h3 class="text-4xl font-black text-gray-900 tracking-tighter">
                  <span v-if="loading">...</span>
                  <span v-else>{{ summaryStats.totalStudents }}</span>
                </h3>
              </div>
              <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-brand-blue group-hover:scale-110 transition-transform">
                <ion-icon :icon="peopleOutline" class="text-2xl"></ion-icon>
              </div>
            </div>
            <p class="text-[12px] font-bold text-gray-400">Alumnos inscritos</p>
          </div>

          <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Asistencia Promedio</p>
                <h3 class="text-4xl font-black text-green-600 tracking-tighter">
                  <span v-if="loading">...</span>
                  <span v-else>{{ summaryStats.avgAttendance }}%</span>
                </h3>
              </div>
              <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 group-hover:scale-110 transition-transform">
                <ion-icon :icon="checkmarkCircleOutline" class="text-2xl"></ion-icon>
              </div>
            </div>
            <div class="flex items-center gap-1.5 text-green-600 font-black text-[12px]">
              <ion-icon :icon="trendingUpOutline"></ion-icon>
              <span>+1.2% <span class="text-gray-400 font-bold">vs periodo anterior</span></span>
            </div>
          </div>

          <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Total Ausencias</p>
                <h3 class="text-4xl font-black text-orange-500 tracking-tighter">
                  <span v-if="loading">...</span>
                  <span v-else>{{ summaryStats.totalAbsences }}</span>
                </h3>
              </div>
              <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform">
                <ion-icon :icon="banOutline" class="text-2xl"></ion-icon>
              </div>
            </div>
            <p class="text-[12px] font-bold text-gray-400">En el periodo seleccionado</p>
          </div>

          <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-[13px] font-bold text-gray-400 tracking-wide mb-1">Total Retardos</p>
                <h3 class="text-4xl font-black text-amber-500 tracking-tighter">
                  <span v-if="loading">...</span>
                  <span v-else>{{ summaryStats.totalLates }}</span>
                </h3>
              </div>
              <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                <ion-icon :icon="timeOutline" class="text-2xl"></ion-icon>
              </div>
            </div>
            <p class="text-[12px] font-bold text-gray-400">En el periodo seleccionado</p>
          </div>
        </div>

        <!-- View Mode Tabs -->
        <div class="bg-white rounded-[32px] shadow-sm border border-gray-100 overflow-hidden animate-fade-in">
          <!-- Tab Bar -->
          <div class="p-4 border-b border-gray-100 flex items-center gap-2 bg-gray-50/30">
            <button 
              v-for="tab in tabs" 
              :key="tab.key"
              @click="activeTab = tab.key"
              class="px-6 py-3 rounded-xl text-[13px] font-black uppercase tracking-widest transition-all flex items-center gap-2"
              :class="activeTab === tab.key 
                ? 'bg-brand-blue text-white shadow-md shadow-blue-500/20' 
                : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900'"
            >
              <ion-icon :icon="tab.icon" class="text-lg"></ion-icon>
              {{ tab.label }}
            </button>
          </div>

          <!-- TAB: Por Grado -->
          <div v-if="activeTab === 'grade'" class="p-8">
            <div class="flex items-center justify-between mb-8">
              <div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">Asistencia por Grado</h3>
                <p class="text-gray-400 text-sm font-bold">Promedio de asistencia por cada grado escolar</p>
              </div>
            </div>

            <div class="space-y-6">
              <div v-for="grade in gradeStats" :key="grade.grade" class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100 hover:border-blue-100 hover:bg-blue-50/10 transition-all group">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-brand-blue/10 text-brand-blue flex items-center justify-center font-black text-xl group-hover:bg-brand-blue group-hover:text-white transition-all">
                      {{ grade.grade }}º
                    </div>
                    <div>
                      <h4 class="text-[16px] font-black text-gray-900">{{ grade.grade }}º Grado</h4>
                      <p class="text-[12px] font-bold text-gray-400">{{ grade.totalStudents }} alumnos • {{ grade.groups }} grupos</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="text-2xl font-black" :class="grade.attendance >= 90 ? 'text-green-600' : grade.attendance >= 75 ? 'text-amber-500' : 'text-red-500'">
                      {{ grade.attendance }}%
                    </span>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Asistencia</p>
                  </div>
                </div>

                <!-- Progress Bar -->
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden mb-4">
                  <div 
                    class="h-full rounded-full transition-all duration-700 ease-out"
                    :class="grade.attendance >= 90 ? 'bg-green-500' : grade.attendance >= 75 ? 'bg-amber-400' : 'bg-red-500'"
                    :style="{ width: grade.attendance + '%' }"
                  ></div>
                </div>

                <!-- Stats Row -->
                <div class="flex items-center gap-6 text-[12px] font-bold">
                  <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    <span class="text-gray-500">Presentes: <span class="text-gray-900 font-black">{{ grade.present }}</span></span>
                  </div>
                  <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                    <span class="text-gray-500">Ausencias: <span class="text-gray-900 font-black">{{ grade.absences }}</span></span>
                  </div>
                  <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                    <span class="text-gray-500">Retardos: <span class="text-gray-900 font-black">{{ grade.lates }}</span></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB: Por Grupo -->
          <div v-if="activeTab === 'group'" class="p-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-8">
              <div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">Asistencia por Grupo</h3>
                <p class="text-gray-400 text-sm font-bold">Detalle estadístico por cada sección</p>
              </div>
              <div class="relative">
                <select v-model="groupFilterGrade" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-[13px] font-black rounded-xl py-3 pl-4 pr-10 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-brand-blue cursor-pointer">
                  <option value="">Todos los Grados</option>
                  <option v-for="g in availableGrades" :key="g" :value="g">{{ g }}º Grado</option>
                </select>
                <ion-icon :icon="chevronDownOutline" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></ion-icon>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div 
                v-for="group in filteredGroupStats" 
                :key="group.name"
                class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100 hover:shadow-md hover:border-blue-100 transition-all group cursor-pointer"
              >
                <div class="flex items-center justify-between mb-5">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl font-black text-[15px] flex items-center justify-center"
                         :class="group.attendance >= 90 ? 'bg-green-50 text-green-600' : group.attendance >= 75 ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600'">
                      {{ group.name }}
                    </div>
                    <div>
                      <h4 class="text-[15px] font-black text-gray-900 leading-tight">Grupo {{ group.name }}</h4>
                      <p class="text-[11px] font-bold text-gray-400">{{ group.totalStudents }} alumnos</p>
                    </div>
                  </div>
                  <span class="text-xl font-black" :class="group.attendance >= 90 ? 'text-green-600' : group.attendance >= 75 ? 'text-amber-500' : 'text-red-500'">
                    {{ group.attendance }}%
                  </span>
                </div>

                <!-- Mini Progress -->
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden mb-4">
                  <div 
                    class="h-full rounded-full transition-all duration-700"
                    :class="group.attendance >= 90 ? 'bg-green-500' : group.attendance >= 75 ? 'bg-amber-400' : 'bg-red-500'"
                    :style="{ width: group.attendance + '%' }"
                  ></div>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center">
                  <div class="bg-white rounded-xl p-3 border border-gray-50">
                    <p class="text-lg font-black text-green-600">{{ group.present }}</p>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Presentes</p>
                  </div>
                  <div class="bg-white rounded-xl p-3 border border-gray-50">
                    <p class="text-lg font-black text-orange-500">{{ group.absences }}</p>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Ausencias</p>
                  </div>
                  <div class="bg-white rounded-xl p-3 border border-gray-50">
                    <p class="text-lg font-black text-amber-500">{{ group.lates }}</p>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Retardos</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB: Por Alumno -->
          <div v-if="activeTab === 'student'" class="p-8">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-8">
              <div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">Asistencia por Alumno</h3>
                <p class="text-gray-400 text-sm font-bold">Estadísticas individuales de cada estudiante</p>
              </div>
              <div class="flex items-center gap-3 w-full lg:w-auto">
                <!-- Search -->
                <div class="relative flex-grow lg:flex-grow-0">
                  <ion-icon :icon="searchOutline" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></ion-icon>
                  <input 
                    v-model="studentSearch"
                    type="text" 
                    placeholder="Buscar alumno..." 
                    class="w-full lg:w-[280px] pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-blue focus:ring-4 focus:ring-blue-500/10 text-[13px] font-bold text-gray-700 transition-all" 
                  />
                </div>
                <!-- Grade Filter -->
                <div class="relative">
                  <select v-model="studentFilterGrade" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-[13px] font-black rounded-xl py-3 pl-4 pr-10 focus:outline-none focus:ring-4 focus:ring-blue-500/10 cursor-pointer">
                    <option value="">Grado</option>
                    <option v-for="g in availableGrades" :key="g" :value="g">{{ g }}º</option>
                  </select>
                  <ion-icon :icon="chevronDownOutline" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></ion-icon>
                </div>
                <!-- Group Filter -->
                <div class="relative">
                  <select v-model="studentFilterGroup" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-[13px] font-black rounded-xl py-3 pl-4 pr-10 focus:outline-none focus:ring-4 focus:ring-blue-500/10 cursor-pointer">
                    <option value="">Grupo</option>
                    <option v-for="l in availableGroups" :key="l" :value="l">{{ l }}</option>
                  </select>
                  <ion-icon :icon="chevronDownOutline" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></ion-icon>
                </div>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                  <tr class="bg-gray-50/30 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase border-b border-gray-50">
                    <th class="p-5 pl-6">Estudiante</th>
                    <th class="p-5">Grado / Grupo</th>
                    <th class="p-5 text-center">% Asistencia</th>
                    <th class="p-5 text-center">Días Asistidos</th>
                    <th class="p-5 text-center">Ausencias</th>
                    <th class="p-5 text-center">Retardos</th>
                    <th class="p-5 pr-6 text-center">Estado</th>
                  </tr>
                </thead>
                <tbody class="text-sm">
                  <tr v-if="filteredStudentStats.length === 0" class="bg-white">
                    <td colspan="7" class="p-12 text-center text-gray-400 font-medium">No se encontraron estudiantes con los filtros aplicados.</td>
                  </tr>
                  <tr 
                    v-for="student in filteredStudentStats" 
                    :key="student.id"
                    class="border-b border-gray-50 hover:bg-blue-50/30 transition-all group"
                  >
                    <td class="p-5 pl-6">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-brand-blue font-black text-sm border-2 border-white shadow-sm overflow-hidden shrink-0 group-hover:scale-110 transition-transform">
                          {{ student.name.charAt(0) }}{{ student.lastName.charAt(0) }}
                        </div>
                        <div>
                          <p class="font-black text-gray-900 leading-tight text-[13px]">{{ student.name }} {{ student.lastName }}</p>
                          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ student.enrollmentCode }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="p-5">
                      <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg font-black text-[12px] border border-indigo-100">
                        {{ student.grade }}º {{ student.group }}
                      </span>
                    </td>
                    <td class="p-5 text-center">
                      <span class="text-[15px] font-black" :class="student.attendance >= 90 ? 'text-green-600' : student.attendance >= 75 ? 'text-amber-500' : 'text-red-500'">
                        {{ student.attendance }}%
                      </span>
                    </td>
                    <td class="p-5 text-center">
                      <span class="text-[14px] font-black text-gray-900">{{ student.daysAttended }}</span>
                      <span class="text-[11px] font-bold text-gray-400"> / {{ student.totalDays }}</span>
                    </td>
                    <td class="p-5 text-center">
                      <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-black text-[13px]"
                            :class="student.absences > 3 ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-700'">
                        {{ student.absences }}
                      </span>
                    </td>
                    <td class="p-5 text-center">
                      <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-black text-[13px]"
                            :class="student.lates > 3 ? 'bg-amber-50 text-amber-600' : 'bg-gray-50 text-gray-700'">
                        {{ student.lates }}
                      </span>
                    </td>
                    <td class="p-5 pr-6 text-center">
                      <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                            :class="{
                              'bg-green-50 text-green-600': student.status === 'regular',
                              'bg-amber-50 text-amber-600': student.status === 'warning',
                              'bg-red-50 text-red-600': student.status === 'critical'
                            }">
                        <div class="w-1.5 h-1.5 rounded-full" 
                             :class="{
                               'bg-green-500': student.status === 'regular',
                               'bg-amber-500': student.status === 'warning',
                               'bg-red-500': student.status === 'critical'
                             }"></div>
                        {{ student.status === 'regular' ? 'Regular' : student.status === 'warning' ? 'Atención' : 'Crítico' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination info -->
            <div class="p-6 border-t border-gray-50 flex items-center justify-between bg-gray-50/20">
              <span class="text-[13px] text-gray-500 font-bold">
                Mostrando <span class="text-gray-900">{{ filteredStudentStats.length }}</span> de <span class="text-gray-900">{{ studentStats.length }}</span> alumnos
              </span>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-[11px] font-semibold text-gray-400 mt-12 pb-4 tracking-wide uppercase">
          © 2026 miEscuelaApp. Todos los derechos reservados.
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent } from '@ionic/vue';
import {
  businessOutline, calendarOutline, chevronDownOutline, downloadOutline,
  peopleOutline, checkmarkCircleOutline, trendingUpOutline, banOutline,
  timeOutline, schoolOutline, gridOutline, personOutline, searchOutline
} from 'ionicons/icons';
import { storage } from '@/services/storage';
import api from '@/services/api';

const activeSchoolName = ref('');
const dateRange = ref('week');
const activeTab = ref('grade');
const loading = ref(true);

const tabs = [
  { key: 'grade', label: 'Por Grado', icon: schoolOutline },
  { key: 'group', label: 'Por Grupo', icon: gridOutline },
  { key: 'student', label: 'Por Alumno', icon: personOutline }
];

// Filters
const groupFilterGrade = ref('');
const studentSearch = ref('');
const studentFilterGrade = ref('');
const studentFilterGroup = ref('');

// Data from API
const summaryStats = ref({
  totalStudents: 0,
  avgAttendance: 0,
  totalAbsences: 0,
  totalLates: 0
});

const gradeStats = ref<any[]>([]);
const groupStats = ref<any[]>([]);
const studentStats = ref<any[]>([]);

// Fetch reports data from backend
const fetchReportsData = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/director/reports', {
      params: { range: dateRange.value }
    });
    if (res.data.success) {
      const d = res.data.data;
      summaryStats.value = d.summary;
      gradeStats.value = d.gradeStats;
      groupStats.value = d.groupStats;
      studentStats.value = d.studentStats;
    }
  } catch (error) {
    console.error('Error fetching reports data:', error);
  } finally {
    loading.value = false;
  }
};

// Computed: available grades from data
const availableGrades = computed(() => {
  const grades = [...new Set(gradeStats.value.map(g => g.grade))];
  return grades.sort();
});

// Computed: available groups from data
const availableGroups = computed(() => {
  const groups = [...new Set(studentStats.value.map(s => s.group))];
  return groups.sort();
});

const filteredGroupStats = computed(() => {
  if (!groupFilterGrade.value) return groupStats.value;
  return groupStats.value.filter(g => String(g.grade) === String(groupFilterGrade.value));
});

const filteredStudentStats = computed(() => {
  return studentStats.value.filter(s => {
    const nameMatch = `${s.name} ${s.lastName}`.toLowerCase().includes((studentSearch.value || '').toLowerCase());
    const codeMatch = s.enrollmentCode.toLowerCase().includes((studentSearch.value || '').toLowerCase());
    const gradeMatch = !studentFilterGrade.value || String(s.grade) === String(studentFilterGrade.value);
    const groupMatch = !studentFilterGroup.value || s.group === studentFilterGroup.value;
    return (nameMatch || codeMatch) && gradeMatch && groupMatch;
  });
});

const handleRefresh = async (event: any) => {
  await fetchReportsData();
  event.target.complete();
};

// Re-fetch when date range changes
watch(dateRange, () => {
  fetchReportsData();
});

onMounted(async () => {
  const currentId = await storage.get('current_school_id');
  const schools = await storage.get('user_schools');
  if (currentId && schools) {
    const active = schools.find((s: any) => s.id === currentId);
    if (active) activeSchoolName.value = active.name;
  }
  fetchReportsData();
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
</style>
