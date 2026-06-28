<template>
  <nav class="top-navbar">
    <div class="navbar-container">
      <!-- Brand -->
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

      <!-- Navigation Links - Left Side -->
      <div class="nav-links-wrapper">
        <div class="nav-links">
          <router-link to="/worker/profile" custom v-slot="{ navigate, isActive }">
            <a @click="navigate" class="nav-link" :class="{ active: isActive }">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <span>Profile</span>
            </a>
          </router-link>

          <router-link to="/worker/jobs" custom v-slot="{ navigate, isActive }">
            <a @click="navigate" class="nav-link" :class="{ active: isActive }">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
              </svg>
              <span>Available Jobs</span>
            </a>
          </router-link>

          <router-link to="/worker/applications" custom v-slot="{ navigate, isActive }">
            <a @click="navigate" class="nav-link" :class="{ active: isActive }">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
              </svg>
              <span>My Applications</span>
            </a>
          </router-link>
        </div>
      </div>

      <!-- Mobile Menu Button -->
      <button class="mobile-menu-btn" @click="toggleMenu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>

      <!-- User Info & Logout - Right Side -->
      <div class="user-section">
        <div class="user-info">
          <img 
            :src="profile.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(profile.fullName || 'User') + '&background=4f46e5&color=fff'" 
            alt="Profile" 
            class="user-avatar"
          />
          <div class="user-details">
            <div class="user-name">{{ profile.fullName || 'User' }}</div>
            <div class="user-role">Worker Account</div>
          </div>
        </div>
        <button class="logout-btn" @click="logout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Logout</span>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" :class="{ 'mobile-menu-open': isMenuOpen }">
      <div class="mobile-nav-links">
        <router-link to="/worker/profile" custom v-slot="{ navigate, isActive }">
          <a @click="navigate; closeMenu()" class="mobile-nav-link" :class="{ active: isActive }">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Profile</span>
          </a>
        </router-link>

        <router-link to="/worker/jobs" custom v-slot="{ navigate, isActive }">
          <a @click="navigate; closeMenu()" class="mobile-nav-link" :class="{ active: isActive }">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
            <span>Available Jobs</span>
          </a>
        </router-link>

        <router-link to="/worker/applications" custom v-slot="{ navigate, isActive }">
          <a @click="navigate; closeMenu()" class="mobile-nav-link" :class="{ active: isActive }">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <span>My Applications</span>
          </a>
        </router-link>
      </div>
      <div class="mobile-user-section">
        <div class="mobile-user-info">
          <img 
            :src="profile.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(profile.fullName || 'User') + '&background=4f46e5&color=fff'" 
            alt="Profile" 
            class="mobile-user-avatar"
          />
          <div class="mobile-user-details">
            <div class="mobile-user-name">{{ profile.fullName || 'User' }}</div>
            <div class="mobile-user-role">Worker Account</div>
          </div>
        </div>
        <button class="mobile-logout-btn" @click="logout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Logout</span>
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { showConfirm } from '../../../../utils/notifications';
import { getProfile } from '../../../../utils/storage.js';

const profile = getProfile() || {};
const router = useRouter();
const isMenuOpen = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
  isMenuOpen.value = false;
};

const logout = () => {
  closeMenu();
  showConfirm('Logout?', 'Are you sure you want to logout?', 'Yes, logout', 'Cancel').then((result) => {
    if (result.isConfirmed) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_role'); 
      localStorage.removeItem('fullname');
      router.push('/');
    }
  });
};

// Close menu when clicking outside on mobile
onMounted(() => {
  document.addEventListener('click', (e) => {
    const navbar = document.querySelector('.top-navbar');
    if (navbar && !navbar.contains(e.target) && isMenuOpen.value) {
      closeMenu();
    }
  });
});

onUnmounted(() => {
  document.removeEventListener('click', () => {});
});
</script>

<style scoped>
.top-navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.navbar-container {
  max-width: 100%;
  margin: 0 auto;
  padding: 0.75rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
}

/* Brand */
.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.brand:hover {
  transform: scale(1.02);
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

/* Navigation Links Wrapper - Left Side */
.nav-links-wrapper {
  flex: 1;
  margin-left: 3rem;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 10px;
  color: #64748b;
  font-weight: 500;
  font-size: 0.9rem;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-link svg {
  stroke-width: 1.5;
}

.nav-link:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.nav-link.active {
  background: linear-gradient(135deg, #eef2ff, #ffffff);
  color: #4f46e5;
}

.nav-link.active svg {
  stroke: #4f46e5;
}

/* User Section - Right Side */
.user-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e2e8f0;
}

.user-details {
  text-align: left;
}

.user-name {
  font-weight: 600;
  font-size: 0.85rem;
  color: #0f172a;
}

.user-role {
  font-size: 0.7rem;
  color: #94a3b8;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  color: #ef4444;
  font-weight: 500;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background: #fef2f2;
  border-color: #ef4444;
  transform: translateY(-1px);
}

/* Mobile Menu Button */
.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  color: #475569;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.mobile-menu-btn:hover {
  background: #f1f5f9;
  color: #4f46e5;
}

/* Mobile Menu */
.mobile-menu {
  position: fixed;
  top: 0;
  right: -100%;
  width: 80%;
  max-width: 320px;
  height: 100vh;
  background: white;
  box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
  transition: right 0.3s ease;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
}

.mobile-menu-open {
  right: 0;
}

.mobile-nav-links {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 2rem;
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: 12px;
  color: #475569;
  font-weight: 500;
  font-size: 1rem;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mobile-nav-link:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.mobile-nav-link.active {
  background: #eef2ff;
  color: #4f46e5;
}

.mobile-user-section {
  border-top: 1px solid #e2e8f0;
  padding-top: 1rem;
  margin-top: auto;
}

.mobile-user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 12px;
  margin-bottom: 1rem;
}

.mobile-user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e2e8f0;
}

.mobile-user-details {
  flex: 1;
}

.mobile-user-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: #0f172a;
}

.mobile-user-role {
  font-size: 0.75rem;
  color: #94a3b8;
}

.mobile-logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.75rem;
  background: transparent;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  color: #ef4444;
  font-weight: 500;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mobile-logout-btn:hover {
  background: #fef2f2;
  border-color: #ef4444;
}
/* Change from 991px to 768px for tablet/mobile breakpoint */
@media (max-width: 768px) {
  .navbar-container {
    padding: 0.75rem 1rem;
  }
  
  .nav-links-wrapper {
    display: none;
  }
  
  .user-section {
    display: none;
  }
  
  .mobile-menu-btn {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .brand {
    flex: 1;
  }
}

/* Keep desktop navigation visible on larger screens */
@media (min-width: 769px) {
  .nav-links-wrapper {
    display: flex !important;
  }
  
  .user-section {
    display: flex !important;
  }
  
  .mobile-menu-btn {
    display: none !important;
  }
  
  .mobile-menu {
    display: none !important;
  }
}
</style>