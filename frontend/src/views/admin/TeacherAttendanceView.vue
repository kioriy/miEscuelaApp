<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50">
      <div class="min-h-full flex flex-col bg-gray-50 pb-28">

    <!-- Header Area -->
    <div class="flex-none px-4 md:px-8 pt-8 pb-4">
      <div class="max-w-4xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
          <h1 class="text-3xl font-black text-gray-900 tracking-tight">
            Pase de Lista: <span class="text-[#EF4444]">Ausentes</span>
          </h1>
          <p class="text-gray-500 font-medium mt-1 text-sm md:text-base">
            Mostrando estudiantes sin registro de entrada.
          </p>
        </div>
        <div class="w-full md:w-80">
          <div class="relative">
            <ion-icon :icon="searchOutline" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></ion-icon>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Buscar por nombre o matrícula..." 
              class="w-full bg-white border border-gray-200 text-gray-900 font-medium rounded-xl pl-11 pr-4 py-3 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition-all"
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Student List Scrollable Area -->
    <div class="flex-grow overflow-y-auto px-4 md:px-8 pb-32 custom-scrollbar">
       <div class="max-w-4xl mx-auto space-y-4">
          
          <div v-if="loading" class="flex flex-col items-center justify-center py-12">
            <div class="w-10 h-10 border-4 border-brand-blue border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Cargando lista...</p>
          </div>

          <div v-else-if="filteredStudents.length === 0" class="text-center py-12 bg-white rounded-[32px] border border-gray-100 shadow-sm">
            <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center text-green-500 mx-auto mb-4">
               <ion-icon :icon="checkmarkCircleOutline" class="text-4xl"></ion-icon>
            </div>
            <h4 class="text-lg font-black text-gray-900 mb-1">Pase de Lista Completo</h4>
            <p class="text-gray-400 font-medium px-8 text-sm">No hay alumnos pendientes para esta clase o no coinciden con la búsqueda.</p>
          </div>

          <!-- Dynamic Student Cards -->
          <TransitionGroup name="list">
            <div 
              v-for="student in filteredStudents" 
              :key="student.id"
              class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all hover:border-gray-200 hover:shadow-md"
            >
              <!-- Student Info -->
              <div class="flex items-center gap-4">
                <!-- Avatar -->
                <div class="w-12 h-12 md:w-16 md:h-16 rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 border-2 border-white shadow-sm overflow-hidden flex items-center justify-center flex-shrink-0 text-brand-blue font-black text-xl">
                   <img v-if="student.avatar" :src="student.avatar" :alt="student.first_name" class="w-full h-full object-cover">
                   <span v-else>{{ student.first_name[0] }}{{ student.last_name[0] }}</span>
                </div>
                <div>
                  <h3 class="text-base md:text-lg font-black text-gray-900">{{ student.first_name }} {{ student.last_name }}</h3>
                  <p class="text-xs md:text-sm text-gray-500 font-medium">Matrícula: #{{ student.enrollment_code }}</p>
                  <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase bg-red-50 text-red-500 border border-red-100">
                    Sin Registro
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <button 
                  @click="markPresent(student.id)"
                  :disabled="processingId === student.id"
                  class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#22C55E] hover:bg-green-600 text-white font-black px-4 md:px-6 py-2.5 md:py-3 rounded-xl shadow-sm transition-all text-sm md:text-base disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ion-icon :icon="processingId === student.id ? timeOutline : checkmarkOutline"></ion-icon>
                  Presente
                </button>
                <button 
                  @click="openJustifyModal(student)"
                  :disabled="processingId === student.id"
                  class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#3B82F6] hover:bg-blue-600 text-white font-black px-4 md:px-6 py-2.5 md:py-3 rounded-xl shadow-sm transition-all text-sm md:text-base disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ion-icon :icon="clipboardOutline"></ion-icon>
                  Justificar
                </button>
              </div>
            </div>
          </TransitionGroup>
          
          <!-- Divider & Finalize Section Context -->
          <div v-if="!loading" class="pt-8 pb-4 text-center max-w-sm mx-auto animate-fade-in">
            <div class="h-px bg-gray-200 w-full mb-8"></div>
            <p class="text-xs md:text-sm text-gray-500 font-medium mb-6">
              Al finalizar, los alumnos que permanezcan en esta lista serán registrados como Ausentes.
            </p>
            <button 
              @click="finalizeAttendance"
              :disabled="finalizing"
              class="w-full bg-brand-blue hover:bg-blue-600 text-white font-black px-6 py-3.5 rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2 text-base md:text-lg disabled:opacity-75 disabled:cursor-not-allowed"
            >
              <ion-icon :icon="finalizing ? timeOutline : checkmarkCircleOutline" :class="{'animate-spin': finalizing}"></ion-icon>
              {{ finalizing ? 'Finalizando...' : 'Finalizar Pase de Lista' }}
            </button>
            <router-link to="/admin/teacher/dashboard" class="inline-block mt-4 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
              Cancelar y volver al panel
            </router-link>
          </div>

       </div>
    </div>
      </div>
    </ion-content>

    <!-- Bottom Stats Bar (Absolute to IonPage) -->
    <div class="absolute bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-[0_-10px_30px_rgba(0,0,0,0.03)] z-30">
        <div class="flex h-16 md:h-20 px-2 lg:px-0">
          <div class="flex-1 flex flex-col items-center justify-center border-r border-gray-100">
             <span class="text-[9px] md:text-xs font-black text-gray-400 tracking-wider uppercase mb-0.5 md:mb-1">Total Grupo</span>
             <span class="text-lg md:text-2xl font-black text-gray-900">{{ stats.total }}</span>
          </div>
          <div class="flex-1 flex flex-col items-center justify-center">
             <span class="text-[9px] md:text-xs font-black text-gray-400 tracking-wider uppercase mb-0.5 md:mb-1">Presentes</span>
             <span class="text-lg md:text-2xl font-black text-[#22C55E]">{{ stats.present }}</span>
          </div>
          <div class="flex-1 flex flex-col items-center justify-center bg-red-50 relative">
             <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
             <span class="text-[9px] md:text-xs font-black text-red-500 tracking-wider uppercase mb-0.5 md:mb-1">Ausentes</span>
             <div class="flex items-center gap-1.5 text-lg md:text-2xl font-black text-red-500">
                <ion-icon :icon="alertCircleOutline" class="text-base md:text-lg"></ion-icon>
                <span>{{ stats.absent }}</span>
             </div>
          </div>
        </div>
    </div>

    <!-- Justify Modal -->
    <div v-if="showJustifyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
       <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl">
          <div class="flex justify-between items-center mb-4">
             <h3 class="text-xl font-black text-gray-900">Justificar Inasistencia</h3>
             <button @click="closeJustifyModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200">
                <ion-icon :icon="closeOutline" class="text-xl"></ion-icon>
             </button>
          </div>
          <div class="mb-5 flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
             <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-brand-blue font-black overflow-hidden flex-shrink-0">
                 <img v-if="justifyingStudent?.avatar" :src="justifyingStudent.avatar" class="w-full h-full object-cover">
                 <span v-else>{{ justifyingStudent?.first_name[0] }}</span>
             </div>
             <div>
                <p class="font-bold text-gray-900 leading-tight">{{ justifyingStudent?.first_name }} {{ justifyingStudent?.last_name }}</p>
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wide">ID: {{ justifyingStudent?.enrollment_code }}</p>
             </div>
          </div>
          <div class="mb-6">
             <label class="block text-sm font-bold text-gray-700 mb-2">Motivo de Justificación (Opcional)</label>
             <textarea 
               v-model="justifyNotes"
               rows="3" 
               placeholder="Ej: Estudiante se reportó enfermo..."
               class="w-full border border-gray-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-brand-blue transition-all resize-none text-sm"
             ></textarea>
          </div>
          <div class="flex gap-3">
             <button @click="closeJustifyModal" class="flex-1 bg-white border border-gray-200 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-50 transition-all">
                Cancelar
             </button>
             <button 
               @click="submitJustify" 
               :disabled="processingId !== null"
               class="flex-1 bg-brand-blue text-white font-black py-3 rounded-xl hover:bg-blue-600 shadow-lg shadow-brand-blue/20 transition-all disabled:opacity-50"
             >
                {{ processingId !== null ? 'Procesando...' : 'Confirmar' }}
             </button>
          </div>
       </div>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent, IonIcon, toastController } from '@ionic/vue';
import { 
  searchOutline, 
  checkmarkOutline, 
  clipboardOutline, 
  checkmarkCircleOutline,
  alertCircleOutline,
  timeOutline,
  closeOutline
} from 'ionicons/icons';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const classroomId = route.params.classroomId;

const loading = ref(true);
const finalizing = ref(false);
const processingId = ref<number | null>(null);
const searchQuery = ref('');

const students = ref<any[]>([]);
const stats = ref({
  total: 0,
  present: 0,
  absent: 0
});

// Modal State
const showJustifyModal = ref(false);
const justifyingStudent = ref<any>(null);
const justifyNotes = ref('');

const showToast = async (message: string, color: 'success' | 'danger' | 'warning' = 'success') => {
  const toast = await toastController.create({
    message,
    duration: 3000,
    position: 'bottom',
    color,
    buttons: [
      {
        text: 'Cerrar',
        role: 'cancel'
      }
    ]
  });
  await toast.present();
};

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value;
  const q = searchQuery.value.toLowerCase();
  return students.value.filter(s => 
    s.first_name.toLowerCase().includes(q) || 
    s.last_name.toLowerCase().includes(q) || 
    s.enrollment_code.toLowerCase().includes(q)
  );
});

const loadPending = async () => {
  loading.value = true;
  try {
    const res = await api.get(`/admin/teacher/attendance/${classroomId}/pending`);
    if (res.data.success) {
      students.value = res.data.data.pending;
      stats.value = res.data.data.stats;
    }
  } catch (err) {
    console.error("Failed to load pending students", err);
  } finally {
    loading.value = false;
  }
};

const markPresent = async (studentId: number) => {
  processingId.value = studentId;
  try {
    const res = await api.post(`/admin/teacher/attendance/${classroomId}/mark`, {
      student_id: studentId,
      status: 'present'
    });
    if (res.data.success) {
      // Remove from list
      students.value = students.value.filter(s => s.id !== studentId);
      stats.value.present++;
      stats.value.absent--;
      showToast('Asistencia marcada correctamente');
    }
  } catch (err) {
    console.error("Failed to mark present", err);
    showToast('Error al marcar asistencia', 'danger');
  } finally {
    processingId.value = null;
  }
};

const openJustifyModal = (student: any) => {
  justifyingStudent.value = student;
  justifyNotes.value = '';
  showJustifyModal.value = true;
};

const closeJustifyModal = () => {
  showJustifyModal.value = false;
  justifyingStudent.value = null;
  justifyNotes.value = '';
};

const submitJustify = async () => {
  if (!justifyingStudent.value) return;
  const studentId = justifyingStudent.value.id;
  processingId.value = studentId;
  try {
    const res = await api.post(`/admin/teacher/attendance/${classroomId}/mark`, {
      student_id: studentId,
      status: 'excused',
      notes: justifyNotes.value
    });
    if (res.data.success) {
      students.value = students.value.filter(s => s.id !== studentId);
      // Wait, excused shouldn't increase `present`, but maybe decreases `absent`?
      stats.value.absent--;
      showToast('Inasistencia justificada correctamente');
      closeJustifyModal();
    }
  } catch (err) {
    console.error("Failed to justify", err);
    showToast('Error al justificar inasistencia', 'danger');
  } finally {
    processingId.value = null;
  }
};

const finalizeAttendance = async () => {
  finalizing.value = true;
  try {
    const res = await api.post(`/admin/teacher/attendance/${classroomId}/finalize`);
    if (res.data.success) {
      showToast(res.data.message || 'Pase de lista finalizado');
      router.push('/admin/teacher/dashboard');
    }
  } catch (err) {
    console.error("Failed to finalize attendance", err);
    showToast('Error al finalizar el pase de lista', 'danger');
    finalizing.value = false;
  }
};

onMounted(() => {
  loadPending();
});

</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.4s ease;
}
.list-enter-from {
  opacity: 0;
  transform: translateY(20px);
}
.list-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 10px;
}
</style>
