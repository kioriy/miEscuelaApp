<template>
  <div class="relative group w-full" :class="containerClass">
    <button 
      @click="toggleMenu" 
      class="flex items-center gap-2 transition-colors w-full text-left"
      :class="buttonClass"
    >
      <div v-if="showAvatar" class="w-8 h-8 rounded-full overflow-hidden shrink-0 border-2 border-white shadow-sm">
        <img v-if="userAvatar" :src="userAvatar" alt="User" class="w-full h-full object-cover">
        <ion-icon v-else :icon="personOutline" class="text-gray-400 m-auto mt-1.5"></ion-icon>
      </div>
      
      <div class="flex flex-col flex-grow overflow-hidden">
        <span v-if="showName" class="text-sm font-black truncate" :class="nameColor">{{ userName }}</span>
        <div class="flex items-center gap-1">
          <span class="text-xs font-bold truncate" :class="roleColor">{{ currentRoleLabel }}</span>
          <ion-icon :icon="chevronDownOutline" class="text-[10px] opacity-70"></ion-icon>
        </div>
      </div>
    </button>

    <!-- Dropdown Menu -->
    <div 
      v-if="isOpen" 
      class="absolute z-50 mt-2 w-48 rounded-2xl bg-white shadow-xl border border-gray-100 py-2 overflow-hidden"
      :class="menuPositionClass"
    >
      <template v-if="availableProfiles.length > 1">
        <div class="px-4 py-2 border-b border-gray-50 mb-1">
          <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Cambiar Perfil</p>
        </div>
        
        <button 
          v-for="profile in availableProfiles" 
          :key="profile"
          @click="selectProfile(profile)"
          class="w-full text-left px-4 py-2.5 flex items-center gap-3 hover:bg-gray-50 transition-colors"
          :class="currentProfile === profile ? 'bg-blue-50/50' : ''"
        >
          <div :class="getProfileIconBg(profile)" class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
            <ion-icon :icon="getProfileIcon(profile)" :class="getProfileIconColor(profile)" class="text-lg"></ion-icon>
          </div>
          <div class="flex flex-col">
            <span class="text-sm font-bold text-gray-900 leading-none">{{ getRoleLabel(profile) }}</span>
            <span v-if="currentProfile === profile" class="text-[10px] font-bold text-brand-blue mt-0.5">Activo</span>
          </div>
        </button>
      </template>
      
      <div :class="availableProfiles.length > 1 ? 'border-t border-gray-50 mt-1 pt-1' : ''">
        <button @click="logout" class="w-full text-left px-4 py-2.5 flex items-center gap-3 hover:bg-red-50 text-red-500 transition-colors group">
          <div class="w-8 h-8 rounded-lg bg-red-50 group-hover:bg-red-100 flex items-center justify-center shrink-0 transition-colors">
            <ion-icon :icon="logOutOutline" class="text-lg"></ion-icon>
          </div>
          <span class="text-sm font-bold">Cerrar Sesión</span>
        </button>
      </div>
    </div>
    
    <!-- Backdrop for closing -->
    <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-40"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { IonIcon } from '@ionic/vue';
import { 
  personOutline, chevronDownOutline, logOutOutline,
  shieldCheckmarkOutline, briefcaseOutline, schoolOutline, heartOutline
} from 'ionicons/icons';
import { storage } from '@/services/storage';
import { useRouter } from 'vue-router';

const props = defineProps({
  variant: {
    type: String,
    default: 'sidebar' // 'sidebar' (dark text), 'header' (light text or custom)
  },
  showAvatar: {
    type: Boolean,
    default: true
  },
  showName: {
    type: Boolean,
    default: true
  },
  fallbackMode: {
    type: String,
    default: 'hide' // 'hide' or 'static'
  }
});

const router = useRouter();
const isOpen = ref(false);
const availableProfiles = ref<string[]>([]);
const currentProfile = ref<string>('');
const userName = ref('Usuario');
const userAvatar = ref('');

const loadData = async () => {
  const user = await storage.get('auth_user');
  
  const profiles = await storage.get('available_profiles');
  if (profiles && Array.isArray(profiles) && profiles.length > 0) {
    availableProfiles.value = profiles;
  } else if (user && user.role) {
    availableProfiles.value = [user.role];
  }
  
  const current = await storage.get('current_profile');
  if (current) {
    currentProfile.value = current;
  } else if (user && user.role) {
    currentProfile.value = user.role;
  }

  if (user) {
    userName.value = user.name;
    if (user.avatar_url) {
      userAvatar.value = user.avatar_url;
    } else if (user.profile_photo_path) {
      userAvatar.value = user.profile_photo_path.startsWith('http') 
        ? user.profile_photo_path 
        : `${import.meta.env.VITE_API_URL?.replace('/api', '') || ''}/storage/${user.profile_photo_path}`;
    }
  }
};

onMounted(() => {
  loadData();
});

const toggleMenu = () => {
  isOpen.value = !isOpen.value;
};

const selectProfile = async (profile: string) => {
  if (profile === currentProfile.value) {
    isOpen.value = false;
    return;
  }
  
  currentProfile.value = profile;
  await storage.set('current_profile', profile);
  isOpen.value = false;
  
  // Route appropriately, forcing a reload to clear contextual stores/state
  if (['super_admin', 'director', 'teacher'].includes(profile)) {
    window.location.href = '/admin/dashboard';
  } else if (profile === 'parent') {
    window.location.href = '/parent/dashboard';
  }
};

const logout = async () => {
  await storage.clear();
  window.location.href = '/login';
};

// Styling bindings
const containerClass = computed(() => {
  if (props.variant === 'sidebar') return 'p-1.5 rounded-xl hover:bg-gray-50';
  if (props.variant === 'header') return '';
  return '';
});

const buttonClass = computed(() => {
  return '';
});

const nameColor = computed(() => {
  if (props.variant === 'header') return 'text-gray-900';
  return 'text-gray-900 group-hover:text-brand-blue';
});

const roleColor = computed(() => {
  if (props.variant === 'header') return 'text-gray-500';
  return 'text-gray-500 group-hover:text-gray-600';
});

const menuPositionClass = computed(() => {
  if (props.variant === 'header') return 'right-0 top-full mt-2';
  return 'bottom-full left-0 mb-2'; // Default sidebar opens upwards
});

// Role details rendering
const getRoleLabel = (role: string) => {
  switch (role) {
    case 'super_admin': return 'IT / Sistemas';
    case 'director': return 'Director';
    case 'teacher': return 'Profesor';
    case 'parent': return 'Padre de Familia';
    default: return 'Usuario';
  }
};

const currentRoleLabel = computed(() => getRoleLabel(currentProfile.value));

const getProfileIcon = (role: string) => {
  switch (role) {
    case 'super_admin': return shieldCheckmarkOutline;
    case 'director': return briefcaseOutline;
    case 'teacher': return schoolOutline;
    case 'parent': return heartOutline;
    default: return personOutline;
  }
};

const getProfileIconBg = (role: string) => {
  switch (role) {
    case 'super_admin': return 'bg-purple-100';
    case 'director': return 'bg-amber-100';
    case 'teacher': return 'bg-blue-100';
    case 'parent': return 'bg-emerald-100';
    default: return 'bg-gray-100';
  }
};

const getProfileIconColor = (role: string) => {
  switch (role) {
    case 'super_admin': return 'text-purple-600';
    case 'director': return 'text-amber-600';
    case 'teacher': return 'text-blue-600';
    case 'parent': return 'text-emerald-600';
    default: return 'text-gray-600';
  }
};
</script>
