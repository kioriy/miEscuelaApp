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
          <p class="text-[10px] text-gray-500 font-bold tracking-widest uppercase">Detalle de Escuela</p>
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
              <ion-icon :icon="homeOutline" class="text-base"></ion-icon>
              Inicio
            </button>
            <ion-icon :icon="chevronForward" class="mx-2 text-gray-300 text-xs"></ion-icon>
            <button @click="$router.push('/admin/schools')" class="hover:text-blue-600 transition-colors">Escuelas</button>
            <ion-icon :icon="chevronForward" class="mx-2 text-gray-300 text-xs"></ion-icon>
            <span class="text-gray-900 font-bold">Detalle de Escuela</span>
          </nav>

          <div class="flex items-center justify-between gap-4">
            <div>
              <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">Perfil de la Escuela</h1>
              <p class="text-gray-500 font-medium text-[15px]">Información detallada del plantel educativo.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
              <button @click="$router.push(`/admin/schools/${route.params.id}/edit`)" class="bg-brand-blue text-white font-bold py-2.5 px-5 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 flex items-center gap-2 transition-all">
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

        <!-- Loading -->
        <div v-if="isLoading" class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-12 text-center">
          <p class="text-gray-400 font-medium">Cargando información de la escuela...</p>
        </div>

        <!-- School Detail -->
        <div v-else-if="school" class="space-y-6 mb-12">

          <!-- School Header Card -->
          <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden p-8">
            <div class="flex items-center gap-6">
              <!-- Logo -->
              <div class="w-24 h-24 rounded-2xl overflow-hidden bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0">
                <img v-if="logoUrl" :src="logoUrl" :alt="school.name" class="w-full h-full object-cover" />
                <span v-else class="text-2xl font-black text-blue-300">{{ school.name?.substring(0, 2)?.toUpperCase() }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-black text-gray-900 tracking-tight mb-1">{{ school.name }}</h2>
                <p class="text-gray-500 font-medium text-[15px] mb-3">
                  <span v-if="school.cct" class="text-brand-blue font-bold uppercase">CCT: {{ school.cct }}</span>
                  <span v-else class="text-gray-400">Sin CCT</span>
                  <span class="mx-2 text-gray-300">•</span>
                  ID: sch_{{ school.id }}
                </p>
                <div class="flex items-center gap-2 flex-wrap">
                  <span v-if="school.is_active" class="bg-green-100/50 border border-green-200 text-green-700 text-[11px] px-2.5 py-1 rounded-full font-bold flex items-center gap-1.5">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Activa
                  </span>
                  <span v-else class="bg-red-100/50 border border-red-200 text-red-700 text-[11px] px-2.5 py-1 rounded-full font-bold flex items-center gap-1.5">
                    <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div> Inactiva
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Stats Row -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 text-center">
              <p class="text-[28px] font-black text-gray-900 leading-none mb-1">{{ school.students_count || 0 }}</p>
              <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Estudiantes</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 text-center">
              <p class="text-[28px] font-black text-indigo-600 leading-none mb-1">{{ school.users_count || 0 }}</p>
              <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Personal</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 text-center">
              <p class="text-[28px] font-black text-brand-blue leading-none mb-1">{{ school.kiosks_count || 0 }}</p>
              <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Kioscos</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 text-center">
              <p class="text-[28px] font-black text-emerald-600 leading-none mb-1">{{ school.groups_count || 0 }}</p>
              <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Grupos</p>
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
                    <ion-icon :icon="businessOutline" class="text-brand-blue text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Nombre</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ school.name }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="cardOutline" class="text-brand-blue text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Clave CCT</p>
                    <p class="text-[15px] font-bold text-gray-900 uppercase">{{ school.cct || '—' }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="locationOutline" class="text-emerald-600 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Dirección</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ school.address || '—' }}</p>
                    <a v-if="school.address && school.address !== 'Por Definir'" :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(school.address)" target="_blank" class="text-[12px] font-bold text-brand-blue hover:underline mt-1 inline-flex items-center gap-1">
                      <ion-icon :icon="navigateCircleOutline" class="text-sm"></ion-icon>
                      Ver en Google Maps
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact & Config -->
            <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6">
              <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest mb-5">Contacto & Configuración</h3>
              
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="callOutline" class="text-amber-600 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Teléfono</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ school.contact_phone || '—' }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="timeOutline" class="text-indigo-600 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Zona Horaria</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ school.timezone || 'America/Mexico_City' }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="calendarOutline" class="text-gray-500 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Fecha de Registro</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ formatDate(school.created_at) }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center shrink-0 mt-0.5">
                    <ion-icon :icon="barcodeOutline" class="text-purple-600 text-lg"></ion-icon>
                  </div>
                  <div>
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Modo Escáner QR</p>
                    <p class="text-[15px] font-bold text-gray-900">{{ scanTypeLabel }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Kiosks Section -->
          <div v-if="school.owned_kiosks && school.owned_kiosks.length > 0" class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6">
            <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest mb-5">Kioscos Autorizados</h3>
            <div class="flex flex-wrap gap-2">
              <span v-for="kiosk in school.owned_kiosks" :key="kiosk.id" class="inline-flex items-center gap-2 py-2 px-4 rounded-xl bg-gray-50 border border-gray-200 text-sm font-bold text-gray-700">
                <div class="w-2 h-2 rounded-full" :class="kiosk.is_active ? 'bg-green-500' : 'bg-gray-300'"></div>
                {{ kiosk.activation_code }}
              </span>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6">
            <h3 class="text-[13px] font-black text-gray-400 uppercase tracking-widest mb-5">Acciones Rápidas</h3>
            <div class="flex items-center gap-3 flex-wrap">
              <button @click="$router.push(`/admin/schools/${route.params.id}/edit`)" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 text-brand-blue font-bold text-sm hover:bg-blue-100 transition-colors">
                <ion-icon :icon="createOutline" class="text-base"></ion-icon>
                Editar Escuela
              </button>
              <button @click="confirmDelete" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-50 text-red-600 font-bold text-sm hover:bg-red-100 transition-colors">
                <ion-icon :icon="trashOutline" class="text-base"></ion-icon>
                Eliminar Escuela
              </button>
            </div>
          </div>

        </div>

        <!-- Not Found -->
        <div v-else class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-12 text-center">
          <p class="text-gray-400 font-medium">No se encontró la escuela.</p>
          <button @click="$router.push('/admin/schools')" class="mt-4 text-brand-blue font-bold text-sm hover:underline">Volver a la lista</button>
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
import { IonPage, IonContent, IonIcon, alertController } from '@ionic/vue';
import {
  homeOutline, chevronForward, createOutline, arrowBackOutline,
  businessOutline, cardOutline, locationOutline, navigateCircleOutline,
  callOutline, timeOutline, calendarOutline, trashOutline, barcodeOutline
} from 'ionicons/icons';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();

const isLoading = ref(true);
const school = ref<any>(null);

const logoUrl = computed(() => {
  if (!school.value?.logo_path) return null;
  return school.value.logo_path.startsWith('http')
    ? school.value.logo_path
    : `${import.meta.env.VITE_API_URL?.replace('/api', '') || ''}/storage/${school.value.logo_path}`;
});

const scanTypeLabel = computed(() => {
  if (!school.value?.qr_scan_type) return 'Solo Matrícula (ID puro)';
  const types: Record<string, string> = {
    'raw_id': 'Solo Matrícula (ID puro)',
    'hash_split': 'URL con Hash (#)',
    'query_param': 'Parámetro URL (?id=)',
    'sep_url': 'URL Portal SEP Jalisco'
  };
  return types[school.value.qr_scan_type] || 'Solo Matrícula (ID puro)';
});

const formatDate = (dateStr: string) => {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('es-MX', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
};

const fetchSchool = async () => {
  try {
    isLoading.value = true;
    const res = await api.get(`/admin/schools/${route.params.id}`);
    if (res.data.success) {
      school.value = res.data.data;
    }
  } catch (error) {
    console.error('Error cargando escuela', error);
    school.value = null;
  } finally {
    isLoading.value = false;
  }
};

const confirmDelete = async () => {
  const confirmAlert = await alertController.create({
    header: 'Eliminar Escuela',
    message: `¿Estás seguro de que deseas eliminar "${school.value?.name}"? Esta acción eliminará también todos los kioscos y datos asociados.`,
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      {
        text: 'Eliminar',
        role: 'destructive',
        handler: async () => {
          try {
            await api.delete(`/admin/schools/${route.params.id}`);
            router.push('/admin/schools');
          } catch (err: any) {
            window.alert(err.response?.data?.message || 'Error al eliminar la escuela.');
          }
        }
      }
    ]
  });
  await confirmAlert.present();
};

onMounted(() => {
  fetchSchool();
});
</script>
