<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50">
      <div class="min-h-full flex flex-col bg-gray-50 pb-28">
        
        <!-- Header -->
        <div class="px-4 md:px-8 pt-8 pb-4">
          <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
              <ion-icon :icon="chatbubbleOutline" class="text-brand-blue"></ion-icon>
              {{ isDirector ? 'Mensajería Institucional' : 'Enviar Mensaje' }}
            </h1>
            <p class="text-gray-500 font-medium mt-1 text-sm md:text-base">
              {{ isDirector 
                ? 'Como Director, puede enviar avisos a toda la escuela, grupos específicos o alumnos individuales.' 
                : 'Envíe avisos o circulares a padres de familia por grupo o alumno.' 
              }}
            </p>
          </div>
        </div>

        <!-- Main Form -->
        <div class="flex-grow px-4 md:px-8">
          <div class="max-w-4xl mx-auto bg-white rounded-[32px] border border-gray-100 shadow-sm p-6 md:p-8">
            
            <div class="space-y-8">
              <!-- Target Type Selector -->
              <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Alcance del Mensaje</label>
                <div :class="isDirector ? 'grid-cols-1 sm:grid-cols-3' : 'grid-cols-2'" class="grid gap-4">
                  <button 
                    v-if="isDirector"
                    @click="formData.type = 'all_school'; formData.target_id = 'all'"
                    :class="formData.type === 'all_school' ? 'border-brand-blue bg-blue-50 text-brand-blue shadow-md' : 'border-gray-100 bg-gray-50 text-gray-500 hover:bg-gray-100'"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all group shrink-0"
                  >
                    <ion-icon :icon="businessOutline" class="text-2xl mb-1"></ion-icon>
                    <span class="text-sm font-bold leading-tight">Toda la Escuela</span>
                  </button>
                  <button 
                    @click="formData.type = 'group'; formData.target_id = ''"
                    :class="formData.type === 'group' ? 'border-brand-blue bg-blue-50 text-brand-blue shadow-md' : 'border-gray-100 bg-gray-50 text-gray-500 hover:bg-gray-100'"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all group shrink-0"
                  >
                    <ion-icon :icon="peopleOutline" class="text-2xl mb-1"></ion-icon>
                    <span class="text-sm font-bold leading-tight">Por Grupo</span>
                  </button>
                  <button 
                    @click="formData.type = 'student'; formData.target_id = ''"
                    :class="formData.type === 'student' ? 'border-brand-blue bg-blue-50 text-brand-blue shadow-md' : 'border-gray-100 bg-gray-50 text-gray-500 hover:bg-gray-100'"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all group shrink-0"
                  >
                    <ion-icon :icon="personOutline" class="text-2xl mb-1"></ion-icon>
                    <span class="text-sm font-bold leading-tight">Alumno Específico</span>
                  </button>
                </div>
              </div>

              <!-- Target Selection -->
              <div v-if="formData.type === 'group'" class="animate-fade-in">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Seleccionar Grupo</label>
                <div class="relative">
                  <select 
                    v-model="formData.target_id"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 font-bold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue appearance-none cursor-pointer"
                  >
                    <option value="" disabled>Seleccione un grupo...</option>
                    <option v-for="group in classrooms" :key="group.id" :value="group.id">
                      {{ group.name }}
                    </option>
                  </select>
                  <ion-icon :icon="chevronDownOutline" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></ion-icon>
                </div>
              </div>

              <div v-if="formData.type === 'student'" class="animate-fade-in">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Seleccionar Alumno</label>
                <div class="relative">
                  <ion-icon :icon="searchOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></ion-icon>
                  <input 
                    type="text"
                    v-model="studentSearch"
                    placeholder="Buscar alumno por nombre o matrícula..."
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 font-bold rounded-xl pl-11 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue"
                  />
                </div>
                <!-- Search Results -->
                <div v-if="studentSearch && filteredStudents.length > 0" class="mt-2 bg-white border border-gray-100 rounded-2xl shadow-lg max-h-48 overflow-y-auto z-10 relative">
                   <div 
                    v-for="student in filteredStudents" 
                    :key="student.id"
                    @click="selectStudent(student)"
                    class="px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-blue-50 cursor-pointer transition-colors"
                  >
                    <p class="text-sm font-bold text-gray-900">{{ student.full_name }}</p>
                    <p class="text-[10px] text-gray-500 font-medium">ID: {{ student.id }}</p>
                  </div>
                </div>
                <div v-if="selectedStudent" class="mt-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 flex items-center justify-between">
                  <div class="flex items-center gap-2 text-brand-blue">
                    <ion-icon :icon="checkmarkCircle"></ion-icon>
                    <span class="text-sm font-bold">{{ selectedStudent.full_name }}</span>
                  </div>
                  <button @click="selectedStudent = null; formData.target_id = ''" class="text-gray-400 hover:text-red-500 transition-colors">
                    <ion-icon :icon="closeCircleOutline"></ion-icon>
                  </button>
                </div>
              </div>

              <div v-if="formData.type === 'all_school'" class="animate-fade-in bg-amber-50 border border-amber-100 rounded-2xl p-4 flex gap-3 text-amber-700">
                <ion-icon :icon="alertCircleOutline" class="text-xl shrink-0"></ion-icon>
                <p class="text-xs font-bold leading-relaxed">
                  Este mensaje será visible para <span class="uppercase">todos</span> los padres de familia de la institución. Use este alcance para avisos generales, suspensiones o circulares globales.
                </p>
              </div>

              <!-- Message Content -->
              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Asunto / Título</label>
                  <input 
                    v-model="formData.title"
                    type="text" 
                    placeholder="Ej: Aviso Importante de Dirección"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 font-bold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue"
                  />
                </div>
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Contenido de la Circular</label>
                  <textarea 
                    v-model="formData.content"
                    rows="8"
                    placeholder="Escriba aquí el cuerpo del mensaje..."
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 font-medium rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue resize-none"
                  ></textarea>
                </div>
              </div>

              <!-- Submit -->
              <div class="pt-4">
                <button 
                  @click="submitMessage"
                  :disabled="loading || !canSubmit"
                  class="w-full bg-brand-blue hover:bg-blue-600 active:scale-[0.98] text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:active:scale-100"
                >
                  <ion-icon v-if="!loading" :icon="paperPlaneOutline"></ion-icon>
                  <div v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                  {{ loading ? 'Emitiendo Circular...' : (isDirector ? 'Enviar Mensaje Institucional' : 'Enviar Mensaje a Padres') }}
                </button>
              </div>

            </div>

          </div>
        </div>

        <!-- History Preview (Only for Directors) -->
        <div v-if="isDirector" class="px-4 md:px-8 mt-12 mb-12">
          <div class="max-w-4xl mx-auto">
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-6 flex items-center gap-2">
              <ion-icon :icon="timeOutline" class="text-gray-400"></ion-icon>
              Últimos Mensajes Enviados
            </h3>
            
            <div v-if="historyLoading" class="flex justify-center py-8">
              <div class="w-8 h-8 border-3 border-gray-100 border-t-brand-blue rounded-full animate-spin"></div>
            </div>
            
            <div v-else-if="history.length === 0" class="bg-white rounded-3xl border border-gray-100 p-8 text-center">
              <p class="text-gray-400 font-bold italic">No has enviado mensajes recientemente.</p>
            </div>

            <div v-else class="space-y-4">
              <div 
                v-for="msg in history" 
                :key="msg.id" 
                class="msg-card bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden"
              >
                <!-- Card Header -->
                <div class="p-5">
                  <div class="flex justify-between items-start gap-3 mb-3">
                    <!-- Title + timestamp -->
                    <div class="flex-1 min-w-0">
                      <h4 class="font-black text-gray-900 leading-tight">{{ msg.title }}</h4>
                      <span class="text-[10px] font-bold text-gray-400 mt-0.5 block">{{ msg.time_ago }}</span>
                    </div>
                    <!-- Actions -->
                    <div class="flex items-center gap-2 shrink-0">
                      <button
                        @click="openEditModal(msg)"
                        class="edit-btn flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 text-gray-400 hover:text-brand-blue transition-all text-xs font-bold"
                        title="Editar mensaje"
                      >
                        <ion-icon :icon="createOutline" class="text-base"></ion-icon>
                        Editar
                      </button>
                      <button
                        @click="toggleExpand(msg.id)"
                        class="expand-btn flex items-center justify-center w-8 h-8 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-100 text-gray-400 transition-all"
                        :title="expandedIds.has(msg.id) ? 'Colapsar' : 'Ver mensaje completo'"
                      >
                        <ion-icon 
                          :icon="chevronDownOutline" 
                          class="text-base transition-transform duration-300"
                          :class="expandedIds.has(msg.id) ? 'rotate-180' : ''"
                        ></ion-icon>
                      </button>
                    </div>
                  </div>

                  <!-- Content preview (always visible, 2 lines) -->
                  <p 
                    class="text-sm text-gray-600 font-medium leading-relaxed msg-preview"
                    :class="expandedIds.has(msg.id) ? 'expanded' : ''"
                  >{{ msg.content }}</p>

                  <!-- Read more hint when collapsed -->
                  <button 
                    v-if="!expandedIds.has(msg.id)"
                    @click="toggleExpand(msg.id)"
                    class="text-xs font-bold text-brand-blue mt-1 hover:underline"
                  >Ver mensaje completo</button>

                  <!-- Tags -->
                  <div class="flex items-center gap-2 mt-3">
                    <span 
                      :class="msg.is_general ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-blue-50 text-brand-blue border-blue-100'"
                      class="text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded-lg border"
                    >
                      {{ msg.is_general ? 'Todo el plantel' : 'Segmentado' }}
                    </span>
                    <span class="text-[11px] font-bold text-gray-400">Dirigido a: {{ msg.recipients }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Success Modal -->
      <ion-modal 
        :is-open="showSuccessModal" 
        @didDismiss="showSuccessModal = false"
        class="center-modal"
      >
        <div class="modal-content p-8 text-center bg-white flex flex-col items-center justify-center">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-500 mb-6 border-4 border-white shadow-lg">
            <ion-icon :icon="checkmarkCircle" class="text-5xl"></ion-icon>
          </div>
          <h2 class="text-2xl font-black text-gray-900 mb-2 tracking-tight">Circular Emitida</h2>
          <p class="text-gray-500 font-medium mb-8 leading-relaxed">El mensaje se ha procesado exitosamente y estará disponible para los padres de familia.</p>
          <button @click="closeSuccess" class="w-full bg-gray-900 text-white font-bold py-4 rounded-2xl shadow-lg hover:bg-black transition-all active:scale-[0.98]">
            Entendido
          </button>
        </div>
      </ion-modal>

      <!-- Edit Message Modal -->
      <ion-modal
        :is-open="showEditModal"
        @didDismiss="closeEditModal"
        class="center-modal edit-modal"
      >
        <div class="modal-content bg-white flex flex-col" style="max-height: 90vh; overflow-y: auto;">
          <!-- Modal Header -->
          <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                <ion-icon :icon="createOutline" class="text-xl text-brand-blue"></ion-icon>
              </div>
              <div>
                <h2 class="text-lg font-black text-gray-900 leading-tight">Editar Mensaje</h2>
                <p class="text-xs text-gray-400 font-medium">Solo título y contenido</p>
              </div>
            </div>
            <button @click="closeEditModal" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">
              <ion-icon :icon="closeCircleOutline" class="text-xl"></ion-icon>
            </button>
          </div>

          <!-- Edit Form -->
          <div class="px-6 py-5 space-y-5">
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Asunto / Título</label>
              <input
                v-model="editForm.title"
                type="text"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 font-bold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue"
                placeholder="Título del mensaje"
              />
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Contenido de la Circular</label>
              <textarea
                v-model="editForm.content"
                rows="8"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 font-medium rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue resize-none"
                placeholder="Contenido del mensaje..."
              ></textarea>
            </div>

            <!-- Warning note -->
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex gap-2 text-amber-700">
              <ion-icon :icon="alertCircleOutline" class="text-lg shrink-0 mt-0.5"></ion-icon>
              <p class="text-xs font-bold leading-relaxed">Los destinatarios (alcance) no pueden modificarse. Solo se actualiza el texto del mensaje.</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="px-6 pb-6 flex gap-3">
            <button
              @click="closeEditModal"
              class="flex-1 py-3 rounded-2xl border-2 border-gray-100 text-gray-600 font-bold hover:bg-gray-50 transition-all"
            >
              Cancelar
            </button>
            <button
              @click="saveEdit"
              :disabled="editLoading || !editForm.title.trim() || !editForm.content.trim()"
              class="flex-1 bg-brand-blue text-white font-black py-3 rounded-2xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <div v-if="editLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <ion-icon v-else :icon="checkmarkCircle" class="text-lg"></ion-icon>
              {{ editLoading ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </div>
        </div>
      </ion-modal>

    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { IonPage, IonContent, IonIcon, IonModal } from '@ionic/vue';
import { 
  chatbubbleOutline, 
  peopleOutline, 
  personOutline, 
  businessOutline,
  searchOutline, 
  checkmarkCircle,
  paperPlaneOutline,
  closeCircleOutline,
  chevronDownOutline,
  alertCircleOutline,
  timeOutline,
  createOutline
} from 'ionicons/icons';
import api from '@/services/api';
import { storage } from '@/services/storage';
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);
const historyLoading = ref(false);
const showSuccessModal = ref(false);

// ── Expand / collapse state for history cards ──────────────────────────────
const expandedIds = ref<Set<number>>(new Set());
const toggleExpand = (id: number) => {
  const s = new Set(expandedIds.value);
  s.has(id) ? s.delete(id) : s.add(id);
  expandedIds.value = s;
};

// ── Edit modal state ────────────────────────────────────────────────────────
const showEditModal = ref(false);
const editLoading = ref(false);
const editTarget = ref<any>(null);
const editForm = ref({ title: '', content: '' });

const openEditModal = (msg: any) => {
  editTarget.value = msg;
  editForm.value = { title: msg.title, content: msg.content };
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  editTarget.value = null;
};

const saveEdit = async () => {
  if (!editTarget.value || !editForm.value.title.trim() || !editForm.value.content.trim()) return;
  try {
    editLoading.value = true;
    const res = await api.put(`/admin/director/messaging/${editTarget.value.id}`, editForm.value);
    if (res.data.success) {
      // Update locally for instant feedback
      const idx = history.value.findIndex((m: any) => m.id === editTarget.value.id);
      if (idx !== -1) {
        history.value[idx].title   = editForm.value.title;
        history.value[idx].content = editForm.value.content;
      }
      closeEditModal();
    }
  } catch (error: any) {
    console.error('Error updating message:', error);
    alert(error.response?.data?.message || 'Error al actualizar el mensaje');
  } finally {
    editLoading.value = false;
  }
};

const classrooms = ref<any[]>([]);
const students = ref<any[]>([]);
const history = ref<any[]>([]);
const studentSearch = ref('');
const selectedStudent = ref<any>(null);

const currentProfile = ref('');
const isDirector = computed(() => currentProfile.value === 'director');

const formData = ref({
  type: 'group',
  target_id: '',
  title: '',
  content: ''
});

const loadProfile = async () => {
  const profile = await storage.get('current_profile');
  if (profile) {
    currentProfile.value = profile;
    // Default form type based on role
    if (profile === 'director') {
      formData.value.type = 'all_school';
      formData.value.target_id = 'all';
    } else {
      formData.value.type = 'group';
    }
  }
};

const fetchContext = async () => {
  try {
    const endpoint = isDirector.value 
      ? '/admin/director/messaging/context' 
      : '/admin/teacher/messaging/context';
      
    const res = await api.get(endpoint);
    if (res.data.success) {
      classrooms.value = res.data.data.classrooms;
      students.value = res.data.data.students;
    }
  } catch (error) {
    console.error('Error fetching messaging context:', error);
  }
};

const fetchHistory = async () => {
  if (!isDirector.value) return;
  
  historyLoading.value = true;
  try {
    const res = await api.get('/admin/director/messaging/history');
    if (res.data.success) {
      history.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching messaging history:', error);
  } finally {
    historyLoading.value = false;
  }
};

onMounted(async () => {
  await loadProfile();
  fetchContext();
  fetchHistory();
});

const filteredStudents = computed(() => {
  if (!studentSearch.value || studentSearch.value.length < 2) return [];
  const query = studentSearch.value.toLowerCase();
  return students.value.filter(s => 
    s.full_name.toLowerCase().includes(query) || 
    s.id.toString().includes(query)
  ).slice(0, 5);
});

const selectStudent = (student: any) => {
  selectedStudent.value = student;
  formData.value.target_id = student.id.toString();
  studentSearch.value = '';
};

const canSubmit = computed(() => {
  const hasTarget = formData.value.type === 'all_school' || formData.value.target_id !== '';
  return hasTarget && formData.value.title.trim().length > 3 && formData.value.content.trim().length > 10;
});

const submitMessage = async () => {
  if (!canSubmit.value) return;
  
  try {
    loading.value = true;
    const endpoint = isDirector.value 
      ? '/admin/director/messaging/send' 
      : '/admin/teacher/messaging/send';
      
    const res = await api.post(endpoint, formData.value);
    if (res.data.success) {
      showSuccessModal.value = true;
      resetForm();
      if (isDirector.value) fetchHistory();
    }
  } catch (error: any) {
    console.error('Error sending message:', error);
    alert(error.response?.data?.message || 'Error al enviar el mensaje');
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  formData.value = {
    type: isDirector.value ? 'all_school' : 'group',
    target_id: isDirector.value ? 'all' : '',
    title: '',
    content: ''
  };
  selectedStudent.value = null;
  studentSearch.value = '';
};

const closeSuccess = () => {
  showSuccessModal.value = false;
  router.push('/admin/dashboard');
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Message history cards */
.msg-card {
  transition: box-shadow 0.2s ease;
}

/* Content preview: 2-line clamp when collapsed, full when expanded */
.msg-preview {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: max-height 0.35s ease, -webkit-line-clamp 0s;
  max-height: 3.2em;
  white-space: pre-wrap;
  word-break: break-word;
}

.msg-preview.expanded {
  -webkit-line-clamp: unset;
  max-height: 2000px; /* large enough for any message */
  overflow: visible;
}

/* Rotation for expand chevron */
.rotate-180 {
  transform: rotate(180deg);
}

/* Edit button hover */
.edit-btn:active {
  transform: scale(0.96);
}

/* Center modals */
ion-modal.center-modal {
  --width: 90%;
  --max-width: 440px;
  --height: auto;
  --border-radius: 32px;
  --box-shadow: 0 28px 48px rgba(0, 0, 0, 0.4);
}

ion-modal.center-modal .modal-content {
  border-radius: 32px;
}

/* Edit modal is a bit wider */
ion-modal.edit-modal {
  --max-width: 520px;
}
</style>
