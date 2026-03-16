<template>
  <ion-page>
    <div class="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto w-full h-full flex flex-col">

      <!-- Breadcrumbs -->
      <div class="flex items-center gap-2 text-sm font-bold text-gray-400 mb-6">
        <router-link to="/admin/teachers" class="hover:text-brand-blue transition-colors">Profesores</router-link>
        <ion-icon :icon="chevronForward" class="text-xs"></ion-icon>
        <span class="text-gray-900">{{ teacher?.name || 'Cargando...' }}</span>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex-grow flex items-center justify-center">
        <div class="w-10 h-10 border-4 border-gray-100 border-t-brand-blue rounded-full animate-spin"></div>
      </div>

      <!-- Content -->
      <div v-else-if="teacher" class="flex flex-col gap-6 flex-grow overflow-y-auto">

        <!-- Profile Card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6">
          <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full overflow-hidden border-4 border-white shadow-lg bg-orange-100 flex items-center justify-center shrink-0">
            <img v-if="teacher.photo" :src="teacher.photo" :alt="teacher.name" class="w-full h-full object-cover" />
            <span v-else class="text-3xl font-black text-brand-blue">{{ teacher.name?.charAt(0) }}</span>
          </div>
          <div class="flex-grow text-center sm:text-left">
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight mb-1">{{ teacher.name }}</h1>
            <p class="text-sm text-gray-500 font-medium mb-3">{{ teacher.email }}</p>
            <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-4">
              <span class="bg-blue-50 text-brand-blue px-3 py-1 rounded-lg text-xs font-bold border border-blue-100/50 font-mono">
                {{ teacher.enrollment_code || 'Sin Matrícula' }}
              </span>
              <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-lg text-xs font-bold">
                Desde {{ teacher.created_at }}
              </span>
            </div>
            <div class="flex flex-wrap justify-center sm:justify-start gap-2">
              <router-link :to="`/admin/teachers/${teacher.id}/edit`" class="inline-flex items-center gap-1.5 bg-brand-blue text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm hover:bg-blue-700 transition-all">
                <ion-icon :icon="createOutline" class="text-sm"></ion-icon>
                Editar
              </router-link>
            </div>
          </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Grados y Grupos -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="flex items-center gap-2 text-xs font-black text-gray-400 uppercase tracking-widest mb-4">
              <ion-icon :icon="peopleOutline" class="text-brand-blue text-base"></ion-icon>
              Grados y Grupos
            </h3>
            <div v-if="teacher.classrooms && teacher.classrooms.length > 0" class="space-y-2">
              <div v-for="c in teacher.classrooms" :key="c.id" class="flex items-center gap-3 bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center text-brand-blue font-black text-sm">
                  {{ c.grade }}º
                </div>
                <div>
                  <p class="text-sm font-bold text-gray-900">{{ c.grade }}º Grado - Grupo {{ c.group_letter }}</p>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ c.school_level }} • {{ c.shift }}</p>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400 font-medium">Sin grupos asignados.</p>
          </div>

          <!-- Asistencia de Hoy -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="flex items-center gap-2 text-xs font-black text-gray-400 uppercase tracking-widest mb-4">
              <ion-icon :icon="calendarOutline" class="text-brand-blue text-base"></ion-icon>
              Asistencia Hoy
            </h3>
            <div class="space-y-3">
              <!-- Status Badge -->
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500">Estatus:</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold"
                  :class="{
                    'bg-emerald-50 text-emerald-600': teacher.today_attendance.status === 'present',
                    'bg-amber-50 text-amber-600': teacher.today_attendance.status === 'late',
                    'bg-red-50 text-red-600': teacher.today_attendance.status === 'absent'
                  }"
                >
                  <div class="w-1.5 h-1.5 rounded-full"
                    :class="{
                      'bg-emerald-500': teacher.today_attendance.status === 'present',
                      'bg-amber-500': teacher.today_attendance.status === 'late',
                      'bg-red-500': teacher.today_attendance.status === 'absent'
                    }"
                  ></div>
                  {{ teacher.today_attendance.status === 'present' ? 'Presente' : teacher.today_attendance.status === 'late' ? 'Retardo' : 'Ausente' }}
                </span>
              </div>

              <!-- Entry -->
              <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                <span class="text-xs font-bold text-gray-500">Hora de Entrada</span>
                <span class="text-sm font-black" :class="teacher.today_attendance.entry_time ? 'text-gray-900' : 'text-gray-300'">
                  {{ teacher.today_attendance.entry_time || '--:-- --' }}
                </span>
              </div>

              <!-- Exit -->
              <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                <span class="text-xs font-bold text-gray-500">Hora de Salida</span>
                <span class="text-sm font-black" :class="teacher.today_attendance.exit_time ? 'text-gray-900' : 'text-gray-300'">
                  {{ teacher.today_attendance.exit_time || '--:-- --' }}
                </span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Error -->
      <div v-else class="flex-grow flex items-center justify-center">
        <p class="text-sm text-gray-400 font-bold">No se encontró al profesor.</p>
      </div>

    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { IonPage, IonIcon } from '@ionic/vue';
import { chevronForward, createOutline, peopleOutline, calendarOutline } from 'ionicons/icons';
import api from '@/services/api';

const route = useRoute();
const teacher = ref<any>(null);
const loading = ref(true);

onMounted(async () => {
  const id = route.params.id;
  try {
    const res = await api.get(`/admin/teachers/${id}`);
    if (res.data.success) {
      teacher.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching teacher detail', error);
  } finally {
    loading.value = false;
  }
});
</script>
