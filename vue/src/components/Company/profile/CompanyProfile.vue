<template>
  <div class="company-profile-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="company-profile-container">
      <div class="page-header">
        <div>
          <h2 class="page-title">Company Profile</h2>
          <p class="page-subtitle">Manage your company information and settings</p>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="tabs-container">
        <div class="tabs-wrapper">
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'profile' }"
            @click="activeTab = 'profile'"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
            General Information
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'password' }"
            @click="activeTab = 'password'"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Change Password
          </button>
        </div>
      </div>

      <!-- General Information Tab -->
      <GeneralInfoTab 
        v-if="activeTab === 'profile'"
        :profile="profile"
        :logoPreview="logoPreview"
        @save-profile="saveProfile"
        @logo-change="handleLogoChange"
        @trigger-logo-upload="triggerLogoUpload"
      />

      <!-- Change Password Tab -->
      <PasswordTab v-if="activeTab === 'password'" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { showError, showSuccess } from '../../../utils/notifications';
import axios from 'axios';
import GeneralInfoTab from './tabs/GeneralInfoTab.vue';
import PasswordTab from './tabs/PasswordTab.vue';
import { getProfile, setProfile } from '../../../utils/storage';

const activeTab = ref('profile');

const profile = ref({
  name: '',
  email: '',
  location: '',
  description: '',
  logo: null,
  logoUrl: '',
  logoDataUrl: '',
  urls: {
    website: '',
    linkedin: '',
    twitter: '',
    facebook: '',
    instagram: '',
    mail: ''
  }
});

const logoPreview = computed(() => {
  if (profile.value.logoDataUrl) return profile.value.logoDataUrl;
  if (profile.value.logoUrl) return profile.value.logoUrl;
  if (typeof profile.value.logo === 'string' && profile.value.logo) return profile.value.logo;
  return 'https://via.placeholder.com/150';
});

onMounted(async () => {
  await loadProfileData();
});

const loadProfileData = async () => {
  try {
    const storedProfile = getProfile();
    if (storedProfile) {
      Object.assign(profile.value, storedProfile);
      if (storedProfile.logoDataUrl) profile.value.logoDataUrl = storedProfile.logoDataUrl;
      if (!profile.value.logoUrl && typeof profile.value.logo === 'string') profile.value.logoUrl = profile.value.logo;
    }
    await fetchCompanyInfo();
  } catch (error) {
    console.error('Error loading profile:', error);
  }
};

const fetchCompanyInfo = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      window.location.href = '/login';
      return;
    }

    const response = await axios.get(`${import.meta.env.VITE_API_URL}/company/info`, { 
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    if (response.data.status === 'success') {
      const data = response.data.company;
      profile.value.name = data.companyName;
      profile.value.email = data.email;
      profile.value.location = data.location || '';
      profile.value.description = data.industry || '';
      profile.value.logoUrl = data.logoUrl || '';
      profile.value.logo = data.logoUrl || '';

      if (response.data.urls) {
        profile.value.urls = {
          website: response.data.urls.url_website || '',
          linkedin: response.data.urls.url_linkedin || '',
          twitter: response.data.urls.url_twitter || '',
          facebook: response.data.urls.url_facebook || '',
          instagram: response.data.urls.url_instagram || '',
          mail: response.data.urls.url_gmail || ''
        };
      }
      
      saveToStorage();
    }
  } catch (error) {
    showError('Error', 'Failed to load company information');
  }
};

const saveToStorage = () => {
  const profileForStorage = {
    ...profile.value,
    logo: null,
    logoFile: null
  };
  setProfile(profileForStorage);
};

const saveProfile = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      window.location.href = '/login';
      return;
    }

    const formData = new FormData();
    formData.append('companyName', profile.value.name);
    formData.append('email', profile.value.email);
    formData.append('location', profile.value.location);
    formData.append('industry', profile.value.description);

    formData.append('url_website', profile.value.urls.website);
    formData.append('url_linkedin', profile.value.urls.linkedin);
    formData.append('url_twitter', profile.value.urls.twitter);
    formData.append('url_facebook', profile.value.urls.facebook);
    formData.append('url_instagram', profile.value.urls.instagram);
    formData.append('url_gmail', profile.value.urls.mail);

    if (profile.value.logo instanceof File) {
      formData.append('logo', profile.value.logo);
    }

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/company/update`, formData, { 
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.status === 'success') {
      showSuccess('Success', 'Your company information has been updated successfully.', 2000);
      await fetchCompanyInfo();
    }
  } catch (error) {
    console.error(error);
    showError('Error', error.response?.data?.message || 'Failed to update profile');
  }
};

const triggerLogoUpload = () => {
  document.getElementById('logo-upload').click();
};

const handleLogoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    profile.value.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      profile.value.logoDataUrl = e.target.result;
      saveToStorage();
    };
    reader.readAsDataURL(file);
  }
};
</script>

<style scoped>
.company-profile-wrapper {
  min-height: 100vh;
  position: relative;

}

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

.company-profile-container {
  position: relative;
  z-index: 1;
  max-width: 1500px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.5rem;
  letter-spacing: -0.5px;
}

.page-subtitle {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0;
}

.tabs-container {
  background: white;
  border-radius: 16px;
  padding: 0.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.tabs-wrapper {
  display: flex;
  gap: 0.5rem;
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: transparent;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tab-btn svg {
  transition: all 0.3s ease;
}

.tab-btn.active {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.tab-btn.active svg {
  stroke: white;
}

.tab-btn:hover:not(.active) {
  background: #f1f5f9;
  color: #334155;
}

@media (max-width: 768px) {
  .company-profile-container {
    padding: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .tabs-wrapper {
    flex-direction: column;
  }
  
  .tab-btn {
    justify-content: center;
  }
}
</style>