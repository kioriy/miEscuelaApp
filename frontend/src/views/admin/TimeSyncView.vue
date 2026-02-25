<template>
  <ion-page>
    <div class="p-6 max-w-lg mx-auto w-full h-full flex flex-col justify-center items-center">
      <div v-if="loading" class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-blue mx-auto mb-4"></div>
        <p class="text-gray-500 font-medium">Generando código de seguridad...</p>
      </div>

      <div v-else-if="error" class="bg-red-50 text-red-600 p-6 rounded-2xl w-full text-center border border-red-100">
        <ion-icon :icon="warning" class="text-4xl mb-2"></ion-icon>
        <p class="font-bold">{{ error }}</p>
        <button @click="fetchToken" class="mt-4 bg-red-100 text-red-700 px-6 py-2 rounded-xl font-bold hover:bg-red-200 transition-colors">Reintentar</button>
      </div>

      <div v-else class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center w-full relative overflow-hidden">
        <!-- Decorative bg -->
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-50 rounded-full blur-2xl opacity-60"></div>
        
        <div class="w-16 h-16 bg-blue-50 text-brand-blue rounded-2xl flex items-center justify-center mx-auto mb-6 relative z-10 border border-blue-100/50">
          <ion-icon :icon="time" class="text-3xl"></ion-icon>
        </div>
        
        <h2 class="text-2xl font-black text-gray-900 mb-2 tracking-tight">Sincronizar Hora</h2>
        <p class="text-gray-500 text-sm mb-8 leading-relaxed">
          Usa tu escáner del Kiosco para leer este código QR. Esto ajustará la hora del sistema para registrar las asistencias correctamente.
        </p>
        
        <div v-if="tokenData" class="bg-white p-4 rounded-2xl inline-block mx-auto mb-6 border border-gray-100 shadow-sm">
          <qrcode-vue :value="tokenData" :size="220" level="H" />
        </div>

        <div v-if="tokenData" class="flex justify-center items-center gap-2 mb-6 text-sm font-bold text-gray-500">
          <ion-icon :icon="timerOutline" class="text-lg"></ion-icon>
          Expira en: <span :class="countdown < 60 ? 'text-red-500' : 'text-brand-blue'">{{ Math.floor(countdown / 60) }}:{{ (countdown % 60).toString().padStart(2, '0') }}</span>
        </div>

        <button @click="fetchToken" class="w-full bg-gray-50 text-gray-700 border border-gray-200 font-bold py-3.5 rounded-xl hover:bg-gray-100 transition-colors flex items-center justify-center gap-2">
          <ion-icon :icon="refreshOutline" class="text-lg"></ion-icon>
          Generar nuevo código
        </button>
      </div>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { IonPage, IonIcon } from '@ionic/vue';
import { time, warning, timerOutline, refreshOutline } from 'ionicons/icons';
import QrcodeVue from 'qrcode.vue';
import api from '@/services/api';

const loading = ref(true);
const error = ref('');
const tokenData = ref('');
const countdown = ref(0);
let timer: any = null;

const fetchToken = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const res = await api.get('/admin/school/time-sync-token');
    if (res.data?.success) {
      tokenData.value = res.data.token;
      startCountdown(res.data.expires_at);
    } else {
      error.value = 'Error al obtener el código. Intente nuevamente.';
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error de conexión con el servidor.';
  } finally {
    loading.value = false;
  }
};

const startCountdown = (expiresAt: number) => {
  if (timer) clearInterval(timer);
  
  const updateCountdown = () => {
    const now = Math.floor(Date.now() / 1000);
    const left = expiresAt - now;
    if (left <= 0) {
      countdown.value = 0;
      clearInterval(timer);
      tokenData.value = ''; // Hide QR
      error.value = 'El código QR ha expirado. Por favor, genere uno nuevo.';
    } else {
      countdown.value = left;
    }
  };
  
  updateCountdown();
  timer = setInterval(updateCountdown, 1000);
};

onMounted(() => {
  fetchToken();
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
