<template>
  <Teleport to="body">
    <transition name="impersonation-slide">
      <div v-if="isImpersonating" class="impersonation-banner">
        <div class="impersonation-banner-content">
          <div class="impersonation-info">
            <div class="impersonation-icon-wrapper">
              <ion-icon :icon="eyeOutline" class="impersonation-icon"></ion-icon>
            </div>
            <div class="impersonation-text">
              <span class="impersonation-label">Modo Suplantación</span>
              <span class="impersonation-user">Viendo como <strong>{{ impersonatedUserName }}</strong> ({{ impersonatedUserRole }})</span>
            </div>
          </div>
          <button @click="stopImpersonating" :disabled="isLoading" class="impersonation-stop-btn">
            <ion-icon :icon="arrowUndoOutline" class="btn-icon"></ion-icon>
            <span>{{ isLoading ? 'Volviendo...' : 'Volver a mi cuenta' }}</span>
          </button>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { IonIcon } from '@ionic/vue';
import { eyeOutline, arrowUndoOutline } from 'ionicons/icons';
import { storage } from '@/services/storage';
import { useRouter, useRoute } from 'vue-router';
import api from '@/services/api';

const router = useRouter();
const route = useRoute();

const isImpersonating = ref(false);
const isLoading = ref(false);
const impersonatedUserName = ref('');
const impersonatedUserRole = ref('');

const getRoleLabel = (role: string) => {
  switch (role) {
    case 'super_admin': return 'IT / Sistemas';
    case 'director': return 'Director';
    case 'teacher': return 'Profesor';
    case 'parent': return 'Padre de Familia';
    default: return 'Usuario';
  }
};

const checkImpersonationStatus = async () => {
  const adminToken = await storage.get('impersonation_admin_token');
  const adminUser = await storage.get('impersonation_admin_user');
  const currentUser = await storage.get('auth_user');
  const currentProfile = await storage.get('current_profile');
  
  if (adminToken && adminUser) {
    isImpersonating.value = true;
    if (currentUser) {
      impersonatedUserName.value = currentUser.name || 'Usuario';
      impersonatedUserRole.value = getRoleLabel(currentProfile || currentUser.role || '');
    }
  } else {
    isImpersonating.value = false;
  }
};

const stopImpersonating = async () => {
  isLoading.value = true;
  
  try {
    // Retrieve the original admin data
    const adminToken = await storage.get('impersonation_admin_token');
    const adminUser = await storage.get('impersonation_admin_user');
    const adminSchools = await storage.get('impersonation_admin_schools');
    const adminProfiles = await storage.get('impersonation_admin_profiles');
    const adminSchoolId = await storage.get('impersonation_admin_school_id');
    
    if (!adminToken || !adminUser) {
      console.error('No admin impersonation data found');
      isLoading.value = false;
      return;
    }

    // Delete the impersonated user's token on the backend
    try {
      await api.delete('/user/current-token');
    } catch (e) {
      // It's okay if this fails, we'll just restore the admin session
    }

    // Restore the admin session
    await storage.set('auth_token', adminToken);
    await storage.set('auth_user', adminUser);
    await storage.set('user_schools', adminSchools || []);
    await storage.set('available_profiles', adminProfiles || ['super_admin']);
    await storage.set('current_profile', 'super_admin');
    
    if (adminSchoolId) {
      await storage.set('current_school_id', adminSchoolId);
    } else {
      // Admin had no school selected (global view) — clear it
      await storage.remove('current_school_id');
    }

    // Clean up impersonation data
    await storage.remove('impersonation_admin_token');
    await storage.remove('impersonation_admin_user');
    await storage.remove('impersonation_admin_schools');
    await storage.remove('impersonation_admin_profiles');
    await storage.remove('impersonation_admin_school_id');

    isImpersonating.value = false;

    // Redirect to admin users page with full page reload to reset all state
    window.location.href = '/admin/users';
  } catch (error) {
    console.error('Error stopping impersonation:', error);
    isLoading.value = false;
  }
};

onMounted(() => {
  checkImpersonationStatus();
});

// Re-check on route changes
watch(() => route.path, () => {
  checkImpersonationStatus();
});

// Expose for external use
defineExpose({ checkImpersonationStatus });
</script>

<style scoped>
.impersonation-banner {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 99999;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
  box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4), 0 2px 8px rgba(0, 0, 0, 0.1);
  animation: bannerGlow 3s ease-in-out infinite alternate;
}

@keyframes bannerGlow {
  0% { box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4), 0 2px 8px rgba(0, 0, 0, 0.1); }
  100% { box-shadow: 0 4px 28px rgba(245, 158, 11, 0.6), 0 2px 12px rgba(0, 0, 0, 0.15); }
}

.impersonation-banner-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 20px;
  max-width: 1400px;
  margin: 0 auto;
  gap: 12px;
}

.impersonation-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.impersonation-icon-wrapper {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, 0.3);
  animation: pulse-icon 2s ease-in-out infinite;
}

@keyframes pulse-icon {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.08); }
}

.impersonation-icon {
  font-size: 18px;
  color: white;
}

.impersonation-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.impersonation-label {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.85);
}

.impersonation-user {
  font-size: 13px;
  color: white;
  font-weight: 500;
}

.impersonation-user strong {
  font-weight: 800;
}

.impersonation-stop-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.95);
  color: #b45309;
  font-weight: 700;
  font-size: 12px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.impersonation-stop-btn:hover {
  background: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.impersonation-stop-btn:active {
  transform: translateY(0);
}

.impersonation-stop-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.btn-icon {
  font-size: 16px;
}

/* Animation */
.impersonation-slide-enter-active {
  animation: slideDown 0.3s ease-out;
}

.impersonation-slide-leave-active {
  animation: slideDown 0.2s ease-in reverse;
}

@keyframes slideDown {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Mobile Responsive */
@media (max-width: 640px) {
  .impersonation-banner-content {
    flex-direction: column;
    padding: 8px 16px;
    gap: 8px;
  }
  
  .impersonation-info {
    gap: 8px;
  }
  
  .impersonation-icon-wrapper {
    width: 28px;
    height: 28px;
  }
  
  .impersonation-icon {
    font-size: 14px;
  }
  
  .impersonation-label {
    font-size: 9px;
  }
  
  .impersonation-user {
    font-size: 12px;
  }
  
  .impersonation-stop-btn {
    width: 100%;
    justify-content: center;
    padding: 10px 16px;
  }
}
</style>
