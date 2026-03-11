<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50">
      <div class="min-h-full flex flex-col bg-gray-50 pb-28">
        
        <!-- Header -->
        <div class="px-4 md:px-8 pt-8 pb-4">
          <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
              <ion-icon :icon="chatbubbleOutline" class="text-brand-blue"></ion-icon>
              Enviar Mensaje
            </h1>
            <p class="text-gray-500 font-medium mt-1 text-sm md:text-base">
              Envía avisos o circulares a padres de familia por grupo o alumno.
            </p>
          </div>
        </div>

        <!-- Main Form -->
        <div class="flex-grow px-4 md:px-8">
          <div class="max-w-4xl mx-auto bg-white rounded-[32px] border border-gray-100 shadow-sm p-6 md:p-8">
            
            <div class="space-y-8">
              <!-- Target Type Selector -->
              <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Destinatario</label>
                <div class="grid grid-cols-2 gap-4">
                  <button 
                    @click="formData.type = 'group'"
                    :class="formData.type === 'group' ? 'border-brand-blue bg-blue-50 text-brand-blue' : 'border-gray-100 bg-gray-50 text-gray-500 hover:bg-gray-100'"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all group"
                  >
                    <ion-icon :icon="peopleOutline" class="text-2xl mb-1"></ion-icon>
                    <span class="text-sm font-bold">Por Grupo</span>
                  </button>
                  <button 
                    @click="formData.type = 'student'"
                    :class="formData.type === 'student' ? 'border-brand-blue bg-blue-50 text-brand-blue' : 'border-gray-100 bg-gray-50 text-gray-500 hover:bg-gray-100'"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all group"
                  >
                    <ion-icon :icon="personOutline" class="text-2xl mb-1"></ion-icon>
                    <span class="text-sm font-bold">Alumno Específico</span>
                  </button>
                </div>
              </div>

              <!-- Target Selection -->
              <div v-if="formData.type === 'group'" class="animate-fade-in">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Seleccionar Grupo</label>
                <select 
                  v-model="formData.target_id"
                  class="w-full bg-gray-50 border border-gray-200 text-gray-900 font-bold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue appearance-none cursor-pointer"
                >
                  <option value="" disabled>Seleccione un grupo...</option>
                  <option v-for="group in classrooms" :key="group.id" :value="group.id">
                    {{ group.name }}
                  </option>
                </select>
              </div>

              <div v-if="formData.type === 'student'" class="animate-fade-in">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Seleccionar Alumno</label>
                <div class="relative">
                  <ion-icon :icon="searchOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></ion-icon>
                  <input 
                    type="text"
                    v-model="studentSearch"
                    placeholder="Buscar alumno..."
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
                  <button @click="selectedStudent = null; formData.target_id = ''" class="text-gray-400 hover:text-red-500">
                    <ion-icon :icon="closeCircleOutline"></ion-icon>
                  </button>
                </div>
              </div>

              <!-- Message Content -->
              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Asunto / Título</label>
                  <input 
                    v-model="formData.title"
                    type="text" 
                    placeholder="Ej: Aviso de reunión de padres"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 font-bold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue"
                  />
                </div>
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Mensaje</label>
                  <textarea 
                    v-model="formData.content"
                    rows="6"
                    placeholder="Escriba aquí el contenido del aviso..."
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
                  {{ loading ? 'Enviando Mensaje...' : 'Enviar Mensaje a Padres' }}
                </button>
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
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-500 mb-6">
            <ion-icon :icon="checkmarkCircle" class="text-5xl"></ion-icon>
          </div>
          <h2 class="text-2xl font-black text-gray-900 mb-2">¡Mensaje Enviado!</h2>
          <p class="text-gray-500 font-medium mb-8">Los padres de familia recibirán la notificación de inmediato.</p>
          <button @click="resetAndGoBack" class="w-full bg-gray-900 text-white font-bold py-4 rounded-2xl shadow-lg hover:bg-black transition-all active:scale-[0.98]">
            Volver al Panel
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
  searchOutline, 
  checkmarkCircle,
  paperPlaneOutline,
  closeCircleOutline
} from 'ionicons/icons';
import api from '@/services/api';
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);
const showSuccessModal = ref(false);

const classrooms = ref<any[]>([]);
const students = ref<any[]>([]);
const studentSearch = ref('');
const selectedStudent = ref<any>(null);

const formData = ref({
  type: 'group',
  target_id: '',
  title: '',
  content: ''
});

const fetchContext = async () => {
  try {
    const res = await api.get('/admin/teacher/messaging/context');
    if (res.data.success) {
      classrooms.value = res.data.data.classrooms;
      students.value = res.data.data.students;
    }
  } catch (error) {
    console.error('Error fetching messaging context:', error);
  }
};

onMounted(() => {
  fetchContext();
});

const filteredStudents = computed(() => {
  if (!studentSearch.value) return [];
  const query = studentSearch.value.toLowerCase();
  return students.value.filter(s => 
    s.full_name.toLowerCase().includes(query) || 
    s.id.toString().includes(query)
  ).slice(0, 5);
});

const selectStudent = (student: any) => {
  selectedStudent.value = student;
  formData.value.target_id = student.id;
  studentSearch.value = '';
};

const canSubmit = computed(() => {
  return formData.value.target_id && formData.value.title && formData.value.content;
});

const submitMessage = async () => {
  if (!canSubmit.value) return;
  
  try {
    loading.value = true;
    const res = await api.post('/admin/teacher/messaging/send', formData.value);
    if (res.data.success) {
      showSuccessModal.value = true;
      resetForm();
    }
  } catch (error) {
    console.error('Error sending message:', error);
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  formData.value = {
    type: 'group',
    target_id: '',
    title: '',
    content: ''
  };
  selectedStudent.value = null;
  studentSearch.value = '';
};

const resetAndGoBack = () => {
  showSuccessModal.value = false;
  router.push('/admin/dashboard');
};
</script>

<style scoped>
.font-sans {
  font-family: 'Inter', sans-serif;
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
  --max-width: 400px;
  --height: auto;
  --border-radius: 32px;
  --box-shadow: 0 28px 48px rgba(0, 0, 0, 0.4);
}

ion-modal.center-modal .modal-content {
  border-radius: 32px;
}
</style>
