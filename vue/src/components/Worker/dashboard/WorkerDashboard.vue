<template>
  <div class="dashboard-container">
    <!-- Top Navbar -->
    <TopNavbar :workerName="workerName" :workerPhoto="workerPhoto" />

    <!-- Main Content -->
    <main class="main-content-worker">
      <router-view></router-view>
    </main>
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
  
 
   if (urlToken && urlRole) {
    localStorage.setItem('auth_token', urlToken);
    localStorage.setItem('user_role', urlRole);
    
 
    
    token = urlToken;
    role = urlRole;
  }
  
   if (!token || !role || role !== 'worker') {
    router.push('/');
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_role');
    return; // Stop execution
  }
  else{
    await fetchCompanyInfo();
    await loadProfileData();
    setProfile(profile.value);
  }
  // If we get here, we have valid auth

});

const fetchCompanyInfo = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      window.location.href = '/login';
      return;
    }
 console.log('i sendd : ',token)
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/worker/info`, { 
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    console.log('response',response.data)
    if (response.data.status === 'success') {
      const data = response.data;
      profile.value.fullName = data.worker.fullname;
      profile.value.email = data.worker.email;
      profile.value.location = data.worker.location;
      profile.value.bio = data.worker.industry;

      profile.value.github = data.urls.url_github;
      profile.value.gmail = data.urls.url_gmail;
      profile.value.linkedin = data.urls.url_linkedin;
      profile.value.website = data.urls.url_website;

      if(data.cv.file_size){
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
      
    }
  } catch (error) {
    showError('Error', 'Failed to load worker information');
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
    console.log('loadProfileDataD',response.data.data)
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
  background-color: #f3f4f6;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
  .main-content-worker {
    padding: 1.5rem 1rem;
  }
}

@media (max-width: 768px) {
  .main-content-worker {
    padding: 1rem;
  }
}
</style>