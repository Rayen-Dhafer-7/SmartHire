<template>
  <div class="dashboard-container">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner"></div>
        <h3>Loading</h3>
        <p>Please wait while we load your company information...</p>
      </div>
    </div>

    <!-- Main Content -->
    <template v-else>
      <!-- Sidebar -->
      <Sidebar :n1="n1" :n2="n2" />

      <!-- Main Content -->
      <main class="main-content">
        <router-view></router-view>
      </main>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Sidebar from './navigation/Sidebar.vue'
import { showError } from '../../../utils/notifications'
import { setProfile } from '../../../utils/storage'
import '../../../assets/styles/dashboard.css'

const router = useRouter()

const isLoading = ref(true)
const n1 = ref(0)
const n2 = ref(0)

const profile = ref({
  name: '',
  email: '',
  location: '',
  description: '',
  logo: null,
  urls: {
    website: '',
    linkedin: '',
    twitter: '',
    facebook: '',
    instagram: '',
    mail: ''
  }
})

const logoPreview = ref(null)

const fetchCompanyInfo = async () => {
  try {
    const token = localStorage.getItem('auth_token')

    if (!token) {
      window.location.href = '/login'
      return
    }

    const response = await axios.get(`${import.meta.env.VITE_API_URL}/company/info`, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })

    if (response.data.status === 'success') {
      n1.value = response.data.old_posts
      n2.value = response.data.in_progress_posts

      const data = response.data.company

      profile.value.name = data.companyName
      profile.value.email = data.email
      profile.value.location = data.location
      profile.value.description = data.industry

      logoPreview.value = data.logoUrl
      profile.value.logo = data.logoUrl

      if (response.data.urls) {
        profile.value.urls = {
          website: response.data.urls.url_website || '',
          linkedin: response.data.urls.url_linkedin || '',
          twitter: response.data.urls.url_twitter || '',
          facebook: response.data.urls.url_facebook || '',
          instagram: response.data.urls.url_instagram || '',
          mail: response.data.urls.url_gmail || ''
        }
      }
    }
  } catch (error) {
    showError('Error', 'Failed to load company information')
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  const token = localStorage.getItem('auth_token')
  const role = localStorage.getItem('user_role')

  if (!token || !role || role !== 'company') {
    router.push('/')
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_role')
    return
  }

  await fetchCompanyInfo()

  logoPreview.value = profile.value.logo

  setProfile(profile.value)
})
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  position: relative;
  background: #f7f8fb;
}

/* Loading Overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #f7f8fb 0%, #ffffff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.loading-content {
  text-align: center;
  animation: slideUp 0.5s ease;
}

.spinner {
  width: 60px;
  height: 60px;
  margin: 0 auto 1.5rem;
  border: 3px solid #e2e8f0;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.loading-content h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.5rem;
  letter-spacing: -0.5px;
}

.loading-content p {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.main-content {
  margin-left: 280px;
  min-height: 100vh;
  padding: 2rem;
  transition: margin-left 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .main-content {
    margin-left: 260px;
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .main-content {
    margin-left: 0;
    padding: 1rem;
  }
  
  .loading-content h3 {
    font-size: 1.25rem;
  }
  
  .spinner {
    width: 40px;
    height: 40px;
  }
}
</style>