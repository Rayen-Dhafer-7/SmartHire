<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <Sidebar :n1="n1" :n2="n2" />

    <!-- Main Content -->
    <main class="main-content">
      <router-view></router-view>
    </main>
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