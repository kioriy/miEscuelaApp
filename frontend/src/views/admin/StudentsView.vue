<template>
  <ion-page>
    <ion-content class="ion-padding-bottom">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="p-8 lg:p-12 w-full max-w-[1400px] mx-auto min-h-full flex flex-col bg-gray-50 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
          <div>
            <h1 class="text-[32px] font-black text-gray-900 tracking-tight leading-none mb-2">Administración de Estudiantes</h1>
            <p class="text-gray-500 font-medium tracking-wide mb-1.5">Gestiona la matrícula, grupos y registros fotográficos.</p>
            <p v-if="activeSchoolName" class="text-[15px] font-black text-brand-blue flex items-center gap-1.5 mt-1"><ion-icon :icon="business"></ion-icon> {{ activeSchoolName }}</p>
          </div>
          <div class="flex items-center gap-3 shrink-0">
            <button @click="fetchStudents()" class="bg-white border border-gray-200 text-gray-700 font-bold w-10 h-10 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center justify-center transition-all" title="Actualizar lista">
              <ion-icon :icon="refreshOutline" class="text-lg"></ion-icon>
            </button>
            <button @click="showBulkModal = true" class="bg-white border border-gray-200 text-brand-blue font-bold py-2.5 px-5 rounded-xl text-sm shadow-sm hover:bg-blue-50 flex items-center gap-2 transition-all">
              <ion-icon :icon="imagesOutline" class="text-lg"></ion-icon>
              Importar Fotos (ZIP)
            </button>
            <button @click="$router.push('/admin/students/create')" class="bg-brand-blue text-white font-bold py-2.5 px-5 rounded-xl text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 flex items-center gap-2 transition-all">
              <ion-icon :icon="personAddOutline" class="text-lg"></ion-icon>
              Nuevo Estudiante
            </button>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
          <div class="bg-white p-6 rounded-[24px] shadow-sm border border-gray-100 transition-transform hover:-translate-y-1">
            <h3 class="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-2">Matrícula Total</h3>
            <div class="flex items-baseline gap-2">
              <span class="text-4xl font-black text-gray-900 tracking-tighter">
                <span v-if="loading">...</span>
                <span v-else>{{ students.length }}</span>
              </span>
              <span class="text-[11px] font-bold text-gray-400 uppercase">Alumnos</span>
            </div>
          </div>
          <div class="bg-white p-6 rounded-[24px] shadow-sm border border-gray-100 transition-transform hover:-translate-y-1">
            <h3 class="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-2">Con Fotografía</h3>
            <div class="flex items-baseline gap-2">
              <span class="text-4xl font-black text-emerald-600 tracking-tighter">
                <span v-if="loading">...</span>
                <span v-else>{{ studentsWithPhoto }}</span>
              </span>
              <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md uppercase tracking-tighter">
                <span v-if="loading">...</span>
                <span v-else>{{ photoPercentage }}%</span>
              </span>
            </div>
          </div>
          <div class="bg-white p-6 rounded-[24px] shadow-sm border border-gray-100 transition-transform hover:-translate-y-1">
            <h3 class="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-2">Sin Fotografía</h3>
            <div class="flex items-baseline gap-2">
              <span class="text-4xl font-black text-orange-500 tracking-tighter">
                <span v-if="loading">...</span>
                <span v-else>{{ students.length - studentsWithPhoto }}</span>
              </span>
              <span class="text-[11px] font-bold text-orange-500 bg-orange-50 px-2 py-0.5 rounded-md uppercase tracking-tighter">Pendientes</span>
            </div>
          </div>
          <div class="bg-white p-6 rounded-[24px] shadow-sm border border-gray-100 transition-transform hover:-translate-y-1">
            <h3 class="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-2">Grupos Activos</h3>
            <div class="flex items-baseline gap-2">
              <span class="text-4xl font-black text-indigo-600 tracking-tighter">
                <span v-if="loading">...</span>
                <span v-else>{{ uniqueGroups.length }}</span>
              </span>
              <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md uppercase tracking-tighter">Secciones</span>
            </div>
          </div>
        </div>

        <!-- Table & Filters Section -->
        <div class="bg-white rounded-[32px] shadow-sm border border-gray-100 overflow-hidden mb-10">
          <!-- Toolbar -->
          <div class="p-6 border-b border-gray-100 flex flex-col lg:flex-row items-center gap-4 bg-white/50">
            <div class="flex-grow w-full relative">
              <ion-icon :icon="searchOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></ion-icon>
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Buscar por nombre o matrícula..." 
                class="pl-12 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-2xl focus:outline-none focus:border-brand-blue focus:ring-4 focus:ring-blue-500/10 text-[14px] font-bold text-gray-700 w-full transition-all" 
              />
            </div>
            
            <div class="flex items-center gap-3 shrink-0 w-full lg:w-auto">
              <div v-if="isAdmin" class="relative flex-1 lg:flex-none">
                <select v-model="filterSchool" @change="fetchStudents" class="w-full appearance-none bg-gray-50/50 border border-gray-200 text-brand-blue text-[13px] font-black rounded-2xl py-3 pl-4 pr-10 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-brand-blue cursor-pointer">
                  <option value="">Todas las Escuelas</option>
                  <option v-for="school in userSchools" :key="school.id" :value="school.id">{{ school.name }}</option>
                </select>
                <ion-icon :icon="chevronDown" class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-blue pointer-events-none"></ion-icon>
              </div>
              
              <div class="relative flex-1 lg:flex-none">
                <select v-model="filterGrade" class="w-full appearance-none bg-gray-50/50 border border-gray-200 text-gray-700 text-[13px] font-black rounded-2xl py-3 pl-4 pr-10 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-brand-blue cursor-pointer">
                  <option value="">Todos los Grados</option>
                  <option v-for="grade in grades" :key="grade" :value="grade">{{ grade }}º Grado</option>
                </select>
                <ion-icon :icon="chevronDown" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
              </div>
              
              <div class="relative flex-1 lg:flex-none">
                <select v-model="filterGroup" class="w-full appearance-none bg-gray-50/50 border border-gray-200 text-gray-700 text-[13px] font-black rounded-2xl py-3 pl-4 pr-10 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-brand-blue cursor-pointer">
                  <option value="">Todos los Grupos</option>
                  <option v-for="letter in groups" :key="letter" :value="letter">Grupo {{ letter }}</option>
                </select>
                <ion-icon :icon="chevronDown" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
              </div>

              <button @click="resetFilters" class="w-12 h-12 flex items-center justify-center border border-gray-200 bg-white rounded-2xl text-gray-500 hover:bg-gray-50 hover:text-brand-blue transition-all" title="Resetear Filtros">
                <ion-icon :icon="filter" class="text-xl"></ion-icon>
              </button>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
              <thead>
                <tr class="bg-gray-50/30 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase border-b border-gray-50">
                  <th class="p-5 pl-8">Estudiante</th>
                  <th class="p-5">Matrícula</th>
                  <th class="p-5">Grado / Grupo</th>
                  <th class="p-5">Tutor de Contacto</th>
                  <th class="p-5">Foto</th>
                  <th class="p-5 pr-8 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="text-sm">
                <tr v-if="loading" class="animate-pulse">
                  <td colspan="6" class="p-12 text-center text-gray-400 font-black uppercase tracking-widest bg-white">Cargando Estudiantes...</td>
                </tr>
                <tr v-else-if="filteredStudents.length === 0" class="bg-white">
                  <td colspan="6" class="p-12 text-center text-gray-400 font-medium">No se encontraron estudiantes con los filtros aplicados.</td>
                </tr>
                <tr v-else v-for="student in filteredStudents" :key="student.id" class="border-b border-gray-50 hover:bg-blue-50/30 transition-all group">
                  <td class="p-5 pl-8">
                    <div class="flex items-center gap-4">
                      <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center text-brand-blue font-black border-2 border-white shadow-sm overflow-hidden shrink-0 group-hover:scale-110 transition-transform">
                        <img v-if="student.photo_url" :src="student.photo_url" class="w-full h-full object-cover" />
                        <span v-else class="text-lg">{{ student.first_name[0] }}{{ student.last_name[0] }}</span>
                      </div>
                      <div>
                        <p class="font-black text-gray-900 leading-tight mb-0.5">{{ student.first_name }} {{ student.last_name }}</p>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">{{ student.classroom?.shift }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="p-5 font-bold text-brand-blue text-[13px] tracking-tight">
                    {{ student.enrollment_code }}
                  </td>
                  <td class="p-5">
                    <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg font-black text-[12px] border border-indigo-100">
                      {{ student.classroom?.grade }}º {{ student.classroom?.group_letter }}
                    </span>
                  </td>
                  <td class="p-5">
                    <div class="flex flex-col">
                      <p class="text-[13px] font-bold text-gray-700">{{ student.tutor_email || 'Sin vincular' }}</p>
                      <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ student.secondary_tutor_email ? '2 Contactos' : '1 Contacto' }}</p>
                    </div>
                  </td>
                  <td class="p-5">
                    <span v-if="student.photo_path" class="inline-flex items-center gap-1.5 text-emerald-600 font-black text-[11px] uppercase tracking-widest">
                      <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> Registrada
                    </span>
                    <span v-else class="inline-flex items-center gap-1.5 text-orange-400 font-black text-[11px] uppercase tracking-widest">
                       <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div> Pendiente
                    </span>
                  </td>
                  <td class="p-5 pr-8">
                    <div class="flex items-center justify-end gap-2">
                      <button @click="$router.push(`/admin/students/${student.id}`)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-indigo-500 hover:text-white transition-all shadow-sm" title="Ver detalle">
                        <ion-icon :icon="eyeOutline" class="text-lg"></ion-icon>
                      </button>
                      <button @click="$router.push(`/admin/students/${student.id}/edit`)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-brand-blue hover:text-white transition-all shadow-sm" title="Editar">
                        <ion-icon :icon="createOutline" class="text-lg"></ion-icon>
                      </button>
                      <button @click="confirmDelete(student)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Eliminar">
                        <ion-icon :icon="trashOutline" class="text-lg"></ion-icon>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="p-6 border-t border-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50/20">
            <span class="text-[13px] text-gray-500 font-bold">
              Mostrando <span class="text-gray-900">{{ filteredStudents.length }}</span> de <span class="text-gray-900">{{ students.length }}</span> alumnos
            </span>
            <div class="flex items-center gap-2">
              <button disabled class="px-5 py-2 border border-gray-200 bg-white rounded-xl text-gray-400 font-black uppercase text-[11px] tracking-widest cursor-not-allowed">Anterior</button>
              <button disabled class="px-5 py-2 border border-gray-100 bg-white shadow-sm rounded-xl text-brand-blue font-black uppercase text-[11px] tracking-widest hover:bg-blue-50 transition-all">Siguiente</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Bulk Photo Modal (Integrated Placeholder) -->
      <BulkPhotoUploadModal 
        v-if="showBulkModal" 
        @close="showBulkModal = false"
        @success="handleBulkSuccess"
      />

    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { IonPage, IonContent, IonRefresher, IonRefresherContent, alertController } from '@ionic/vue';
import { 
  personAddOutline, searchOutline, chevronDown, filter,
  createOutline, trashOutline, imagesOutline, eyeOutline, business, refreshOutline
} from 'ionicons/icons';
import api from '@/services/api';
import { storage } from '@/services/storage';
import BulkPhotoUploadModal from './modals/BulkPhotoUploadModal.vue';

const loading = ref(true);
const students = ref<any[]>([]);
const searchQuery = ref('');
const filterGrade = ref('');
const filterGroup = ref('');
const filterSchool = ref('');
const showBulkModal = ref(false);
const activeSchoolName = ref('');
const isAdmin = ref(false);
const userSchools = ref<any[]>([]);

const fetchStudents = async () => {
  loading.value = true;
  try {
    const params: any = {};
    if (isAdmin.value && filterSchool.value) {
      params.school_id = filterSchool.value;
    }
    const res = await api.get('/admin/students', { params });
    if (res.data.success) {
      students.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching students', error);
  } finally {
    loading.value = false;
  }
};

const studentsWithPhoto = computed(() => students.value.filter(s => !!s.photo_path).length);
const photoPercentage = computed(() => {
  if (students.value.length === 0) return 0;
  return Math.round((studentsWithPhoto.value / students.value.length) * 100);
});

const grades = computed(() => [...new Set(students.value.filter(s => s.classroom).map(s => s.classroom.grade))].sort());
const groups = computed(() => [...new Set(students.value.filter(s => s.classroom).map(s => s.classroom.group_letter))].sort());
const uniqueGroups = computed(() => [...new Set(students.value.filter(s => s.classroom).map(s => `${s.classroom.grade}${s.classroom.group_letter}`))]);

const filteredStudents = computed(() => {
  return students.value.filter(s => {
    const nameMatch = `${s.first_name || ''} ${s.last_name || ''}`.toLowerCase().includes((searchQuery.value || '').toLowerCase());
    const codeMatch = String(s.enrollment_code || '').toLowerCase().includes((searchQuery.value || '').toLowerCase());
    const gradeMatch = !filterGrade.value || (s.classroom && String(s.classroom.grade) === String(filterGrade.value));
    const groupMatch = !filterGroup.value || (s.classroom && String(s.classroom.group_letter) === String(filterGroup.value));
    return (nameMatch || codeMatch) && gradeMatch && groupMatch;
  });
});

const resetFilters = () => {
  searchQuery.value = '';
  filterGrade.value = '';
  filterGroup.value = '';
  filterSchool.value = '';
  if (isAdmin.value) {
    fetchStudents();
  }
};

const handleRefresh = async (event: any) => {
  await fetchStudents();
  event.target.complete();
};

const handleBulkSuccess = () => {
  showBulkModal.value = false;
  fetchStudents();
};

const confirmDelete = async (student: any) => {
  const alert = await alertController.create({
    header: 'Eliminar Estudiante',
    message: `¿Estás seguro de que deseas eliminar a ${student.first_name} ${student.last_name}? Esta acción no se puede deshacer.`,
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      { 
        text: 'Eliminar', 
        role: 'destructive',
        handler: async () => {
          try {
            await api.delete(`/admin/students/${student.id}`);
            fetchStudents();
          } catch (error) {
            console.error('Error deleting student', error);
          }
        }
      }
    ]
  });
  await alert.present();
};

onMounted(async () => {
  const user = await storage.get('auth_user');
  if (user && user.role === 'super_admin') {
    isAdmin.value = true;
  }

  const currentId = await storage.get('current_school_id');
  const storedSchools = await storage.get('user_schools');
  if (storedSchools) {
    userSchools.value = storedSchools;
    const active = storedSchools.find((s: any) => s.id === currentId);
    if (active) activeSchoolName.value = active.name;
    
    // Set active school filter if admin has one currently selected from Sidebar
    if (isAdmin.value && currentId) {
      filterSchool.value = currentId;
    }
  }

  // Fallback for Super Admin: always fetch master list of schools to guarantee the selector is populated
  if (isAdmin.value) {
    try {
      const res = await api.get('/admin/schools');
      if (res.data.success) {
        userSchools.value = res.data.data;
      }
    } catch (e) {
      console.warn("Could not fetch master schools list for filter", e);
    }
  }

  fetchStudents();
});
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}
</style>
