<template>
  <ion-page class="bg-[#F8F9FA]">
    <div class="min-h-screen flex flex-col bg-[#F8F9FA] font-sans">
      
      <!-- Top Navigation Header -->
      <header class="bg-[#F8F9FA] pt-6 pb-2 border-b-0 flex-none sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
          <div class="flex items-center gap-2 text-sm font-bold text-gray-500">
             <button @click="$router.push('/parent/dashboard')" class="hover:text-gray-900 transition-colors">Autorizaciones</button>
             <ion-icon :icon="chevronForwardOutline" class="text-xs"></ion-icon>
             <span class="text-gray-900">Nueva Persona Autorizada</span>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <ion-content :fullscreen="true" class="ion-padding" style="--background: transparent; --ion-background-color: transparent;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 pb-12">
            
            <!-- Page Title -->
            <div class="mb-8">
               <h1 class="text-3xl font-black text-[#1B254B] tracking-tight mb-2">
                  Autorizar Recogida
               </h1>
               <p class="text-[#8F9BBA] font-medium text-base">
                  Complete los datos de la persona autorizada para retirar al estudiante de forma segura.
               </p>
            </div>

            <!-- Form Container (Grid 2 columns) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
               
               <!-- Left Column: Photo Area (Col Span 4 / 5) -->
               <div class="md:col-span-5 lg:col-span-4">
                  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full flex flex-col">
                     <h2 class="text-lg font-black text-[#1B254B] mb-3">Foto de Verificación</h2>
                     <p class="text-sm font-medium text-[#8F9BBA] mb-6 leading-relaxed">
                        Esta foto será utilizada por el personal de seguridad en el quiosco de entrada.
                     </p>

                     <!-- Dropzone -->
                     <div class="relative flex-grow min-h-[220px] mb-6">
                        <input 
                           type="file" 
                           ref="photoInput" 
                           accept="image/jpeg, image/png" 
                           class="hidden" 
                           @change="handleFileUpload"
                        >
                        <button 
                           @click="triggerFileInput"
                           class="w-full h-full bg-[#F4F7FE] border-2 border-dashed border-[#E0E5F2] hover:border-brand-blue hover:bg-blue-50/50 transition-colors rounded-2xl flex flex-col items-center justify-center p-6 group relative overflow-hidden"
                           :class="{ 'border-transparent bg-transparent': photoPreview }"
                        >
                           
                           <!-- Default State -->
                           <div v-show="!photoPreview" class="flex flex-col items-center">
                              <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-sm text-brand-blue mb-4 relative z-10 group-hover:scale-110 transition-transform">
                                 <ion-icon :icon="camera" class="text-2xl"></ion-icon>
                                 <!-- Plus badge -->
                                 <div class="absolute -top-1 -right-1 bg-white rounded-full p-0.5">
                                    <ion-icon :icon="addCircle" class="text-xs"></ion-icon>
                                 </div>
                              </div>
                              <span class="text-sm font-black text-[#1B254B] mb-1 relative z-10">Subir o Tomar Foto</span>
                              <span class="text-[11px] font-bold text-[#A3AED0] relative z-10">Formatos JPG, PNG (Máx 5MB)</span>
                           </div>

                           <!-- Preview State -->
                           <div v-show="photoPreview" class="absolute inset-0 w-full h-full">
                              <img :src="photoPreview || undefined" alt="Preview" class="w-full h-full object-cover rounded-2xl">
                              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-2xl">
                                 <div class="bg-white/90 px-4 py-2 rounded-full text-sm font-bold text-gray-900 flex items-center gap-2">
                                    <ion-icon :icon="camera"></ion-icon>
                                    Cambiar Foto
                                 </div>
                              </div>
                           </div>
                        </button>
                     </div>

                     <!-- Warning Alert -->
                     <div class="bg-[#FFF9E5] rounded-xl p-4 flex items-start gap-3 border border-[#FBECC5]">
                        <ion-icon :icon="warning" class="text-[#F59E0B] mt-0.5 flex-shrink-0 text-lg"></ion-icon>
                        <p class="text-[12px] font-semibold text-[#9E6A00] leading-tight">
                           Asegúrese de que el rostro sea claramente visible y sin accesorios que cubran la cara.
                        </p>
                     </div>
                  </div>
               </div>

               <!-- Right Column: Form Fields (Col Span 8 / 7) -->
               <div class="md:col-span-7 lg:col-span-8">
                  <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 h-full flex flex-col">
                     
                     <!-- Nombre Completo -->
                     <div class="mb-6">
                        <label class="block text-sm font-black text-[#1B254B] mb-2">Nombre Completo</label>
                        <input type="text" v-model="form.name" placeholder="Ej. Juan Pérez" class="w-full bg-white border border-[#E0E5F2] hover:border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-blue-100 rounded-xl px-4 py-3.5 text-sm font-medium text-gray-900 placeholder-gray-400 outline-none transition-all">
                     </div>

                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <!-- Parentesco -->
                        <div>
                           <label class="block text-sm font-black text-[#1B254B] mb-2">Parentesco / Relación</label>
                           <div class="relative">
                              <select v-model="form.relationship" class="w-full bg-white border border-[#E0E5F2] hover:border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-blue-100 rounded-xl px-4 py-3.5 text-sm font-medium text-gray-900 placeholder-gray-400 outline-none transition-all appearance-none cursor-pointer">
                                 <option value="" disabled selected>Seleccione una opción</option>
                                 <option value="padre">Padre</option>
                                 <option value="madre">Madre</option>
                                 <option value="abuelo">Abuelo(a)</option>
                                 <option value="tio">Tío(a)</option>
                                 <option value="hermano">Hermano(a) Mayor</option>
                                 <option value="otro">Otro Familiar / Tutor</option>
                              </select>
                              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                 <ion-icon :icon="chevronDownOutline" class="text-lg"></ion-icon>
                              </div>
                           </div>
                        </div>

                        <!-- Telefono -->
                        <div>
                           <label class="block text-sm font-black text-[#1B254B] mb-2">Teléfono de Contacto</label>
                           <input type="tel" v-model="form.phone" placeholder="10 digitos" class="w-full bg-white border border-[#E0E5F2] hover:border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-blue-100 rounded-xl px-4 py-3.5 text-sm font-medium text-gray-900 placeholder-gray-400 outline-none transition-all">
                        </div>
                     </div>

                     <!-- Estudiante a Recoger -->
                     <div class="mb-8">
                        <label class="block text-sm font-black text-[#1B254B] mb-2">Estudiante a Recoger</label>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                           <!-- Select Input styling -->
                           <div class="relative flex-grow sm:w-1/2">
                              <select @change="handleStudentSelect" :value="''" class="w-full bg-white border border-[#E0E5F2] hover:border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-blue-100 rounded-xl px-4 py-3.5 text-sm font-medium text-gray-900 placeholder-gray-400 outline-none transition-all appearance-none cursor-pointer">
                                 <option value="" disabled>{{ selectedStudents.length === 0 ? 'Todos los estudiantes' : 'Seleccione un estudiante...' }}</option>
                                 <option value="all" v-if="selectedStudents.length > 0">Autorizar a todos</option>
                                 <option v-for="student in availableStudents" :key="student.id" :value="student.id">
                                    {{ student.full_name }} - {{ student.classroom_label }}
                                 </option>
                              </select>
                              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                 <ion-icon :icon="chevronDownOutline" class="text-lg"></ion-icon>
                              </div>
                           </div>

                           <!-- Tags area mimicking selection -->
                           <div class="flex-grow sm:w-1/2 flex items-center flex-wrap gap-2">
                              <span v-if="selectedStudents.length === 0" class="inline-flex items-center gap-2 bg-[#E1F0FF] text-brand-blue border border-brand-blue/20 px-4 py-2.5 rounded-full text-xs font-black">
                                 Todos los estudiantes
                              </span>
                              
                              <span v-for="student in selectedStudents" :key="student.id" class="inline-flex items-center gap-2 bg-[#E1F0FF] text-brand-blue border border-brand-blue/20 px-4 py-2.5 rounded-full text-xs font-black">
                                 {{ student.full_name }}
                                 <button @click="removeStudent(student.id)" class="hover:bg-blue-200/50 rounded-full p-0.5 transition-colors text-brand-blue">
                                    <ion-icon :icon="closeOutline" class="text-sm"></ion-icon>
                                 </button>
                              </span>
                           </div>
                        </div>
                     </div>
                     
                     <!-- Divider -->
                     <hr class="border-t border-[#E0E5F2] mb-6 border-dashed">

                     <!-- Actions -->
                     <div class="mt-auto flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-6 items-center">
                        <button @click="$router.push('/parent/dashboard')" class="w-full sm:w-auto font-black text-gray-500 hover:text-gray-700 px-6 py-3.5 transition-colors text-sm">
                           Cancelar
                        </button>
                        <button @click="saveAuthorization" :disabled="isSaving" class="w-full sm:w-auto bg-[#1875FF] hover:bg-blue-600 text-white font-black py-3.5 px-8 rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2 text-sm focus:ring-4 focus:ring-blue-200 disabled:opacity-50 disabled:cursor-not-allowed">
                           <ion-icon :icon="saveOutline" class="text-lg"></ion-icon>
                           {{ isSaving ? 'Guardando...' : 'Guardar Autorización' }}
                        </button>
                     </div>

                  </div>
               </div>

            </div>

            <!-- Bottom Security Banner -->
            <div class="mt-8 bg-[#F4F7FE] border-2 border-[#E0E5F2] rounded-2xl p-5 flex items-start sm:items-center gap-4">
               <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-brand-blue flex-shrink-0 shadow-sm border border-white">
                  <ion-icon :icon="shieldCheckmark" class="text-xl"></ion-icon>
               </div>
               <div>
                  <h4 class="text-sm font-black text-[#1B254B] mb-0.5">Proceso de Seguridad Encriptado</h4>
                  <p class="text-[13px] font-medium text-[#8F9BBA]">
                     Sus datos y fotos están protegidos mediante protocolos de seguridad avanzados.
                  </p>
               </div>
            </div>

        </div>
      </ion-content>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  chevronForwardOutline, 
  camera, 
  addCircle, 
  warning, 
  chevronDownOutline, 
  closeOutline, 
  saveOutline,
  shieldCheckmark
} from 'ionicons/icons';
import api from '@/services/api';

const students = ref<any[]>([]);
const selectedStudents = ref<any[]>([]);
const photoInput = ref<HTMLInputElement | null>(null);
const photoPreview = ref<string | null>(null);

const form = ref({
   name: '',
   relationship: '',
   phone: ''
});
const isSaving = ref(false);

const availableStudents = computed(() => {
   return students.value.filter(s => !selectedStudents.value.some(sel => sel.id === s.id));
});

const handleStudentSelect = (event: Event) => {
   const target = event.target as HTMLSelectElement;
   const value = target.value;
   if (value === 'all') {
      selectedStudents.value = [];
   } else if (value !== '') {
      const studentId = parseInt(value);
      const student = students.value.find(s => s.id === studentId);
      if (student && !selectedStudents.value.some(s => s.id === studentId)) {
         selectedStudents.value.push(student);
      }
   }
   target.value = ''; // Reset select
};

const removeStudent = (studentId: number) => {
   selectedStudents.value = selectedStudents.value.filter(s => s.id !== studentId);
};

const triggerFileInput = () => {
   if (photoInput.value) {
      photoInput.value.click();
   }
};

const handleFileUpload = (event: Event) => {
   const target = event.target as HTMLInputElement;
   const file = target.files?.[0];

   if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
         photoPreview.value = e.target?.result as string;
      };
      reader.readAsDataURL(file);
   }
};

import { useRouter } from 'vue-router';
const router = useRouter();

const saveAuthorization = async () => {
   if (!form.value.name || !form.value.relationship) {
      alert("Por favor completa el nombre y el parentesco.");
      return;
   }

   isSaving.value = true;
   try {
      const formData = new FormData();
      formData.append('name', form.value.name);
      formData.append('relationship', form.value.relationship);
      if (form.value.phone) formData.append('phone', form.value.phone);

      const file = photoInput.value?.files?.[0];
      if (file) {
         formData.append('photo', file);
      }

      let studentIds = [];
      if (selectedStudents.value.length === 0) {
         // Autorizar a todos
         studentIds = students.value.map(s => s.id);
      } else {
         studentIds = selectedStudents.value.map(s => s.id);
      }
      formData.append('student_ids', JSON.stringify(studentIds));

      const res = await api.post('/parent/authorized-persons', formData, {
         headers: {
            'Content-Type': 'multipart/form-data'
         }
      });

      if (res.data.success) {
         router.push('/parent/dashboard');
      }
   } catch (error: any) {
      console.error('Error saving authorization:', error);
      alert(error.response?.data?.message || 'Error al guardar la autorización.');
   } finally {
      isSaving.value = false;
   }
};

onMounted(async () => {
   try {
      const res = await api.get('/parent/students');
      if (res.data.success) {
         students.value = res.data.data;
      }
   } catch (error) {
      console.error('Error fetching students:', error);
   }
});
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}
</style>
