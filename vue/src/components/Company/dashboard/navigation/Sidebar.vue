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
.sidebar {
  background-color: #ffffff;
  width: 250px;
  height: 100vh;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  border-right: 1px solid #e5e7eb;
}

.brand {
  padding: 1.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #4f46e5;
}

.brand i {
  color: #4f46e5;
}

.nav-menu {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.75rem 1.5rem;
  background: none;
  border: none;
  color: #000000c4;
  text-align: left;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background-color: #4e46e51e;
}

.nav-item.active {
  background-color: rgba(79, 70, 229, 0.1);
  color: #4f46e5;
  border-left-color: #4f46e5;
}

.nav-item i {
  font-size: 1rem;
  width: 24px;
}

/* User Info at Bottom */
.user-info-bottom {
  border-top: 1px solid #e5e7eb;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background-color: #f9fafb;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  border: 2px solid #e5e7eb;
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
  color: #1f2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.logout-btn-bottom {
  background: none;
  border: none;
  color: #ee2d20;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.logout-btn-bottom:hover {
  background-color: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

/* Logout button in nav menu */
.logout-item {
  color: #f02323;
}

.logout-item:hover {
  background-color: #f023233a;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 220px;
  }
  
  .nav-item {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }
  
  .user-info-bottom {
    padding: 0.75rem;
  }
}


.brand {
  display: flex;
  align-items: center;
  gap: 0px;
  padding: 8px 12px;
  text-decoration: none;
  color: inherit;
}

.brand-logo {
  position: relative;
  height: 40px;
  width: auto;
  max-width: 120px;
  object-fit: contain;
  transition: transform 0.2s ease;
}

.brand:hover {
  transform: scale(1.05);
}

.brand-name {
  font-size: 1.45rem;
  font-weight: 600;
  letter-spacing: -0.5px;
  color: #333; /* Change to your preferred color */
  white-space: nowrap;
}

/* Optional: Dark mode support */
@media (prefers-color-scheme: dark) {
  .brand-name {
    color: #204aa3;
  }
}

/* Optional: Responsive design */
@media (max-width: 768px) {
  .brand-logo {
    height: 28px;
  }
  
  .brand-name {
    font-size: 1.1rem;
  }
}

/* Optional: If you want to hide text on mobile */
@media (max-width: 480px) {
  .brand-name {
    display: none;
  }
}
</style>