<template>
  <div class="dashboard-container">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar">
      <div class="container-fluid">
        <!-- Brand -->
        <div class="navbar-brand d-flex align-items-center gap-2">
          <span class="fw-bold brand-text"><i class="bi bi-briefcase-fill"></i> SmartHire</span>
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
              <img :src="workerPhoto || 'https://via.placeholder.com/40'" 
                   alt="Profile" 
                   class="profile-avatar">
              <div class="d-none d-md-block">
                <div class="fw-semibold">{{ workerName || 'Worker' }}</div>
              </div>
            </div>
            <button class="logout-btn-custom" @click="handleLogout">
              <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content-worker">
      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuth } from '../composables/useAuth.js';

const { checkAuth, logout } = useAuth();
const workerName = ref('');
const workerPhoto = ref('');

onMounted(() => {
  checkAuth('worker');
});

const handleLogout = () => {
  logout();
};

</script>

<style scoped>
/* Reset and Variables */
* {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

:root {
  --primary-color: #4f46e5;
  --text-main: #1e293b;
  --text-muted: #64748b;
  --border-color: #e2e8f0;
  --white: #ffffff;
}

.dashboard-container {
  min-height: 100vh;
  background-color: #f3f4f6;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Top Navbar Overrides */
.top-navbar {
  background-color: var(--white);
  border-bottom: 1px solid var(--border-color);
  padding: 0.75rem 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.brand-text {
  color: var(--primary-color);
  font-size: 1.25rem;
}

.brand-icon {
  color: var(--primary-color);
  font-size: 1.5rem;
}

.nav-menu-horizontal {
  display: flex;
  gap: 0.5rem;
  margin-left: 2rem;
}

.nav-link-custom {
  color: var(--text-muted);
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
  color: var(--text-main);
}

.nav-link-custom.active {
  background-color: #eef2ff;
  color: var(--primary-color);
}

.nav-link-custom i {
  font-size: 1.1rem;
}

.profile-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e5e7eb;
}

.user-info {
  color: var(--text-main);
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

.logout-btn-custom i {
  font-size: 1.2rem;
}

.main-content-worker {
  padding: 2rem;
  margin: 0 auto;
  width: 100%;
  max-width: 1400px;
  min-height: calc(100vh - 70px);
}

/* Responsive */
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
  
  .main-content-worker {
    padding: 1.5rem 1rem;
  }
}

@media (max-width: 768px) {
  .brand-text {
    font-size: 1.1rem;
  }
  
  .main-content-worker {
    padding: 1rem;
  }
}
</style>
