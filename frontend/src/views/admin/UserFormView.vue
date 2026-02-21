<template>
  <ion-page>
    <ion-content>
      <div class="h-full bg-gray-50 flex flex-col font-sans">
    
    <!-- Top Global Header Placeholder -->
    <header class="bg-white border-b border-gray-100 py-3 px-6 flex justify-between items-center shrink-0">
      <div class="flex items-center gap-2 text-blue-600">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 14.5l-6.3-3.15L2 15l10 5 10-5-3.7-1.85L12 16.5z"/></svg>
        <div>
          <h1 class="font-bold text-gray-800 leading-tight">Panel Super Admin</h1>
          <p class="text-[10px] text-gray-500 font-bold tracking-widest uppercase">Gestión de Usuarios</p>
        </div>
      </div>
      <div class="flex items-center gap-6 text-sm">
        <button class="flex items-center gap-2 text-gray-500 hover:text-gray-800 font-semibold transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Ayuda
        </button>
        <div class="flex items-center gap-3 pl-6 border-l border-gray-200">
          <span class="font-bold text-gray-900">Admin Principal</span>
          <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-8">
      <div class="max-w-4xl mx-auto w-full">
        
        <!-- Breadcrumb & Header -->
        <div class="mb-8">
          <nav class="flex items-center text-sm text-gray-500 mb-4 font-semibold">
            <button @click="$router.push('/admin/dashboard')" class="hover:text-blue-600 flex items-center gap-1 transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
              Inicio
            </button>
            <svg class="w-4 h-4 mx-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <button @click="$router.push('/admin/users')" class="hover:text-blue-600 transition-colors">Usuarios</button>
            <svg class="w-4 h-4 mx-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-gray-900 font-bold">Nuevo Usuario Simplificado</span>
          </nav>

          <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">Crear Nuevo Usuario</h1>
          <p class="text-gray-600 font-medium text-[15px]">Complete la información para registrar un nuevo integrante en el sistema.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden mb-6 p-8">
          
          <!-- Top: Profile Pic Upload -->
          <div class="flex items-center gap-5 mb-8">
            <div class="w-20 h-20 rounded-2xl bg-blue-50/50 border border-dashed border-blue-200 flex items-center justify-center text-blue-400 cursor-pointer hover:bg-blue-50 transition-colors relative overflow-hidden group">
              <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              <div class="absolute inset-0 bg-black/40 items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hidden md:flex">
                 <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              </div>
              <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/png, image/jpeg" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900 leading-tight">Foto de Perfil</h3>
              <p class="text-sm text-gray-500 font-medium mt-1">Opcional. Formatos aceptados: JPG, PNG.</p>
            </div>
          </div>

          <hr class="border-gray-50 mb-8" />

          <!-- Form Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
             
             <!-- Row 1 -->
             <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Nombre Completo</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                  </div>
                  <input type="text" v-model="form.name" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white" placeholder="Ej. Juan Pérez García">
                </div>
             </div>

             <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Correo Electrónico</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>
                  </div>
                  <input type="email" v-model="form.email" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white" placeholder="usuario@ejemplo.com">
                </div>
             </div>

             <!-- Row 2 -->
             <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Escuela Asignada</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.496 2.132a1 1 0 00-.992 0l-7 4A1 1 0 003 8v7a1 1 0 100 2h14a1 1 0 100-2V8a1 1 0 00.496-1.868l-7-4zM6 9a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1zm3 1a1 1 0 012 0v3a1 1 0 11-2 0v-3zm5-1a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                  </div>
                  <select v-model="form.school_id" class="w-full pl-11 pr-10 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white appearance-none cursor-pointer">
                    <option value="" disabled>Seleccione una escuela</option>
                    <option v-for="school in schools" :key="school.id" :value="school.id">{{ school.name }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
                </div>
             </div>

             <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Rol del Sistema</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                  </div>
                  <select v-model="form.role" class="w-full pl-11 pr-10 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white appearance-none cursor-pointer">
                    <option value="" disabled>Seleccione un rol</option>
                    <option value="super_admin">Súper Admin / Sistemas</option>
                    <option value="admin">Administrador (Director)</option>
                    <option value="teacher">Profesor</option>
                    <option value="parent">Padre / Tutor</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
                </div>
             </div>
          </div>

          <!-- Email Invitation Checkbox -->
          <div class="bg-gray-50/80 border border-gray-100 rounded-3xl p-5 flex items-start gap-4 mb-8 group cursor-pointer hover:bg-gray-50 transition-colors" @click="form.sendInvite = !form.sendInvite">
            <div class="pt-0.5">
              <div class="w-6 h-6 rounded bg-white border-2 flex items-center justify-center transition-colors" :class="form.sendInvite ? 'bg-blue-600 border-blue-600' : 'border-gray-300 group-hover:border-blue-400'">
                <svg v-if="form.sendInvite" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
              </div>
            </div>
            <div>
               <h4 class="text-[15px] font-bold text-gray-900 mb-0.5">Enviar invitación por correo</h4>
               <p class="text-[13px] text-gray-500 font-medium">Se enviará un enlace automático para que el usuario configure su contraseña.</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-50">
             <button type="button" @click="$router.push('/admin/users')" :disabled="isSubmitting" class="px-6 py-3 rounded-xl font-bold text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors disabled:opacity-50">
               Cancelar
             </button>
             <button type="button" @click="submitForm" :disabled="isSubmitting" class="px-8 py-3 rounded-xl font-bold text-sm text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 active:scale-[0.98] transition-all flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
               <svg v-if="isSubmitting" class="animate-spin -ml-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
               <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path></svg>
               {{ isSubmitting ? 'Registrando...' : 'Registrar Usuario' }}
             </button>
          </div>

        </div>

        <!-- Info Alert -->
        <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-5 flex items-start gap-4 shadow-sm mb-12">
           <div class="mt-0.5 shrink-0 bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold text-xs">
             i
           </div>
           <div>
             <h4 class="text-[13px] font-bold text-blue-900 mb-1">Nota importante:</h4>
             <p class="text-[13px] text-blue-800/80 leading-relaxed font-medium">Al asignar el rol de <strong class="font-bold">Súper Admin</strong>, el usuario tendrá acceso total a la configuración global del sistema y a todas las escuelas registradas. Maneje estos permisos con precaución.</p>
           </div>
        </div>

      </div>

      <!-- Footer Policy -->
      <footer class="text-center pb-8 pt-4 border-t border-gray-200 max-w-4xl mx-auto w-full">
        <div class="flex items-center justify-center gap-2 text-gray-400 font-medium text-sm mb-4">
           <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
           Panel de Administración Segura
        </div>
        <div class="flex items-center justify-center gap-6 text-[13px] font-bold text-blue-500 mb-6">
          <a href="#" class="hover:text-blue-700 transition-colors">Términos de Servicio</a>
          <a href="#" class="hover:text-blue-700 transition-colors">Política de Privacidad</a>
          <a href="#" class="hover:text-blue-700 transition-colors">Soporte Técnico</a>
        </div>
        <p class="text-xs font-semibold text-gray-500">© 2024 Sistema de Control Escolar Integral. Todos los derechos reservados.</p>
      </footer>

    </main>

  </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import api from '@/services/api';

const router = useRouter();

const isSubmitting = ref(false);

const form = ref({
  name: '',
  email: '',
  school_id: '',
  role: '',
  sendInvite: true
});

const schools = ref<any[]>([]);

const fetchSchools = async () => {
  try {
    const res = await api.get('/admin/schools');
    if (res.data.success) {
      schools.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching schools for dropdown', error);
  }
};

const submitForm = async () => {
  if (!form.value.name || !form.value.email || !form.value.role) {
    alert('Por favor, completa al menos Nombre, Correo y Rol.');
    return;
  }

  try {
    isSubmitting.value = true;
    const response = await api.post('/admin/users', form.value);
    
    if (response.data.success) {
      alert('¡Usuario invitado exitosamente!');
      router.push('/admin/users');
    }
  } catch (error: any) {
    console.error('Error al invitar usuario:', error);
    if (error.response?.data?.errors?.email) {
       alert('Ese correo ya se encuentra registrado en el sistema.');
    } else {
       alert(error.response?.data?.message || 'Error al conectar con el servidor.');
    }
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchSchools();
});
</script>
