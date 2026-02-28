<template>
  <div v-if="schools.length > 1" class="school-switcher-container">
    <ion-item lines="none" class="switcher-item">
      <ion-icon :icon="businessOutline" slot="start" class="school-icon" />
      <ion-select 
        :value="currentSchoolId" 
        @ionChange="handleSchoolChange"
        interface="popover"
        toggle-icon="chevron-down-outline"
        expanded-icon="chevron-up-outline"
        class="school-select"
      >
        <ion-select-option v-for="school in schools" :key="school.id" :value="school.id">
          {{ school.name }}
        </ion-select-option>
      </ion-select>
    </ion-item>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { IonItem, IonIcon, IonSelect, IonSelectOption } from '@ionic/vue';
import { businessOutline } from 'ionicons/icons';
import { storage } from '@/services/storage';

const schools = ref<any[]>([]);
const currentSchoolId = ref<number | null>(null);

const loadSchools = async () => {
  const storedSchools = await storage.get('user_schools');
  const storedCurrentId = await storage.get('current_school_id');
  
  if (storedSchools) {
    schools.value = storedSchools;
  }
  
  if (storedCurrentId) {
    currentSchoolId.value = storedCurrentId;
  }
};

const handleSchoolChange = async (ev: any) => {
  const newId = ev.detail.value;
  if (newId !== currentSchoolId.value) {
    currentSchoolId.value = newId;
    await storage.set('current_school_id', newId);
    // Recargar la página para limpiar estados previos y que el interceptor use el nuevo ID
    window.location.reload();
  }
};

onMounted(() => {
  loadSchools();
});
</script>

<style scoped>
.school-switcher-container {
  margin: 8px 16px;
  background: rgba(var(--ion-color-primary-rgb), 0.05);
  border-radius: 12px;
  border: 1px solid rgba(var(--ion-color-primary-rgb), 0.1);
}

.switcher-item {
  --background: transparent;
  --padding-start: 12px;
  --min-height: 44px;
}

.school-icon {
  color: var(--ion-color-primary);
  font-size: 20px;
  margin-right: 8px;
}

.school-select {
  --placeholder-color: var(--ion-color-step-600);
  --placeholder-opacity: 1;
  --color: #111827; /* Tailwind gray-900 */
  color: #111827 !important;
  font-weight: 700;
  font-size: 14px;
  width: 100%;
}

/* Reducción de padding para que se vea más compacto en el sidebar */
ion-select::part(container) {
  padding-left: 0;
}
</style>
