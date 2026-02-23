<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50">
      <div class="flex min-h-screen bg-white">
        
        <!-- Left Column: Background Image Overlay (Hidden on Mobile) -->
        <div class="hidden md:flex md:w-1/2 relative">
          <div class="absolute inset-0 z-0">
            <img 
              src="@/assets/images/school_bg.png" 
              alt="School Hallway" 
              class="w-full h-full object-cover"
            />
            <!-- Modern blue gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-brand-blue/90 via-brand-blue/60 to-transparent"></div>
          </div>
          
          <div class="relative z-10 flex flex-col justify-center px-12 lg:px-24 text-white h-full pb-20">
            <div class="bg-white/20 backdrop-blur-sm p-4 w-16 h-16 rounded-2xl flex items-center justify-center mb-8 shadow-lg border border-white/30">
              <ion-icon :icon="shieldCheckmarkOutline" class="text-3xl text-white"></ion-icon>
            </div>
            
            <h1 class="text-4xl lg:text-5xl font-black leading-tight mb-6 drop-shadow-md tracking-tight">
              Seguridad y Control en Tiempo Real para tu Comunidad Escolar
            </h1>
            <p class="text-xl text-white/90 font-light leading-relaxed max-w-lg mb-auto">
              Gestiona ingresos, salidas y asistencia con la tecnología más avanzada del sector educativo.
            </p>
            
            <!-- Bottom decorative line -->
            <div class="flex items-center space-x-4 mt-12 mb-8">
              <div class="h-1 w-16 bg-white rounded-full"></div>
              <span class="text-sm tracking-[0.2em] font-semibold text-white/80 uppercase">Sistema de Gestión Escolar</span>
            </div>
          </div>
        </div>

        <!-- Right Column: Login Form -->
        <div class="flex flex-col w-full md:w-1/2 justify-center px-8 sm:px-16 lg:px-32 relative py-12">
          
          <!-- Logo Header -->
          <div class="flex items-center mb-16 gap-3">
             <div class="bg-brand-blue p-2 rounded-xl text-white flex items-center justify-center shadow-md">
                <ion-icon :icon="school" class="text-2xl"></ion-icon>
             </div>
             <span class="text-2xl font-bold text-gray-900 tracking-tight">miEscuela</span>
          </div>

          <!-- Welcome Title -->
          <div class="mb-10">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Bienvenido de nuevo</h2>
            <p class="text-gray-500 text-base leading-relaxed">
              Accede al panel administrativo para gestionar tu institución.
            </p>
          </div>

          <!-- Google Login Button (functional approach) -->
          <div class="flex justify-center flex-col items-center">
            <button 
              @click="handleGoogleLogin" 
              :disabled="isLoading"
              class="flex items-center justify-center gap-3 w-full max-w-sm px-6 py-3.5 bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-md hover:bg-gray-50 transition-all duration-200 group active:scale-[0.98]"
            >
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 12-4.53z" fill="#EA4335"/>
              </svg>
              <span class="text-gray-700 font-bold text-sm">Continuar con Google</span>
            </button>
            <div v-if="isLoading" class="mt-4 flex items-center gap-2 text-brand-blue animate-pulse">
              <div class="w-1.5 h-1.5 bg-current rounded-full"></div>
              <span class="text-[11px] font-bold uppercase tracking-widest">Iniciando sesión...</span>
            </div>
          </div>

          <!-- Divider -->
          <div class="mt-8 mb-8 flex items-center justify-center">
            <div class="flex-grow border-t border-gray-100"></div>
            <span class="flex-shrink-0 mx-4 text-xs font-semibold text-gray-400 uppercase tracking-widest">Soporte Técnico</span>
            <div class="flex-grow border-t border-gray-100"></div>
          </div>

          <!-- Help Info Card -->
          <div class="bg-blue-50/50 rounded-2xl p-4 flex items-start gap-4 border border-blue-100/50">
            <div class="bg-brand-blue text-white rounded-full p-1.5 flex-shrink-0 mt-0.5">
              <ion-icon :icon="helpCircle" class="text-xl"></ion-icon>
            </div>
            <div>
              <h4 class="text-sm font-bold text-brand-blue">¿Problemas para acceder?</h4>
              <p class="text-xs text-brand-blue/80 mt-1 cursor-pointer hover:underline">
                Contactar al administrador de IT
              </p>
            </div>
          </div>
          
          <!-- Footer info -->
          <div class="absolute bottom-8 left-8 right-8 sm:left-16 sm:right-16 lg:left-32 lg:right-32 flex justify-between items-center text-[11px] text-gray-400 font-medium">
             <span>© 2026 EduControl v4.2.0</span>
             <div class="space-x-4">
                <a href="#" class="hover:text-gray-600 transition-colors">Términos de Servicio</a>
                <a href="#" class="hover:text-gray-600 transition-colors">Privacidad</a>
             </div>
          </div>

        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { IonPage, IonContent, IonIcon, toastController } from '@ionic/vue';
import { shieldCheckmarkOutline, helpCircle, school } from 'ionicons/icons';
import { useRouter } from 'vue-router';
import { googleTokenLogin } from 'vue3-google-login';
import api from '@/services/api';
import { storage } from '@/services/storage';

const router = useRouter();
const isLoading = ref(false);

const handleGoogleLogin = () => {
  isLoading.value = true;
  googleTokenLogin().then((response) => {
    console.log('DEBUG GOOGLE RESPONSE:', JSON.stringify(response, null, 2));
    onGoogleCallback(response);
  }).catch(error => {
    console.error('DEBUG GOOGLE ERROR:', error);
    isLoading.value = false;
    alert('Error al abrir la ventana de Google. Verifica los bloqueadores de popups.');
  });
};

const onGoogleCallback = async (response: any) => {
  try {
    isLoading.value = true;
    
    // Access token should be here
    const access_token = response.access_token;

    if (!access_token) {
      console.warn('Alerta: No se recibió access_token. Respuesta completa:', response);
      if (response.error) {
         throw new Error(`Google Error: ${response.error} - ${response.error_description || ''}`);
      }
      throw new Error('La respuesta de Google no contiene un token de acceso.');
    }

    // Enviar el token al backend de Laravel
    const apiResponse = await api.post('/auth/parent/google', { token: access_token });
    
    if (apiResponse.data.success && apiResponse.data.token) {
      await storage.set('auth_token', apiResponse.data.token);
      await storage.set('auth_user', apiResponse.data.user);
      
      const toast = await toastController.create({
        message: 'Bienvenido de nuevo, ' + apiResponse.data.user.name,
        duration: 3000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
      
      if (apiResponse.data.user.role === 'super_admin' || apiResponse.data.user.role === 'director' || apiResponse.data.user.role === 'teacher') {
         router.push('/admin/dashboard'); 
      } else {
         router.push('/monitor');
      }
    }
    
  } catch (error: any) {
    console.error('Error durante el login con Google:', error);
    
    let msg = 'Error de conexión con el proveedor o el servidor.';
    if (error.message) msg = error.message;
    if (error.response?.data?.message) msg = error.response.data.message;
    
    const toast = await toastController.create({
        message: msg,
        duration: 8000,
        color: 'danger',
        position: 'top'
    });
    await toast.present();
  } finally {
    isLoading.value = false;
  }
};
</script>
