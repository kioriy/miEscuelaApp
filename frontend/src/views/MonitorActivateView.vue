<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50 flex items-center justify-center p-6">
      <div class="min-h-screen w-full flex items-center justify-center p-4">
        <div class="max-w-[700px] w-full bg-white rounded-[48px] shadow-2xl shadow-blue-500/5 p-8 lg:p-12 flex flex-col items-center animate-fade-in border border-gray-100/50">
          
          <!-- Shield Icon -->
          <div class="relative mb-6">
            <div class="absolute inset-0 bg-blue-500 opacity-10 blur-2xl rounded-full"></div>
            <div class="w-20 h-20 bg-gradient-to-tr from-blue-50 to-white rounded-full flex items-center justify-center text-brand-blue shadow-lg border-2 border-white relative z-10">
              <ion-icon :icon="shieldCheckmark" class="text-3xl"></ion-icon>
            </div>
          </div>

          <h1 class="text-[28px] lg:text-[32px] font-black text-gray-900 mb-2 tracking-tight text-center leading-tight">Activación de Kiosko Multi-Sede</h1>
          <p class="text-gray-500 font-bold text-center mb-8 text-sm uppercase tracking-widest">
            Kioskos activados: <span v-if="ownerSchool" :class="isLimitReached ? 'text-red-500' : 'text-brand-blue'">{{ activeKiosksCount }} de {{ allowedKiosksCount }}</span><span v-else>0 de 0</span>
          </p>

          <!-- Limit Reached Alert -->
          <div v-if="isLimitReached" class="w-full bg-red-50 border border-red-100 rounded-3xl p-6 flex items-start gap-4 mb-8 animate-shake">
            <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center text-white shrink-0 mt-0.5">
              <ion-icon :icon="alertCircle" class="text-lg"></ion-icon>
            </div>
            <div class="space-y-1">
              <p class="text-sm font-bold text-red-900 leading-tight">
                Has alcanzado el límite máximo de kioskos activados para tu institución dueña.
              </p>
              <p class="text-[13px] text-red-700/80 font-medium">
                Por favor, ponte en contacto con <strong class="font-black text-red-800">miEscuelaApp</strong> para solicitar la activación de más kioskos en esta sede.
              </p>
            </div>
          </div>

          <div class="w-full space-y-6" :class="{'opacity-40 grayscale pointer-events-none': isLimitReached}">
            
            <!-- Owner School Field -->
            <div class="space-y-2">
              <label class="text-[13px] font-black text-gray-700 ml-1 uppercase tracking-wider">Escuela Dueña (Sede Principal)</label>
              <div class="relative">
                <select v-model="ownerSchoolId" class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 text-gray-900 font-bold focus:border-brand-blue focus:bg-white focus:outline-none transition-all appearance-none cursor-pointer">
                  <option value="" disabled>Seleccionar sede principal</option>
                  <option v-for="school in availableSchools" :key="school.id" :value="school.id">{{ school.name }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
              </div>
            </div>

            <!-- Additional Schools Field -->
            <div class="space-y-2">
              <label class="text-[13px] font-black text-gray-700 ml-1 uppercase tracking-wider">Sedes Adicionales con Acceso</label>
              <div class="flex gap-3">
                <div class="relative flex-grow">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <ion-icon :icon="searchOutline"></ion-icon>
                  </div>
                  <select v-model="selectedAdditionalSchool" class="w-full pl-10 pr-4 py-4 bg-gray-50/50 border-2 border-gray-100 rounded-2xl text-gray-900 font-bold focus:border-brand-blue focus:bg-white focus:outline-none transition-all appearance-none cursor-pointer">
                    <option value="" disabled>Buscar otras sedes...</option>
                    <option v-for="school in unselectedAdditionalSchools" :key="school.id" :value="school.id">{{ school.name }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
                </div>
                <button @click="addAdditionalSchool" :disabled="!selectedAdditionalSchool" class="bg-blue-600 text-white font-bold px-6 py-4 rounded-2xl shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm shrink-0">
                  Asignar
                </button>
              </div>

              <!-- Pills container -->
              <div v-if="additionalSchoolIds.length > 0" class="flex flex-wrap gap-2 mt-3 p-3 bg-gray-50/50 border border-gray-100 rounded-2xl">
                <div v-for="id in additionalSchoolIds" :key="id" class="flex items-center gap-2 bg-white border border-gray-200 px-3 py-1.5 rounded-full shadow-sm">
                  <span class="text-[13px] font-bold text-gray-700">{{ getSchoolName(id) }}</span>
                  <button @click="removeAdditionalSchool(id)" class="text-gray-400 hover:text-red-500 flex items-center">
                    <ion-icon :icon="closeOutline" class="text-sm"></ion-icon>
                  </button>
                </div>
              </div>
            </div>

            <!-- Device Name Field -->
            <div class="space-y-2">
              <label class="text-[13px] font-black text-gray-700 ml-1 uppercase tracking-wider">Nombre del Kiosko</label>
              <input 
                v-model="deviceName"
                type="text" 
                placeholder="Ej. Acceso Peatonal Principal" 
                class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 text-gray-900 font-bold focus:border-brand-blue focus:bg-white focus:outline-none transition-all placeholder:text-gray-300 shadow-inner"
              />
            </div>

            <!-- PIN Field -->
            <div class="space-y-6 text-center pt-4">
              <label class="text-[13px] font-black text-gray-700 uppercase tracking-wider">Ingresa el código de activación (7 caracteres)</label>
              <div class="flex justify-center items-center gap-2">
                <!-- Group 1 (1 digit) -->
                <input 
                  :ref="el => pinInputs[0] = el"
                  type="text" maxlength="1" v-model="pinDigits[0]"
                  @input="handleInput($event, 0)" @keydown.backspace="handleBackspace($event, 0)"
                  class="pin-box"
                />
                
                <span class="text-gray-300 font-black text-xl">-</span>

                <!-- Group 2 (4 digits) -->
                <div class="flex gap-2">
                  <input 
                    v-for="i in [1, 2, 3, 4]" :key="i"
                    :ref="el => pinInputs[i] = el"
                    type="text" maxlength="1" v-model="pinDigits[i]"
                    @input="handleInput($event, i)" @keydown.backspace="handleBackspace($event, i)"
                    class="pin-box"
                  />
                </div>

                <span class="text-gray-300 font-black text-xl">-</span>

                <!-- Group 3 (2 digits) -->
                <div class="flex gap-2">
                  <input 
                    v-for="i in [5, 6]" :key="i"
                    :ref="el => pinInputs[i] = el"
                    type="text" maxlength="1" v-model="pinDigits[i]"
                    @input="handleInput($event, i)" @keydown.backspace="handleBackspace($event, i)"
                    class="pin-box"
                  />
                </div>
              </div>
              <p class="text-[11px] text-gray-400 font-bold mt-4">El formato del código es K - XXXX - XX.</p>
            </div>

            <!-- Action Button -->
            <button 
              @click="activateKiosk"
              :disabled="isLoading || !isFormComplete"
              class="w-full bg-blue-600 text-white font-black py-4 mt-8 rounded-2xl shadow-xl shadow-blue-500/20 hover:bg-blue-700 active:scale-[0.98] transition-all text-lg flex items-center justify-center gap-4 disabled:opacity-50 disabled:cursor-not-allowed group"
            >
              <div v-if="isLoading" class="w-6 h-6 border-3 border-white/30 border-t-white rounded-full animate-spin"></div>
              <template v-else>
                <ion-icon :icon="linkOutline" class="text-2xl group-hover:rotate-12 transition-transform"></ion-icon>
                <span>Vincular Dispositivo Multi-Sede</span>
              </template>
            </button>
          </div>

          <div v-if="isDirector" class="mt-8">
            <button @click="router.push('/admin/dashboard')" class="text-brand-blue font-bold text-sm hover:underline flex items-center gap-2">
              <ion-icon :icon="school" class="text-lg"></ion-icon>
              Cambiar de Escuela / Volver al Panel
            </button>
          </div>

          <div class="mt-8 text-center text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">
            SOPORTE TÉCNICO: 800-SAFE-EDU
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { shieldCheckmark, linkOutline, alertCircle, school, closeOutline, searchOutline } from 'ionicons/icons';
import api from '@/services/api';
import { storage } from '@/services/storage';

const router = useRouter();
const isLoading = ref(false);
const isDirector = ref(false);

const availableSchools = ref<any[]>([]);

const ownerSchoolId = ref<number | ''>('');
const selectedAdditionalSchool = ref<number | ''>('');
const additionalSchoolIds = ref<number[]>([]);

const deviceName = ref('');
const pinDigits = ref(['', '', '', '', '', '', '']);
const pinInputs = ref<any[]>([]);

// Get active and allowed from selected owner
const ownerSchool = computed(() => {
  if (!ownerSchoolId.value) return null;
  return availableSchools.value.find(s => s.id === ownerSchoolId.value);
});

const activeKiosksCount = computed(() => ownerSchool.value?.active_kiosks || 0);
const allowedKiosksCount = computed(() => ownerSchool.value?.allowed_kiosks || 0);

const isLimitReached = computed(() => {
  if (!ownerSchool.value) return false;
  return activeKiosksCount.value >= allowedKiosksCount.value;
});

const isFormComplete = computed(() => {
  return ownerSchoolId.value !== '' && 
         deviceName.value.length > 2 && 
         pinDigits.value.every(d => d.length === 1);
});

const unselectedAdditionalSchools = computed(() => {
  return availableSchools.value.filter(s => 
    s.id !== ownerSchoolId.value && 
    !additionalSchoolIds.value.includes(s.id)
  );
});

watch(ownerSchoolId, (newId) => {
  // If the owner school is in additional schools, remove it
  if (additionalSchoolIds.value.includes(newId as number)) {
    additionalSchoolIds.value = additionalSchoolIds.value.filter(id => id !== newId);
  }
});

const addAdditionalSchool = () => {
  if (selectedAdditionalSchool.value) {
    additionalSchoolIds.value.push(selectedAdditionalSchool.value as number);
    selectedAdditionalSchool.value = '';
  }
};

const removeAdditionalSchool = (id: number) => {
  additionalSchoolIds.value = additionalSchoolIds.value.filter(sId => sId !== id);
};

const getSchoolName = (id: number) => {
  const s = availableSchools.value.find(s => s.id === id);
  return s ? s.name : 'Desconocida';
};

const handleInput = (event: any, idx: number) => {
  const val = event.target.value;
  if (val && idx < 6) {
    pinInputs.value[idx + 1].focus();
  }
};

const handleBackspace = (event: any, idx: number) => {
  if (!pinDigits.value[idx] && idx > 0) {
    pinInputs.value[idx - 1].focus();
  }
};

const fetchSchools = async () => {
  try {
    const res = await api.get('/setup/kiosk/schools');
    if (res.data.success) {
      availableSchools.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching schools:', error);
  }
};

const activateKiosk = async () => {
  const code = pinDigits.value[0] + '-' + 
               pinDigits.value.slice(1, 5).join('').toUpperCase() + '-' + 
               pinDigits.value.slice(5, 7).join('').toUpperCase();        
               
  isLoading.value = true;
  
  try {
    const res = await api.post('/setup/kiosk/activate', {
      activation_code: code,
      device_name: deviceName.value,
      owner_school_id: ownerSchoolId.value,
      additional_school_ids: additionalSchoolIds.value
    });

    if (res.data.success) {
      // Store the kiosk token separately from the admin auth_token
      await storage.set('kiosk_token', res.data.token);
      await storage.set('kiosk_config', res.data.kiosk);
      
      router.push('/monitor');
    }
  } catch (error: any) {
    console.error('Error activating kiosk:', error);
    alert(error.response?.data?.message || 'Error al validar el código.');
    pinDigits.value = ['', '', '', '', '', '', ''];
    pinInputs.value[0].focus();
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  await fetchSchools();
  setTimeout(() => pinInputs.value[0]?.focus(), 500);

  const user = await storage.get('auth_user');
  if (user && (user.role === 'director' || user.role === 'super_admin')) {
    isDirector.value = true;
  }

  const currentSchoolId = await storage.get('current_school_id');
  if (currentSchoolId) {
    const found = availableSchools.value.find(s => s.id === currentSchoolId);
    if (found) {
      ownerSchoolId.value = found.id;
    }
  } else if (availableSchools.value.length === 1) {
    ownerSchoolId.value = availableSchools.value[0].id;
  }
  
});
</script>

<style scoped>
.pin-box {
  @apply w-10 h-16 sm:w-14 sm:h-20 text-center text-3xl font-black text-brand-blue bg-gray-50/50 border-2 border-gray-100 rounded-2xl focus:border-brand-blue focus:bg-white focus:outline-none transition-all uppercase shadow-inner;
}

.animate-fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
