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
            <span class="text-gray-900 font-bold">{{ isEditMode ? 'Edición de Usuario' : 'Nuevo Usuario Simplificado' }}</span>
          </nav>

          <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">{{ isEditMode ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}</h1>
          <p class="text-gray-600 font-medium text-[15px]">{{ isEditMode ? 'Modifique los atributos y permisos del integrante del sistema.' : 'Complete la información para registrar un nuevo integrante en el sistema.' }}</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden mb-6 p-8">
          
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
             <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-900 mb-2">Escuelas Asignadas</label>
                <div class="relative mb-4">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                  </div>
                  <select v-model="selectedSchoolToAdd" @change="addSchool" class="w-full pl-11 pr-10 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white appearance-none cursor-pointer">
                    <option value="" disabled>Buscar y seleccionar escuela ({{ schools.length }}+ registradas)...</option>
                    <option v-for="school in availableSchools" :key="school.id" :value="school.id">{{ school.name }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
                </div>

                <div class="flex flex-col sm:flex-row flex-wrap gap-3 p-4 border border-dashed border-gray-200 rounded-xl bg-gray-50/50">
                  <div v-for="id in form.school_ids" :key="id" class="flex items-center gap-3 bg-white border border-gray-200 px-4 py-3 rounded-xl shadow-sm min-w-[200px] justify-between">
                    <div>
                       <p class="text-[13px] font-bold text-gray-900 leading-tight">{{ getSchoolName(id) }}</p>
                    </div>
                    <button type="button" @click="removeSchool(id)" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                  </div>
                  <div v-if="form.school_ids.length > 0" class="flex items-center justify-center bg-gray-50/50 border border-dashed border-gray-200 px-4 py-3 rounded-xl min-w-[200px]">
                     <p class="text-[13px] text-gray-500 font-medium italic">Escuelas asignadas: {{ form.school_ids.length }}</p>
                  </div>
                  <div v-if="form.school_ids.length === 0" class="text-sm text-gray-400 font-medium w-full text-center py-2">
                    No hay escuelas asignadas
                  </div>
                </div>
                <div class="mt-3 flex items-start gap-2 text-gray-500">
                  <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <p class="text-[13px] font-medium leading-tight">El usuario podrá gestionar todas las instituciones que aparezcan en esta lista.</p>
                </div>
             </div>

             <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-900 mb-2">Rol del Sistema</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                  </div>
                  <select v-model="form.role" class="w-full pl-11 pr-10 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-gray-700 bg-white appearance-none cursor-pointer">
                    <option value="" disabled>Seleccione un rol</option>
                    <option value="super_admin">Súper Admin / Sistemas</option>
                    <option value="director">Administrador (Director)</option>
                    <option value="teacher">Profesor</option>
                    <option value="parent">Padre / Tutor</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
                </div>
             </div>
          </div>

          <!-- Resend Email Button (Edit Mode Only) -->
          <div v-if="isEditMode" class="bg-blue-50/50 border border-blue-100 rounded-3xl p-5 flex items-center justify-between gap-4 mb-8">
            <div>
               <h4 class="text-[15px] font-bold text-gray-900 mb-0.5">Reenviar correo de bienvenida</h4>
               <p class="text-[13px] text-gray-500 font-medium">Si el usuario no recibió sus accesos, puedes enviárselos nuevamente.</p>
            </div>
            <button type="button" @click="resendWelcomeEmail" :disabled="isResending" class="px-5 py-2.5 rounded-xl font-bold text-sm text-brand-blue bg-blue-100 hover:bg-blue-200 transition-colors flex items-center gap-2 disabled:opacity-50">
              <ion-icon name="mail-outline" class="text-lg"></ion-icon>
              {{ isResending ? 'Enviando...' : 'Reenviar Correo' }}
            </button>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-50">
             <button type="button" @click="$router.push('/admin/users')" :disabled="isSubmitting" class="px-6 py-3 rounded-xl font-bold text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors disabled:opacity-50">
               Cancelar
             </button>
             <button type="button" @click="submitForm" :disabled="isSubmitting" class="px-8 py-3 rounded-xl font-bold text-sm text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 active:scale-[0.98] transition-all flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
               <svg v-if="isSubmitting" class="animate-spin -ml-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
               <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path></svg>
               {{ isSubmitting ? 'Guardando...' : (isEditMode ? 'Actualizar Usuario' : 'Registrar Usuario') }}
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
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import api from '@/services/api';

const router = useRouter();
const route = useRoute();

const isSubmitting = ref(false);
const isLoadingData = ref(false);

const isEditMode = computed(() => {
  return !!route.params.id;
});

const form = ref({
  name: '',
  email: '',
  school_ids: [] as number[],
  role: ''
});


const schools = ref<any[]>([]);

const selectedSchoolToAdd = ref<number | ''>('');

const availableSchools = computed(() => {
  return schools.value.filter(s => !form.value.school_ids.includes(s.id));
});

const addSchool = () => {
  if (selectedSchoolToAdd.value !== '' && !form.value.school_ids.includes(selectedSchoolToAdd.value as number)) {
    form.value.school_ids.push(selectedSchoolToAdd.value as number);
    selectedSchoolToAdd.value = '';
  }
};

const removeSchool = (id: number) => {
  form.value.school_ids = form.value.school_ids.filter(sid => sid !== id);
};

const getSchoolName = (id: number) => {
  const school = schools.value.find(s => s.id === id);
  return school ? school.name : '';
};

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

const isResending = ref(false);

const resendWelcomeEmail = async () => {
  isResending.value = true;
  try {
    const res = await api.post(`/admin/users/${route.params.id}/resend-welcome`);
    if (res.data.success) {
      alert('Correo reenviado exitosamente.');
    }
  } catch (error: any) {
    console.error('Error al reenviar correo', error);
    alert(error.response?.data?.message || 'Hubo un error al reenviar el correo.');
  } finally {
    isResending.value = false;
  }
};

const submitForm = async () => {
  if (!form.value.name || !form.value.email || !form.value.role) {
    alert('Por favor, completa al menos Nombre, Correo y Rol.');
    return;
  }

  try {
    isSubmitting.value = true;
    let response;
    
    if (isEditMode.value) {
        response = await api.put(`/admin/users/${route.params.id}`, form.value);
    } else {
        response = await api.post('/admin/users', form.value);
    }
    
    if (response.data.success) {
      alert(isEditMode.value ? '¡Usuario actualizado exitosamente!' : '¡Usuario invitado exitosamente!');
      router.push('/admin/users');
    }
  } catch (error: any) {
    console.error('Error al guardar el usuario:', error);
    if (error.response?.data?.errors) {
       const errors = error.response.data.errors;
       const errorMessages = Object.values(errors).flat().join('\n');
       alert('Errores de validación:\n' + errorMessages);
    } else {
       alert(error.response?.data?.message || 'Error al conectar con el servidor.');
    }
  } finally {
    isSubmitting.value = false;
  }
};

const loadUserData = async () => {
  if (!isEditMode.value) return;
  
  try {
    isLoadingData.value = true;
    const res = await api.get(`/admin/users/${route.params.id}`);
    if (res.data.success) {
      const data = res.data.data;
      form.value.name = data.name;
      form.value.email = data.email;
      form.value.role = data.role;
      form.value.school_ids = data.schools ? data.schools.map((s: any) => s.id) : (data.school_id ? [data.school_id] : []);
    }
  } catch (error) {
    console.error('Error cargando los datos del usuario', error);
    alert('Este usuario no pudo ser encontrado.');
    router.push('/admin/users');
  } finally {
    isLoadingData.value = false;
  }
};

onMounted(() => {
  fetchSchools();
  loadUserData();
});
</script>
