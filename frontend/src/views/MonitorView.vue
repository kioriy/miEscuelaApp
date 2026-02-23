<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-100 font-sans">
      <div class="flex flex-col h-screen min-h-screen">
        
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shrink-0 shadow-sm z-10">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white shadow-sm">
              <ion-icon :icon="school" class="text-xl"></ion-icon>
            </div>
            <div>
              <h1 class="text-lg font-black text-gray-900 leading-tight">miEscuelaApp</h1>
              <span class="text-xs text-gray-500 font-medium tracking-wide">Sistema de Control Escolar</span>
            </div>
          </div>
          
          <div class="flex items-center gap-4 text-right">
            <div>
              <h2 class="text-sm font-bold text-gray-900 leading-tight">{{ schoolInfo.name }}</h2>
              <span class="text-[10px] text-brand-blue font-black tracking-widest uppercase">{{ schoolInfo.kioskName }}</span>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-brand-blue shadow-sm overflow-hidden border border-blue-100">
              <img v-if="schoolInfo.logo" :src="schoolInfo.logo" alt="School Logo" class="w-full h-full object-contain p-1" />
              <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400 font-bold text-xs uppercase">Logo</div>
            </div>
          </div>
        </header>

        <!-- Main Content Layout (Responsive Flex) -->
        <div class="flex flex-col lg:flex-row flex-grow overflow-y-auto lg:overflow-hidden bg-gray-50 p-4 lg:p-6 gap-6">
          
          <!-- Left Column: Current Scan Card -->
          <div class="w-full lg:w-[65%] xl:w-[70%] bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col p-6 lg:p-8 relative overflow-hidden transition-all shrink-0 lg:shrink min-h-[380px] lg:min-h-0 order-1">
            
            <!-- Scanner Input (Subtle) -->
            <div class="absolute top-4 right-4 z-20">
              <input 
                id="monitor-scan-input"
                ref="scanInput"
                v-model="scanValue" 
                @keyup.enter="handleScan" 
                @blur="handleBlur"
                placeholder="Matrícula..." 
                class="bg-blue-50 border border-blue-100 rounded-lg px-3 py-1.5 text-xs font-bold text-brand-blue outline-none focus:ring-2 focus:ring-blue-200 transition-all opacity-40 hover:opacity-100 focus:opacity-100"
                autofocus
              />
            </div>

            <!-- Global Click Overlay (Invisible) -->
            <div 
              @click="ensureFocus" 
              class="absolute inset-0 z-10 cursor-default"
              title="Click para reenfocar"
            ></div>

            <!-- Success Banner -->
            <div v-if="currentStudent" class="p-3 lg:p-4 flex items-center justify-center gap-2 lg:gap-3 mb-4 lg:mb-10 w-full animate-fade-in-down border border-opacity-50 shrink-0 rounded-2xl"
              :class="currentScanType === 'in' ? 'bg-green-100 border-green-200 text-green-700' : 'bg-orange-100 border-orange-200 text-orange-700'"
            >
              <div class="rounded-full w-5 h-5 lg:w-6 lg:h-6 flex items-center justify-center text-white"
                :class="currentScanType === 'in' ? 'bg-green-600' : 'bg-orange-600'"
              >
                <ion-icon :icon="currentScanType === 'in' ? checkmark : idCard" class="text-xs lg:text-sm"></ion-icon>
              </div>
              <span class="font-black text-lg lg:text-xl tracking-wider uppercase">
                {{ currentScanType === 'in' ? 'Entrada Autorizada' : 'Salida Autorizada' }}
              </span>
            </div>
            
            <div v-else class="bg-blue-50/50 rounded-2xl p-3 lg:p-4 flex items-center justify-center gap-2 lg:gap-3 mb-4 lg:mb-10 w-full border border-blue-100/50 shrink-0">
               <span class="text-brand-blue font-black text-lg lg:text-xl tracking-widest uppercase">Esperando Escaneo...</span>
            </div>

            <!-- Student Profile Section -->
            <div class="flex flex-col items-center justify-center flex-grow">
              <!-- Profile Picture Container -->
              <div class="relative mb-4 lg:mb-8">
                <div class="w-28 h-28 sm:w-36 sm:h-36 lg:w-56 lg:h-56 rounded-full overflow-hidden border-[6px] lg:border-8 border-white shadow-xl bg-orange-100 flex items-center justify-center text-4xl text-brand-blue font-black">
                  <!-- Anime Boy Avatar Placeholder -->
                  <img v-if="currentStudent?.photo_url" :src="currentStudent.photo_url" alt="Student Photo" class="w-full h-full object-cover" />
                  <span v-else-if="currentStudent">{{ currentStudent.first_name[0] }}{{ currentStudent.last_name[0] }}</span>
                  <ion-icon v-else :icon="school" class="text-6xl text-blue-200"></ion-icon>
                </div>
                <!-- Badge (Blue ID icon icon) -->
                <div class="absolute bottom-1 right-1 lg:bottom-2 lg:right-2 bg-brand-blue w-10 h-10 lg:w-14 lg:h-14 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                   <ion-icon :icon="idCard" class="text-white text-lg lg:text-2xl"></ion-icon>
                </div>
              </div>

              <!-- Student Details -->
              <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 mb-1 lg:mb-3 tracking-tight text-center">
                {{ currentStudent ? currentStudent.first_name + ' ' + currentStudent.last_name : '---' }}
              </h2>
              <p class="text-sm sm:text-base lg:text-xl text-gray-500 font-semibold tracking-wide text-center">
                ID: {{ currentStudent?.enrollment_code || '0000-0000' }} • {{ currentStudent?.grade || '0' }}º Grado - Grupo {{ currentStudent?.group_letter || '-' }}
              </p>
            </div>

            <div class="w-full border-t border-gray-100 my-4 lg:my-8 shrink-0"></div>

            <!-- Current Scan Time -->
            <div class="flex flex-col items-center justify-center lg:mb-4 shrink-0">
              <div class="flex items-baseline gap-1 lg:gap-2 mb-1 lg:mb-2 text-brand-blue font-black tracking-tighter">
                <span class="text-6xl sm:text-7xl lg:text-8xl leading-none uppercase">{{ currentTime.split(' ')[0] }}</span>
                <span class="text-2xl sm:text-3xl lg:text-4xl uppercase">{{ currentTime.split(' ')[1] }}</span>
              </div>
              <span class="text-[10px] sm:text-xs lg:text-sm font-bold text-gray-500 uppercase tracking-[0.2em] text-center capitalize">{{ currentDate }}</span>
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
                
                <div v-if="historyLoading" class="p-8 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">Cargando Historial...</div>
                <div v-else-if="recentHistory.length === 0" class="p-8 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">Sin actividad reciente</div>

                <div v-for="(item, idx) in recentHistory" :key="idx" 
                  class="bg-white p-4 rounded-xl shadow-sm border-l-4 flex items-center gap-4 transition-transform hover:-translate-y-0.5 relative overflow-hidden"
                  :class="item.type === 'Entrada' ? 'border-green-500' : 'border-orange-500'"
                >
                  <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-white to-transparent pointer-events-none"></div>
                  <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center text-brand-blue font-black border border-gray-100">
                     <img v-if="item.photo_url" :src="item.photo_url" :alt="item.first_name" class="w-full h-full object-cover">
                     <span v-else>{{ item.first_name[0] }}{{ item.last_name[0] }}</span>
                  </div>
                  <div class="flex-grow min-w-0">
                    <h4 class="text-sm font-bold text-gray-900 truncate">{{ item.first_name }} {{ item.last_name }}</h4>
                    <p class="text-xs text-gray-500 truncate">{{ item.grade }}º {{ item.group_letter }} • ID: {{ item.enrollment_code }}</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="text-sm font-black text-gray-900">{{ item.time }}</p>
                    <p class="text-[9px] font-black tracking-wider uppercase" :class="item.type === 'Entrada' ? 'text-green-600' : 'text-orange-500'">{{ item.type }}</p>
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
                      <span class="text-3xl font-black text-brand-blue leading-none">{{ dailyStats.total }}</span>
                      <span class="text-sm font-semibold text-gray-500">Escaneos</span>
                   </div>
                </div>
                <div class="flex gap-4">
                  <div class="text-center">
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-0.5">Entradas</p>
                    <p class="text-lg font-black text-green-600 leading-none">{{ dailyStats.entries }}</p>
                  </div>
                  <div class="text-center">
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-0.5">Salidas</p>
                    <p class="text-lg font-black text-orange-500 leading-none">{{ dailyStats.exits }}</p>
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
              <div class="w-2.5 h-2.5 rounded-full" :class="syncError ? 'bg-red-500' : (isSyncing ? 'bg-amber-500 animate-spin' : 'bg-green-500')"></div>
              <span class="text-xs font-bold text-gray-700">
                {{ syncError ? 'Error de Sincronización' : (isSyncing ? 'Sincronizando...' : 'Sistema Online') }}
              </span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
              <span class="text-xs font-bold text-gray-600">Escáner Activo</span>
            </div>
          </div>
          <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-3">
            <span v-if="syncError" class="text-red-400 flex items-center gap-1"><ion-icon :icon="alertCircleOutline"></ion-icon> Reintentando en 60s</span>
            <span>Versión 2.5.0</span>
            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
            <span>Soporte Técnico: 800-SAFE-EDU</span>
          </div>
        </footer>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { school, checkmark, idCard, timeOutline, alertCircleOutline } from 'ionicons/icons';
import { storage } from '@/services/storage';
import { db } from '@/services/db';
import { SyncService } from '@/services/SyncService';
import api from '@/services/api';

const router = useRouter();
const scanInput = ref<any>(null);
const userProfile = ref({
  name: 'Cargando...',
  role: 'user',
  photo: ''
});

// Scan & Sync State
const scanValue = ref('');
const schoolInfo = ref({
  name: 'Colegio',
  logo: '',
  kioskName: 'Cargando...'
});
const dailyStats = ref({
  entries: 0,
  exits: 0,
  total: 0
});
const currentStudent = ref<any>(null);
const recentHistory = ref<any[]>([]);
const isSyncing = ref(false);
const syncError = ref(false);
const historyLoading = ref(true);
const currentScanType = ref<'in' | 'out'>('in');
const currentTime = ref(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
const currentDate = ref(new Date().toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));

let watchdogInterval: any;

const handleBlur = () => {
  // Reenfocar inmediatamente usando el ID directo por mayor velocidad
  setTimeout(() => {
    const el = document.getElementById('monitor-scan-input');
    if (el) (el as HTMLInputElement).focus();
  }, 10);
};

const ensureFocus = (e?: Event) => {
  // Si ya estamos en el input o en otro campo (por error), no hacer nada
  if (document.activeElement?.tagName === 'INPUT') return;
  
  const el = document.getElementById('monitor-scan-input');
  if (el) (el as HTMLInputElement).focus();
};

const fetchStats = async () => {
  try {
    const res = await api.get('/sync/monitor/stats');
    if (res.data.success) {
      dailyStats.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching monitor stats:', error);
  }
};

const fetchSchoolInfo = async () => {
  try {
    const res = await api.get('/sync/monitor/school');
    if (res.data.success) {
      schoolInfo.value.name = res.data.data.name;
      schoolInfo.value.logo = res.data.data.logo_url;
      schoolInfo.value.kioskName = res.data.data.kiosk_name;
    }
  } catch (error) {
    console.error('Error fetching school info:', error);
  }
};

let timeInterval: any;
let syncInterval: any;

const updateClock = () => {
  currentTime.value = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  currentDate.value = new Date().toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const handleScan = async () => {
  if (!scanValue.value) return;
  
  const matricula = scanValue.value.trim().toUpperCase();
  scanValue.value = '';

  // 1. Buscar alumno en la base de datos local (Dexie)
  const student = await db.students.get({ enrollment_code: matricula });

  if (student) {
    const today = new Date().toISOString().split('T')[0];
    
    // Detectar cambio de día (si el monitor lleva abierto mucho tiempo)
    if (recentHistory.value.length > 0) {
      // Usamos el primer elemento del historial para comparar fechas
      const firstItem = recentHistory.value[0];
      // Nota: Aquí asumimos que recentHistory se cargó solo con datos de hoy
      // Pero para ser extra seguros, si la fecha actual es distinta, limpiamos
      // El toggle de abajo se encargará de poner el primer registro como 'in'
    }

    currentStudent.value = student;
    
    // 2. Determinar si es Entrada o Salida (Lógica Toggle Inteligente)
    // Buscamos el último log local de este alumno
    const lastLog = await db.attendanceLogs
      .where('student_id')
      .equals(student.id)
      .reverse()
      .sortBy('scanned_at')
      .then(logs => logs[0]);

    let type: 'in' | 'out' = 'in';

    if (lastLog) {
      const lastDate = lastLog.scanned_at.split('T')[0];
      const lastTime = new Date(lastLog.scanned_at).getTime();
      const nowTime = new Date().getTime();
      
      // Si el escaneo es hace menos de 10 segundos para el mismo alumno, ignorar
      if (nowTime - lastTime < 10000) {
        console.warn('Escaneo duplicado detectado para:', student.enrollment_code);
        alert('Escaneo duplicado detectado. Por favor espera 10 segundos.');
        return;
      }

      if (lastDate === today) {
        // Si ya hay registros hoy, alternamos
        type = lastLog.type === 'in' ? 'out' : 'in';
      } else {
        // Cambio detectado en el primer escaneo del alumno hoy
        // Limpiamos historial si detectamos que el último log general no es de hoy
        recentHistory.value = [];
        fetchStats();
      }
    }

    currentScanType.value = type;

    // 3. Registrar asistencia localmente
    const newLog = {
      student_id: student.id,
      scanned_at: new Date().toISOString(),
      type: type,
      sync_status: 'pending' as const
    };
    
    await db.attendanceLogs.add(newLog);
    
    // 4. Actualizar historial visual
    recentHistory.value.unshift({
      ...student,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      type: type === 'in' ? 'Entrada' : 'Salida'
    });
    
    if (recentHistory.value.length > 5) recentHistory.value.pop();

    // 5. Intentar sincronización rápida
    SyncService.pushAttendance();
    
    // Reset visual despues de 5s
    setTimeout(() => {
       if (currentStudent.value?.id === student.id) currentStudent.value = null;
    }, 5000);
  } else {
    alert('Matrícula no encontrada localmente.');
  }
};

const runSync = async () => {
  isSyncing.value = true;
  syncError.value = false;
  try {
    await SyncService.syncAll();
    await fetchStats();
  } catch (e) {
    syncError.value = true;
  } finally {
    isSyncing.value = false;
  }
};

onMounted(async () => {
  const kioskConfig = await storage.get('kiosk_config');
  if (!kioskConfig) {
    router.push('/monitor/activate');
    return;
  }

  // Cargar datos de la escuela y kiosco desde el servidor
  fetchSchoolInfo();

  const user = await storage.get('auth_user');
  if (user) {
    userProfile.value.name = user.name || 'Usuario';
    userProfile.value.role = user.role || 'user';
    
    if (user.avatar_url) {
      userProfile.value.photo = user.avatar_url;
    } else if (user.profile_photo_path) {
      userProfile.value.photo = user.profile_photo_path.startsWith('http') 
        ? user.profile_photo_path 
        : `http://localhost:8000/storage/${user.profile_photo_path}`;
    }
  }

  // Inicializar reloj
  timeInterval = setInterval(updateClock, 1000);
  
  // Sincronización automática cada 1 minuto
  runSync();
  syncInterval = setInterval(runSync, 60000);

  // Cargar historial reciente de la BD local (SOLO DE HOY)
  const today = new Date().toISOString().split('T')[0];
  const logs = await db.attendanceLogs
    .where('scanned_at')
    .above(today)
    .reverse()
    .limit(10)
    .toArray();

  for (const log of logs) {
    const s = await db.students.get(log.student_id);
    if (s) {
      recentHistory.value.push({
        ...s,
        time: new Date(log.scanned_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        type: log.type === 'in' ? 'Entrada' : 'Salida'
      });
    }
  }
  historyLoading.value = false;

  // Escuchadores globales con fase de captura (true) para saltar stopPropagation
  document.addEventListener('click', ensureFocus, true);
  window.addEventListener('focus', ensureFocus);
  
  // Watchdog: Forzar foco cada segundo por si acaso
  watchdogInterval = setInterval(ensureFocus, 1000);
});

onUnmounted(() => {
  clearInterval(timeInterval);
  clearInterval(syncInterval);
  clearInterval(watchdogInterval);
  document.removeEventListener('click', ensureFocus, true);
  window.removeEventListener('focus', ensureFocus);
});
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
