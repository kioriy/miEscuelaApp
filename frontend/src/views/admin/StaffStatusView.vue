<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <div class="p-6 lg:p-10 w-full max-w-[1600px] mx-auto min-h-full flex flex-col bg-gray-50 font-sans">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
          <div class="flex items-center gap-4">
            <button @click="$router.push('/admin/dashboard')" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-all shadow-sm">
              <ion-icon :icon="arrowBackOutline" class="text-xl"></ion-icon>
            </button>
            <div>
              <h1 class="text-[28px] font-black text-gray-900 tracking-tight leading-none mb-1">Estado del Personal</h1>
              <p class="text-gray-500 font-medium text-sm">Lista completa de asistencia docente del día</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <button @click="fetchStaffStatus" :disabled="loading" class="bg-white border border-gray-200 text-gray-700 font-bold w-12 h-12 rounded-xl text-lg shadow-sm hover:bg-gray-50 flex items-center justify-center transition-all disabled:opacity-50">
              <ion-icon :icon="refreshOutline" :class="{'animate-spin': loading}"></ion-icon>
            </button>
          </div>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
          <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-brand-blue">
              <ion-icon :icon="peopleOutline" class="text-2xl"></ion-icon>
            </div>
            <div>
              <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Total Personal</p>
              <h3 class="text-2xl font-black text-gray-900">{{ staffData.total }}</h3>
            </div>
          </div>
          <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
              <ion-icon :icon="checkmarkCircleOutline" class="text-2xl"></ion-icon>
            </div>
            <div>
              <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Presentes</p>
              <h3 class="text-2xl font-black text-green-600">{{ staffData.present }}</h3>
            </div>
          </div>
          <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-500">
              <ion-icon :icon="closeCircleOutline" class="text-2xl"></ion-icon>
            </div>
            <div>
              <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Ausentes</p>
              <h3 class="text-2xl font-black text-red-500">{{ staffData.absent }}</h3>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex-grow flex justify-center items-center py-20">
          <div class="w-10 h-10 border-4 border-brand-blue border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Staff Grid -->
        <div v-else-if="staffData.staff.length > 0" class="bg-white rounded-[32px] shadow-sm border border-gray-100 p-6 lg:p-8">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4 text-[11px] font-black uppercase tracking-widest">
              <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-green-500"></div> Presente</div>
              <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-gray-300"></div> Ausente</div>
            </div>

            <!-- Filter tabs -->
            <div class="flex gap-1 bg-gray-100 rounded-lg p-1">
              <button 
                @click="filter = 'all'" 
                :class="filter === 'all' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" 
                class="px-3 py-1.5 rounded-md text-[11px] font-bold transition-all"
              >Todos</button>
              <button 
                @click="filter = 'present'" 
                :class="filter === 'present' ? 'bg-white shadow-sm text-green-600' : 'text-gray-500'" 
                class="px-3 py-1.5 rounded-md text-[11px] font-bold transition-all"
              >Presentes</button>
              <button 
                @click="filter = 'absent'" 
                :class="filter === 'absent' ? 'bg-white shadow-sm text-red-500' : 'text-gray-500'" 
                class="px-3 py-1.5 rounded-md text-[11px] font-bold transition-all"
              >Ausentes</button>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div 
              v-for="person in filteredStaff" 
              :key="person.id" 
              class="p-4 rounded-2xl border border-gray-100 bg-gray-50/30 flex items-center gap-4 group hover:bg-white hover:shadow-md transition-all"
            >
              <div class="relative">
                <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white shadow-sm flex items-center justify-center bg-orange-100">
                  <img v-if="person.avatar_url" :src="person.avatar_url" class="w-full h-full object-cover">
                  <ion-icon v-else :icon="personOutline" class="text-2xl text-gray-500"></ion-icon>
                </div>
                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white" :class="person.status === 'present' ? 'bg-green-500' : 'bg-gray-300'"></div>
              </div>
              <div class="flex-grow min-w-0">
                <h4 class="text-sm font-black text-gray-900 leading-none mb-1 truncate">{{ person.name }}</h4>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cuerpo Docente</p>
                <p class="text-[11px] font-medium text-gray-500 truncate">{{ person.email }}</p>
              </div>
              <div class="text-right flex-shrink-0">
                <span 
                  :class="person.status === 'present' ? 'text-green-600 bg-green-50 border-green-100' : 'text-gray-400 bg-gray-50 border-gray-100'"
                  class="text-[11px] font-black px-2.5 py-1.5 rounded-lg border inline-block"
                >{{ person.time }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-3xl p-10 text-center border border-gray-100 mt-4">
          <ion-icon :icon="peopleOutline" class="text-6xl text-gray-300 mb-4"></ion-icon>
          <h3 class="text-lg font-black text-gray-900 mb-2">Sin personal registrado</h3>
          <p class="text-sm text-gray-500">No se encontraron maestros asignados a esta escuela.</p>
        </div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  arrowBackOutline, refreshOutline, peopleOutline, 
  checkmarkCircleOutline, closeCircleOutline, personOutline
} from 'ionicons/icons';
import api from '@/services/api';

const loading = ref(true);
const filter = ref('all');

const staffData = ref({
  staff: [] as any[],
  present: 0,
  absent: 0,
  total: 0
});

const filteredStaff = computed(() => {
  if (filter.value === 'all') return staffData.value.staff;
  return staffData.value.staff.filter((p: any) => p.status === filter.value);
});

const fetchStaffStatus = async () => {
  loading.value = true;
  try {
    const now = new Date();
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const res = await api.get('/admin/director/staff-status', {
      params: { local_start: todayStart.toISOString() }
    });
    if (res.data.success) {
      staffData.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching staff status:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStaffStatus();
});
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}
</style>
