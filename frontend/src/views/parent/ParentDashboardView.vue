<template>
  <ion-page class="bg-[#F3F4F6]">
    <div class="min-h-screen flex flex-col bg-[#F3F4F6] font-sans">
      
      <!-- Top Navigation Header -->
      <header class="bg-white border-b border-gray-100 flex-none sticky top-0 z-50 shadow-sm" style="padding-top: env(safe-area-inset-top, 0px);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
          
          <!-- Logo & Title -->
          <div class="flex items-center gap-3">
             <img src="@/assets/images/logo.png" alt="miEscuelaApp" class="w-8 h-8 object-contain" />
             <h1 class="text-xl font-black text-gray-900 tracking-tight">Portal para Padres</h1>
          </div>

          <!-- User Profile & Notifications -->
          <div class="flex items-center gap-4">
            <button @click="fetchDashboardData" :disabled="loading" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors relative disabled:opacity-50">
               <ion-icon :icon="refreshOutline" :class="{'animate-spin': loading}" class="text-xl"></ion-icon>
            </button>
            <div class="h-8 w-px bg-gray-200"></div>
            <div class="flex items-center gap-3">
               <ProfileSwitcher variant="header" />
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <ion-content :fullscreen="true" class="ion-padding" style="--background: transparent; --ion-background-color: transparent;">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-20">
           <div class="w-8 h-8 border-4 border-brand-blue border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10 grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- LEFT COLUMN (Main Content) -->
            <div class="lg:col-span-8 space-y-8">
              
              <!-- Greeting -->
              <div v-if="dashboard.user">
                 <h1 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight mb-1 flex items-center gap-2">
                    Hola, {{ dashboard.user.name.split(' ')[0] }} 👋
                 </h1>
                 <p class="text-gray-500 font-medium text-base md:text-lg">
                    Aquí tienes el reporte actual de tus hijos.
                 </p>
              </div>

              <!-- Children Cards Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" v-if="dashboard.children && dashboard.children.length > 0">
                 
                 <div v-for="child in dashboard.children" :key="child.id" class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 flex flex-col relative group hover:shadow-md transition-shadow">
                    
                    <!-- Top Dynamic Colored Area -->
                    <div :class="child.status === 'En la escuela' ? 'bg-[#1875FF]' : 'bg-[#526071] mt-2'" class="h-28 relative p-4 flex justify-end items-start transition-colors">
                       <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-xs font-bold border border-white/20">
                          <span :class="child.status === 'En la escuela' ? 'bg-green-400' : 'bg-gray-300'" class="w-1.5 h-1.5 rounded-full"></span>
                          {{ child.status }}
                       </span>
                    </div>
                    
                    <!-- Avatar that overlaps -->
                    <div :class="child.status === 'En la escuela' ? 'bg-amber-100' : 'bg-orange-100'" class="absolute top-14 left-6 w-24 h-24 rounded-full border-[6px] border-white overflow-hidden z-10 shadow-sm flex items-center justify-center">
                       <img v-if="child.avatar" :src="child.avatar" :alt="child.first_name" class="w-full h-full object-cover">
                       <ion-icon v-else :icon="personOutline" class="text-4xl text-gray-400"></ion-icon>
                    </div>

                    <!-- Card Body -->
                    <div class="pt-14 p-6 flex-grow flex flex-col">
                       <div class="flex justify-between items-start mb-6">
                          <div>
                             <h3 class="text-xl font-black text-gray-900 leading-tight">{{ child.first_name }} {{ child.last_name }}</h3>
                             <p class="text-sm font-bold text-gray-400 mt-0.5">
                                {{ child.classroom_label }}
                             </p>
                          </div>
                          <button class="text-gray-400 hover:text-gray-600 p-1">
                             <ion-icon :icon="ellipsisVertical" class="text-xl"></ion-icon>
                          </button>
                       </div>

                       <!-- Last Record Box -->
                       <div v-if="child.last_record" class="bg-gray-50/80 rounded-2xl p-4 flex items-start gap-4 mb-6 border border-gray-100/50">
                          <div :class="child.last_record.type === 'entrada' ? 'bg-blue-100 text-brand-blue' : 'bg-gray-200 text-gray-600'" class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
                             <ion-icon :icon="child.last_record.type === 'entrada' ? checkmarkOutline : chevronBackOutline" class="text-lg"></ion-icon>
                          </div>
                          <div>
                             <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Último Registro</p>
                             <p class="text-sm font-black text-gray-900 mb-1">
                                {{ child.last_record.type === 'entrada' ? 'Entrada' : 'Salida' }} a las {{ child.last_record.time }}
                             </p>
                             <div class="flex items-start gap-1 text-[11px] font-semibold text-gray-500 leading-tight">
                                <ion-icon :icon="location" class="text-red-500 mt-0.5 flex-shrink-0"></ion-icon>
                                <span>{{ child.last_record.location }} • {{ child.last_record.method }}</span>
                             </div>
                          </div>
                       </div>
                       
                       <div v-else class="bg-gray-50/80 rounded-2xl p-4 flex items-center gap-4 mb-6 border border-gray-100/50 text-gray-400">
                          <p class="text-xs font-bold w-full text-center py-2">Sin registros recientes</p>
                       </div>

                       <div class="mt-auto flex gap-3">
                          <button @click="$router.push('/parent/history?student_id=' + child.id)" class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold py-3 rounded-xl transition-colors border border-gray-100 flex items-center justify-center gap-2">
                             <ion-icon :icon="timeOutline" class="text-lg"></ion-icon>
                             Historial
                          </button>
                          <button class="flex-1 bg-brand-blue hover:bg-blue-600 text-white font-black py-3 rounded-xl shadow-md shadow-blue-500/20 transition-colors flex items-center justify-center gap-2">
                             <ion-icon :icon="megaphone" class="text-lg"></ion-icon>
                             Reportar
                          </button>
                       </div>
                    </div>
                 </div>
              </div>
              
              <!-- Empty State for Children -->
              <div v-else class="bg-white rounded-3xl p-10 text-center border border-gray-100">
                 <ion-icon :icon="schoolOutline" class="text-6xl text-gray-300 mb-4"></ion-icon>
                 <h3 class="text-lg font-black text-gray-900 mb-2">Aún no hay alumnos registrados</h3>
                 <p class="text-sm text-gray-500">Comuníquese con el administrador para vincular a sus hijos a esta cuenta.</p>
              </div>

              <!-- Authorized Persons Section -->
              <div class="pt-4">
                 <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Personas Autorizadas</h2>
                    <button @click="$router.push('/parent/authorized-persons/create')" class="text-brand-blue font-bold text-sm flex items-center gap-1 hover:underline">
                       <ion-icon :icon="addOutline"></ion-icon>
                       Agregar Nuevo
                    </button>
                 </div>

                 <!-- Persons Container -->
                 <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                       
                       <div v-for="person in dashboard.authorized_persons" :key="person.id" class="flex items-center gap-3 p-3 rounded-2xl border border-transparent hover:bg-gray-50 transition-colors">
                          <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center">
                             <img v-if="person.avatar" :src="person.avatar" :alt="person.name" class="w-full h-full object-cover">
                             <ion-icon v-else :icon="personOutline" class="text-xl text-gray-400"></ion-icon>
                          </div>
                          <div>
                             <h4 class="text-sm font-black text-gray-900 flex items-center gap-1">
                                {{ person.name }}
                                <ion-icon v-if="person.verified" :icon="checkmarkCircle" class="text-green-500 text-sm"></ion-icon>
                             </h4>
                             <p class="text-xs font-bold text-gray-400 mt-0.5">{{ person.relationship }} • ID: {{ person.id_snippet }}</p>
                          </div>
                       </div>

                       <!-- Add New Button styled as dotted box -->
                       <button @click="$router.push('/parent/authorized-persons/create')" class="h-full min-h-[72px] rounded-2xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 hover:text-brand-blue hover:border-brand-blue hover:bg-blue-50/50 transition-all group">
                          <ion-icon :icon="personAdd" class="text-xl mb-1"></ion-icon>
                          <span class="text-xs font-bold tracking-wide">Autorizar otro</span>
                       </button>

                    </div>

                    <!-- Note -->
                    <div class="mt-6 bg-[#FFF9E5] text-[#9E6A00] rounded-xl p-4 flex items-start gap-3 border border-[#FBECC5]">
                       <div class="w-5 h-5 rounded-full bg-[#F59E0B] text-white flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">i</div>
                       <p class="text-[13px] font-semibold leading-relaxed">
                          Recuerde que cualquier persona autorizada debe presentar su identificación oficial con fotografía al momento de recoger a los menores.
                       </p>
                    </div>
                 </div>
              </div>

            </div>

            <!-- RIGHT COLUMN (Sidebar) -->
            <div class="lg:col-span-4 space-y-8">
               
               <!-- Announcements Card -->
               <div class="bg-white rounded-[32px] p-6 shadow-sm border border-gray-100">
                  <div class="flex justify-between items-center mb-6">
                     <h2 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <ion-icon :icon="megaphone" class="text-brand-blue"></ion-icon>
                        Avisos Escolares
                     </h2>
                     <span v-if="dashboard.announcements && dashboard.announcements.length > 0" class="bg-blue-50 text-brand-blue font-bold text-[10px] px-2.5 py-1 rounded-lg uppercase tracking-wider">
                        {{ dashboard.announcements.length }} Hoy
                     </span>
                  </div>

                  <!-- Scrollable container: max ~3 cards visible -->
                  <div v-if="dashboard.announcements && dashboard.announcements.length > 0" class="space-y-4 max-h-[320px] overflow-y-auto pr-1 custom-scrollbar">
                     <div v-for="ann in dashboard.announcements" :key="ann.id" class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex items-start gap-4">
                        <div :class="`bg-${ann.color}-100 text-${ann.color}-500`" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                           <ion-icon :icon="getIconMap(ann.icon)" class="text-lg"></ion-icon>
                        </div>
                        <div>
                           <h4 class="text-sm font-black text-gray-900 mb-1">{{ ann.title }}</h4>
                           <p class="text-[13px] text-gray-600 leading-relaxed font-medium mb-2">{{ ann.message }}</p>
                           <p class="text-[10px] text-gray-400 font-bold">{{ ann.time_ago }}</p>
                        </div>
                     </div>
                  </div>
                  
                  <div v-else class="text-center py-6">
                     <p class="text-sm font-bold text-gray-400">No hay avisos del día de hoy</p>
                  </div>

                  <button @click="$router.push('/parent/messages')" class="w-full mt-6 text-brand-blue font-black text-sm hover:underline py-2">
                     Ver todos los avisos
                  </button>
               </div>

               <!-- Recent Activity Timeline -->
               <div>
                  <div class="flex justify-between items-center mb-6 px-1">
                     <h2 class="text-lg font-black text-gray-900 tracking-tight">Actividad Reciente</h2>
                     <button class="text-brand-blue font-black text-sm hover:underline">
                        Ver Todo
                     </button>
                  </div>

                  <div class="bg-white rounded-[32px] p-6 shadow-sm border border-gray-100 flex flex-col relative space-y-6" :class="{'before:content-[\'\'] before:absolute before:inset-y-8 before:left-[19px] before:w-0.5 before:bg-gray-100': dashboard.recent_activity && dashboard.recent_activity.length > 0}">
                     
                     <template v-if="dashboard.recent_activity && dashboard.recent_activity.length > 0">
                        <div v-for="activity in dashboard.recent_activity" :key="activity.id" class="relative pl-6">
                           <div :class="activity.type === 'entrada' ? 'bg-green-500' : 'bg-gray-300'" class="absolute w-2.5 h-2.5 rounded-full left-[-4px] top-1 border-[3px] border-white box-content"></div>
                           <p class="text-[11px] font-black text-gray-400 mb-1 uppercase tracking-wider">{{ activity.time_label }}</p>
                           <h4 class="text-sm font-black text-gray-900 mb-0.5 leading-tight">{{ activity.type === 'entrada' ? 'Entrada' : 'Salida' }}: {{ activity.student_name }}</h4>
                           <p class="text-xs text-gray-500 font-medium">{{ activity.location }} • {{ activity.method }}</p>
                        </div>
                     </template>
                     <div v-else class="text-center py-6">
                         <p class="text-sm font-bold text-gray-400">Aún no hay actividad de asistencias</p>
                     </div>

                  </div>
               </div>

            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8 text-center text-xs font-semibold text-gray-500 mb-8 mt-4">
           <div class="flex items-center justify-center gap-1.5 mb-4 text-gray-400">
               <ion-icon :icon="shieldCheckmarkOutline"></ion-icon>
               Sistema seguro de control escolar miEscuelaApp
           </div>
           <div class="flex justify-center gap-6 text-brand-blue font-bold mb-4">
             <router-link to="/privacy" class="hover:underline">Privacidad</router-link>
           </div>
           &copy; 2026 miEscuelaApp. Todos los derechos reservados.
        </div>
      </ion-content>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  refreshOutline,
  ellipsisVertical,
  checkmarkOutline,
  location,
  timeOutline,
  megaphone,
  addOutline,
  checkmarkCircle,
  personAdd,
  calendarOutline,
  warningOutline,
  shieldCheckmarkOutline,
  personOutline,
  schoolOutline,
  chevronBackOutline
} from 'ionicons/icons';
import api from '@/services/api';
import { useRouter } from 'vue-router';
import { storage } from '@/services/storage';
import ProfileSwitcher from '@/components/ProfileSwitcher.vue';

const router = useRouter();
const loading = ref(true);
const dashboard = ref<any>({
  user: null,
  children: [],
  recent_activity: [],
  announcements: [],
  authorized_persons: []
});

const getIconMap = (iconName: string) => {
   if (iconName === 'warningOutline') return warningOutline;
   return calendarOutline;
};

const fetchDashboardData = async () => {
   try {
      loading.value = true;
      const res = await api.get('/parent/dashboard');
      if (res.data.success) {
         dashboard.value = res.data.data;
      }
   } catch (error) {
      console.error('Error fetching parent dashboard:', error);
   } finally {
      loading.value = false;
   }
};

onMounted(() => {
   fetchDashboardData();
});

</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}
/* Ensure dynamic tailwind colors exist or use explicit maps if using standard tailwind JIT. 
   Since we dynamically construct class strings like `bg-${ann.color}-100`, 
   we might need to safelist them in tailwind.config or just use explicit style/class logic. 
   To be extremely safe with Tailwind JIT, we'll map the inline styles if needed, 
   but for now we depend on standard colors blue/orange which might be compiled. */
.bg-blue-100 { background-color: #dbeafe; }
.text-blue-500 { color: #3b82f6; }
.bg-orange-100 { background-color: #ffedd5; }
.text-orange-500 { color: #f97316; }

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 10px;
}
</style>
