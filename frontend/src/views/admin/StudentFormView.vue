<template>
  <ion-page>
    <ion-content>
      <div class="h-full bg-gray-50 flex flex-col font-sans">
        
        <!-- Header -->
        <header class="bg-white border-b border-gray-100 py-6 px-10 flex justify-between items-center shrink-0 shadow-sm relative z-10">
          <div class="flex items-center gap-4">
            <button @click="$router.back()" class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-500 transition-all">
              <ion-icon :icon="arrowBackOutline" class="text-2xl"></ion-icon>
            </button>
            <div>
              <h1 class="text-2xl font-black text-gray-900 tracking-tight leading-none mb-1">
                {{ isEdit ? 'Editar Estudiante' : 'Nuevo Estudiante' }}
              </h1>
              <p class="text-[11px] text-gray-400 font-bold tracking-widest uppercase">Gestión de Matrícula</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
             <button @click="$router.back()" class="px-6 py-2.5 font-bold text-gray-500 hover:text-gray-900 transition-all">Cancelar</button>
             <button @click="saveStudent" :disabled="submitting" class="bg-brand-blue text-white font-black py-2.5 px-8 rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 disabled:opacity-50 transition-all flex items-center gap-2">
                <ion-icon v-if="!submitting" :icon="saveOutline" class="text-xl"></ion-icon>
                <div v-else class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                {{ submitting ? 'Guardando...' : 'Guardar Estudiante' }}
             </button>
          </div>
        </header>

        <main class="flex-1 overflow-y-auto p-10">
          <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- Sidebar: Photo & Status -->
            <div class="space-y-8">
              <!-- Photo Upload -->
              <div class="bg-white p-8 rounded-[40px] shadow-sm border border-gray-100 text-center">
                <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-6">Fotografía Oficial</h3>
                
                <div class="relative group mx-auto mb-6">
                  <div class="w-48 h-48 rounded-[40px] bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden transition-all group-hover:border-brand-blue">
                    <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover">
                    <div v-else class="flex flex-col items-center gap-2 text-gray-300 group-hover:text-brand-blue transition-colors">
                      <ion-icon :icon="personOutline" class="text-6xl"></ion-icon>
                      <span class="text-[10px] font-black uppercase tracking-widest">Sin Imagen</span>
                    </div>
                  </div>
                  
                  <button @click="triggerPhotoUpload" class="absolute -bottom-2 -right-2 w-12 h-12 bg-white rounded-2xl shadow-lg border border-gray-100 flex items-center justify-center text-brand-blue hover:bg-brand-blue hover:text-white transition-all">
                    <ion-icon :icon="cameraOutline" class="text-xl"></ion-icon>
                  </button>
                  <input type="file" ref="photoInput" class="hidden" accept="image/*" @change="handlePhotoChange">
                </div>
                
                <p class="text-[11px] text-gray-400 font-medium px-4 leading-relaxed">Las fotos deben estar centradas y con fondo claro para un mejor reconocimiento.</p>
                
                <button v-if="photoPreview" @click="removePhoto" class="mt-4 text-[11px] font-black text-red-400 hover:text-red-500 uppercase tracking-widest">Quitar Imagen</button>
              </div>

              <!-- Quick Info -->
              <div v-if="isEdit" class="bg-indigo-600 p-8 rounded-[40px] shadow-lg shadow-indigo-200 text-white relative overflow-hidden">
                <div class="relative z-10">
                  <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-4">Información de Sistema</h4>
                  <div class="space-y-4">
                    <div>
                      <p class="text-[11px] font-bold opacity-60 mb-1">FECHA DE REGISTRO</p>
                      <p class="font-black tracking-tight">{{ formatDate(form.created_at) }}</p>
                    </div>
                    <div>
                      <p class="text-[11px] font-bold opacity-60 mb-1">ÚLTIMO MOVIMIENTO</p>
                      <p class="font-black tracking-tight">Entrada - 08:15 AM</p>
                    </div>
                  </div>
                </div>
                <ion-icon :icon="school" class="absolute -right-6 -bottom-6 text-8xl opacity-10 rotate-12"></ion-icon>
              </div>
            </div>

            <!-- Form Content -->
            <div class="lg:col-span-2 space-y-8">
              
              <!-- Personal Info -->
              <section class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 space-y-8">
                <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                  <div class="w-1.5 h-6 bg-brand-blue rounded-full"></div>
                  Información Personal
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                  <div class="space-y-2">
                    <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Nombre(s) *</label>
                    <input v-model="form.first_name" type="text" class="w-full bg-gray-50 border-2 border-transparent focus:border-brand-blue focus:bg-white p-4 rounded-2xl outline-none font-bold text-gray-900 transition-all">
                  </div>
                  <div class="space-y-2">
                    <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Apellidos *</label>
                    <input v-model="form.last_name" type="text" class="w-full bg-gray-50 border-2 border-transparent focus:border-brand-blue focus:bg-white p-4 rounded-2xl outline-none font-bold text-gray-900 transition-all">
                  </div>
                </div>

                <div class="space-y-2">
                  <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Matrícula / Código de Identificación *</label>
                  <div class="relative">
                    <ion-icon :icon="cardOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></ion-icon>
                    <input v-model="form.enrollment_code" type="text" placeholder="Ej: 2024-ADM-001" class="w-full bg-gray-50 border-2 border-transparent focus:border-brand-blue focus:bg-white p-4 pl-12 rounded-2xl outline-none font-black text-brand-blue tracking-wider transition-all">
                  </div>
                </div>
              </section>

              <!-- Academic Info -->
              <section class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 space-y-8">
                <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                  <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                  Asignación Académica
                </h3>

                <div v-if="isAdmin" class="space-y-2">
                  <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Escuela Principal *</label>
                  <select v-model="form.school_id" class="w-full bg-indigo-50/50 p-4 rounded-2xl outline-none font-black text-indigo-900 appearance-none border-2 border-transparent focus:border-indigo-500 transition-all cursor-pointer">
                    <option disabled value="">Seleccione una escuela...</option>
                    <option v-for="school in userSchools" :key="school.id" :value="school.id">{{ school.name }}</option>
                  </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                  <div class="space-y-2">
                    <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Nivel</label>
                    <select v-model="form.school_level" class="w-full bg-gray-50 p-4 rounded-2xl outline-none font-bold text-gray-900 appearance-none border-2 border-transparent focus:border-indigo-500 transition-all">
                      <option value="Preescolar">Preescolar</option>
                      <option value="Primaria">Primaria</option>
                      <option value="Secundaria">Secundaria</option>
                      <option value="Preparatoria">Preparatoria</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Grado</label>
                    <select v-model="form.grade" class="w-full bg-gray-50 p-4 rounded-2xl outline-none font-bold text-gray-900 appearance-none border-2 border-transparent focus:border-indigo-500 transition-all">
                      <option v-for="g in 6" :key="g" :value="g">{{ g }}º</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Grupo</label>
                    <select v-model="form.group_letter" class="w-full bg-gray-50 p-4 rounded-2xl outline-none font-bold text-gray-900 appearance-none border-2 border-transparent focus:border-indigo-500 transition-all">
                      <option value="A">A</option>
                      <option value="B">B</option>
                      <option value="C">C</option>
                    </select>
                  </div>
                </div>

                <div class="space-y-4">
                   <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Turno</label>
                   <div class="flex gap-4">
                      <button 
                        @click="form.shift = 'matutino'"
                        :class="form.shift === 'matutino' ? 'bg-brand-blue text-white shadow-md' : 'bg-gray-50 text-gray-400'"
                        class="flex-1 py-4 rounded-2xl font-black uppercase text-[11px] tracking-widest transition-all"
                      >Matutino</button>
                      <button 
                         @click="form.shift = 'vespertino'"
                        :class="form.shift === 'vespertino' ? 'bg-brand-blue text-white shadow-md' : 'bg-gray-50 text-gray-400'"
                        class="flex-1 py-4 rounded-2xl font-black uppercase text-[11px] tracking-widest transition-all"
                      >Vespertino</button>
                   </div>
                </div>
              </section>

              <!-- Family Context -->
              <section class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 space-y-8">
                <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                  <div class="w-1.5 h-6 bg-orange-500 rounded-full"></div>
                  Vinculación Familiar (Alertas)
                </h3>

                <div class="space-y-6">
                  <div class="space-y-2">
                    <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Email Tutor Principal</label>
                    <div class="relative">
                      <ion-icon :icon="mailOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></ion-icon>
                      <input v-model="form.tutor_email" type="email" placeholder="ejemplo@correo.com" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500 focus:bg-white p-4 pl-12 rounded-2xl outline-none font-bold text-gray-900 transition-all">
                    </div>
                  </div>
                  <div class="space-y-2">
                    <label class="text-[12px] font-black text-gray-500 uppercase tracking-widest ml-1">Email Secundario (Opcional)</label>
                    <div class="relative">
                      <ion-icon :icon="mailOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></ion-icon>
                      <input v-model="form.secondary_tutor_email" type="email" placeholder="auxiliar@correo.com" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500 focus:bg-white p-4 pl-12 rounded-2xl outline-none font-bold text-gray-900 transition-all">
                    </div>
                  </div>
                </div>
              </section>
            </div>
          </div>
        </main>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  arrowBackOutline, saveOutline, personOutline, cameraOutline, 
  cardOutline, school, mailOutline 
} from 'ionicons/icons';
import api from '@/services/api';
import { storage } from '@/services/storage';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);
const submitting = ref(false);
const photoPreview = ref<string | null>(null);
const photoFile = ref<File | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);
const isAdmin = ref(false);
const userSchools = ref<any[]>([]);

const form = ref({
  school_id: '',
  first_name: '',
  last_name: '',
  enrollment_code: '',
  school_level: 'Primaria',
  grade: '1',
  group_letter: 'A',
  shift: 'matutino',
  tutor_email: '',
  secondary_tutor_email: '',
  created_at: ''
});

const handlePhotoChange = (e: any) => {
  const file = e.target.files[0];
  if (file) {
    photoFile.value = file;
    photoPreview.value = URL.createObjectURL(file);
  }
};

const removePhoto = () => {
  photoFile.value = null;
  photoPreview.value = null;
};

const triggerPhotoUpload = () => {
  photoInput.value?.click();
};

const fetchStudent = async () => {
  if (!isEdit.value) return;
  
  try {
    const res = await api.get(`/admin/students/${route.params.id}`);
    if (res.data.success) {
      const data = res.data.data;
      form.value = { ...data };
      if (data.classroom) {
        form.value.school_level = data.classroom.school_level;
        form.value.grade = data.classroom.grade;
        form.value.group_letter = data.classroom.group_letter;
        form.value.shift = data.classroom.shift;
      }
      if (data.school_id) {
        form.value.school_id = data.school_id;
      }
      if (data.photo_url) {
        photoPreview.value = data.photo_url;
      }
    }
  } catch (error) {
    console.error('Error fetching student', error);
  }
};

const saveStudent = async () => {
  if (!form.value.first_name || !form.value.last_name || !form.value.enrollment_code) {
    alert('Por favor completa los campos obligatorios.');
    return;
  }

  submitting.value = true;
  
  const formData = new FormData();
  Object.keys(form.value).forEach(key => {
    formData.append(key, (form.value as any)[key]);
  });
  
  if (photoFile.value) {
    formData.append('photo', photoFile.value);
  }

  try {
    if (isEdit.value) {
      // Laravel requires _method=PUT for FormData uploads
      formData.append('_method', 'PUT');
      await api.post(`/admin/students/${route.params.id}`, formData);
    } else {
      await api.post('/admin/students', formData);
    }
    router.push('/admin/students');
  } catch (error) {
    console.error('Error saving student', error);
    alert('Error al guardar el estudiante.');
  } finally {
    submitting.value = false;
  }
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return 'Reciente';
  return new Date(dateStr).toLocaleDateString('es-MX', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
};

onMounted(async () => {
  const user = await storage.get('auth_user');
  if (user && user.role === 'super_admin') {
    isAdmin.value = true;
  }
  const storedSchools = await storage.get('user_schools');
  if (storedSchools) {
    userSchools.value = storedSchools;
    if (!isEdit.value && isAdmin.value) {
      const currentId = await storage.get('current_school_id');
      form.value.school_id = currentId || storedSchools[0]?.id || '';
    }
  }

  // Fallback for Super Admin: always fetch master list of schools to guarantee the selector is populated
  if (isAdmin.value) {
    try {
      const res = await api.get('/admin/schools');
      if (res.data.success) {
        userSchools.value = res.data.data;
        if (!isEdit.value && !form.value.school_id && userSchools.value.length > 0) {
          form.value.school_id = userSchools.value[0].id;
        }
      }
    } catch (e) {
      console.warn("Could not fetch master schools list for form selector", e);
    }
  }
  
  fetchStudent();
});
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}
</style>
