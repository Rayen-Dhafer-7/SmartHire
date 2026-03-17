<template>
  <nav class="navbar navbar-expand-lg top-navbar">
    <div class="container-fluid">
      <!-- Brand -->
        <div class="brand">
          <img :src="logo" alt="SmartHire - Your Future Awaits" class="brand-logo" />
          <span class="brand-name">SmartHire</span>
        </div>

      <!-- Toggle Button for Mobile -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#workerNavbar" aria-controls="workerNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navbar Content -->
      <div class="collapse navbar-collapse" id="workerNavbar">
        <!-- Navigation Links -->
        <div class="navbar-nav me-auto mb-2 mb-lg-0 nav-menu-horizontal">
          <router-link to="/worker/profile" custom v-slot="{ navigate, isActive }">
            <a @click="navigate" class="nav-link-custom" :class="{ active: isActive }" href="#">
              <i class="bi bi-person-circle"></i> Profile
            </a>
          </router-link>
          <router-link to="/worker/jobs" custom v-slot="{ navigate, isActive }">
            <a @click="navigate" class="nav-link-custom" :class="{ active: isActive }" href="#">
              <i class="bi bi-briefcase"></i> Available Jobs
            </a>
          </router-link>
          <router-link to="/worker/applications" custom v-slot="{ navigate, isActive }">
            <a @click="navigate" class="nav-link-custom" :class="{ active: isActive }" href="#">
              <i class="bi bi-file-text"></i> My Applications
            </a>
          </router-link>
        </div>

        <!-- User Profile & Logout -->
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center gap-2 user-info">
            <!-- UPDATED: Now uses profile.photo directly which is updated when photo changes -->
            <img :src="profile.photo || 'https://via.placeholder.com/150'" 
                 alt="Profile" 
                 class="profile-avatar">
            <div class="d-none d-md-block">
              <div class="fw-semibold">{{ profile.fullName || 'User' }}</div>
            </div>
          </div>
          <button class="logout-btn-custom" @click="logout">
            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span>
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { showConfirm } from '../../../../utils/notifications';
import { getProfile } from '../../../../utils/storage.js';
import logo from '../../../../../public/LogoSH.png';


// Get profile from storage
const profile = getProfile() || {};

const router = useRouter();

const logout = () => {
  showConfirm('Logout?', 'Are you sure you want to logout?', 'Yes, logout', 'Cancel').then((result) => {
    if (result.isConfirmed) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_role'); 
      router.push('/');
    }
  });
};
</script>

<style scoped>
/* Modern Top Navbar */
.top-navbar {
  background-color: var(--white);
  border-bottom: 1px solid var(--border-color);
  padding: 0.875rem 2rem;
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-base);
}

.top-navbar:hover {
  box-shadow: var(--shadow-md);
}

/* Brand Section */
.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 8px;
  text-decoration: none;
  color: inherit;
  transition: all var(--transition-fast);
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
  font-size: 1.35rem;
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
.nav-menu-horizontal {
  display: flex;
  gap: 0.75rem;
  margin-left: 3.5rem;
}

.nav-link-custom {
  color: var(--text-gray);
  font-weight: 500;
  font-size: 0.95rem;
  padding: 8px 14px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  transition: all var(--transition-fast);
  border: none;
  background: none;
  cursor: pointer;
  white-space: nowrap;
}

.nav-link-custom:hover {
  background-color: var(--bg-light);
  color: var(--text-main);
  transform: translateY(-2px);
}

.nav-link-custom.active {
  background: linear-gradient(135deg, rgba(13, 124, 140, 0.1) 0%, rgba(13, 124, 140, 0.05) 100%);
  color: var(--primary-color);
  font-weight: 600;
}

.nav-link-custom i {
  font-size: 1rem;
}

/* User Info & Avatar */
.user-info {
  color: var(--text-main);
  font-weight: 500;
  font-size: 0.95rem;
}

.profile-avatar {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  object-fit: cover;
  border: 2px solid var(--border-color);
  transition: all var(--transition-fast);
  cursor: pointer;
}

.profile-avatar:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow-sm);
  transform: scale(1.05);
}

/* Logout Button */
.logout-btn-custom {
  background: none;
  border: none;
  color: var(--error);
  font-weight: 600;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 8px 14px;
  border-radius: 8px;
  transition: all var(--transition-fast);
  cursor: pointer;
}

.logout-btn-custom:hover {
  background-color: var(--error-light);
  color: var(--error);
  transform: translateY(-2px);
}

.logout-btn-custom i {
  font-size: 1rem;
}

/* Responsive Design */
@media (max-width: 991px) {
  .top-navbar {
    padding: 0.75rem 1.25rem;
  }
  
  .nav-menu-horizontal {
    flex-direction: column;
    margin-left: 0;
    margin-top: 1rem;
    width: 100%;
    gap: 0.5rem;
  }
  
  .nav-link-custom {
    width: 100%;
    padding: 10px 12px;
    justify-content: flex-start;
  }
  
  .logout-btn-custom {
    margin-top: 1rem;
    width: 100%;
    justify-content: flex-start;
  }
}

@media (max-width: 768px) {
  .brand-logo {
    height: 32px;
  }
  
  .brand-name {
    font-size: 1.15rem;
  }
  
  .nav-menu-horizontal {
    margin-left: 0;
  }
}

@media (max-width: 480px) {
  .brand-name {
    display: none;
  }
  
  .brand {
    gap: 6px;
  }
}
</style>
