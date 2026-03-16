<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50">
      <div class="min-h-full flex flex-col bg-gray-50 pb-28">
        
        <!-- Header -->
        <div class="px-4 md:px-8 pt-8 pb-4">
          <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
              <ion-icon :icon="chatbubbleOutline" class="text-brand-blue"></ion-icon>
              Mensajería Institucional
            </h1>
            <p class="text-gray-500 font-medium mt-1 text-sm md:text-base">
              Como Director, puede enviar avisos a toda la escuela, grupos específicos o alumnos individuales.
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
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <button 
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
                  {{ loading ? 'Emitiendo Circular...' : 'Enviar Mensaje Institucional' }}
                </button>
              </div>

            </div>

          </div>
        </div>

        <!-- History Preview -->
        <div class="px-4 md:px-8 mt-12 mb-12">
          <div class="max-w-4xl mx-auto">
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-6 flex items-center gap-2">
              <ion-icon :icon="historyOutline" class="text-gray-400"></ion-icon>
              Últimos Mensajes Enviados
            </h3>
            
            <div v-if="historyLoading" class="flex justify-center py-8">
              <div class="w-8 h-8 border-3 border-gray-100 border-t-brand-blue rounded-full animate-spin"></div>
            </div>
            
            <div v-else-if="history.length === 0" class="bg-white rounded-3xl border border-gray-100 p-8 text-center">
              <p class="text-gray-400 font-bold italic">No has enviado mensajes recientemente.</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="msg in history" :key="msg.id" class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-2">
                  <h4 class="font-black text-gray-900 leading-tight">{{ msg.title }}</h4>
                  <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">{{ msg.time_ago }}</span>
                </div>
                <p class="text-sm text-gray-600 font-medium mb-3 line-clamp-2">{{ msg.content }}</p>
                <div class="flex items-center gap-2">
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
          <button @click="showSuccessModal = false" class="w-full bg-gray-900 text-white font-bold py-4 rounded-2xl shadow-lg hover:bg-black transition-all active:scale-[0.98]">
            Entendido
          </button>
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
  historyOutline
} from 'ionicons/icons';
import api from '@/services/api';

const loading = ref(false);
const historyLoading = ref(false);
const showSuccessModal = ref(false);

const classrooms = ref<any[]>([]);
const students = ref<any[]>([]);
const history = ref<any[]>([]);
const studentSearch = ref('');
const selectedStudent = ref<any>(null);

const formData = ref({
  type: 'all_school',
  target_id: 'all',
  title: '',
  content: ''
});

const fetchContext = async () => {
  try {
    const res = await api.get('/admin/director/messaging/context');
    if (res.data.success) {
      classrooms.value = res.data.data.classrooms;
      students.value = res.data.data.students;
    }
  } catch (error) {
    console.error('Error fetching messaging context:', error);
  }
};

const fetchHistory = async () => {
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

onMounted(() => {
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
    const res = await api.post('/admin/director/messaging/send', formData.value);
    if (res.data.success) {
      showSuccessModal.value = true;
      resetForm();
      fetchHistory();
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
    type: 'all_school',
    target_id: 'all',
    title: '',
    content: ''
  };
  selectedStudent.value = null;
  studentSearch.value = '';
};
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}
.animate-fade-in {
  animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

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
</style>
