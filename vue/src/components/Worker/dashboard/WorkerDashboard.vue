<template>
  <div class="dashboard-container">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner"></div>
        <h3>Loading</h3>
        <p>Please wait while we load your profile information...</p>
      </div>
    </div>

    <!-- Main Content -->
    <template v-else>
      <!-- Top Navbar -->
      <TopNavbar  :workerName="workerName" :workerPhoto="workerPhoto" />

      <!-- Main Content -->
      <main class="main-content-worker">
        <router-view></router-view>
      </main>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import TopNavbar from './navigation/TopNavbar.vue';
import { setProfile, getProfile } from '../../../utils/storage.js';
import axios from 'axios';
import { showError, showSuccess, showConfirm } from '../../../utils/notifications.js';

const router = useRouter();
const isLoading = ref(true);
const workerName = ref('');
const workerPhoto = ref('');

const profile = ref({
  fullName: '',
  email: '',
  photo: '',
  location: '',
  bio: '',
  skills: [], 
  linkedin: '',
  github: '',
  website: '',
  gmail: '',
  experience: [],
  projects: [],
  certifications: [],
  education: [],
  resumeName: '',
  resumeId: '',
  resumeDate: '',
  resumeSize: '',
  resumePath: ''
});

const logoPreview = ref(null);

onMounted(async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const urlToken = urlParams.get('token');
  const urlRole = urlParams.get('role');
  
  let token = localStorage.getItem('auth_token');
  let role = localStorage.getItem('user_role');
  
  // Check if we have URL parameters (fresh redirect from login)
  if (urlToken && urlRole) {
    localStorage.setItem('auth_token', urlToken);
    localStorage.setItem('user_role', urlRole);
    
    token = urlToken;
    role = urlRole;
    
    // Refresh the page to clear URL parameters and start fresh
    window.location.reload();
    return; // Stop execution here as page will reload
  }
  
  if (!token || !role || role !== 'worker') {
    router.push('/');
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_role');
    localStorage.removeItem('fullname');
    return; // Stop execution
  }
  
  await fetchWorkerInfo();
  await loadProfileData();
  setProfile(profile.value);
});

const fetchWorkerInfo = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      window.location.href = '/login';
      return;
    }
    
    console.log('Fetching worker info...');
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/worker/info`, { 
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    
    console.log('Worker info response:', response.data);
    
    if (response.data.status === 'success') {
      const data = response.data;
      profile.value.fullName = data.worker.fullname;
      profile.value.email = data.worker.email;
      profile.value.location = data.worker.location;
      profile.value.bio = data.worker.industry;

      workerName.value = data.worker.fullname;
      localStorage.setItem('fullname', data.worker.fullname);

      profile.value.github = data.urls.url_github;
      profile.value.gmail = data.urls.url_gmail;
      profile.value.linkedin = data.urls.url_linkedin;
      profile.value.website = data.urls.url_website;

      if(data.cv && data.cv.file_size) {
        profile.value.resumeName = data.cv.original_name;
        profile.value.resumeId = data.cv.id;
        profile.value.resumePath = data.cv.file_path;
        profile.value.resumeSize = (data.cv.file_size / 1024).toFixed(2) + ' KB';

        const uploadedAt = new Date(data.cv.uploaded_at);
        const day = String(uploadedAt.getDate()).padStart(2, '0');
        const month = String(uploadedAt.getMonth() + 1).padStart(2, '0');
        const year = uploadedAt.getFullYear();
        profile.value.resumeDate = `${day}/${month}/${year}`;
      }
      
      logoPreview.value = data.worker.photoUrl;
      profile.value.photo = data.worker.photoUrl;
      workerPhoto.value = data.worker.photoUrl;
    }
  } catch (error) {
    console.error('Error fetching worker info:', error);
    showError('Error', 'Failed to load worker information');
  } finally {
    isLoading.value = false;
  }
};

const loadProfileData = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/worker/profile/data`, { 
      headers: { 'Authorization': `Bearer ${token}` }
    });
    
    if (response.data.status === 'success') {
      profile.value.skills = response.data.data.skills.map(skill => ({
        id: skill.id,
        name: skill.skill_name
      })) || [];
      
      profile.value.experience = response.data.data.experience || [];
      profile.value.education = response.data.data.education || [];
      profile.value.certifications = response.data.data.certifications || [];
      profile.value.projects = response.data.data.projects || [];
    }
    console.log('Profile data loaded:', response.data.data);
  } catch (error) {
    console.error('Error loading profile data:', error);
  }
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

.dashboard-container {
  min-height: 100vh;
  background-color: #f7f8fb;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  position: relative;
}

/* Animated Background */
.animated-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}

.gradient-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.4;
  animation: float 20s ease-in-out infinite;
}

.orb-1 {
  width: min(500px, 50vw);
  height: min(500px, 50vw);
  background: radial-gradient(circle, rgba(99, 102, 241, 0.4), rgba(139, 92, 246, 0.2));
  top: -10%;
  left: -10%;
}

.orb-2 {
  width: min(600px, 60vw);
  height: min(600px, 60vw);
  background: radial-gradient(circle, rgba(236, 72, 153, 0.3), rgba(168, 85, 247, 0.2));
  bottom: -15%;
  right: -15%;
  animation-delay: -5s;
  animation-duration: 25s;
}

.orb-3 {
  width: min(400px, 40vw);
  height: min(400px, 40vw);
  background: radial-gradient(circle, rgba(14, 165, 233, 0.3), rgba(6, 182, 212, 0.2));
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: -10s;
  animation-duration: 30s;
}

.grid-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
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

.main-content-worker {
  position: relative;
  z-index: 1;
  padding: 2rem;
  margin: 0 auto;
  width: 100%;
  max-width: 1400px;
  min-height: calc(100vh - 70px);
}

/* Responsive */
@media (max-width: 991px) {
  .main-content-worker {
    padding: 1.5rem 1rem;
  }
}

@media (max-width: 768px) {
  .main-content-worker {
    padding: 1rem;
  }
  
  .spinner {
    width: 40px;
    height: 40px;
  }
  
  .loading-content h3 {
    font-size: 1.25rem;
  }
}
</style>