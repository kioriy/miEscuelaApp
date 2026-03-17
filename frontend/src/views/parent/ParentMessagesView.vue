<template>
  <ion-page class="bg-[#F3F4F6]">
    <div class="min-h-screen flex flex-col bg-[#F3F4F6] font-sans">
      
      <!-- Top Navigation Header -->
      <header class="bg-white border-b border-gray-100 flex-none sticky top-0 z-50 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <button @click="$router.push('/parent/dashboard')" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors">
               <ion-icon :icon="arrowBackOutline" class="text-xl"></ion-icon>
            </button>
            <h1 class="text-xl font-black text-gray-900 tracking-tight">Todos los Avisos</h1>
          </div>
          <div class="flex items-center gap-2">
             <span class="bg-blue-50 text-brand-blue font-bold text-[10px] px-2.5 py-1 rounded-lg uppercase tracking-wider">
                {{ announcements.length }} mensajes
             </span>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <ion-content :fullscreen="true" class="ion-padding" style="--background: transparent; --ion-background-color: transparent;">
        
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-20">
           <div class="w-8 h-8 border-4 border-brand-blue border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div v-else class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">
          
          <!-- Empty State -->
          <div v-if="announcements.length === 0" class="bg-white rounded-3xl p-10 text-center border border-gray-100 mt-8">
             <ion-icon :icon="mailOutline" class="text-6xl text-gray-300 mb-4"></ion-icon>
             <h3 class="text-lg font-black text-gray-900 mb-2">Sin avisos</h3>
             <p class="text-sm text-gray-500">No se han emitido avisos o circulares por el momento.</p>
          </div>

          <!-- Messages List -->
          <div v-for="ann in announcements" :key="ann.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-start gap-4 hover:shadow-md transition-shadow">
            <div :class="`bg-${ann.color}-100 text-${ann.color}-500`" class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
               <ion-icon :icon="getIconMap(ann.icon)" class="text-xl"></ion-icon>
            </div>
            <div class="flex-grow min-w-0">
               <div class="flex items-start justify-between gap-2 mb-1">
                  <h4 class="text-base font-black text-gray-900 leading-tight">{{ ann.title }}</h4>
                  <span class="text-[10px] font-bold text-gray-400 whitespace-nowrap mt-0.5">{{ ann.time_ago }}</span>
               </div>
               <p class="text-sm text-gray-600 leading-relaxed font-medium mb-2">{{ ann.message }}</p>
               <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400">
                  <ion-icon :icon="calendarOutline" class="text-sm"></ion-icon>
                  <span>{{ ann.date }} • {{ ann.time }}</span>
               </div>
            </div>
          </div>

          <!-- Load More -->
          <div v-if="hasMorePages" class="text-center py-4">
             <button @click="loadMore" :disabled="loadingMore" class="bg-white border border-gray-200 text-gray-700 font-bold px-6 py-3 rounded-xl shadow-sm hover:bg-gray-50 transition-all disabled:opacity-50">
                <span v-if="loadingMore">Cargando...</span>
                <span v-else>Cargar más avisos</span>
             </button>
          </div>
        </div>

        <!-- Footer -->
        <div class="max-w-4xl mx-auto px-4 py-8 text-center text-xs font-semibold text-gray-500 mb-8">
           © 2026 miEscuelaApp. Todos los derechos reservados.
        </div>
      </ion-content>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  arrowBackOutline, 
  calendarOutline, 
  warningOutline,
  mailOutline
} from 'ionicons/icons';
import api from '@/services/api';

const loading = ref(true);
const loadingMore = ref(false);
const announcements = ref<any[]>([]);
const currentPage = ref(1);
const hasMorePages = ref(false);

const getIconMap = (iconName: string) => {
   if (iconName === 'warningOutline') return warningOutline;
   return calendarOutline;
};

const fetchAnnouncements = async (page = 1) => {
   try {
      if (page === 1) {
         loading.value = true;
      } else {
         loadingMore.value = true;
      }

      const res = await api.get('/parent/announcements', {
         params: { page }
      });

      if (res.data.success) {
         const data = res.data.data;
         if (page === 1) {
            announcements.value = data.data;
         } else {
            announcements.value.push(...data.data);
         }
         currentPage.value = data.current_page;
         hasMorePages.value = data.current_page < data.last_page;
      }
   } catch (error) {
      console.error('Error fetching announcements:', error);
   } finally {
      loading.value = false;
      loadingMore.value = false;
   }
};

const loadMore = () => {
   fetchAnnouncements(currentPage.value + 1);
};

onMounted(() => {
   fetchAnnouncements();
});
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}

.bg-blue-100 { background-color: #dbeafe; }
.text-blue-500 { color: #3b82f6; }
.bg-orange-100 { background-color: #ffedd5; }
.text-orange-500 { color: #f97316; }
</style>
