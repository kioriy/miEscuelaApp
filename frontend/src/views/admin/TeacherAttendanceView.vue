<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50">
      <div class="min-h-full flex flex-col bg-gray-50 pb-28">

    <!-- Header Area -->
    <div class="flex-none px-4 md:px-8 pt-8 pb-4">
      <div class="max-w-4xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
          <h1 class="text-3xl font-black text-gray-900 tracking-tight">
            Pase de Lista: <span class="text-[#EF4444]">Ausentes</span>
          </h1>
          <p class="text-gray-500 font-medium mt-1 text-sm md:text-base">
            Mostrando estudiantes sin registro de entrada.
          </p>
        </div>
        <div class="w-full md:w-80">
          <div class="relative">
            <ion-icon :icon="searchOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></ion-icon>
            <input 
              type="text" 
              placeholder="Buscar por nombre o ID..." 
              class="w-full bg-white border border-gray-200 text-gray-900 font-medium rounded-xl pl-11 pr-4 py-3 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition-all"
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Student List Scrollable Area -->
    <div class="flex-grow overflow-y-auto px-4 md:px-8 pb-32">
       <div class="max-w-4xl mx-auto space-y-4">
          <!-- Student Card 1 -->
          <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all hover:border-gray-200 hover:shadow-md">
            <!-- Student Info -->
            <div class="flex items-center gap-4">
              <!-- Avatar -->
              <div class="w-14 h-14 md:w-16 md:h-16 rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 border-2 border-white shadow-sm overflow-hidden flex items-center justify-center flex-shrink-0">
                 <!-- Using a generic memoji/avatar URL as placeholder -->
                 <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=Valentina" alt="Valentina" class="w-full h-full object-cover">
              </div>
              <div>
                <h3 class="text-lg font-black text-gray-900">Valentina Cruz</h3>
                <p class="text-sm text-gray-500 font-medium">Matrícula: #40298</p>
                <div class="mt-1.5 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase bg-red-50 text-red-500 border border-red-100">
                  Sin Registro
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
              <button class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-[#22C55E] hover:bg-green-600 text-white font-black px-4 md:px-6 py-2.5 md:py-3 rounded-xl shadow-sm transition-all text-sm md:text-base">
                <ion-icon :icon="checkmarkOutline"></ion-icon>
                Marcar Presente
              </button>
              <button class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-[#3B82F6] hover:bg-blue-600 text-white font-black px-4 md:px-6 py-2.5 md:py-3 rounded-xl shadow-sm transition-all text-sm md:text-base">
                <ion-icon :icon="clipboardOutline"></ion-icon>
                Justificar
              </button>
            </div>
          </div>

          <!-- Student Card 2 -->
          <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all hover:border-gray-200 hover:shadow-md">
            <!-- Student Info -->
            <div class="flex items-center gap-4">
              <!-- Avatar -->
              <div class="w-14 h-14 md:w-16 md:h-16 rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 border-2 border-white shadow-sm overflow-hidden flex items-center justify-center flex-shrink-0">
                 <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=Diego" alt="Diego" class="w-full h-full object-cover">
              </div>
              <div>
                <h3 class="text-lg font-black text-gray-900">Diego Santos</h3>
                <p class="text-sm text-gray-500 font-medium">Matrícula: #40299</p>
                <div class="mt-1.5 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase bg-red-50 text-red-500 border border-red-100">
                  Sin Registro
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
              <button class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-[#22C55E] hover:bg-green-600 text-white font-black px-4 md:px-6 py-2.5 md:py-3 rounded-xl shadow-sm transition-all text-sm md:text-base">
                <ion-icon :icon="checkmarkOutline"></ion-icon>
                Marcar Presente
              </button>
              <button class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-[#3B82F6] hover:bg-blue-600 text-white font-black px-4 md:px-6 py-2.5 md:py-3 rounded-xl shadow-sm transition-all text-sm md:text-base">
                <ion-icon :icon="clipboardOutline"></ion-icon>
                Justificar
              </button>
            </div>
          </div>
          
          <!-- Divider & Finalize Section Context -->
          <div class="pt-8 pb-4 text-center max-w-sm mx-auto">
            <div class="h-px bg-gray-200 w-full mb-8"></div>
            <p class="text-xs md:text-sm text-gray-500 font-medium mb-6">
              Al finalizar, se enviará automáticamente un reporte a la dirección y las notificaciones correspondientes a los padres de familia.
            </p>
            <button class="w-full bg-brand-blue hover:bg-blue-600 text-white font-black px-6 py-3.5 rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2 text-base md:text-lg">
              <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
              Finalizar Pase de Lista
            </button>
            <router-link to="/admin/teacher/dashboard" class="inline-block mt-4 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
              Cancelar y volver al panel
            </router-link>
          </div>

       </div>
    </div>
      </div>
    </ion-content>

    <!-- Bottom Stats Bar (Absolute to IonPage) -->
    <div class="absolute bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-[0_-10px_30px_rgba(0,0,0,0.03)] z-30">
        <div class="flex h-20 px-2 lg:px-0">
          <!-- Total -->
          <div class="flex-1 flex flex-col items-center justify-center border-r border-gray-100">
             <span class="text-[10px] md:text-xs font-black text-gray-400 tracking-wider uppercase mb-1">Total Grupo</span>
             <span class="text-xl md:text-2xl font-black text-gray-900">25</span>
          </div>
          <!-- Present -->
          <div class="flex-1 flex flex-col items-center justify-center">
             <span class="text-[10px] md:text-xs font-black text-gray-400 tracking-wider uppercase mb-1">Presentes</span>
             <span class="text-xl md:text-2xl font-black text-[#22C55E]">23</span>
          </div>
          <!-- Absent (Highlighted) -->
          <div class="flex-1 flex flex-col items-center justify-center bg-red-50 relative">
             <!-- Top red border accent -->
             <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
             <span class="text-[10px] md:text-xs font-black text-red-500 tracking-wider uppercase mb-1">Ausentes</span>
             <div class="flex items-center gap-1.5 text-xl md:text-2xl font-black text-red-500">
                <ion-icon :icon="alertCircleOutline" class="text-lg"></ion-icon>
                <span>2</span>
             </div>
          </div>
        </div>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  searchOutline, 
  checkmarkOutline, 
  clipboardOutline, 
  checkmarkCircleOutline,
  alertCircleOutline
} from 'ionicons/icons';

const route = useRoute();
const classroomId = route.params.classroomId;

</script>
