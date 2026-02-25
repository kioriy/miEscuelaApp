<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-100 font-sans">
      <div class="flex flex-col h-screen min-h-screen">
        
        <!-- Emergency Mode Overlay -->
        <div v-if="emergencyMode" class="fixed inset-0 bg-gray-900 border-8 border-red-600 z-[100] flex flex-col items-center justify-center text-center p-8">
          <ion-icon :icon="warning" class="text-8xl text-red-500 mb-6 animate-pulse"></ion-icon>
          <h1 class="text-4xl lg:text-5xl font-black text-white mb-4 uppercase tracking-widest text-shadow-lg">Reloj Desincronizado</h1>
          <p class="text-xl lg:text-2xl text-gray-300 font-bold max-w-3xl leading-relaxed mb-12">
            La fecha actual del Kiosco es incorrecta. <br/>
            Por favor, solicite al Administrador que escanee el <br/>
            <span class="text-white bg-red-600/30 border border-red-500/50 px-3 py-1 rounded-xl mx-2">Código QR de Sincronización</span>
            desde su panel.
          </p>
          
          <input 
            id="emergency-scan-input"
            v-model="scanValue"
            @keyup.enter="handleScan"
            @blur="handleBlur"
            class="opacity-0 absolute"
            autofocus
          />
          <div class="animate-bounce mt-4">
            <ion-icon :icon="barcodeOutline" class="text-6xl text-white opacity-50"></ion-icon>
          </div>
        </div>

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
            
            <!-- Error Banner (non-blocking) -->
            <div v-else-if="scanError" class="bg-red-100 border border-red-200 text-red-700 p-3 lg:p-4 flex items-center justify-center gap-2 lg:gap-3 mb-4 lg:mb-10 w-full animate-tada shrink-0 rounded-2xl">
              <div class="rounded-full w-5 h-5 lg:w-6 lg:h-6 flex items-center justify-center bg-red-600 text-white">
                <ion-icon :icon="alertCircleOutline" class="text-xs lg:text-sm"></ion-icon>
              </div>
              <span class="font-black text-lg lg:text-xl tracking-wider uppercase">
                {{ scanError }}
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

          <!-- Columna Derecha: Historial y Resumen -->
          <div class="w-full lg:w-[35%] xl:w-[30%] flex flex-col gap-6 lg:h-full lg:max-h-full lg:overflow-hidden lg:min-h-0">
            
            <!-- Sección Historial -->
            <div class="flex-grow flex flex-col min-h-0">
              <div class="flex items-center justify-between mb-4 px-2 shrink-0">
                <div class="flex items-center gap-2">
                  <ion-icon :icon="timeOutline" class="text-xl text-brand-blue"></ion-icon>
                  <h3 class="text-lg font-bold text-gray-900">Historial Reciente</h3>
                </div>
                <span class="bg-gray-100 text-gray-500 text-[10px] px-3 py-1 rounded-full font-black uppercase tracking-widest border border-gray-200">Hoy</span>
              </div>

              <!-- Lista con scroll -->
              <div class="flex-grow overflow-y-auto space-y-3 pr-2 custom-scrollbar min-h-0">
                
                <div v-if="historyLoading" class="h-full flex items-center justify-center">
                   <div class="w-8 h-8 border-4 border-gray-100 border-t-brand-blue rounded-full animate-spin"></div>
                </div>
                <div v-else-if="recentHistory.length === 0" class="h-full flex flex-col items-center justify-center opacity-30 gap-3 grayscale">
                   <ion-icon :icon="timeOutline" class="text-5xl"></ion-icon>
                   <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Sin actividad hoy</p>
                </div>

                <div v-for="(item, idx) in recentHistory" :key="idx" 
                  class="bg-white p-4 rounded-xl shadow-sm border-l-4 flex items-center gap-4 transition-transform hover:-translate-y-0.5 relative overflow-hidden group"
                  :class="item.type === 'Entrada' ? 'border-green-500' : 'border-orange-500'"
                >
                  <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-white to-transparent pointer-events-none group-hover:from-blue-50/20 transition-all"></div>
                  <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center text-brand-blue font-black border border-gray-100 relative shadow-inner">
                     <img v-if="item.photo_url" :src="item.photo_url" :alt="item.first_name" class="w-full h-full object-cover">
                     <ion-icon v-else :icon="personCircleOutline" class="text-2xl text-gray-300"></ion-icon>
                     
                     <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-lg flex items-center justify-center text-[10px] shadow-sm transform rotate-3"
                        :class="item.type === 'Entrada' ? 'bg-green-500 text-white' : 'bg-orange-500 text-white'"
                      >
                        <ion-icon :icon="item.type === 'Entrada' ? arrowDown : arrowUp"></ion-icon>
                      </div>
                  </div>
                  <div class="flex-grow min-w-0">
                    <h4 class="text-sm font-black text-gray-900 truncate tracking-tight uppercase">{{ item.first_name }} {{ item.last_name }}</h4>
                    <p class="text-[10px] font-bold text-gray-400 truncate tracking-widest">{{ item.grade }}º {{ item.group_letter }} • ID: {{ item.enrollment_code }}</p>
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
              <div class="flex items-center justify-between mb-4">
                 <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Resumen del Día</h3>
                 <ion-icon :icon="pieChartOutline" class="text-brand-blue/30 text-xl"></ion-icon>
              </div>
              
              <div class="flex items-end justify-between">
                <div>
                   <div class="flex items-baseline gap-1 break-words">
                      <span class="text-3xl font-black text-brand-blue leading-none">{{ dailyStats.total }}</span>
                      <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Escaneos</span>
                   </div>
                </div>
                <div class="flex gap-4">
                  <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-0.5">Entradas</p>
                    <p class="text-lg font-black text-green-600 leading-none">{{ dailyStats.entries }}</p>
                  </div>
                  <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-0.5">Salidas</p>
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
import { 
  school, checkmark, idCard, timeOutline, alertCircleOutline, 
  pieChartOutline, arrowDown, arrowUp, personCircleOutline, warning, barcodeOutline
} from 'ionicons/icons';
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
const scanError = ref<string | null>(null);
const currentScanType = ref<'in' | 'out'>('in');

// Inicializamos en placeholders, se recarga en onMounted
const currentTime = ref('--:--');
const currentDate = ref('Cargando...');
const lastLocalDate = ref('');

const emergencyMode = ref(false);
const timeOffsetMs = ref(0);

const getLocalTime = () => {
  return new Date(Date.now() + timeOffsetMs.value);
};

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

const recalculateLocalStats = async () => {
  const now = getLocalTime();
  const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const startOfTodayISO = todayStart.toISOString();
  
  const logs = await db.attendanceLogs
    .where('scanned_at')
    .aboveOrEqual(startOfTodayISO)
    .toArray();
    
  const entries = logs.filter(l => l.type === 'in').length;
  const exits = logs.filter(l => l.type === 'out').length;
  
  dailyStats.value = {
    entries: entries,
    exits: exits,
    total: entries + exits
  };
};

const fetchStats = async () => {
  try {
    const now = getLocalTime();
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const startOfTodayISO = todayStart.toISOString();

    const res = await api.get('/sync/monitor/stats', {
      params: { local_start: startOfTodayISO }
    });
    if (res.data.success) {
      dailyStats.value = res.data.data;
    }
  } catch (error) {
    console.warn('Error al obtener estadísticas del servidor, recalculando locales...');
    await recalculateLocalStats();
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
  const now = getLocalTime();
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  currentDate.value = now.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  
  // Detectar cambio de día (medianoche)
  if (now.toDateString() !== lastLocalDate.value) {
    console.log('🌙 Cambio de día detectado (Medianoche). Reiniciando historial local...');
    lastLocalDate.value = now.toDateString();
    recentHistory.value = [];
    dailyStats.value = { entries: 0, exits: 0, total: 0 };
    fetchStats(); // Forzar actualización de estadísticas desde el servidor
  }
};

const handleScan = async () => {
  let rawVal = scanValue.value.trim();
  scanValue.value = '';
  if (!rawVal) return;

  // Interceptar Token de Sincronización de Tiempo
  if (rawVal.length > 50) {
    try {
      const decoded = atob(rawVal);
      if (decoded.includes('"type":"SYNC_TIME"')) {
        const payload = JSON.parse(decoded);
        if (payload.type === 'SYNC_TIME' && payload.timestamp) {
          const offset = (payload.timestamp * 1000) - Date.now();
          timeOffsetMs.value = offset;
          await storage.set('kiosk_time_offset', offset);
          emergencyMode.value = false;
          
          scanError.value = '¡Hora Sincronizada!';
          updateClock(); // Update labels
          setTimeout(() => { scanError.value = null; }, 3000);
          
          // Opcional: reportar al servidor
          api.post('/sync/monitor/apply-time-offset', {
            token: rawVal,
            kiosk_local_timestamp: Math.floor(Date.now() / 1000)
          }).catch(() => {});
          
          return;
        }
      }
    } catch(e) { /* ignores invalid base64 */ }
  }

  if (emergencyMode.value) {
    return;
  }

  const matricula = rawVal.toUpperCase();

  // 1. Buscar alumno en la base de datos local (Dexie)
  const student = await db.students.get({ enrollment_code: matricula });

  // Limpiar errores previos
  scanError.value = null;

  if (student) {
    const now = getLocalTime();
    // Obtener inicio del día local en formato ISO para filtrado preciso
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const localTodayISO = todayStart.toISOString().split('T')[0];
    
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
      const nowTime = getLocalTime().getTime();
      
      // Si el escaneo es hace menos de 10 segundos para el mismo alumno, ignorar (feedback visual)
      if (nowTime - lastTime < 10000) {
        console.warn('Escaneo duplicado detectado para:', student.enrollment_code);
        scanError.value = 'Escaneo Duplicado';
        currentStudent.value = null;
        setTimeout(() => { scanError.value = null; }, 3000);
        return;
      }

      if (lastDate === localTodayISO) {
        // Si ya hay registros hoy, alternamos
        type = lastLog.type === 'in' ? 'out' : 'in';
      }
    }

    currentScanType.value = type;

    // 3. Registrar asistencia localmente
    const newLog = {
      student_id: student.id,
      scanned_at: getLocalTime().toISOString(),
      type: type,
      sync_status: 'pending' as const
    };
    
    await db.attendanceLogs.add(newLog);
    
    // 4. Actualizar historial visual
    recentHistory.value.unshift({
      ...student,
      time: getLocalTime().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      type: type === 'in' ? 'Entrada' : 'Salida'
    });
    
    if (recentHistory.value.length > 10) recentHistory.value.pop();

    // 5. Intentar sincronización rápida y actualizar estadísticas locales inmediatamente
    recalculateLocalStats();
    SyncService.pushAttendance();
    
    // Reset visual despues de 5s
    setTimeout(() => {
       if (currentStudent.value?.id === student.id) currentStudent.value = null;
    }, 5000);
  } else {
    // Matrícula no encontrada (No bloqueante)
    scanError.value = 'ID No Encontrado';
    currentStudent.value = null;
    setTimeout(() => { scanError.value = null; }, 3000);
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

  // Load saved offset if any
  const savedOffset = await storage.get('kiosk_time_offset');
  if (savedOffset) {
    timeOffsetMs.value = Number(savedOffset);
  }

  // Si la hora corregida (o la del sistema) es menor a 2025, el reloj está mal
  if (getLocalTime().getFullYear() < 2025) {
    emergencyMode.value = true;
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

  // Inicializar reloj con estado real
  updateClock();
  timeInterval = setInterval(updateClock, 1000);
  
  // Sincronización automática cada 1 minuto
  runSync();
  syncInterval = setInterval(runSync, 60000);

  // Cargar historial reciente de la BD local (SOLO DE HOY)
  // Usar el inicio del día local convertido a ISO para evitar desfases de zona horaria (UTC)
  const startOfToday = getLocalTime();
  startOfToday.setHours(0, 0, 0, 0);
  const startOfTodayISO = startOfToday.toISOString();
  
  const logs = await db.attendanceLogs
    .where('scanned_at')
    .aboveOrEqual(startOfTodayISO)
    .reverse()
    .limit(10)
    .toArray();

  // Cargar historial y estadísticas
  await recalculateLocalStats();
  
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
