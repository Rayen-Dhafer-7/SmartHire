<template>
  <aside class="sidebar">

    <div class="brand">
      <img :src="logo" alt="SmartHire - Your Future Awaits" class="brand-logo" />
      <span class="brand-name">SmartHire</span>
    </div>

    <nav class="nav-menu">

      <router-link to="/company/profile" custom v-slot="{ navigate, isActive }">
        <button @click="navigate" class="nav-item" :class="{ active: isActive }">
          <i class="bi bi-person-circle me-2"></i> Profile
        </button>
      </router-link>

      <router-link to="/company/old-posts" custom v-slot="{ navigate, isActive }">
        <button @click="navigate" class="nav-item" :class="{ active: isActive }">
          <i class="bi bi-file-earmark-text me-2"></i>
          Old Hire &nbsp;{{ n1 }}
        </button>
      </router-link>

      <router-link to="/company/inprogress-posts" custom v-slot="{ navigate, isActive }">
        <button @click="navigate" class="nav-item" :class="{ active: isActive }">
          <i class="bi bi-clock-history me-2"></i>
          In Progress &nbsp;{{ n2 }}
        </button>
      </router-link>

      <router-link to="/company/add-post" custom v-slot="{ navigate, isActive }">
        <button @click="navigate" class="nav-item" :class="{ active: isActive }">
          <i class="bi bi-plus-circle me-2"></i> Add New Post
        </button>
      </router-link>

      <button @click="logout" class="nav-item logout-item">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
      </button>

    </nav>

    <div class="user-info-bottom">
      <div class="user-avatar">
        <img :src="logoSource" class="company-logo" />
      </div>

      <div class="user-details">
        <div class="user-name">{{ companyName }}</div>
      </div>
    </div>

  </aside>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { showConfirm } from '../../../../utils/notifications'
import { getProfile } from '../../../../utils/storage'
import logo from '../../../../../public/LogoSH.png'

const props = defineProps({
  n1: Number,
  n2: Number
})

const router = useRouter()

const profile = ref(getProfile() || {})

let checkInterval

onMounted(() => {
  checkInterval = setInterval(() => {
    const newProfile = getProfile()

    if (newProfile && JSON.stringify(newProfile) !== JSON.stringify(profile.value)) {
      profile.value = newProfile
    }
  }, 2000)
})

onUnmounted(() => {
  if (checkInterval) clearInterval(checkInterval)
})

const logoSource = computed(() => {
  if (!profile.value) return 'https://via.placeholder.com/40'

  if (profile.value.logoDataUrl) return profile.value.logoDataUrl
  if (profile.value.logoUrl) return profile.value.logoUrl
  if (typeof profile.value.logo === 'string') return profile.value.logo

  return 'https://via.placeholder.com/40'
})

const companyName = computed(() => {
  return profile.value?.name || 'Company'
})

const logout = () => {
  showConfirm('Logout?', 'Are you sure you want to logout?', 'Yes, logout', 'Cancel')
    .then((result) => {
      if (result.isConfirmed) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user_role')
        localStorage.removeItem('company_profile')
        router.push('/')
      }
    })
}
</script>
<style scoped>
/* Modern Sidebar */
.sidebar {
  background-color: var(--white);
  width: 260px;
  height: 100vh;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  border-right: 1px solid var(--border-color);
  transition: all var(--transition-base);
  overflow-y: auto;
}

/* Brand Section */
.brand {
  padding: 1.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--primary-color);
  transition: all var(--transition-fast);
  height: 80px;
}

.brand:hover {
  transform: scale(1.03);
}

.brand-logo {
  position: relative;
  height: 40px;
  width: auto;
  max-width: 120px;
  object-fit: contain;
  transition: transform var(--transition-fast);
}

.brand:hover .brand-logo {
  transform: translateY(-2px);
}

.brand-name {
  font-size: 1.3rem;
  font-weight: 700;
  letter-spacing: -0.5px;
  color: var(--primary-color);
  white-space: nowrap;
  transition: color var(--transition-fast);
}

.brand:hover .brand-name {
  color: var(--primary-dark);
}

/* Navigation Menu */
.nav-menu {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-menu::-webkit-scrollbar {
  width: 6px;
}

.nav-menu::-webkit-scrollbar-track {
  background: transparent;
}

.nav-menu::-webkit-scrollbar-thumb {
  background: var(--border-color);
  border-radius: 3px;
}

.nav-menu::-webkit-scrollbar-thumb:hover {
  background: var(--border-light);
}

/* Navigation Items */
.nav-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 10px 16px;
  margin: 0 8px;
  background: none;
  border: none;
  color: var(--text-gray);
  text-align: left;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-fast);
  border-radius: 8px;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background-color: var(--bg-light);
  color: var(--text-main);
  transform: translateX(4px);
}

.nav-item.active {
  background: linear-gradient(135deg, rgba(13, 124, 140, 0.1) 0%, rgba(13, 124, 140, 0.05) 100%);
  color: var(--primary-color);
  border-left-color: var(--primary-color);
  font-weight: 600;
}

.nav-item i {
  font-size: 1.1rem;
  width: 24px;
  text-align: center;
  margin-right: 8px;
}

/* Logout Item */
.logout-item {
  color: var(--error);
}

.logout-item:hover {
  background-color: var(--error-light);
  color: var(--error);
}

/* User Info at Bottom */
.user-info-bottom {
  border-top: 1px solid var(--border-color);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background-color: var(--bg-light);
  transition: all var(--transition-base);
}

.user-avatar {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
  border: 2px solid var(--border-color);
  transition: all var(--transition-fast);
}

.user-avatar:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow-sm);
}

.company-logo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-details {
  flex: 1;
  min-width: 0;
  overflow: hidden;
}

.user-name {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--text-main);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color var(--transition-fast);
}

.user-info-bottom:hover .user-name {
  color: var(--primary-color);
}

/* Responsive Design */
@media (max-width: 768px) {
  .sidebar {
    width: 240px;
  }
  
  .brand {
    height: auto;
    padding: 1.25rem;
  }
  
  .nav-item {
    padding: 10px 12px;
    font-size: 0.9rem;
    margin: 0 4px;
  }
  
  .nav-item i {
    width: 20px;
  }
  
  .user-info-bottom {
    padding: 0.75rem;
  }
}

@media (max-width: 480px) {
  .sidebar {
    width: 200px;
  }
  
  .brand-name {
    display: none;
  }
  
  .brand {
    justify-content: center;
  }
}
</style>
