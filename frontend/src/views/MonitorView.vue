<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-100 font-sans">
      <div class="flex flex-col h-screen min-h-screen">
        
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shrink-0 shadow-sm z-10">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-brand-blue shadow-sm">
              <ion-icon :icon="school" class="text-xl"></ion-icon>
            </div>
            <div>
              <h1 class="text-lg font-bold text-gray-900 leading-tight">Monitor de Acceso</h1>
              <span class="text-xs text-gray-500 font-medium tracking-wide">Control de Captura Escolar</span>
            </div>
          </div>
          <div class="flex items-center gap-3 text-right">
            <div>
              <h2 class="text-sm font-bold text-gray-900 leading-tight">Guardia Principal</h2>
              <span class="text-xs text-gray-500 font-medium">Estación Norte</span>
            </div>
            <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden border-2 border-white shadow-sm">
              <!-- Avatar placeholder -->
              <img src="https://i.pravatar.cc/150?img=11" alt="Guardia" class="w-full h-full object-cover" />
            </div>
          </div>
        </header>

        <!-- Main Content Layout (Responsive Flex) -->
        <div class="flex flex-col lg:flex-row flex-grow overflow-y-auto lg:overflow-hidden bg-gray-50 p-4 lg:p-6 gap-6">
          
          <!-- Left Column: Current Scan Card -->
          <div class="w-full lg:w-[65%] xl:w-[70%] bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col p-6 lg:p-8 relative overflow-hidden transition-all shrink-0 lg:shrink min-h-[380px] lg:min-h-0 order-1">
            
            <!-- Success Banner -->
            <div class="bg-green-100 rounded-2xl p-3 lg:p-4 flex items-center justify-center gap-2 lg:gap-3 mb-4 lg:mb-10 w-full animate-fade-in-down border border-green-200/50 shrink-0">
              <div class="bg-green-600 rounded-full w-5 h-5 lg:w-6 lg:h-6 flex items-center justify-center text-white">
                <ion-icon :icon="checkmark" class="text-xs lg:text-sm"></ion-icon>
              </div>
              <span class="text-green-700 font-black text-lg lg:text-xl tracking-wider uppercase">Entrada Autorizada</span>
            </div>

            <!-- Student Profile Section -->
            <div class="flex flex-col items-center justify-center flex-grow">
              <!-- Profile Picture Container -->
              <div class="relative mb-4 lg:mb-8">
                <div class="w-28 h-28 sm:w-36 sm:h-36 lg:w-56 lg:h-56 rounded-full overflow-hidden border-[6px] lg:border-8 border-white shadow-xl bg-orange-100">
                  <!-- Anime Boy Avatar Placeholder -->
                  <img src="https://i.pravatar.cc/300?img=12" alt="Student Photo" class="w-full h-full object-cover" />
                </div>
                <!-- Badge (Blue ID icon icon) -->
                <div class="absolute bottom-1 right-1 lg:bottom-2 lg:right-2 bg-brand-blue w-10 h-10 lg:w-14 lg:h-14 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                   <ion-icon :icon="idCard" class="text-white text-lg lg:text-2xl"></ion-icon>
                </div>
              </div>

              <!-- Student Details -->
              <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 mb-1 lg:mb-3 tracking-tight text-center">Sofia Martinez</h2>
              <p class="text-sm sm:text-base lg:text-xl text-gray-500 font-semibold tracking-wide text-center">
                ID: 2024-8921 • 10º Grado - Grupo B
              </p>
            </div>

            <div class="w-full border-t border-gray-100 my-4 lg:my-8 shrink-0"></div>

            <!-- Current Scan Time -->
            <div class="flex flex-col items-center justify-center lg:mb-4 shrink-0">
              <div class="flex items-baseline gap-1 lg:gap-2 mb-1 lg:mb-2 text-brand-blue font-black tracking-tighter">
                <span class="text-6xl sm:text-7xl lg:text-8xl leading-none">08:30</span>
                <span class="text-2xl sm:text-3xl lg:text-4xl">AM</span>
              </div>
              <span class="text-[10px] sm:text-xs lg:text-sm font-bold text-gray-500 uppercase tracking-[0.2em] text-center">Lunes, 24 de Octubre de 2024</span>
            </div>

          </div>

          <!-- Right Column: History & Summary -->
          <div class="w-full lg:w-[35%] xl:w-[30%] flex flex-col gap-6">
            
            <!-- History Section -->
            <div class="flex-grow flex flex-col">
              <div class="flex items-center justify-between mb-4 px-2">
                <div class="flex items-center gap-2">
                  <ion-icon :icon="timeOutline" class="text-xl text-brand-blue"></ion-icon>
                  <h3 class="text-lg font-bold text-gray-900">Historial Reciente</h3>
                </div>
                <span class="bg-gray-200 text-gray-600 text-xs px-3 py-1 rounded-full font-semibold">Hoy</span>
              </div>

              <!-- Scrollable List -->
              <div class="flex-grow overflow-y-auto space-y-3 pr-2 custom-scrollbar">
                
                <!-- Item 1: Salida (Orange) -->
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-orange-500 flex items-center gap-4 transition-transform hover:-translate-y-0.5 relative overflow-hidden">
                  <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-white to-transparent pointer-events-none"></div>
                  <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0">
                     <img src="https://i.pravatar.cc/150?img=33" alt="Juan" class="w-full h-full object-cover">
                  </div>
                  <div class="flex-grow min-w-0">
                    <h4 class="text-sm font-bold text-gray-900 truncate">Juan Perez</h4>
                    <p class="text-xs text-gray-500 truncate">11º Grado • ID: 2023-1102</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="text-sm font-black text-gray-900">08:28</p>
                    <p class="text-[9px] font-black tracking-wider text-orange-500 uppercase">Salida</p>
                  </div>
                </div>

                <!-- Item 2: Entrada (Green) -->
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500 flex items-center gap-4 transition-transform hover:-translate-y-0.5 relative">
                  <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-white to-transparent pointer-events-none"></div>
                  <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0">
                     <img src="https://i.pravatar.cc/150?img=47" alt="Maria" class="w-full h-full object-cover">
                  </div>
                  <div class="flex-grow min-w-0">
                    <h4 class="text-sm font-bold text-gray-900 truncate">Maria Lopez</h4>
                    <p class="text-xs text-gray-500 truncate">9º Grado • ID: 2025-0043</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="text-sm font-black text-gray-900">08:25</p>
                    <p class="text-[9px] font-black tracking-wider text-green-600 uppercase">Entrada</p>
                  </div>
                </div>

                <!-- Item 3: Entrada (Green) -->
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500 flex items-center gap-4 transition-transform hover:-translate-y-0.5 relative opacity-70">
                  <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0 grayscale">
                    <img src="https://i.pravatar.cc/150?img=68" alt="Carlos" class="w-full h-full object-cover">
                  </div>
                  <div class="flex-grow min-w-0">
                    <h4 class="text-sm font-bold text-gray-900 truncate">Carlos Ruiz</h4>
                    <p class="text-xs text-gray-500 truncate">12º Grado • ID: 2022-9981</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="text-sm font-black text-gray-900">08:20</p>
                    <p class="text-[9px] font-black tracking-wider text-green-600 uppercase">Entrada</p>
                  </div>
                </div>

              </div>
            </div>

            <!-- Daily Summary Card -->
            <div class="bg-blue-50/70 border border-blue-100 p-5 rounded-2xl shrink-0 shadow-sm mt-auto">
              <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Resumen Diario</h3>
              <div class="flex items-end justify-between">
                <div>
                   <div class="flex items-baseline gap-1 break-words">
                      <span class="text-3xl font-black text-brand-blue leading-none">482</span>
                      <span class="text-sm font-semibold text-gray-500">Escaneos</span>
                   </div>
                </div>
                <div class="flex gap-4">
                  <div class="text-center">
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-0.5">Entradas</p>
                    <p class="text-lg font-black text-green-600 leading-none">450</p>
                  </div>
                  <div class="text-center">
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-0.5">Salidas</p>
                    <p class="text-lg font-black text-orange-500 leading-none">32</p>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- Footer / Status Bar -->
        <footer class="bg-white border-t border-gray-200 px-6 py-3 flex justify-between items-center shrink-0">
          <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
              <div class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></div>
              <span class="text-xs font-bold text-gray-700">Sistema Online</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
              <span class="text-xs font-bold text-gray-600">Escáner Activo</span>
            </div>
          </div>
          <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-3">
            <span>Licencia v.2.4.0</span>
            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
            <span>Soporte Técnico: 800-SAFE-EDU</span>
          </div>
        </footer>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { school, checkmark, idCard, timeOutline } from 'ionicons/icons';
</script>

<style scoped>
/* Custom scrollbar for history list to keep it clean */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}
</style>
