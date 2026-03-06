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
              <h1 class="text-2xl font-black text-gray-900 tracking-tight leading-none mb-1">Detalle del Estudiante</h1>
              <p class="text-[11px] text-gray-400 font-bold tracking-widest uppercase">Información Completa</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <button @click="$router.push(`/admin/students/${route.params.id}/edit`)" class="bg-brand-blue text-white font-bold py-2.5 px-6 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 flex items-center gap-2 transition-all">
              <ion-icon :icon="createOutline" class="text-lg"></ion-icon>
              Editar
            </button>
            <button @click="$router.back()" class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-6 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center gap-2 transition-all">
              <ion-icon :icon="arrowBackOutline" class="text-lg"></ion-icon>
              Volver
            </button>
          </div>
        </header>

        <main class="flex-1 overflow-y-auto p-10">
          <!-- Loading -->
          <div v-if="isLoading" class="max-w-4xl mx-auto bg-white rounded-[32px] shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-12 h-12 border-4 border-blue-100 border-t-brand-blue rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-400 font-bold uppercase tracking-widest text-[11px]">Cargando información del estudiante...</p>
          </div>

          <!-- Detail Content -->
          <div v-else-if="student" class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- Sidebar: Photo & Quick Info -->
            <div class="space-y-8">
              <!-- Photo -->
              <div class="bg-white p-8 rounded-[40px] shadow-sm border border-gray-100 text-center">
                <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-6">Fotografía Oficial</h3>
                <div class="w-48 h-48 rounded-[40px] bg-gray-50 border-2 border-gray-200 flex items-center justify-center overflow-hidden mx-auto mb-4">
                  <img v-if="student.photo_url" :src="student.photo_url" class="w-full h-full object-cover">
                  <div v-else class="flex flex-col items-center gap-2 text-gray-300">
                    <ion-icon :icon="personOutline" class="text-6xl"></ion-icon>
                    <span class="text-[10px] font-black uppercase tracking-widest">Sin Imagen</span>
                  </div>
                </div>
                <p class="text-lg font-black text-gray-900 leading-tight">{{ student.first_name }} {{ student.last_name }}</p>
                <p class="text-[13px] font-bold text-brand-blue tracking-tight mt-1">{{ student.enrollment_code }}</p>
              </div>

              <!-- System Info Card -->
              <div class="bg-indigo-600 p-8 rounded-[40px] shadow-lg shadow-indigo-200 text-white relative overflow-hidden">
                <div class="relative z-10">
                  <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-4">Estado del Alumno</h4>
                  <div class="space-y-4">
                    <div>
                      <p class="text-[11px] font-bold opacity-60 mb-1">TURNO</p>
                      <p class="font-black tracking-tight capitalize">{{ student.classroom?.shift || 'Sin asignar' }}</p>
                    </div>
                    <div>
                      <p class="text-[11px] font-bold opacity-60 mb-1">NIVEL</p>
                      <p class="font-black tracking-tight">{{ student.classroom?.school_level || 'Sin definir' }}</p>
                    </div>
                    <div>
                      <p class="text-[11px] font-bold opacity-60 mb-1">FECHA DE REGISTRO</p>
                      <p class="font-black tracking-tight">{{ formatDate(student.created_at) }}</p>
                    </div>
                  </div>
                </div>
                <ion-icon :icon="school" class="absolute -right-6 -bottom-6 text-8xl opacity-10 rotate-12"></ion-icon>
              </div>
            </div>

            <!-- Main Detail Content -->
            <div class="lg:col-span-2 space-y-8">

              <!-- Personal Info -->
              <section class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 space-y-6">
                <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                  <div class="w-1.5 h-6 bg-brand-blue rounded-full"></div>
                  Información Personal
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nombre(s)</p>
                    <p class="text-[16px] font-bold text-gray-900">{{ student.first_name }}</p>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-1">Apellidos</p>
                    <p class="text-[16px] font-bold text-gray-900">{{ student.last_name }}</p>
                  </div>
                </div>

                <div>
                  <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-1">Matrícula / Código</p>
                  <p class="text-[16px] font-black text-brand-blue tracking-wider">{{ student.enrollment_code }}</p>
                </div>
              </section>

              <!-- Academic Info -->
              <section class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 space-y-6">
                <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                  <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                  Asignación Académica
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                  <div class="bg-gray-50 p-5 rounded-2xl text-center">
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Nivel</p>
                    <p class="text-xl font-black text-gray-900">{{ student.classroom?.school_level || '—' }}</p>
                  </div>
                  <div class="bg-gray-50 p-5 rounded-2xl text-center">
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Grado</p>
                    <p class="text-xl font-black text-gray-900">{{ student.classroom?.grade }}º</p>
                  </div>
                  <div class="bg-gray-50 p-5 rounded-2xl text-center">
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Grupo</p>
                    <p class="text-xl font-black text-gray-900">{{ student.classroom?.group_letter }}</p>
                  </div>
                </div>

                <div class="bg-indigo-50 px-5 py-4 rounded-2xl border border-indigo-100">
                  <div class="flex items-center gap-3">
                    <span class="text-[12px] font-black text-indigo-600 uppercase tracking-widest">Turno:</span>
                    <span class="font-black text-indigo-700 capitalize">{{ student.classroom?.shift || 'Sin asignar' }}</span>
                  </div>
                </div>
              </section>

              <!-- Family Context -->
              <section class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 space-y-6">
                <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                  <div class="w-1.5 h-6 bg-orange-500 rounded-full"></div>
                  Vinculación Familiar
                </h3>

                <div class="space-y-4">
                  <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center shrink-0 mt-0.5">
                      <ion-icon :icon="mailOutline" class="text-orange-500 text-lg"></ion-icon>
                    </div>
                    <div>
                      <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Email Tutor Principal</p>
                      <p class="text-[15px] font-bold text-gray-900">{{ student.tutor_email || 'Sin vincular' }}</p>
                    </div>
                  </div>
                  <div v-if="student.secondary_tutor_email" class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center shrink-0 mt-0.5">
                      <ion-icon :icon="mailOutline" class="text-orange-500 text-lg"></ion-icon>
                    </div>
                    <div>
                      <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Email Secundario</p>
                      <p class="text-[15px] font-bold text-gray-900">{{ student.secondary_tutor_email }}</p>
                    </div>
                  </div>
                </div>
              </section>

              <!-- Photo Status -->
              <section class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 space-y-6">
                <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                  <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                  Estado de Fotografía
                </h3>
                <div class="flex items-center gap-4">
                  <div v-if="student.photo_path" class="flex items-center gap-3 bg-emerald-50 px-5 py-3 rounded-2xl border border-emerald-100">
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span class="text-sm font-black text-emerald-700 uppercase tracking-widest">Fotografía Registrada</span>
                  </div>
                  <div v-else class="flex items-center gap-3 bg-orange-50 px-5 py-3 rounded-2xl border border-orange-100">
                    <div class="w-3 h-3 rounded-full bg-orange-400"></div>
                    <span class="text-sm font-black text-orange-600 uppercase tracking-widest">Fotografía Pendiente</span>
                  </div>
                </div>
              </section>

            </div>
          </div>

          <!-- Not Found -->
          <div v-else class="max-w-4xl mx-auto bg-white rounded-[32px] shadow-sm border border-gray-100 p-12 text-center">
            <p class="text-gray-400 font-medium">No se encontró el estudiante.</p>
            <button @click="$router.push('/admin/students')" class="mt-4 text-brand-blue font-bold text-sm hover:underline">Volver a la lista</button>
          </div>
        </main>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import {
  arrowBackOutline, createOutline, personOutline,
  school, mailOutline
} from 'ionicons/icons';
import api from '@/services/api';

const route = useRoute();

const isLoading = ref(true);
const student = ref<any>(null);

const formatDate = (dateStr: string) => {
  if (!dateStr) return 'Reciente';
  return new Date(dateStr).toLocaleDateString('es-MX', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
};

const fetchStudent = async () => {
  try {
    isLoading.value = true;
    const res = await api.get(`/admin/students/${route.params.id}`);
    if (res.data.success) {
      student.value = res.data.data;
    }
  } catch (error) {
    console.error('Error cargando estudiante', error);
    student.value = null;
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchStudent);
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}
</style>
