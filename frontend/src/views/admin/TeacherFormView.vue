<template>
  <ion-page>
    <div class="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto w-full h-full flex flex-col">
      
      <!-- Top Navigation / Breadcrumbs -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <div class="flex items-center gap-2 text-sm font-bold text-gray-400 mb-2">
            <router-link to="/admin/teachers" class="hover:text-brand-blue transition-colors">Profesores</router-link>
            <ion-icon :icon="chevronForward" class="text-xs"></ion-icon>
            <span class="text-gray-900">Alta de Profesor</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight leading-none">Formulario Alta de Profesor</h1>
          <p class="text-sm font-medium text-gray-500 mt-1">Complete la información para registrar un nuevo docente en el sistema.</p>
        </div>
        <router-link to="/admin/teachers" class="hidden sm:flex px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition-all">
          Cancelar
        </router-link>
      </div>

      <!-- Main Form Card -->
      <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex-grow flex flex-col">
        <div class="p-6 sm:p-8 flex-grow overflow-y-auto">
          
          <!-- Seccion: Información General -->
          <div class="mb-10">
            <h3 class="flex items-center gap-2 text-base font-black text-gray-900 mb-4">
              <ion-icon :icon="person" class="text-brand-blue text-xl"></ion-icon>
              Información General
            </h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 ml-1">Nombre Completo</label>
                <input 
                  v-model="name"
                  type="text" 
                  placeholder="Ej. Ana María García López" 
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue focus:bg-white transition-all font-medium text-gray-900"
                >
              </div>
              
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 ml-1">Correo Electrónico</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <ion-icon :icon="mail" class="text-gray-400 text-lg"></ion-icon>
                  </div>
                  <input 
                    v-model="email"
                    type="email" 
                    placeholder="maestro@escuela.edu.mx" 
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue focus:bg-white transition-all font-medium text-gray-900"
                  >
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 ml-1">Matrícula / Código QR (Opcional)</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <ion-icon :icon="barcodeOutline" class="text-gray-400 text-lg"></ion-icon>
                  </div>
                  <input 
                    v-model="enrollment_code"
                    type="text" 
                    placeholder="Ej. PROF-24-XT90" 
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue focus:bg-white transition-all font-medium text-gray-900 font-mono"
                  >
                </div>
                <p class="text-[10px] font-bold text-gray-400 mt-1.5 ml-1 flex items-center gap-1">
                  <ion-icon :icon="informationCircleOutline" class="text-xs"></ion-icon>
                  Se generará automáticamente si se deja vacío.
                </p>
              </div>
            </div>
          </div>

          <hr class="border-gray-100 mb-8">

          <!-- Seccion: Grados y Grupos Asignados -->
          <div class="mb-10">
            <h3 class="flex items-center gap-2 text-base font-black text-gray-900 mb-1">
              <ion-icon :icon="people" class="text-brand-blue text-xl"></ion-icon>
              Grados y Grupos Asignados
            </h3>
            <p class="text-[11px] font-bold text-gray-400 mb-5 ml-7">Seleccionar combinaciones. Puede elegir múltiples grados y grupos para este profesor.</p>
            
            <!-- Grid selector de grupos -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 ml-0 sm:ml-7">
              <button 
                v-for="grupo in classrooms" 
                :key="grupo.id"
                @click="toggleGrupo(grupo.id)"
                class="px-4 py-3 rounded-xl border text-sm font-bold transition-all text-center flex flex-col items-center justify-center"
                :class="selectedGrupos.includes(grupo.id) 
                  ? 'bg-blue-50 border-brand-blue/30 text-brand-blue shadow-inner' 
                  : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50 shadow-sm'"
              >
                <span>{{ grupo.grade }}º {{ grupo.group_letter }}</span>
                <span class="text-[10px] font-normal opacity-75 mt-0.5">{{ grupo.school_level }}</span>
              </button>
            </div>
          </div>

          <hr class="border-gray-100 mb-8">

          <!-- Seccion: Acceso al Sistema -->
          <div class="mb-4">
            <h3 class="flex items-center gap-2 text-base font-black text-gray-900 mb-4">
              <ion-icon :icon="shieldCheckmark" class="text-brand-blue text-xl"></ion-icon>
              Acceso al Sistema
            </h3>
            
            <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 sm:p-5 flex items-center justify-between ml-0 sm:ml-7">
              <div>
                <h4 class="text-sm font-bold text-gray-900">Acceso Activo</h4>
                <p class="text-[11px] font-bold text-gray-500 mt-0.5">Permitir que el profesor inicie sesión inmediatamente.</p>
              </div>
              
              <!-- Custom Toggle Switch -->
              <button 
                @click="accesoActivo = !accesoActivo"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2 shrink-0"
                :class="accesoActivo ? 'bg-brand-blue' : 'bg-gray-200'"
              >
                <span 
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm"
                  :class="accesoActivo ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
            </div>
          </div>

        </div>
        
        <!-- Bottom Action Bar -->
        <div class="p-6 sm:p-8 bg-white border-t border-gray-100 mt-auto shrink-0 flex flex-col sm:flex-row gap-3">
          <router-link to="/admin/teachers" class="sm:hidden w-full text-center px-5 py-3.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-bold transition-all">
            Cancelar
          </router-link>
          <button 
            @click="saveTeacher"
            :disabled="isSaving"
            class="w-full flex justify-center items-center gap-2 bg-brand-blue hover:bg-blue-700 text-white px-5 py-3.5 rounded-xl text-sm font-bold shadow-sm shadow-blue-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <ion-icon :icon="saveOutline" class="text-lg"></ion-icon>
            {{ isSaving ? 'Guardando...' : 'Guardar Profesor' }}
          </button>
        </div>

      </div>

    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { IonPage, IonIcon } from '@ionic/vue';
import { 
  chevronForward, person, mail, people, shieldCheckmark, saveOutline, 
  barcodeOutline, informationCircleOutline
} from 'ionicons/icons';
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '@/services/api';

const router = useRouter();
const route = useRoute();

interface Classroom {
  id: number;
  school_level: string;
  grade: string;
  group_letter: string;
  shift: string;
}

const classrooms = ref<Classroom[]>([]);
const name = ref('');
const email = ref('');
const enrollment_code = ref('');
const selectedGrupos = ref<number[]>([]);
const accesoActivo = ref(true);
const isSaving = ref(false);
const isEditing = ref(false);
const teacherId = ref<string | null>(null);

const toggleGrupo = (id: number) => {
  if (selectedGrupos.value.includes(id)) {
    selectedGrupos.value = selectedGrupos.value.filter(g => g !== id);
  } else {
    selectedGrupos.value.push(id);
  }
};

const saveTeacher = async () => {
  if (!name.value || !email.value) {
    alert('Por favor, ingresa el nombre y correo del profesor.');
    return;
  }

  isSaving.value = true;
  try {
    const payload = {
      name: name.value,
      email: email.value,
      enrollment_code: enrollment_code.value,
      is_active: accesoActivo.value,
      groups: selectedGrupos.value
    };

    let res;
    if (isEditing.value && teacherId.value) {
      res = await api.put(`/admin/teachers/${teacherId.value}`, payload);
    } else {
      res = await api.post('/admin/teachers', payload);
    }

    if (res.data.success) {
      router.push('/admin/teachers');
    }
  } catch (error: any) {
    console.error('Error saving teacher', error);
    alert(error.response?.data?.message || 'Hubo un error al guardar el profesor.');
  } finally {
    isSaving.value = false;
  }
};

onMounted(async () => {
  try {
    const resClassrooms = await api.get('/admin/classrooms');
    if (resClassrooms.data.success) {
      classrooms.value = resClassrooms.data.data;
    }
  } catch(e) { console.error('Error fetching classrooms', e); }

  const idParam = route.params.id;
  if (idParam && idParam !== 'create') {
    isEditing.value = true;
    teacherId.value = idParam as string;
    
    // Fetch teacher details placeholder (needs show endpoint in controller)
    // For now we will rely on a future endpoint, here's the structure
    try {
      const res = await api.get(`/admin/teachers/${teacherId.value}`);
      if (res.data.success) {
         const data = res.data.data;
         name.value = data.name;
         email.value = data.email;
         enrollment_code.value = data.enrollment_code || '';
         accesoActivo.value = data.is_active;
         
         // Select the assigned groups
         if(data.groups) {
           selectedGrupos.value = data.groups;
         }
      }
    } catch(e) { console.error(e); }
  }
});
</script>
