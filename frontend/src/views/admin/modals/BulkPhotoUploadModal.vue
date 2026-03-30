<template>
  <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-opacity">
    <div class="bg-white w-full max-w-xl rounded-[40px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fade-in font-sans">
      
      <!-- Header -->
      <div class="p-8 border-b border-gray-100 flex items-center justify-between bg-white relative">
        <div>
          <h2 class="text-2xl font-black text-gray-900 tracking-tight leading-none mb-1">Carga Masiva de Fotos</h2>
          <p class="text-[12px] font-bold text-gray-400 uppercase tracking-widest">Sincronización vía archivo ZIP</p>
        </div>
        <button @click="$emit('close')" class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
          <ion-icon :icon="closeOutline" class="text-2xl"></ion-icon>
        </button>
      </div>

      <!-- Body -->
      <div class="flex-grow overflow-y-auto p-8 custom-scrollbar">
        
        <!-- Step 1: Upload -->
        <div v-if="step === 'upload'" class="space-y-6">
          <div class="bg-blue-50 border border-blue-100 rounded-3xl p-6 relative overflow-hidden group">
            <div class="relative z-10">
              <h4 class="text-brand-blue font-black text-sm uppercase tracking-widest mb-3 flex items-center gap-2">
                <ion-icon :icon="informationCircleOutline" class="text-lg"></ion-icon>
                Instrucciones Críticas
              </h4>
              <ul class="space-y-2 text-[13px] text-blue-800/80 font-bold leading-relaxed">
                <li class="flex gap-2">
                  <span class="text-brand-blue">•</span>
                  Las fotos deben estar en formato JPG o PNG.
                </li>
                <li class="flex gap-2">
                  <span class="text-brand-blue">•</span>
                  Vínculo por Nombre: El nombre del archivo debe ser el nombre del alumno normalizado (ej: <code class="bg-white/50 px-1.5 rounded text-brand-blue italic">juan_perez_garcia.jpg</code>).
                </li>
                <li class="flex gap-2">
                  <span class="text-brand-blue">•</span>
                  Comprime todas las fotos directamente en un archivo ZIP (sin carpetas anidadas).
                </li>
              </ul>
            </div>
            <ion-icon :icon="imagesOutline" class="absolute -right-8 -bottom-8 text-8xl text-blue-200/30 rotate-12 group-hover:rotate-0 transition-transform duration-700"></ion-icon>
          </div>

          <div 
            @dragover.prevent="isDragging = true" 
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="border-4 border-dashed rounded-[32px] p-10 flex flex-col items-center justify-center text-center transition-all cursor-pointer"
            :class="isDragging ? 'border-brand-blue bg-blue-50 scale-[0.98]' : 'border-gray-100 bg-gray-50 hover:border-gray-200 hover:bg-gray-100/50'"
            @click="triggerBulkPhotoUpload"
          >
            <input id="bulkPhotoInput" type="file" ref="fileInput" class="hidden" accept=".zip" @change="handleFileChange">
            
            <div class="w-20 h-20 bg-white rounded-3xl shadow-sm flex items-center justify-center mb-6 text-brand-blue">
               <ion-icon :icon="cloudUploadOutline" class="text-4xl"></ion-icon>
            </div>
            
            <h4 class="text-lg font-black text-gray-900 mb-2">Selecciona el archivo ZIP</h4>
            <p class="text-gray-400 font-medium text-sm px-6">Arrastra tu archivo aquí o haz clic para buscar en tu equipo.</p>
            
            <div v-if="selectedFile" class="mt-6 flex items-center gap-3 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100 animate-fade-in">
              <ion-icon :icon="documentText" class="text-brand-blue"></ion-icon>
              <span class="text-sm font-bold text-gray-700">{{ selectedFile.name }}</span>
              <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-l pl-3 ml-2">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</span>
            </div>
          </div>
        </div>

        <!-- Step 2: Processing -->
        <div v-if="step === 'processing'" class="py-12 flex flex-col items-center justify-center text-center space-y-8 animate-fade-in">
          <div class="relative w-32 h-32 flex items-center justify-center">
            <div class="absolute inset-0 border-8 border-gray-100 rounded-full"></div>
            <div 
              class="absolute inset-0 border-8 border-brand-blue rounded-full border-t-transparent animate-spin"
            ></div>
            <span class="text-2xl font-black text-gray-900">{{ progress }}%</span>
          </div>
          
          <div>
            <h4 class="text-xl font-black text-gray-900 mb-2">{{ processingStatus }}</h4>
            <p class="text-gray-400 font-bold uppercase text-[11px] tracking-[0.2em] animate-pulse">Este proceso puede tardar unos minutos...</p>
          </div>

          <div class="w-full bg-gray-50 rounded-2xl p-6 border border-gray-100 text-left">
            <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Logs de procesamiento</p>
            <div class="space-y-1.5 font-mono text-[10px] leading-tight text-gray-600 max-h-32 overflow-y-auto custom-scrollbar">
              <div v-for="(log, i) in logs" :key="i" class="flex gap-2">
                <span class="text-brand-blue font-bold">[{{ new Date().toLocaleTimeString() }}]</span>
                <span>{{ log }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Result -->
        <div v-if="step === 'result'" class="space-y-8 animate-fade-in">
          <div class="text-center">
            <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center text-green-500 mx-auto mb-4 border-2 border-green-100">
               <ion-icon :icon="checkmarkCircleOutline" class="text-4xl"></ion-icon>
            </div>
            <h4 class="text-xl font-black text-gray-900 mb-1">¡Procesamiento Completado!</h4>
            <p class="text-gray-400 font-medium text-sm">Resumen de la vinculación fotográfica</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="bg-emerald-50/50 border border-emerald-100 p-5 rounded-3xl text-center">
              <p class="text-3xl font-black text-emerald-600 leading-none mb-1">{{ results.success }}</p>
              <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Vinculados</p>
            </div>
            <div class="bg-red-50/50 border border-red-100 p-5 rounded-3xl text-center">
              <p class="text-3xl font-black text-red-500 leading-none mb-1">{{ results.errors.length }}</p>
              <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Sin Coincidencia</p>
            </div>
          </div>

          <div v-if="results.errors.length > 0" class="bg-white border border-gray-100 rounded-[32px] overflow-hidden">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex items-center justify-between">
              <span class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Archivos no vinculados</span>
              <span class="bg-red-50 text-red-600 text-[10px] font-black px-2 py-0.5 rounded-full">{{ results.errors.length }}</span>
            </div>
            <div class="max-h-48 overflow-y-auto p-4 space-y-2 custom-scrollbar">
              <div v-for="(err, i) in results.errors" :key="i" class="flex items-center gap-3 p-3 bg-red-50/30 rounded-xl border border-red-50">
                <ion-icon :icon="closeCircleOutline" class="text-red-400"></ion-icon>
                <span class="text-[12px] font-bold text-gray-600">{{ err }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="p-8 border-t border-gray-50 flex items-center justify-between bg-gray-50/50">
        <div v-if="step === 'upload'">
           <p class="text-[11px] font-bold text-gray-400 leading-tight">Máximo 50MB por archivo ZIP.</p>
        </div>
        <div v-else-if="step === 'result'" class="flex-grow flex justify-center">
           <button @click="$emit('success')" class="bg-brand-blue text-white font-black px-12 py-4 rounded-2xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-all flex items-center gap-3">
             Finalizar y Ver Cambios
           </button>
        </div>
        
        <div v-if="step === 'upload'" class="flex gap-4">
          <button @click="$emit('close')" class="px-6 py-4 font-black text-gray-500 hover:text-gray-900 transition-all uppercase text-[12px] tracking-widest">Cancelar</button>
          <button 
            @click="startUpload" 
            :disabled="!selectedFile"
            class="bg-brand-blue text-white font-black px-8 py-4 rounded-2xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed uppercase text-[12px] tracking-widest"
          >
            Comenzar Carga
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { 
  closeOutline, cloudUploadOutline, imagesOutline, informationCircleOutline, 
  documentText, checkmarkCircleOutline, closeCircleOutline
} from 'ionicons/icons';
import api from '@/services/api';

const emit = defineEmits(['close', 'success']);

const fileInput = ref<HTMLInputElement | null>(null);

const triggerBulkPhotoUpload = () => {
  if (fileInput.value) {
    fileInput.value.click();
  }
};

const step = ref('upload'); // upload, processing, result
const isDragging = ref(false);
const selectedFile = ref<File | null>(null);
const progress = ref(0);
const processingStatus = ref('Subiendo archivo...');
const logs = ref<string[]>([]);
const results = ref({
  success: 0,
  errors: [] as string[]
});

const handleFileChange = (e: any) => {
  const file = e.target.files[0];
  if (file && file.name.endsWith('.zip')) {
    selectedFile.value = file;
  }
};

const handleDrop = (e: DragEvent) => {
  isDragging.value = false;
  const file = e.dataTransfer?.files[0];
  if (file && file.name.endsWith('.zip')) {
    selectedFile.value = file;
  }
};

const startUpload = async () => {
  if (!selectedFile.value) return;

  step.value = 'processing';
  logs.value.push('Iniciando transferencia de datos...');
  
  const formData = new FormData();
  formData.append('zip_file', selectedFile.value);

  try {
    processingStatus.value = 'Extrayendo y procesando fotos...';
    logs.value.push('Archivo recibido en servidor.');
    logs.value.push('Extrayendo archivo ZIP...');
    
    const res = await api.post('/admin/students/photos/bulk', formData, {
      timeout: 300000, // 5 minutos para archivos grandes
      onUploadProgress: (progressEvent: any) => {
        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        progress.value = percentCompleted;
      }
    });

    if (res.data.success) {
      logs.value.push('Procesamiento de vinculación finalizado.');
      results.value = res.data.data;
      step.value = 'result';
    }
  } catch (error: any) {
    console.error('Error in bulk upload', error);
    logs.value.push('ERROR: ' + (error.response?.data?.message || 'Fallo en la conexión.'));
    processingStatus.value = 'Hubo un error en el proceso.';
  }
};
</script>

<style scoped>
.font-sans {
  font-family: 'Outfit', sans-serif, system-ui;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f1f1f1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #e2e2e2;
}
</style>
