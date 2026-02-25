<template>
  <ion-page>
    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full h-full flex flex-col">
      
      <!-- Top Header Area -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight leading-none mb-1">Administración de Profesores Detallada</h1>
          <p class="text-sm font-medium text-gray-500">Panel de gestión y monitoreo en tiempo real</p>
        </div>
        <router-link to="/admin/teachers/create" class="bg-brand-blue hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm shadow-blue-500/30 transition-all flex items-center gap-2 shrink-0">
          <ion-icon :icon="add" class="text-lg"></ion-icon>
          Dar de Alta Profesor
        </router-link>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-8 lg:w-2/3">
        <!-- Maestros Presentes KPI -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between relative overflow-hidden group">
          <div class="flex justify-between items-start mb-2">
            <div>
              <h3 class="text-sm font-bold tracking-tight text-gray-500 mb-1">Maestros Presentes</h3>
              <div class="flex items-baseline gap-1">
                <span class="text-3xl sm:text-4xl font-black text-gray-900 leading-none">25</span>
                <span class="text-xl font-bold text-gray-400">/30</span>
              </div>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-brand-blue transition-transform group-hover:scale-110">
              <ion-icon :icon="person" class="text-xl"></ion-icon>
            </div>
          </div>
          <div class="mt-4 flex items-center gap-2 text-xs font-bold text-gray-400">
            <span class="text-emerald-500 bg-emerald-50 px-1.5 py-0.5 rounded">83%</span> del total
          </div>
        </div>

        <!-- Grupos Cubiertos KPI -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between relative overflow-hidden group">
          <div class="flex justify-between items-start mb-2">
            <div>
              <h3 class="text-sm font-bold tracking-tight text-gray-500 mb-1">Grupos Cubiertos</h3>
              <div class="flex items-baseline gap-1">
                <span class="text-3xl sm:text-4xl font-black text-gray-900 leading-none">12</span>
                <span class="text-xl font-bold text-gray-400">/12</span>
              </div>
            </div>
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 transition-transform group-hover:scale-110">
              <ion-icon :icon="checkbox" class="text-xl"></ion-icon>
            </div>
          </div>
          <p class="mt-4 text-xs font-bold text-gray-400">Sin vacantes pendientes</p>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="flex flex-col md:flex-row gap-3 mb-6 items-stretch md:items-center justify-between">
        <!-- Search -->
        <div class="relative flex-grow max-w-md">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <ion-icon :icon="searchOutline" class="text-gray-400 text-lg"></ion-icon>
          </div>
          <input 
            type="text" 
            placeholder="Buscar profesor por nombre o correo..." 
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition-all"
          >
        </div>

        <!-- Filter Buttons -->
        <div class="flex items-center gap-3 shrink-0">
          <button class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors shadow-sm">
            <ion-icon :icon="filterOutline" class="text-lg"></ion-icon>
            Filtrar por Grado
          </button>
          <button class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors shadow-sm">
            <ion-icon :icon="checkmarkCircleOutline" class="text-lg"></ion-icon>
            Estatus
          </button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden flex-grow flex flex-col min-h-0">
        <div class="overflow-x-auto flex-grow">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/50 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                <th class="py-4 px-6 font-black w-16">Foto</th>
                <th class="py-4 px-6 font-black">Nombre Completo</th>
                <th class="py-4 px-6 font-black">Correo Electrónico</th>
                <th class="py-4 px-6 font-black">Grados y Grupos Asignados</th>
                <th class="py-4 px-6 font-black">Estatus en Plantel</th>
                <th class="py-4 px-6 text-right font-black rounded-tr-2xl">Acciones</th>
              </tr>
            </thead>
            <tbody class="text-sm">
              <tr v-if="loading" class="animate-pulse">
                <td colspan="6" class="p-8 text-center text-gray-400 font-bold">Cargando profesores...</td>
              </tr>
              <tr v-else-if="teachers.length === 0">
                <td colspan="6" class="p-12 text-center text-gray-400 font-medium bg-white">No se han registrado profesores aún.</td>
              </tr>
              <tr v-else v-for="teacher in teachers" :key="teacher.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors group">
                <td class="py-3 px-6">
                  <div class="w-10 h-10 rounded-full bg-orange-100 border border-gray-100 overflow-hidden flex items-center justify-center shrink-0">
                    <img v-if="teacher.photo" :src="teacher.photo" :alt="teacher.name" class="w-full h-full object-cover" />
                    <span v-else class="text-lg font-black text-brand-blue">{{ teacher.name.charAt(0) }}</span>
                  </div>
                </td>
                <td class="py-3 px-6 font-bold text-gray-900 whitespace-nowrap">{{ teacher.name }}</td>
                <td class="py-3 px-6 text-gray-500">{{ teacher.email }}</td>
                <td class="py-3 px-6">
                  <div class="flex flex-wrap gap-2">
                    <span v-for="grade in teacher.grades" :key="grade" class="bg-blue-50 text-brand-blue px-2.5 py-1 rounded-lg text-xs font-bold border border-blue-100/50">
                      {{ grade }}
                    </span>
                  </div>
                </td>
                <td class="py-3 px-6">
                  <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold"
                    :class="teacher.status === 'Presente' ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-500'"
                  >
                    <div class="w-1.5 h-1.5 rounded-full" :class="teacher.status === 'Presente' ? 'bg-emerald-500' : 'bg-gray-400'"></div>
                    {{ teacher.status }}
                  </div>
                </td>
                <td class="py-3 px-6 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-2">
                    <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-emerald-500 hover:text-white transition-all shadow-sm">
                      <ion-icon :icon="eyeOutline" class="text-lg"></ion-icon>
                    </button>
                    <button @click="$router.push(`/admin/teachers/${teacher.id}/edit`)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-brand-blue hover:text-white transition-all shadow-sm">
                      <ion-icon :icon="createOutline" class="text-lg"></ion-icon>
                    </button>
                    <button @click="deleteTeacher(teacher)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                      <ion-icon :icon="trashOutline" class="text-lg"></ion-icon>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination Footer -->
        <div class="bg-white border-t border-gray-100 px-6 py-4 flex items-center justify-between shrink-0">
          <span class="text-xs font-bold text-gray-500">Mostrando 4 de 30 profesores</span>
          <div class="flex items-center gap-2">
            <button class="px-3 py-1.5 text-xs font-bold text-gray-400 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors border border-transparent">
              Anterior
            </button>
            <button class="px-3 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-sm rounded-lg transition-colors">
              Siguiente
            </button>
          </div>
        </div>
      </div>

    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { IonPage, IonIcon, alertController } from '@ionic/vue';
import { 
  add, person, checkbox, searchOutline, filterOutline, checkmarkCircleOutline, 
  eyeOutline, createOutline, trashOutline 
} from 'ionicons/icons';
import api from '@/services/api';

const teachers = ref<any[]>([]);
const loading = ref(true);

const fetchTeachers = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/teachers');
    if (res.data.success) {
      teachers.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching teachers', error);
  } finally {
    loading.value = false;
  }
};

const deleteTeacher = async (teacher: any) => {
  const alert = await alertController.create({
    header: 'Eliminar Profesor',
    message: `¿Estás seguro de eliminar a ${teacher.name}?`,
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      { 
        text: 'Eliminar', 
        role: 'destructive',
        handler: async () => {
          try {
            await api.delete(`/admin/teachers/${teacher.id}`);
            fetchTeachers();
          } catch (error) {
            console.error('Error deleting teacher', error);
          }
        }
      }
    ]
  });
  await alert.present();
};

onMounted(fetchTeachers);
</script>
