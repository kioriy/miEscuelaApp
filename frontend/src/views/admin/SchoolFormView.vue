<template>
  <ion-page>
    <ion-content>
      <div class="h-full bg-gray-50 flex flex-col font-sans">
    
    <!-- Top Global Header Placeholder -->
    <header class="bg-white border-b border-gray-100 py-3 px-6 flex justify-between items-center shrink-0">
      <div class="flex items-center gap-2 text-blue-600">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 14.5l-6.3-3.15L2 15l10 5 10-5-3.7-1.85L12 16.5z"/></svg>
        <div>
          <h1 class="font-bold text-gray-800 leading-tight">Panel de Super Admin</h1>
          <p class="text-xs text-gray-400">Configuración de Red de Escuelas</p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <div class="text-right hidden sm:block">
          <p class="font-semibold text-sm text-gray-800 leading-tight">Administrador Central</p>
          <p class="text-xs text-gray-400">Soporte Global</p>
        </div>
        <div class="h-9 w-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-8">
      <div class="max-w-4xl mx-auto w-full">
        
        <!-- Breadcrumb -->
        <nav class="flex items-center text-sm text-gray-500 mb-6 font-medium">
          <button @click="$router.push('/admin/schools')" class="hover:text-blue-600 transition-colors">Escuelas</button>
          <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          <span class="text-gray-900 font-semibold">Nueva Escuela</span>
        </nav>

        <!-- Main Form Card -->
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden mb-6">
          
          <!-- Card Header -->
          <div class="px-8 py-6 border-b border-gray-50 flex items-center gap-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            <h2 class="text-xl font-bold text-gray-800">Agregar Nueva Escuela</h2>
          </div>

          <!-- Card Body -->
          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
              
              <!-- Left Column: Logo Upload -->
              <div class="md:col-span-4 flex flex-col items-start">
                <label class="block text-sm font-bold text-gray-800 mb-3">Logotipo</label>
                
                <div class="w-40 h-40 rounded-3xl border-2 border-dashed border-gray-300 bg-gray-50/50 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-100 hover:border-blue-400 transition-all group relative overflow-hidden">
                  <!-- Image Preview -->
                  <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover absolute inset-0 z-10" />
                  
                  <!-- Placeholder Content -->
                  <div class="flex flex-col items-center text-gray-400 group-hover:text-blue-500 relative z-0" :class="{ 'opacity-0': logoPreview }">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span class="text-xs font-bold uppercase tracking-wider">Subir Logotipo</span>
                  </div>
                  <!-- Hidden File Input -->
                  <input type="file" @change="handleLogoUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" accept="image/png, image/jpeg" />
                </div>
                
                <p class="text-[11px] text-gray-400 mt-3 leading-tight italic max-w-[160px]">
                  PNG, JPG hasta 2MB<br/>Resolución: 512x512px
                </p>
              </div>

              <!-- Right Column: Form Fields -->
              <div class="md:col-span-8 flex flex-col gap-6">
                
                <!-- Row: Name and CCT -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <!-- School Name -->
                  <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Nombre de la Escuela</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                      </div>
                      <input type="text" v-model="form.name" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white shadow-sm" placeholder="Instituto Occidente">
                    </div>
                  </div>

                  <!-- CCT -->
                  <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Clave de Centro (CCT)</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                      </div>
                      <input type="text" v-model="form.cct" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white shadow-sm uppercase" placeholder="Ej. 14PJN0123M">
                    </div>
                  </div>
                </div>

                <!-- Row: Address and Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <!-- Address -->
                  <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Dirección Completa</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                      </div>
                      <input type="text" v-model="form.address" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white shadow-sm" placeholder="Ej. Av. Siempre Viva 742">
                    </div>
                  </div>

                  <!-- Contact Phone -->
                  <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Teléfono de Contacto</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                      </div>
                      <input type="tel" v-model="form.contact_phone" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white shadow-sm" placeholder="Ej. 55 1234 5678">
                    </div>
                  </div>
                </div>

                <!-- Timezone and Status ROW -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  
                  <!-- Timezone -->
                  <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Zona Horaria</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                      </div>
                      <select v-model="form.timezone" class="w-full pl-11 pr-10 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white appearance-none shadow-sm cursor-pointer">
                        <option value="America/Mexico_City">CDMX (GMT-6)</option>
                        <option value="America/Monterrey">Monterrey</option>
                        <option value="America/Tijuana">Tijuana</option>
                        <option value="UTC">UTC Universal</option>
                      </select>
                      <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                      </div>
                    </div>
                  </div>

                  <!-- Status -->
                  <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Estado de la Escuela</label>
                    <div class="w-full py-2.5 px-4 rounded-xl border border-gray-200 bg-white shadow-sm flex items-center gap-3 h-[46px]">
                      <button type="button" @click="form.isActive = !form.isActive" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" :class="form.isActive ? 'bg-emerald-500' : 'bg-gray-200'" role="switch" :aria-checked="form.isActive">
                        <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="form.isActive ? 'translate-x-5' : 'translate-x-0'"></span>
                      </button>
                      <span class="text-sm font-semibold text-gray-700">{{ form.isActive ? 'Activa' : 'Inactiva' }}</span>
                    </div>
                  </div>

                </div>

              </div>
            </div>

            <!-- Divider -->
            <hr class="my-8 border-gray-100" />

            <!-- Kiosk Configuration Section -->
            <div>
              <div class="mb-5">
                <h3 class="text-sm font-bold text-gray-800 leading-tight">Configuración de Kioscos</h3>
                <p class="text-xs text-gray-500 mt-1">Define cuántos dispositivos de escaneo están permitidos para este plantel.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                
                <!-- Max Kiosks Input -->
                <div class="md:col-span-5">
                   <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Cantidad Máxima</label>
                   <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                      </div>
                      <input type="number" v-model="form.maxKiosks" min="1" max="50" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white shadow-sm font-semibold">
                   </div>
                </div>

                <!-- Info Banner -->
                <div class="md:col-span-7 md:pt-6">
                  <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3 flex items-center gap-3">
                     <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                       <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                     </div>
                     <p class="text-[13px] font-medium text-blue-800 tracking-tight">Se generarán IDs únicos al guardar.</p>
                  </div>
                </div>

              </div>

              <!-- Registered Kiosks Tags -->
              <div class="mt-6">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">IDs de Kioscos Autorizados</label>
                <div class="min-h-[50px] bg-gray-50/50 border border-gray-200 rounded-xl p-2 flex flex-wrap gap-2 items-center">
                  
                  <span v-for="(kiosk, index) in dummyKiosks" :key="index" class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-lg bg-white border border-gray-200 shadow-sm text-sm font-semibold text-gray-700 hover:border-gray-300 transition-colors">
                    {{ kiosk }}
                    <button type="button" class="text-gray-400 hover:text-red-500 focus:outline-none h-4 w-4 flex items-center justify-center rounded-full hover:bg-gray-100">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                  </span>
                  
                  <button type="button" class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-lg bg-transparent border border-dashed border-blue-300 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Vincular Manual
                  </button>

                </div>
              </div>

            </div>

          </div>

          <!-- Card Footer Action Buttons -->
          <div class="px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-end gap-4">
             <button type="button" @click="$router.push('/admin/schools')" :disabled="isSubmitting" class="px-5 py-2.5 rounded-xl font-semibold text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-200/50 transition-colors disabled:opacity-50">
               Cancelar
             </button>
             <button type="button" @click="submitForm" :disabled="isSubmitting" class="px-7 py-2.5 rounded-xl font-bold text-sm text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-600/20 transition-all active:scale-[0.98] flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
               <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
               {{ isSubmitting ? 'Guardando...' : 'Guardar Escuela' }}
             </button>
          </div>

        </div>

        <!-- Security Review Banner -->
        <div class="bg-orange-50 border border-orange-100 rounded-[20px] p-5 flex items-start gap-4 shadow-sm mb-12">
           <div class="mt-0.5text-orange-500 shrink-0">
             <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
           </div>
           <div>
             <h4 class="text-sm font-bold text-orange-900 mb-1">Revisión de Seguridad</h4>
             <p class="text-[13px] text-orange-800/80 leading-relaxed font-medium">Al dar de alta una nueva escuela, se enviará automáticamente un correo de configuración al contacto administrativo registrado.</p>
           </div>
        </div>

      </div>

      <!-- Footer Footer -->
      <footer class="text-center pb-8 pt-4 border-t border-gray-200 max-w-4xl mx-auto w-full">
        <p class="text-xs font-semibold text-gray-500 mb-2">© 2024 AdminCore - Sistema Integral de Monitoreo Escolar.</p>
        <div class="flex items-center justify-center gap-4 text-[10px] font-bold tracking-wider text-blue-500 uppercase">
          <a href="#" class="hover:text-blue-700 transition-colors">Logs del Sistema</a>
          <span class="text-gray-300">•</span>
          <a href="#" class="hover:text-blue-700 transition-colors">Auditoría Global</a>
        </div>
      </footer>

    </main>
  </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import api from '@/services/api';

const router = useRouter();

const isSubmitting = ref(false);

const form = ref({
  cct: '',
  name: '',
  address: '',
  contact_phone: '',
  timezone: 'America/Mexico_City',
  isActive: true,
  maxKiosks: 1,
  logo_base64: '' // We will send the image as base64 for simplicity
});

const logoPreview = ref<string | null>(null);

const handleLogoUpload = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;

  // Validaciones
  if (file.size > 2 * 1024 * 1024) {
    alert("El archivo excede los 2MB permitidos.");
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    const result = e.target?.result as string;
    logoPreview.value = result;
    form.value.logo_base64 = result;
  };
  reader.readAsDataURL(file);
};

// Mocked tags for design
const dummyKiosks = ref(['Pendiente de generar']);

const submitForm = async () => {
  if (!form.value.name || !form.value.cct) {
    alert('Por favor, ingresa el nombre de la escuela y su Clave de Centro de Trabajo (CCT).');
    return;
  }

  try {
    isSubmitting.value = true;
    const response = await api.post('/admin/schools', form.value);
    
    if (response.data.success) {
      alert('¡Escuela creada exitosamente! Se han generado ' + form.value.maxKiosks + ' kioscos.');
      router.push('/admin/schools');
    }
  } catch (error: any) {
    console.error('Error al guardar la escuela:', error);
    alert(error.response?.data?.message || 'Error al conectar con el servidor.');
  } finally {
    isSubmitting.value = false;
  }
};

</script>

<style scoped>
/* Custom switch transitions */
</style>
