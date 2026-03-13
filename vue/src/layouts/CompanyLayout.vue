<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">
        <i class="bi bi-briefcase-fill"></i> SmartHire
      </div>

      <nav class="nav-menu">
        <router-link to="/company/profile" custom v-slot="{ navigate, isActive }">
          <button @click="navigate" class="nav-item" :class="{ active: isActive }">
             Profile
          </button>
        </router-link>

        <router-link to="/company/old-posts" custom v-slot="{ navigate, isActive }">
          <button @click="navigate" class="nav-item" :class="{ active: isActive }">
             Old Hire Posts
          </button>
        </router-link>

        <router-link to="/company/inprogress-posts" custom v-slot="{ navigate, isActive }">
          <button @click="navigate" class="nav-item" :class="{ active: isActive }">
             In Progress Posts
          </button>
        </router-link>

        <router-link to="/company/add-post" custom v-slot="{ navigate, isActive }">
          <button @click="navigate" class="nav-item" :class="{ active: isActive }">
             + Add New Post
          </button>
        </router-link>
        
        <button class="nav-item logout-btn" @click="handleLogout">
           Logout
        </button>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuth } from '../composables/useAuth.js';
import '../../assets/styles/dashboard.css';

const { checkAuth, logout } = useAuth();

onMounted(() => {
  checkAuth('company');
});

const handleLogout = () => {
  logout();
};

</script>
