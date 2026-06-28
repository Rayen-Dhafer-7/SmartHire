<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="brand">
        <svg viewBox="0 0 100 100" width="40" height="40" xmlns="http://www.w3.org/2000/svg">
          <rect x="2" y="2" width="96" height="96" rx="18" ry="18" fill="#1B74E4"/>
          <text 
            x="50" y="72"
            font-family="Arial Black, Arial, sans-serif" 
            font-weight="900" 
            font-size="58" 
            fill="white" 
            text-anchor="middle"
            letter-spacing="-2"
          >Sh</text>
        </svg>
        <span class="brand-name">SmartHire</span>
      </div>
    </div>

    <nav class="nav-menu">
      <router-link to="/company/profile" custom v-slot="{ navigate, isActive }">
        <button @click="navigate" class="nav-item" :class="{ active: isActive }">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Profile</span>
        </button>
      </router-link>

      <router-link to="/company/old-posts" custom v-slot="{ navigate, isActive }">
        <button @click="navigate" class="nav-item" :class="{ active: isActive }">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <span>Old Hire</span>
          <span class="badge old-badge">{{ n1 }}</span>
        </button>
      </router-link>

      <router-link to="/company/inprogress-posts" custom v-slot="{ navigate, isActive }">
        <button @click="navigate" class="nav-item" :class="{ active: isActive }">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          <span>In Progress</span>
          <span class="badge progress-badge">{{ n2 }}</span>
        </button>
      </router-link>

      <router-link to="/company/add-post" custom v-slot="{ navigate, isActive }">
        <button @click="navigate" class="nav-item" :class="{ active: isActive }">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Add New Post</span>
        </button>
      </router-link>

      <button @click="logout" class="nav-item logout-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Logout</span>
      </button>
    </nav>

    <div class="user-info">
      <div class="user-avatar">
        <img 
          :src="logoSource" 
          class="avatar-image"
          alt="Company Logo"
          style="width: 100%; height: 100%; object-fit: contain;"
        />
      </div>
      <div class="user-details">
        <div class="user-name">{{ companyName }}</div>
        <div class="user-role">Company Account</div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { showConfirm } from '../../../../utils/notifications'
import { getProfile } from '../../../../utils/storage'

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
  background: linear-gradient(180deg, #ffffff 0%, #fefefe 100%);
  width: 280px;
  height: 100vh;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  border-right: 1px solid #e2e8f0;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.02);
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: white;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  transition: all 0.3s ease;
}

.brand:hover {
  transform: translateX(2px);
}

.brand svg {
  flex-shrink: 0;
}

.brand-name {
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: -0.5px;
  background: linear-gradient(135deg, #1e293b, #334155);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  white-space: nowrap;
}

/* Navigation Menu */
.nav-menu {
  flex: 1;
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  overflow-y: auto;
}

/* FIXED NAV ITEM STYLES - Text and badge on same line */
.nav-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  width: 100%;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  border-radius: 12px;
  color: #475569;
  text-align: left;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  white-space: nowrap;
}

.nav-item svg {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  stroke-width: 1.5;
  transition: all 0.2s ease;
}

.nav-item span:first-of-type {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-item:hover {
  background: #f1f5f9;
  color: #0f172a;
  transform: translateX(4px);
}

.nav-item.active {
  background: linear-gradient(135deg, #eef2ff, #ffffff);
  color: #4f46e5;
  border-left: 3px solid #4f46e5;
  box-shadow: 0 2px 4px rgba(79, 70, 229, 0.1);
}

.nav-item.active svg {
  stroke: #4f46e5;
}

/* Badges - Always on same line as text */
.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  line-height: 1;
  flex-shrink: 0;
  margin-left: auto;
}

.old-badge {
  background: #f1f5f9;
  color: #475569;
}

.progress-badge {
  background: #eef2ff;
  color: #4f46e5;
}

/* Logout Item */
.logout-item {
  margin-top: auto;
  color: #ef4444;
  border-top: 1px solid #e2e8f0;
  border-radius: 0;
  margin-top: 1rem;
  padding-top: 1rem;
}

.logout-item:hover {
  background: #fef2f2;
  color: #dc2626;
}

.logout-item:hover svg {
  stroke: #dc2626;
}

/* User Info */
.user-info {
  padding: 1rem 1.25rem;
  margin: 1rem;
  background: #f8fafc;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border: 1px solid #e2e8f0;
}

.user-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: white;
  border: 2px solid #e2e8f0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.avatar-image {
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
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 0.125rem;
}

.user-role {
  font-size: 0.7rem;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Scrollbar Styling */
.nav-menu::-webkit-scrollbar {
  width: 4px;
}

.nav-menu::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.nav-menu::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.nav-menu::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 260px;
  }
  
  .brand-name {
    font-size: 1.1rem;
  }
  
  .nav-item {
    padding: 0.625rem 0.875rem;
    font-size: 0.85rem;
    gap: 0.75rem;
  }
  
  .badge {
    min-width: 24px;
    padding: 0.2rem 0.4rem;
    font-size: 0.65rem;
  }
  
  .user-info {
    padding: 0.75rem;
    margin: 0.75rem;
  }
  
  .user-avatar {
    width: 36px;
    height: 36px;
  }
  
  .user-name {
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    border-right: none;
    border-bottom: 1px solid #e2e8f0;
  }
  
  .nav-menu {
    flex-direction: row;
    flex-wrap: wrap;
    padding: 1rem;
    gap: 0.5rem;
  }
  
  .nav-item {
    width: auto;
    flex: 1;
    min-width: 120px;
    justify-content: center;
  }
  
  .badge {
    margin-left: 0.5rem;
  }
  
  .logout-item {
    margin-top: 0;
    border-top: none;
  }
  
  .user-info {
    display: none;
  }
  
  .sidebar-header {
    padding: 1rem;
  }
}
</style>