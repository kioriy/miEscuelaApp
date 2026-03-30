<template>
  <div class="animate-fade-in space-y-6 max-w-7xl mx-auto">
    <!-- Header: Portal Docente & User info (Usually handled by Layout, but depending on design we might add something here) -->
    
    <!-- Title and Control Panel -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row justify-between items-center gap-4">
      <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Panel de Control</h1>
        <p class="text-gray-500 font-medium flex items-center gap-2 mt-1">
          <ion-icon :icon="bookOutline" class="text-gray-400"></ion-icon>
          {{ currentClass.name }}
        </p>
      </div>
      <div class="flex items-center gap-3 w-full md:w-auto">

        <!-- Refresh Button -->
        <button 
          @click="refreshDashboard" 
          class="w-[46px] h-[46px] flex-shrink-0 flex items-center justify-center bg-white hover:bg-gray-50 border border-gray-200 text-gray-500 hover:text-brand-blue rounded-xl transition-all" 
          title="Actualizar datos"
          :disabled="loading"
        >
          <ion-icon :icon="refreshOutline" class="text-xl" :class="{'animate-spin': loading}"></ion-icon>
        </button>

        <!-- Class Switcher Dropdown -->
        <div class="relative flex-1 md:flex-none">
          <button @click="isClassDropdownOpen = !isClassDropdownOpen" class="w-full flex items-center justify-between md:justify-center gap-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold px-5 py-3 rounded-xl transition-all">
            <span class="flex items-center gap-2">
              <ion-icon :icon="swapHorizontalOutline"></ion-icon>
              Cambiar Clase
            </span>
            <ion-icon :icon="chevronDownOutline" class="text-sm transition-transform duration-200" :class="{ 'rotate-180': isClassDropdownOpen }"></ion-icon>
          </button>
          
          <!-- Dropdown Menu -->
          <div v-if="isClassDropdownOpen" class="absolute z-50 top-full mt-2 left-0 w-full min-w-[240px] bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden animate-fade-in">
            <div class="p-2 space-y-1">
              <button 
                v-for="classroom in assignedClasses" 
                :key="classroom.id"
                @click="selectClass(classroom.id)"
                class="w-full text-left px-4 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center justify-between group"
                :class="currentClass.id === classroom.id ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-700 hover:bg-gray-50'"
              >
                <span>{{ classroom.name }}</span>
                <ion-icon v-if="currentClass.id === classroom.id" :icon="checkmarkOutline" class="text-brand-blue text-lg"></ion-icon>
              </button>
            </div>
            <div v-if="assignedClasses.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center font-medium">
              No hay más clases asignadas
            </div>
          </div>
        </div>

        <router-link 
          v-if="currentClass.id"
          :to="{ name: 'TeacherAttendance', params: { classroomId: currentClass.id } }"
          class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-brand-blue hover:bg-blue-600 text-white font-black px-6 py-3 rounded-xl shadow-md shadow-blue-500/20 transition-all"
        >
          <ion-icon :icon="listOutline"></ion-icon>
          Tomar Asistencia
        </router-link>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Estudiantes -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
          <p class="text-sm font-bold text-gray-500">Total Estudiantes</p>
          <ion-icon :icon="peopleOutline" class="text-gray-300 text-2xl"></ion-icon>
        </div>
        <div class="flex items-end gap-2">
          <h2 class="text-4xl font-black text-gray-900">{{ stats.total }}</h2>
        </div>
        <div class="w-full h-1.5 bg-gray-600 rounded-full mt-4"></div>
      </div>

      <!-- Presentes -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
          <p class="text-sm font-bold text-gray-500">Presentes</p>
          <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-500">
             <ion-icon :icon="checkmarkOutline" class="text-lg"></ion-icon>
          </div>
        </div>
        <div class="flex items-end gap-2">
          <h2 class="text-4xl font-black text-gray-900">{{ stats.present }}</h2>
          <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-md mb-1">+{{ Math.round((stats.present / stats.total) * 100) || 0 }}%</span>
        </div>
        <div class="w-full h-1.5 bg-gray-100 rounded-full mt-4 flex">
           <div class="h-full bg-green-500 rounded-full" :style="{ width: ((stats.present / stats.total) * 100) + '%' }"></div>
        </div>
      </div>

      <!-- Retardos -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
          <p class="text-sm font-bold text-gray-500">Retardos</p>
          <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
             <ion-icon :icon="timeOutline" class="text-lg"></ion-icon>
          </div>
        </div>
        <div class="flex items-end gap-2">
          <h2 class="text-4xl font-black text-gray-900">{{ stats.late }}</h2>
          <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md mb-1">{{ Math.round((stats.late / stats.total) * 100) || 0 }}%</span>
        </div>
        <div class="w-full h-1.5 bg-gray-100 rounded-full mt-4 flex">
           <div class="h-full bg-amber-400 rounded-full" :style="{ width: ((stats.late / stats.total) * 100) + '%' }"></div>
        </div>
      </div>

      <!-- Ausentes -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
          <p class="text-sm font-bold text-gray-500">Ausentes</p>
          <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-500">
             <ion-icon :icon="closeOutline" class="text-lg"></ion-icon>
          </div>
        </div>
        <div class="flex items-end gap-2">
          <h2 class="text-4xl font-black text-gray-900">{{ stats.absent }}</h2>
          <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-md mb-1">-{{ Math.round((stats.absent / stats.total) * 100) || 0 }}%</span>
        </div>
        <div class="w-full h-1.5 bg-gray-100 rounded-full mt-4 flex">
           <div class="h-full bg-red-500 rounded-full" :style="{ width: ((stats.absent / stats.total) * 100) + '%' }"></div>
        </div>
      </div>
    </div>

    <!-- Main Content Layout -->
    <div class="flex flex-col xl:flex-row gap-6">
      
      <!-- Left Column: Attendance List -->
      <div class="flex-grow xl:w-2/3 flex flex-col gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
          <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-gray-900">Lista de Asistencia</h3>
            <div class="flex items-center gap-2">
               <button class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-50 transition-colors">
                  <ion-icon :icon="filterOutline" class="text-xl"></ion-icon>
               </button>
               <button class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-50 transition-colors">
                  <ion-icon :icon="ellipsisVerticalOutline" class="text-xl"></ion-icon>
               </button>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr>
                  <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest bg-gray-50/50">Estudiante</th>
                  <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest bg-gray-50/50">Estado</th>
                  <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest bg-gray-50/50">Hora de Entrada</th>

                </tr>
              </thead>
              <tbody>
                <tr v-for="student in students" :key="student.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors group">
                  <td class="py-4 px-6">
                    <div class="flex items-center gap-4">
                      <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-100 flex-shrink-0">
                        <img v-if="student.avatar" :src="student.avatar" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-500 font-bold">
                           {{ student.name.charAt(0) }}
                        </div>
                      </div>
                      <div>
                        <p class="text-sm font-black text-gray-900 group-hover:text-brand-blue transition-colors">{{ student.name }}</p>
                        <p class="text-[11px] font-bold text-gray-400">ID: {{ student.id_number }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="py-4 px-6">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider" 
                         :class="{
                           'bg-green-50 text-green-600': student.status === 'present',
                           'bg-amber-50 text-amber-600': student.status === 'late',
                           'bg-red-50 text-red-600': student.status === 'absent'
                         }">
                      <div class="w-1.5 h-1.5 rounded-full" 
                           :class="{
                             'bg-green-500': student.status === 'present',
                             'bg-amber-500': student.status === 'late',
                             'bg-red-500': student.status === 'absent'
                           }"></div>
                      {{ formatStatus(student.status) }}
                    </div>
                  </td>
                  <td class="py-4 px-6">
                    <span class="text-sm font-medium text-gray-600" v-if="student.time">{{ student.time }}</span>
                    <span class="text-sm font-medium text-gray-400" v-else>-</span>
                  </td>

                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-gray-50 flex justify-center">
             <button class="text-brand-blue font-bold text-sm tracking-wide hover:underline px-4 py-2">
                Ver lista completa
             </button>
          </div>
        </div>
      </div>

      <!-- Right Column: Activity & Messages -->
      <div class="xl:w-1/3 flex flex-col gap-6">
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
           <div class="flex items-center gap-2 mb-6">
              <div class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center">
                 <ion-icon :icon="timeOutline" class="text-lg"></ion-icon>
              </div>
              <h3 class="text-lg font-black text-gray-900 tracking-tight">Actividad Reciente</h3>
           </div>

           <div class="space-y-6 relative before:absolute before:inset-0 before:ml-4 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-100 before:to-transparent">
              <div v-for="activity in recentActivity" :key="activity.id" class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group select-none">
                 <div class="flex gap-4 w-full">
                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center z-10 border-2 border-white shadow-sm"
                         :class="{
                           'bg-green-100 text-green-600': activity.type === 'attendance',
                           'bg-blue-100 text-brand-blue': activity.type === 'message',
                           'bg-amber-100 text-amber-500': activity.type === 'late',
                           'bg-gray-100 text-gray-500': activity.type === 'system'
                         }">
                       <ion-icon :icon="getActivityIcon(activity.type)"></ion-icon>
                    </div>
                    <div class="flex-grow pt-1">
                       <p class="text-sm text-gray-900 font-medium">
                          <span class="font-black">{{ activity.subject }}</span> {{ activity.action }}
                       </p>
                       <p class="text-[11px] font-bold text-gray-400 mt-0.5">{{ activity.time }}</p>
                       <div v-if="activity.quote" class="mt-2 text-xs font-medium text-gray-600 italic bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                          "{{ activity.quote }}"
                       </div>
                    </div>
                 </div>
              </div>
           </div>
        </div>

        <!-- Sent Messages History -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
           <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-2">
                 <div class="w-8 h-8 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center">
                    <ion-icon :icon="megaphoneOutline" class="text-lg"></ion-icon>
                 </div>
                 <h3 class="text-lg font-black text-gray-900 tracking-tight">Historial de Envío</h3>
              </div>
              <router-link to="/admin/teacher/messaging" class="text-[11px] font-black text-brand-blue uppercase hover:underline">Nuevo Aviso</router-link>
           </div>

           <div v-if="historyLoading" class="flex-grow flex items-center justify-center py-12">
              <div class="w-6 h-6 border-2 border-brand-blue border-t-transparent rounded-full animate-spin"></div>
           </div>

           <div v-else-if="messageHistory.length === 0" class="flex-grow flex flex-col items-center justify-center py-12 px-6 text-center">
              <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mb-4">
                 <ion-icon :icon="mailOutline" class="text-2xl"></ion-icon>
              </div>
              <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-relaxed">No has enviado<br>avisos todavía.</p>
           </div>

           <div v-else class="space-y-4 flex-grow overflow-y-auto max-h-[400px] pr-2 custom-scrollbar">
              <div v-for="msg in messageHistory" :key="msg.id" class="p-4 rounded-xl border border-gray-50 bg-gray-50/30 hover:bg-gray-50 transition-colors group">
                 <div class="flex justify-between items-start mb-2">
                    <span class="text-[10px] font-black text-brand-blue uppercase tracking-tighter bg-blue-50 px-1.5 py-0.5 rounded">Para: {{ msg.recipients }}</span>
                    <span class="text-[10px] font-bold text-gray-400">{{ msg.time_ago }}</span>
                 </div>
                 <h4 class="text-sm font-black text-gray-900 mb-1 group-hover:text-brand-blue transition-colors truncate">{{ msg.title }}</h4>
                 <p class="text-[11px] text-gray-500 line-clamp-2 leading-relaxed">{{ msg.content }}</p>
                 <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                    <span class="text-[9px] font-bold text-gray-400 italic">Enviado: {{ msg.created_at }}</span>
                    <button class="text-[10px] font-black text-gray-400 hover:text-brand-blue flex items-center gap-1">
                       Detalle <ion-icon :icon="chevronForwardOutline"></ion-icon>
                    </button>
                 </div>
              </div>
           </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { 
  bookOutline, swapHorizontalOutline, listOutline, peopleOutline, 
  checkmarkOutline, timeOutline, closeOutline, filterOutline, 
  ellipsisVerticalOutline, createOutline, chatbubbleEllipsesOutline,
  megaphoneOutline, sendOutline, checkmarkCircleOutline, mailOutline,
  documentTextOutline, chevronDownOutline, refreshOutline, chevronForwardOutline
} from 'ionicons/icons';
import { IonIcon, toastController } from '@ionic/vue';
import api from '@/services/api';

const loading = ref(true);

const currentClass = ref({
  id: null as number | null,
  name: 'Cargando...'
});

const assignedClasses = ref<any[]>([]);
const isClassDropdownOpen = ref(false);

const stats = ref({
  total: 0,
  present: 0,
  late: 0,
  absent: 0
});

const students = ref<any[]>([]);
const recentActivity = ref<any[]>([]);
const messageHistory = ref<any[]>([]);
const historyLoading = ref(false);

const showToast = async (message: string, color: 'success' | 'danger' | 'warning' = 'success') => {
  const toast = await toastController.create({
    message,
    duration: 3000,
    position: 'bottom',
    color,
    buttons: [
      {
        text: 'Cerrar',
        role: 'cancel'
      }
    ]
  });
  await toast.present();
};

const formatStatus = (status: string) => {
  const map: Record<string, string> = {
    'present': 'Presente',
    'late': 'Retardo',
    'absent': 'Ausente'
  };
  return map[status] || status;
};

const getActivityIcon = (type: string) => {
  switch (type) {
    case 'present': return checkmarkOutline;
    case 'message': return mailOutline;
    case 'late': return timeOutline;
    case 'system': return documentTextOutline;
    default: return timeOutline;
  }
};

const fetchDashboardData = async (classroomId?: number) => {
  loading.value = true;
  try {
    const url = classroomId ? `/admin/teacher/dashboard?classroom_id=${classroomId}` : '/admin/teacher/dashboard';
    const res = await api.get(url);
    if (res.data.success) {
      currentClass.value = res.data.data.currentClass;
      assignedClasses.value = res.data.data.assignedClasses || [];
      stats.value = res.data.data.stats;
      students.value = res.data.data.students;
      recentActivity.value = res.data.data.recentActivity;
    }
  } catch (error) {
    console.error('Error fetching teacher dashboard data:', error);
    showToast('Error al cargar datos del panel', 'danger');
  } finally {
    loading.value = false;
  }
};

const refreshDashboard = async () => {
  try {
    await Promise.all([
      fetchDashboardData(currentClass.value.id || undefined),
      fetchMessageHistory()
    ]);
    showToast('Datos actualizados');
  } catch (error) {
    showToast('Error al actualizar datos', 'danger');
  }
};

const selectClass = async (classroomId: number) => {
  isClassDropdownOpen.value = false;
  if (currentClass.value.id !== classroomId) {
    await fetchDashboardData(classroomId);
    fetchMessageHistory(); // History is global, but good to refresh it when switching context
  }
};

const fetchMessageHistory = async () => {
  try {
    historyLoading.value = true;
    const res = await api.get('/admin/teacher/messaging/history');
    if (res.data.success) {
      messageHistory.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching message history:', error);
    showToast('Error al cargar historial de mensajes', 'danger');
  } finally {
    historyLoading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
  fetchMessageHistory();
});
</script>
