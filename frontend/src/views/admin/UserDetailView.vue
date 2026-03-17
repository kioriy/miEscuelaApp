<template>
  <ion-page>
    <ion-content>
      <div class="h-full bg-gray-50 flex flex-col font-sans">
    
    <!-- Top Global Header -->
    <header class="bg-white border-b border-gray-100 py-3 px-6 flex justify-between items-center shrink-0">
      <div class="flex items-center gap-2 text-blue-600">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 14.5l-6.3-3.15L2 15l10 5 10-5-3.7-1.85L12 16.5z"/></svg>
        <div>
          <h1 class="font-bold text-gray-800 leading-tight">Panel Super Admin</h1>
          <p class="text-[10px] text-gray-500 font-bold tracking-widest uppercase">Detalle de Usuario</p>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-8">
      <div class="max-w-4xl mx-auto w-full">
        
        <!-- Breadcrumb -->
        <div class="mb-8">
          <nav class="flex items-center text-sm text-gray-500 mb-4 font-semibold">
            <button @click="$router.push('/admin/dashboard')" class="hover:text-blue-600 flex items-center gap-1 transition-colors">
              <ion-icon :icon="homeOutline" class="text-base"></ion-icon>
              Inicio
            </button>
            <ion-icon :icon="chevronForward" class="mx-2 text-gray-300 text-xs"></ion-icon>
            <button @click="$router.push('/admin/users')" class="hover:text-blue-600 transition-colors">Usuarios</button>
            <ion-icon :icon="chevronForward" class="mx-2 text-gray-300 text-xs"></ion-icon>
            <span class="text-gray-900 font-bold">Detalle del Usuario</span>
          </nav>

          <div class="flex items-center justify-between gap-4">
            <div>
              <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">Perfil del Usuario</h1>
              <p class="text-gray-500 font-medium text-[15px]">Información detallada del integrante del sistema.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
              <button @click="$router.push(`/admin/users/${route.params.id}/edit`)" class="bg-brand-blue text-white font-bold py-2.5 px-5 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 flex items-center gap-2 transition-all">
                <ion-icon :icon="createOutline" class="text-lg"></ion-icon>
                Editar
              </button>
              <button @click="$router.back()" class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center gap-2 transition-all">
                <ion-icon :icon="arrowBackOutline" class="text-lg"></ion-icon>
                Volver
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-12 text-center">
          <p class="text-gray-400 font-medium">Cargando información del usuario...</p>
        </div>

        <!-- User Detail Card -->
        <div v-else-if="user" class="space-y-6 mb-12">

          <!-- Profile Header Card -->
          <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden p-8">
            <div class="flex items-center gap-6">
              <!-- Avatar -->
              <div class="w-24 h-24 rounded-2xl overflow-hidden bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0">
                <img v-if="avatarUrl" :src="avatarUrl" :alt="user.name" class="w-full h-full object-cover" />
                <span v-else class="text-3xl font-black text-blue-300">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-black text-gray-900 tracking-tight mb-1">{{ user.name }}</h2>
                <p class="text-brand-blue font-medium text-[15px] mb-3">{{ user.email }}</p>
                <div class="flex items-center gap-2 flex-wrap">
                  <span :class="roleBadgeClass">{{ roleLabel }}</span>
                  <span class="bg-green-100/50 border border-green-200 text-green-700 text-[11px] px-2.5 py-1 rounded-full font-bold">Activo</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Information Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- General Info -->
            <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6">
              <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest mb-5">Información General</h3>
              
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="personOutline" class="text-brand-blue text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Nombre Completo</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ user.name }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="mailOutline" class="text-brand-blue text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Correo Electrónico</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ user.email }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="shieldCheckmarkOutline" class="text-indigo-600 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Rol del Sistema</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ roleLabel }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- School & Meta -->
            <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6">
              <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest mb-5">Asignación & Registro</h3>
              
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="businessOutline" class="text-emerald-600 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Escuela Asignada</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ user.school ? user.school.name : 'Agencia Central' }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="calendarOutline" class="text-amber-600 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Fecha de Registro</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ formatDate(user.created_at) }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="timeOutline" class="text-gray-500 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Última Actualización</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ formatDate(user.updated_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6">
            <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest mb-5">Acciones Rápidas</h3>
            <div class="flex items-center gap-3 flex-wrap">
              <button @click="$router.push(`/admin/users/${route.params.id}/edit`)" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 text-brand-blue font-bold text-sm hover:bg-blue-100 transition-colors">
                <ion-icon :icon="createOutline" class="text-base"></ion-icon>
                Editar Usuario
              </button>
              <button @click="resendWelcomeEmail" :disabled="isResending" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-sm hover:bg-emerald-100 transition-colors disabled:opacity-50">
                <ion-icon :icon="mailOutline" class="text-base"></ion-icon>
                {{ isResending ? 'Enviando...' : 'Reenviar Correo de Bienvenida' }}
              </button>
              <button @click="confirmDelete" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-50 text-red-600 font-bold text-sm hover:bg-red-100 transition-colors">
                <ion-icon :icon="trashOutline" class="text-base"></ion-icon>
                Eliminar Usuario
              </button>
            </div>
          </div>

        </div>

        <!-- Not Found -->
        <div v-else class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-12 text-center">
          <p class="text-gray-400 font-medium">No se encontró el usuario.</p>
          <button @click="$router.push('/admin/users')" class="mt-4 text-brand-blue font-bold text-sm hover:underline">Volver a la lista</button>
        </div>

      </div>
    </main>

  </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent, IonIcon, alertController, toastController } from '@ionic/vue';
import {
  homeOutline, chevronForward, createOutline, arrowBackOutline,
  personOutline, mailOutline, shieldCheckmarkOutline, businessOutline,
  calendarOutline, timeOutline, trashOutline
} from 'ionicons/icons';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();

const isLoading = ref(true);
const isResending = ref(false);
const user = ref<any>(null);

const avatarUrl = computed(() => {
  if (user.value?.avatar_url) return user.value.avatar_url;
  if (user.value?.profile_photo_path) {
    const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000';
    return `${baseUrl}/storage/${user.value.profile_photo_path}`;
  }
  return null;
});

const roleLabels: Record<string, string> = {
  super_admin: 'Súper Admin',
  director: 'Director',
  teacher: 'Maestro',
  parent: 'Padre / Tutor',
  admin: 'Admin'
};

const roleLabel = computed(() => roleLabels[user.value?.role] || user.value?.role || '—');

const roleBadgeClass = computed(() => {
  const base = 'text-[11px] px-2.5 py-1 rounded-full font-bold border';
  switch (user.value?.role) {
    case 'super_admin': return `${base} bg-gray-100 border-gray-200 text-gray-600`;
    case 'director': return `${base} bg-indigo-50 border-indigo-100 text-indigo-600`;
    case 'teacher': return `${base} bg-blue-50 border-blue-100 text-brand-blue`;
    case 'parent': return `${base} bg-emerald-50 border-emerald-100 text-emerald-600`;
    default: return `${base} bg-gray-50 border-gray-200 text-gray-500`;
  }
});

const formatDate = (dateStr: string) => {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('es-MX', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
};

const fetchUser = async () => {
  try {
    isLoading.value = true;
    const res = await api.get(`/admin/users/${route.params.id}`);
    if (res.data.success) {
      user.value = res.data.data;
    }
  } catch (error) {
    console.error('Error cargando usuario', error);
    user.value = null;
  } finally {
    isLoading.value = false;
  }
};

const resendWelcomeEmail = async () => {
  isResending.value = true;
  try {
    const res = await api.post(`/admin/users/${route.params.id}/resend-welcome`);
    if (res.data.success) {
      const toast = await toastController.create({
        message: 'Correo de bienvenida reenviado exitosamente.',
        duration: 3000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.response?.data?.message || 'Error al reenviar el correo.',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isResending.value = false;
  }
};

const confirmDelete = async () => {
  const confirmAlert = await alertController.create({
    header: 'Eliminar Usuario',
    message: `¿Estás seguro de que deseas eliminar a ${user.value?.name}? Esta acción no se puede deshacer.`,
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      {
        text: 'Eliminar',
        role: 'destructive',
        handler: async () => {
          try {
            await api.delete(`/admin/users/${route.params.id}`);
            router.push('/admin/users');
          } catch (err: any) {
            window.alert(err.response?.data?.message || 'Error al eliminar usuario.');
          }
        }
      }
    ]
  });
  await confirmAlert.present();
};

onMounted(() => {
  fetchUser();
});
</script>
