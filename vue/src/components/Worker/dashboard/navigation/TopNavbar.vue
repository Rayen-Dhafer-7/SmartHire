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
/* Top Navbar Overrides from parent or dashboard.css logic, here explicit for component */
.top-navbar {
  background-color: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  padding: 0.75rem 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.brand-text {
  color: #4f46e5;
  font-size: 1.25rem;
}

.nav-menu-horizontal {
  display: flex;
  gap: 0.5rem;
  margin-left: 4.9rem;
}

.nav-link-custom {
  color: #64748b;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  transition: all 0.2s;
  border: none;
  background: none;
  cursor: pointer;
}

.nav-link-custom:hover {
  background-color: #f3f4f6;
  color: #1e293b;
}

.nav-link-custom.active {
  background-color: #eef2ff;
  color: #4f46e5;
}

.profile-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e5e7eb;
  transition: border-color 0.3s ease;
}

.profile-avatar:hover {
  border-color: #4f46e5;
}

.user-info {
  color: #1e293b;
}

.logout-btn-custom {
  background: none;
  border: none;
  color: #ef4444;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: all 0.2s;
  cursor: pointer;
}

.logout-btn-custom:hover {
  background-color: #fef2f2;
  color: #dc2626;
}

@media (max-width: 991px) {
  .top-navbar {
    padding: 0.75rem 1rem;
  }
  
  .nav-menu-horizontal {
    flex-direction: column;
    margin-left: 0;
    margin-top: 1rem;
    width: 100%;
    gap: 0.25rem;
  }
  
  .nav-link-custom {
    width: 100%;
    padding: 0.75rem 1rem;
    justify-content: flex-start;
  }
  
  .logout-btn-custom {
    margin-top: 1rem;
    width: 100%;
    justify-content: flex-start;
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