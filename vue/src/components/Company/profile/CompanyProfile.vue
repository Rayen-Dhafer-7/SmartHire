<template>
  <div>
    <div class="page-header">
      <h2 class="page-title">Company Profile</h2>
    </div>

    <!-- Tabs Navigation -->
    <div class="card-body p-0">
      <ul class="nav nav-tabs nav-fill">
        <li class="nav-item">
          <button 
            class="nav-link" 
            :class="{ active: activeTab === 'profile' }"
            @click="activeTab = 'profile'"
          >
            <i class="bi bi-building me-2"></i> General Information
          </button>
        </li>
        <li class="nav-item">
          <button 
            class="nav-link" 
            :class="{ active: activeTab === 'password' }"
            @click="activeTab = 'password'"
          >
            <i class="bi bi-key me-2"></i> Change Password
          </button>
        </li>
      </ul>
    </div>
    <br><br>

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
  logoDataUrl: '', // NEW: Store the data URL for persistence
  urls: {
    website: '',
    linkedin: '',
    twitter: '',
    facebook: '',
    instagram: '',
    mail: ''
  }
});

// Use computed property to always return a valid logo source
const logoPreview = computed(() => {
  // Priority 1: Data URL from FileReader (when user selects new image)
  if (profile.value.logoDataUrl) {
    return profile.value.logoDataUrl;
  }
  
  // Priority 2: URL from API/storage
  if (profile.value.logoUrl) {
    return profile.value.logoUrl;
  }
  
  // Priority 3: Direct logo string
  if (typeof profile.value.logo === 'string' && profile.value.logo) {
    return profile.value.logo;
  }
  
  // Fallback
  return 'https://via.placeholder.com/150';
});

onMounted(async () => {
  await loadProfileData();
});

const loadProfileData = async () => {
  try {
    // Load from storage first
    const storedProfile = getProfile();
    
    if (storedProfile) {
      // Merge with current profile
      Object.assign(profile.value, storedProfile);
      console.log('Loaded profile from storage:', storedProfile);
      
      // If we have logoDataUrl in storage, use it
      if (storedProfile.logoDataUrl) {
        profile.value.logoDataUrl = storedProfile.logoDataUrl;
      }
      
      // If we don't have logoUrl but have logo (string), use it
      if (!profile.value.logoUrl && typeof profile.value.logo === 'string') {
        profile.value.logoUrl = profile.value.logo;
      }
    } else {
      // No stored profile, fetch from API
      await fetchCompanyInfo();
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
      profile.value.location = data.location;
      profile.value.description = data.industry;
      profile.value.logoUrl = data.logoUrl || '';
      profile.value.logo = data.logoUrl || ''; // Keep for compatibility

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
      
      // Save to storage
      saveToStorage();
    }
  } catch (error) {
    showError('Error', 'Failed to load company information');
  }
};

const saveToStorage = () => {
  // Create a clean object for storage (without File objects)
  const profileForStorage = {
    ...profile.value,
    logo: null, // Don't store File objects
    logoFile: null // Clear any File objects
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

    // Check if we have a File object to upload
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
      await fetchCompanyInfo(); // Refresh from API
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
    // Store the File object for upload
    profile.value.logo = file;
    
    // Create data URL for immediate preview and persistence
    const reader = new FileReader();
    reader.onload = (e) => {
      // Store the data URL for persistence
      profile.value.logoDataUrl = e.target.result;
      
      // Save to storage immediately so it persists on page reload/route change
      saveToStorage();
    };
    reader.readAsDataURL(file);
  }
};
</script>

<style scoped>
/* Tabs Styling */
.nav-tabs {
  border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link {
  background-color: white;
  color: #6c757d;
  border: none;
  border-bottom: 3px solid transparent;
  padding: 1rem 1.5rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
  color: #4f46e5;
  border-bottom-color: #4f46e5;
  background-color: rgba(79, 70, 229, 0.05);
}

.nav-tabs .nav-link.active {
  color: #4f46e5;
  background-color: white;
  border-bottom-color: #4f46e5;
  font-weight: 600;
}

.page-header {
  margin-bottom: 20px;
}
.page-title {
  font-size: 24px;
  font-weight: 600;
}
</style>