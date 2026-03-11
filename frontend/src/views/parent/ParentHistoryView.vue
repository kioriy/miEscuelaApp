<template>
  <ion-page class="bg-[#F8F9FA]">
    <div class="min-h-screen flex flex-col bg-[#F8F9FA] font-sans">
      
      <!-- Top Navigation Header -->
      <header class="bg-[#F8F9FA] pt-6 pb-2 border-b-0 flex-none sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
          <div class="flex items-center gap-2 text-sm font-bold text-gray-500">
             <button @click="$router.push('/parent/dashboard')" class="hover:text-gray-900 transition-colors">Portal Escolar</button>
             <ion-icon :icon="chevronForwardOutline" class="text-xs"></ion-icon>
             <span class="text-gray-900">Historial de Asistencias</span>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <ion-content :fullscreen="true" class="ion-padding" style="--background: transparent; --ion-background-color: transparent;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 pb-12">
            
            <!-- Page Title & Filter -->
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
               <div>
                 <h1 class="text-3xl font-black text-[#1B254B] tracking-tight mb-2">
                    Historial Escolar
                 </h1>
                 <p class="text-[#8F9BBA] font-medium text-base">
                    Revise los registros detallados de entrada y salida de sus estudiantes.
                 </p>
               </div>
               
               <!-- Filter Dropdown -->
               <div class="w-full md:w-64">
                  <label class="block text-xs font-bold text-gray-400 mb-1 ml-1 uppercase tracking-wider">Filtrar por estudiante</label>
                  <div class="relative">
                     <select v-model="selectedStudentId" @change="fetchHistory(1)" class="w-full appearance-none bg-white border border-[#E0E5F2] hover:border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-blue-100 rounded-xl px-4 py-3 text-sm font-black text-[#1B254B] outline-none shadow-sm cursor-pointer transition-all">
                        <option value="">Todos los estudiantes</option>
                        <option v-for="child in children" :key="child.id" :value="child.id">
                           {{ child.name }}
                        </option>
                     </select>
                     <ion-icon :icon="chevronDownOutline" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
                  </div>
               </div>
            </div>

            <!-- Stats Numeralia Grid -->
            <div v-if="!loading && stats" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
               
               <!-- Asistencias del mes -->
               <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md">
                  <div class="flex items-start justify-between mb-4">
                     <p class="text-xs font-black text-gray-400 uppercase tracking-widest pt-1">Asistencias del mes</p>
                     <div class="w-10 h-10 bg-blue-50 text-brand-blue rounded-xl flex items-center justify-center">
                        <ion-icon :icon="calendarOutline" class="text-xl"></ion-icon>
                     </div>
                  </div>
                  <div>
                     <h3 class="text-3xl font-black text-[#1B254B] mb-1">{{ stats.attendances.value }}</h3>
                     <p :class="stats.attendances.is_positive ? 'text-green-500' : 'text-red-500'" class="text-xs font-bold flex items-center gap-1">
                        {{ stats.attendances.trend }}
                     </p>
                  </div>
               </div>

               <!-- Promedio puntualidad -->
               <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md">
                  <div class="flex items-start justify-between mb-4">
                     <p class="text-xs font-black text-gray-400 uppercase tracking-widest pt-1">Promedio puntualidad</p>
                     <div class="w-10 h-10 bg-green-50 text-green-500 rounded-xl flex items-center justify-center">
                        <ion-icon :icon="timerOutline" class="text-xl"></ion-icon>
                     </div>
                  </div>
                  <div>
                     <h3 class="text-3xl font-black text-[#1B254B] mb-1">{{ stats.punctuality.value }}</h3>
                     <p :class="stats.punctuality.is_positive ? 'text-green-500' : 'text-red-500'" class="text-xs font-bold flex items-center gap-1">
                        {{ stats.punctuality.trend }}
                     </p>
                  </div>
               </div>

               <!-- Total retardos -->
               <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md">
                  <div class="flex items-start justify-between mb-4">
                     <p class="text-xs font-black text-gray-400 uppercase tracking-widest pt-1">Total retardos</p>
                     <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center">
                        <ion-icon :icon="alertCircleOutline" class="text-xl"></ion-icon>
                     </div>
                  </div>
                  <div>
                     <h3 class="text-3xl font-black text-[#1B254B] mb-1">{{ stats.lates.value }}</h3>
                     <p :class="stats.lates.is_positive ? 'text-green-500' : 'text-orange-500'" class="text-xs font-bold flex items-center gap-1">
                        {{ stats.lates.trend }}
                     </p>
                  </div>
               </div>

            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-20">
               <div class="w-8 h-8 border-4 border-brand-blue border-t-transparent rounded-full animate-spin"></div>
            </div>

            <!-- History Timeline -->
            <div v-else class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
               <div v-if="history.length > 0" class="relative">
                  
                  <!-- Timeline line -->
                  <div class="absolute inset-y-6 left-5 md:left-8 w-px bg-gray-100 hidden sm:block"></div>

                  <div class="space-y-6">
                     <div v-for="record in history" :key="record.id" class="relative flex flex-col sm:flex-row gap-4 sm:gap-6 group">
                        
                        <!-- Timeline Node & Time -->
                        <div class="flex items-center sm:items-start gap-4 sm:w-32 flex-shrink-0 relative z-10">
                           <div :class="record.type === 'entrada' ? 'bg-green-500' : 'bg-gray-300'" class="hidden sm:flex w-3 h-3 rounded-full mt-1.5 border-4 border-white box-content shadow-sm relative -left-[5px]"></div>
                           <div>
                              <p class="text-xs font-black text-gray-400 uppercase tracking-widest pt-1">{{ record.time_label.split(', ')[0] }}</p>
                              <p class="text-sm font-black text-[#1B254B]">{{ record.time_label.split(', ')[1] }}</p>
                           </div>
                        </div>

                        <!-- Record Card -->
                        <div class="flex-grow bg-gray-50 border border-gray-100 rounded-2xl p-4 flex flex-col md:flex-row items-start md:items-center gap-4 transition-colors hover:bg-gray-100/50">
                           <!-- Status Badge (Mobile only) -->
                           <span :class="record.type === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'" class="sm:hidden text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-md mb-2 md:mb-0 inline-block">
                              {{ record.type }}
                           </span>

                           <!-- Avatar -->
                           <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-sm flex-shrink-0 bg-white">
                              <img :src="record.avatar" :alt="record.student_name" class="w-full h-full object-cover">
                           </div>
                           
                           <!-- Details -->
                           <div class="flex-grow">
                              <div class="flex items-center gap-2 mb-0.5">
                                 <h4 class="text-base font-black text-[#1B254B] leading-tight">{{ record.student_name }}</h4>
                                 <span :class="record.type === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'" class="hidden sm:inline-block text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md">
                                    {{ record.type }}
                                 </span>
                              </div>
                              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs font-bold text-gray-500">
                                 <div class="flex items-center gap-1">
                                    <ion-icon :icon="location" class="text-red-500"></ion-icon>
                                    {{ record.location }}
                                 </div>
                                 <div class="flex items-center gap-1">
                                    <ion-icon :icon="scanOutline" class="text-brand-blue"></ion-icon>
                                    {{ record.method }}
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Empty State -->
               <div v-else class="text-center py-16">
                  <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                     <ion-icon :icon="timeOutline" class="text-3xl text-gray-300"></ion-icon>
                  </div>
                  <h3 class="text-lg font-black text-[#1B254B] mb-2">Sin historial</h3>
                  <p class="text-sm font-medium text-gray-500 max-w-sm mx-auto">
                     No se encontraron registros de asistencia para los filtros seleccionados.
                  </p>
               </div>

               <!-- Load More Button -->
               <div v-if="pagination.current_page < pagination.last_page" class="mt-8 text-center relative z-10">
                  <button @click="fetchHistory(pagination.current_page + 1)" :disabled="loadingMore" class="bg-white border border-[#E0E5F2] hover:border-brand-blue hover:text-brand-blue text-[#1B254B] font-black text-sm px-6 py-3 rounded-xl shadow-sm transition-all disabled:opacity-50 flex items-center gap-2 mx-auto">
                     <span v-if="loadingMore" class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                     Cargar registros anteriores
                  </button>
               </div>
            </div>

        </div>
      </ion-content>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  chevronForwardOutline, chevronDownOutline, timeOutline, location, scanOutline,
  calendarOutline, timerOutline, alertCircleOutline
} from 'ionicons/icons';
import api from '@/services/api';

const route = useRoute();
const loading = ref(true);
const loadingMore = ref(false);

const children = ref<any[]>([]);
const history = ref<any[]>([]);
const stats = ref<any>(null);
const selectedStudentId = ref<string | number>('');

const pagination = ref({
   current_page: 1,
   last_page: 1,
   total: 0
});

const fetchHistory = async (page = 1) => {
   if (page === 1) loading.value = true;
   else loadingMore.value = true;

   try {
      const params: any = { page };
      if (selectedStudentId.value) {
         params.student_id = selectedStudentId.value;
      }
      const res = await api.get('/parent/history', { params });
      
      if (res.data.success) {
         // Update children list for filter
         children.value = res.data.data.children;
         stats.value = res.data.data.stats;
         
         // Set history logs
         const newLogs = res.data.data.history.data;
         if (page === 1) {
            history.value = newLogs;
         } else {
            history.value = [...history.value, ...newLogs];
         }

         // Update pagination details
         pagination.value = {
            current_page: res.data.data.history.current_page,
            last_page: res.data.data.history.last_page,
            total: res.data.data.history.total
         };
      }
   } catch (error) {
      console.error('Error fetching history:', error);
   } finally {
      loading.value = false;
      loadingMore.value = false;
   }
};

onMounted(() => {
   if (route.query.student_id) {
      selectedStudentId.value = route.query.student_id as string;
   }
   fetchHistory();
});
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}
</style>
