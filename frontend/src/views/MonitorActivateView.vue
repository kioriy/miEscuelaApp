<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50 flex items-center justify-center p-6">
      <div class="min-h-screen w-full flex items-center justify-center p-4">
        <div class="max-w-[600px] w-full bg-white rounded-[48px] shadow-2xl shadow-blue-500/5 p-10 lg:p-16 flex flex-col items-center animate-fade-in border border-gray-100/50">
          
          <!-- Shield Icon -->
          <div class="relative mb-10">
            <div class="absolute inset-0 bg-blue-500 opacity-10 blur-2xl rounded-full"></div>
            <div class="w-24 h-24 bg-gradient-to-tr from-blue-50 to-white rounded-full flex items-center justify-center text-brand-blue shadow-lg border-2 border-white relative z-10">
              <ion-icon :icon="shieldCheckmark" class="text-4xl"></ion-icon>
            </div>
          </div>

          <h1 class="text-[32px] font-black text-gray-900 mb-2 tracking-tight text-center leading-tight">Activar Kiosko de Monitoreo</h1>
          <p class="text-gray-400 font-bold text-center mb-8 text-sm uppercase tracking-widest">
            Kioskos activados: <span :class="isLimitReached ? 'text-red-500' : 'text-brand-blue'">{{ activationStatus.active }} de {{ activationStatus.total }}</span>
          </p>

          <!-- Limit Reached Alert -->
          <div v-if="isLimitReached" class="w-full bg-red-50 border border-red-100 rounded-3xl p-6 flex items-start gap-4 mb-10 animate-shake">
            <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center text-white shrink-0 mt-0.5">
              <ion-icon :icon="alertCircle" class="text-lg"></ion-icon>
            </div>
            <div class="space-y-1">
              <p class="text-sm font-bold text-red-900 leading-tight">
                Has alcanzado el límite máximo de kioskos activados para tu institución.
              </p>
              <p class="text-[13px] text-red-700/80 font-medium">
                Por favor, ponte en contacto con <strong class="font-black text-red-800">miEscuelaApp</strong> para solicitar la activación de más kioskos.
              </p>
            </div>
          </div>

          <div class="w-full space-y-10" :class="{'opacity-40 grayscale pointer-events-none': isLimitReached}">
            <!-- Device Name Field -->
            <div class="space-y-3">
              <label class="text-[13px] font-black text-gray-700 ml-1 uppercase tracking-wider">Nombre del Kiosko</label>
              <input 
                v-model="deviceName"
                type="text" 
                placeholder="Ej. Entrada Principal" 
                class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 text-gray-900 font-bold focus:border-brand-blue focus:bg-white focus:outline-none transition-all placeholder:text-gray-300 shadow-inner"
              />
            </div>

            <!-- PIN Field -->
            <div class="space-y-6 text-center">
              <label class="text-[13px] font-black text-gray-700 uppercase tracking-wider">Ingresa el código de activación</label>
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
              <p class="text-[11px] text-gray-400 font-bold mt-4">El código de 7 caracteres se encuentra en su panel de administración.</p>
            </div>

            <!-- Action Button -->
            <button 
              @click="activateKiosk"
              :disabled="isLoading || !isFormComplete"
              class="w-full bg-brand-blue text-white font-black py-5 rounded-[24px] shadow-xl shadow-blue-500/20 hover:bg-blue-600 active:scale-[0.98] transition-all text-lg flex items-center justify-center gap-4 disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed group"
            >
              <div v-if="isLoading" class="w-6 h-6 border-3 border-white/30 border-t-white rounded-full animate-spin"></div>
              <template v-else>
                <ion-icon :icon="linkOutline" class="text-2xl group-hover:rotate-12 transition-transform"></ion-icon>
                <span>Vincular Dispositivo</span>
              </template>
            </button>
          </div>

          <div class="mt-16 text-center text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">
            Soporte Técnico: 800-SAFE-EDU
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { shieldCheckmark, linkOutline, alertCircle } from 'ionicons/icons';
import api from '@/services/api';
import { storage } from '@/services/storage';

const router = useRouter();
const isLoading = ref(false);
const deviceName = ref('');
const pinDigits = ref(['', '', '', '', '', '', '']);
const pinInputs = ref<any[]>([]);

const activationStatus = ref({
  active: 0,
  total: 0
});

const isLimitReached = computed(() => {
  return activationStatus.value.total > 0 && activationStatus.value.active >= activationStatus.value.total;
});

const isFormComplete = computed(() => {
  return deviceName.value.length > 2 && pinDigits.value.every(d => d.length === 1);
});

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

const fetchStatus = async () => {
  try {
    const res = await api.get('/setup/kiosk/status');
    if (res.data.success) {
      activationStatus.value.active = res.data.data.active_count;
      activationStatus.value.total = res.data.data.total_allowed;
    }
  } catch (error) {
    console.error('Error fetching activation status:', error);
  }
};

const activateKiosk = async () => {
  // Formato en DB: K-XXXX-NN (7 caracteres + 2 guiones)
  const code = pinDigits.value[0].toUpperCase() + '-' + 
               pinDigits.value.slice(1, 5).join('').toUpperCase() + '-' + 
               pinDigits.value.slice(5, 7).join('').toUpperCase();
               
  isLoading.value = true;
  
  try {
    const res = await api.post('/setup/kiosk/activate', {
      activation_code: code,
      device_name: deviceName.value
    });

    if (res.data.success) {
      await storage.set('auth_token', res.data.token);
      await storage.set('auth_user', res.data.kiosk);
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

onMounted(() => {
  fetchStatus();
  setTimeout(() => pinInputs.value[0]?.focus(), 500);
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
