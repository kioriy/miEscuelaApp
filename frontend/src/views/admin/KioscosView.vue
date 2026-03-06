<template>
  <ion-page>
    <ion-content class="ion-padding-bottom" style="--background: #F9FAFB;">
      <div class="p-8 lg:p-12 w-full max-w-[1400px] mx-auto min-h-full flex flex-col bg-gray-50 text-gray-900 font-sans">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 pb-6 mb-8">
          <div class="flex items-center gap-4">
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Gestión Detallada de Kioscos y Claves</h1>
            <span class="bg-blue-50 text-brand-blue text-[10px] font-black px-2 py-1 rounded-md uppercase tracking-widest">Super Admin</span>
          </div>
          <div class="flex items-center gap-4 text-gray-400">
            <button class="relative hover:text-gray-900 transition-colors">
              <ion-icon :icon="notifications" class="text-xl"></ion-icon>
              <div class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full border border-white"></div>
            </button>
            <button class="hover:text-gray-900 transition-colors">
              <ion-icon :icon="settings" class="text-xl"></ion-icon>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
          <!-- Left Column -->
          <div class="lg:col-span-7 flex flex-col gap-8">
            <!-- Staff Section -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
              <h3 class="text-sm font-bold text-gray-900 mb-4">Staff a cargo del control</h3>
              <div class="relative">
                <ion-icon :icon="personOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></ion-icon>
                <select v-model="selectedStaff" @change="fetchKioscos" class="w-full appearance-none bg-gray-50 border border-gray-200 rounded-xl pl-12 pr-10 py-3 text-sm font-bold text-gray-700 outline-none focus:ring-2 focus:ring-blue-100 focus:border-brand-blue cursor-pointer">
                  <option value="">Todos los kioscos...</option>
                  <option v-for="director in directors" :key="director.id" :value="director.id">
                    {{ director.name }} - {{ director.schools?.map((s: any) => s.name).join(', ') || 'Sin escuela' }}
                  </option>
                </select>
                <ion-icon :icon="chevronDown" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
              </div>
            </div>

            <!-- Kioskos Asignados -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col max-h-[600px]">
              <div class="p-6 border-b border-gray-50 flex items-center justify-between shrink-0">
                <h3 class="text-sm font-bold text-gray-900">Kioscos Asignados</h3>
                <button class="text-brand-blue text-xs font-bold hover:underline">Ver todos</button>
              </div>
              
              <div class="overflow-y-auto flex-1 p-2">
                <table class="w-full text-left">
                  <thead>
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                      <th class="p-4">Kiosco</th>
                      <th class="p-4">Escuela Propietaria</th>
                      <th class="p-4 text-right">Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(kiosk, index) in assignedKiosks" :key="index"
                        @click="selectKiosk(kiosk)"
                        :class="[
                          'border-b border-gray-50 cursor-pointer transition-colors hover:bg-gray-50/50',
                          selectedKiosk?.id === kiosk.id ? 'bg-blue-50/30 relative' : ''
                        ]">
                      <!-- Indication line for selected -->
                      <td v-if="selectedKiosk?.id === kiosk.id" class="absolute left-0 top-0 bottom-0 w-1 bg-brand-blue rounded-r-md"></td>
                      
                      <td class="p-4">
                        <p class="text-sm font-bold text-gray-900">{{ kiosk.name }}</p>
                        <p class="text-[11px] font-medium text-gray-400">ID: {{ kiosk.id }}</p>
                      </td>
                      <td class="p-4">
                        <p class="text-sm font-bold text-gray-700">{{ kiosk.ownerSchool }}</p>
                        <p v-if="kiosk.affiliatedSchools?.length > 0" class="text-[10px] text-gray-400 mt-0.5">{{ kiosk.affiliatedSchools.length }} sedes afiliadas</p>
                      </td>
                      <td class="p-4 text-right">
                        <span v-if="kiosk.status === 'Activo'" class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full border border-green-100">
                          <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> {{ kiosk.status }}
                        </span>
                        <span v-else class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-1 rounded-full border border-gray-200">
                          <div class="w-1.5 h-1.5 rounded-full bg-gray-400"></div> {{ kiosk.status }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="p-4 bg-gray-50/50 border-t border-gray-50 text-center shrink-0">
                <p class="text-xs font-medium text-gray-500">Viendo {{ assignedKiosks.length }} kioscos activos asignados.</p>
              </div>
            </div>
          </div>

          <!-- Right Column (Details panel) -->
          <div class="lg:col-span-5 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden" v-if="selectedKiosk">
            <!-- Details Header -->
            <div class="p-6 border-b border-gray-50">
              <div class="flex items-center justify-between mb-4">
                <span class="text-[10px] font-black text-brand-blue uppercase tracking-widest">Detalles del Kiosco</span>
                <button class="text-gray-400 hover:text-gray-700 transition-colors">
                  <ion-icon :icon="closeOutline" class="text-xl"></ion-icon>
                </button>
              </div>
              <h2 class="text-2xl font-black text-gray-900 bg-clip-text mb-1">{{ selectedKiosk.name }}</h2>
              <p class="text-xs font-medium text-gray-500">Ubicado en: {{ selectedKiosk.location || 'Recepción Principal Edificio A' }}</p>
            </div>

            <!-- Scrollable Content -->
            <div class="p-6 overflow-y-auto flex-1 space-y-8">
              <!-- Key Info Blocks -->
              <div class="grid grid-cols-2 gap-4">
                <div class="p-4 border border-gray-100 rounded-xl bg-gray-50/50">
                  <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Escuela Propietaria</p>
                  <select v-model="selectedKiosk.owner_school_id" class="w-full appearance-none bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-sm font-bold text-gray-900 outline-none focus:ring-2 focus:ring-blue-100 focus:border-brand-blue">
                    <option v-for="school in allSchools" :key="school.id" :value="school.id">
                      {{ school.name }}
                    </option>
                  </select>
                </div>
                <!-- Activation Code block -->
                <div class="p-4 border border-blue-100 rounded-xl bg-blue-50/20 relative group">
                  <p class="text-[9px] font-black text-brand-blue uppercase tracking-widest mb-2">Clave de Registro</p>
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-black text-brand-blue tracking-widest">{{ selectedKiosk.activation_code || 'K-81F6-13' }}</p>
                    <button class="text-gray-400 hover:text-brand-blue transition-colors">
                      <ion-icon :icon="copyOutline" class="text-lg"></ion-icon>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Affiliated Schools section -->
              <div>
                <div class="flex items-center justify-between mb-4 mt-2">
                  <h3 class="flex items-center gap-2 text-sm font-bold text-gray-900">
                    <ion-icon :icon="businessOutline" class="text-brand-blue text-lg"></ion-icon>
                    Sedes Afiliadas
                  </h3>
                  <span class="text-[10px] font-black bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ selectedKiosk.affiliatedSchools?.length || 0 }} Vinculadas</span>
                </div>
                
                <div class="mb-4 relative flex gap-2">
                  <div class="relative flex-1">
                    <ion-icon :icon="searchOutline" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></ion-icon>
                    <select v-model="selectedSchoolToLink" class="w-full appearance-none bg-white border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-100 outline-none transition-all placeholder:text-gray-400">
                      <option value="">Buscar sede para afiliar...</option>
                      <option v-for="school in availableSchoolsToLink" :key="school.id" :value="school.id">
                        {{ school.name }}
                      </option>
                    </select>
                  </div>
                  <button @click="linkSchool" :disabled="!selectedSchoolToLink || isLinking" class="bg-brand-blue text-white font-bold py-2.5 px-4 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 flex items-center justify-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <ion-icon :icon="linkOutline" class="text-lg"></ion-icon>
                    <span class="hidden sm:inline">Vincular</span>
                  </button>
                </div>

                <div class="mb-4">
                  <p class="text-center mt-2 text-[10px] font-medium text-gray-400 italic">* Los cambios se aplicarán al reiniciar el kiosco.</p>
                </div>

                <!-- Linked Schools List -->
                <div class="space-y-3">
                  <div v-for="school in selectedKiosk.affiliatedSchools" :key="school.id" class="p-4 border border-gray-100 rounded-xl flex items-center justify-between hover:border-brand-blue/30 transition-colors bg-white shadow-sm">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 shrink-0">
                        <ion-icon :icon="business" class="text-sm"></ion-icon>
                      </div>
                      <div>
                        <p class="text-[13px] font-bold text-gray-900 leading-tight">{{ school.name }}</p>
                        <p class="text-[10px] font-medium text-gray-400">Cód: {{ school.cct || 'S/N' }}</p>
                      </div>
                    </div>
                    <button @click="unlinkSchool(school.id)" class="text-gray-300 hover:text-red-500 transition-colors p-1 group" title="Desvincular">
                      <ion-icon :icon="unlinkOutline" class="text-lg group-hover:scale-110 transition-transform"></ion-icon>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Pendientes de activación -->
              <div v-if="pendingKiosks.length > 0" class="pt-6 border-t border-gray-50">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Activaciones Pendientes ({{ selectedKiosk.ownerSchool }})</h3>
                <div class="space-y-3">
                  <div v-for="pending in pendingKiosks" :key="pending.id" class="p-4 border border-dashed border-gray-200 rounded-xl bg-gray-50 flex items-center justify-between">
                    <div>
                      <p class="text-sm font-bold text-gray-900">PIN de Activación</p>
                      <p class="text-xs text-gray-500">Kiosco Autogenerado ID: {{ pending.id }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-black text-brand-blue tracking-widest bg-white px-3 py-1 rounded-lg border border-blue-100 shadow-sm">{{ pending.activation_code }}</span>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- Action buttons bottom fixed -->
            <div class="p-6 border-t border-gray-100 bg-gray-50/50 flex items-center gap-3 shrink-0">
              <button class="flex-1 bg-white border border-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl text-sm shadow-sm hover:bg-gray-50 transition-all text-center">
                Descargar Reporte
              </button>
              <button @click="saveKioskChanges" :disabled="isSaving" class="flex-1 bg-brand-blue text-white font-bold py-3 px-4 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 transition-all text-center disabled:opacity-50 disabled:cursor-not-allowed">
                {{ isSaving ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </div>
          
          <!-- Empty State if no kiosk selected -->
          <div v-else class="lg:col-span-5 flex flex-col items-center justify-center bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center">
            <div class="w-16 h-16 bg-blue-50 text-brand-blue rounded-full flex items-center justify-center mb-4">
              <ion-icon :icon="storefrontOutline" class="text-3xl"></ion-icon>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Ningún Kiosco Seleccionado</h3>
            <p class="text-sm text-gray-500 max-w-[250px]">Selecciona un kiosco de la lista izquierda para ver sus detalles, sedes afiliadas e información técnica.</p>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { 
  notifications, settings, personOutline, chevronDown, closeOutline,
  copyOutline, businessOutline, searchOutline, linkOutline, business,
  unlinkOutline, storefrontOutline
} from 'ionicons/icons';
import api from '@/services/api';

const selectedStaff = ref('');
const directors = ref<any[]>([]);
const allSchools = ref<any[]>([]);

const assignedKiosks = ref<any[]>([]);
const allKiosks = ref<any[]>([]);
const selectedKiosk = ref<any>(null);

const selectedSchoolToLink = ref('');
const isLinking = ref(false);
const isSaving = ref(false);

const selectKiosk = (kiosk: any) => {
  // Creating a copy so changes don't apply until saved if needed, or work directly
  selectedKiosk.value = { ...kiosk };
  selectedSchoolToLink.value = '';
};

// Computed to get available schools to link (excluding already linked ones)
const availableSchoolsToLink = computed(() => {
  if (!selectedKiosk.value) return [];
  const linkedIds = selectedKiosk.value.affiliatedSchools?.map((s: any) => s.id) || [];
  return allSchools.value.filter(s => !linkedIds.includes(s.id));
});

const pendingKiosks = computed(() => {
  if (!selectedKiosk.value) return [];
  // Find kiosks from the same owner school that are NOT active
  return allKiosks.value.filter(k => k.owner_school_id === selectedKiosk.value.owner_school_id && !k.is_active);
});

const fetchDirectors = async () => {
  try {
    const res = await api.get('/admin/users');
    if (res.data.success) {
      directors.value = res.data.data.filter((u: any) => u.role === 'director');
    }
  } catch (error) {
    console.error('Error fetching users/directors', error);
  }
};

const fetchSchools = async () => {
  try {
    const res = await api.get('/admin/schools');
    if (res.data.success) {
      allSchools.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching schools', error);
  }
};

const fetchKioscos = async () => {
  try {
    const res = await api.get('/admin/kioscos');
    if (res.data.success) {
      
      let kiosksData = res.data.data;
      
      // Map API data first to retain all records
      allKiosks.value = kiosksData.map((k: any) => ({
          id: k.id,
          name: k.name || 'Kiosco',
          owner_school_id: k.owner_school_id,
          ownerSchool: k.owner_school?.name || 'Escuela no asignada',
          status: k.status || 'Offline',
          activation_code: k.activation_code,
          is_active: k.is_active,
          location: k.location || 'Localidad Principal',
          last_ip: k.last_ip || 'N/A',
          last_played_at: k.updated_at ? new Date(k.updated_at).toLocaleString() : 'N/A',
          affiliatedSchools: k.schools || []
      }));
      
      let filteredKiosks = allKiosks.value;

      // Filter by director if selected (strictly by owner school)
      if (selectedStaff.value) {
        const director = directors.value.find(d => d.id == selectedStaff.value);
        if (director) {
           const directorSchoolIds = director.schools?.map((s: any) => s.id) || [];
           filteredKiosks = filteredKiosks.filter((k: any) => directorSchoolIds.includes(k.owner_school_id));
        } else {
           filteredKiosks = [];
        }
      }

      // assignedKiosks -> Left List -> only show activated kiosks
      assignedKiosks.value = filteredKiosks.filter((k: any) => k.is_active);
      
      if (assignedKiosks.value.length > 0) {
         if(!selectedKiosk.value || !assignedKiosks.value.find(k => k.id === selectedKiosk.value.id)){
             selectKiosk(assignedKiosks.value[0]);
         } else {
             selectKiosk(assignedKiosks.value.find(k => k.id === selectedKiosk.value.id));
         }
      } else {
         selectedKiosk.value = null;
      }
    }
  } catch (error) {
    console.error('Error fetching kioscos', error);
  }
};

const linkSchool = async () => {
  if (!selectedKiosk.value || !selectedSchoolToLink.value) return;
  isLinking.value = true;
  try {
    const res = await api.post(`/admin/kioscos/${selectedKiosk.value.id}/link-school`, {
      school_id: selectedSchoolToLink.value
    });
    if (res.data.success) {
       await fetchKioscos();
       selectedSchoolToLink.value = '';
    }
  } catch (error) {
    console.error('Error linking school', error);
    alert('Error al vincular sede.');
  } finally {
    isLinking.value = false;
  }
};

const unlinkSchool = async (schoolId: number) => {
  if (!selectedKiosk.value) return;
  if (!confirm('¿Seguro que deseas desvincular esta sede?')) return;
  
  try {
    const res = await api.post(`/admin/kioscos/${selectedKiosk.value.id}/unlink-school`, {
      school_id: schoolId
    });
    if (res.data.success) {
       // Update local reactive state immediately so UI updates
       if (selectedKiosk.value && selectedKiosk.value.affiliatedSchools) {
         selectedKiosk.value.affiliatedSchools = selectedKiosk.value.affiliatedSchools.filter((s: any) => s.id !== schoolId);
       }
       await fetchKioscos();
    }
  } catch (error: any) {
    console.error('Error unlinking school', error);
    alert(error.response?.data?.message || 'Error al desvincular sede.');
  }
};

const saveKioskChanges = async () => {
  if(!selectedKiosk.value) return;
  isSaving.value = true;
  try {
      // We'll call the update endpoint
      const res = await api.put(`/admin/kioscos/${selectedKiosk.value.id}`, {
          name: selectedKiosk.value.name,
          location: selectedKiosk.value.location,
          owner_school_id: selectedKiosk.value.owner_school_id // Backend might ignore it if not validated
      });
      if(res.data.success) {
         alert('Cambios guardados.');
         await fetchKioscos();
      }
  } catch(error) {
      console.error('Error guardando cambios del kiosco', error);
      alert('Ocurrió un error al guardar los cambios.');
  } finally {
      isSaving.value = false;
  }
};

onMounted(async () => {
  await fetchDirectors();
  await fetchSchools();
  await fetchKioscos();
});
</script>

<style scoped>
/* Scoped styles mainly using Tailwind utility classes. */
</style>
