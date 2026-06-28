<template>
  <div class="worker-profile-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="worker-profile-container">
      <div class="page-header">
        <div>
          <h2 class="page-title">Worker Profile</h2>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="tabs-container">
        <div class="tabs-wrapper">
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'cv' }"
            @click="activeTab = 'cv'"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            CV Upload
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'profile' }"
            @click="activeTab = 'profile'"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
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

      <!-- CV Upload Tab -->
      <CVUploadTab 
        v-if="activeTab === 'cv'" 
        :profile="profile" 
        :isExtracting="isExtracting"
        @file-selected="handleResumeSelected"
        @remove-cv="removeCV"
        @update-profile="mergeProfileData"
      />

      <!-- General Information Tab -->
      <ProfileTab 
        v-if="activeTab === 'profile'" 
        :profile="profile" 
        :logoPreview="logoPreview"
        :isGeneratingCV="isGeneratingCV"
        @save-profile="saveProfile"
        @reset-form="resetForm"
        @photo-selected="handlePhotoSelected"
        @download-cv="downloadCV"
        @generate-cv="generateCV"
        @add-education="addEducation"
        @remove-education="removeEducation"
        @add-experience="addExperience"
        @remove-experience="removeExperience"
        @add-project="addProject"
        @remove-project="removeProject"
        @add-skill="addSkill"
        @remove-skill="removeSkill"
        @add-certification="addCertification"
        @remove-certification="removeCertification"
      />

      <!-- Change Password Tab -->
      <PasswordTab v-if="activeTab === 'password'" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { showError, showSuccess, showConfirm } from '../../../utils/notifications';

import CVUploadTab from './tabs/CVUploadTab.vue';
import ProfileTab from './tabs/ProfileTab.vue';
import PasswordTab from './tabs/PasswordTab.vue';
import { setProfile, getProfile } from '../../../utils/storage.js';

const activeTab = ref('profile');

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
  resumePath: '',
  photoFile: null
});

const logoPreview = ref(null);
const selectedResumeFile = ref(null);
const isGeneratingCV = ref(false);
const isExtracting = ref(false);

onMounted(async () => {
  if (!sessionStorage.getItem('profilePageReloaded')) {
    sessionStorage.setItem('profilePageReloaded', 'true');
    window.location.reload();
    return;
  }

  profile.value = getProfile();
  logoPreview.value = profile.value.photo;
});

const fetchWorkerInfo = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      window.location.href = '/login';
      return;
    }

    const response = await axios.get(`${import.meta.env.VITE_API_URL}/worker/info`, {  
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });

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

      if (data.cv?.file_size) {
        profile.value.resumeName = data.cv.original_name;
        profile.value.resumeId = data.cv.id;
        profile.value.resumePath = data.cv.file_path;
        profile.value.resumeSize = (data.cv.file_size / 1024).toFixed(2) + ' KB';

        const uploadedAt = new Date(data.cv.uploaded_at);
        profile.value.resumeDate = `${String(uploadedAt.getDate()).padStart(2, '0')}/${String(uploadedAt.getMonth() + 1).padStart(2, '0')}/${uploadedAt.getFullYear()}`;
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
  } catch (error) {
    console.error('Error loading profile data:', error);
  }
};

const handlePhotoSelected = (file) => {
  const reader = new FileReader();
  reader.onload = (e) => {
    logoPreview.value = e.target.result;
    profile.value.photo = e.target.result;
  };
  reader.readAsDataURL(file);
  profile.value.photoFile = file;
};

const handleResumeSelected = async (file) => {
  try {
    if (!file || !(file instanceof File)) {
      showError('Invalid File', 'Please select a valid file.');
      return;
    }
    
    const token = localStorage.getItem('auth_token');
    const formData = new FormData();
    formData.append('cv', file, file.name);

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/cv/upload`, formData, {  
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.status === 'success') {
      profile.value.resumeName = file.name;
      profile.value.resumeSize = formatFileSize(file.size);
      profile.value.resumeDate = new Date().toLocaleDateString();
      profile.value.resumeId = response.data.cv_id || response.data.id;
      profile.value.resumePath = response.data.file_path;
      
      showSuccess('Success', 'CV uploaded successfully.', 2000);
      await fetchWorkerInfo();
    } else {
      throw new Error(response.data.message || 'Failed to upload CV');
    }
  } catch (error) {
    console.error('CV upload error:', error);
    showError('Upload Failed', error.response?.data?.message || 'Failed to upload CV');
    throw error;
  }
};

const removeCV = async (cvId) => {
  try {
    const token = localStorage.getItem('auth_token');
    await axios.delete(`${import.meta.env.VITE_API_URL}/worker/cv/remove/${cvId}`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    profile.value.resumeName = '';
    profile.value.resumeSize = '';
    profile.value.resumeDate = '';
    selectedResumeFile.value = null;
   } catch (error) {
    console.error('Failed to remove CV:', error);
    showError('Error', 'Failed to remove CV');
  }
};

const downloadCV = () => {
  if (profile.value.resumePath) {
    window.open(profile.value.resumePath, '_blank');
  }
};

const generateCV = async () => {
  try {
    isGeneratingCV.value = true;
    const token = localStorage.getItem('auth_token');

    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/worker/cv/generate`,
      {},
      {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
      }
    );

    if (response.data.status === 'success') {
      const data = response.data.data;
      profile.value.resumeName = data.original_name;
      profile.value.resumeId = data.id;
      profile.value.resumePath = data.file_path;
      profile.value.resumeSize = (data.file_size / 1024).toFixed(2) + ' KB';

      const uploadedAt = new Date(data.uploaded_at);
      profile.value.resumeDate = `${String(uploadedAt.getDate()).padStart(2, '0')}/${String(uploadedAt.getMonth() + 1).padStart(2, '0')}/${uploadedAt.getFullYear()}`;

      showSuccess('CV Generated!', 'Your CV has been created successfully.', 2500);
    }
  } catch (error) {
    console.error('Generate CV error:', error);
    showError('Error', error.response?.data?.message || 'Failed to generate CV');
  } finally {
    isGeneratingCV.value = false;
  }
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const mergeProfileData = async () => {
  isExtracting.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    const formData = new FormData();
    formData.append('path', profile.value.resumePath);

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/cv/text`, formData, {  
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.status === 'success') {
      await fetchWorkerInfo();
      activeTab.value = 'profile';
      await loadProfileData();
      showSuccess('Success', 'Profile data merged successfully.', 2000);
    }
  } catch (error) {
    showError('Error', 'Failed to merge profile data');
  } finally {
    isExtracting.value = false;
  }
};

const saveProfile = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const formData = new FormData();
    formData.append('fullName', profile.value.fullName);
    formData.append('email', profile.value.email);
    formData.append('location', profile.value.location);
    formData.append('industry', profile.value.bio);

    if (profile.value.linkedin) formData.append('url_linkedin', profile.value.linkedin);
    if (profile.value.github) formData.append('url_github', profile.value.github);
    if (profile.value.website) formData.append('url_website', profile.value.website);
    if (profile.value.gmail) formData.append('url_gmail', profile.value.gmail);

    if (profile.value.photoFile instanceof File) {
      formData.append('profile', profile.value.photoFile);
    }

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/update`, formData, {  
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'multipart/form-data' }
    });
    
    if (response.data.status === 'success') {
      setProfile(profile.value);
      showSuccess('Profile Saved!', 'Your profile has been updated successfully.');
      await fetchWorkerInfo();
      profile.value.photoFile = null;
    } else {
      throw new Error(response.data.message || 'Failed to update profile');
    }
  } catch (error) {
    showError('Error', error.response?.data?.message || 'Failed to update profile');
  }
};

const resetForm = () => {
  showConfirm('Reset Form?', 'Discard unsaved changes?', 'Yes', 'Cancel')
    .then(res => {
      if (res.isConfirmed) {
        profile.value.photoFile = null;
        loadProfileData();
      }
    });
};

// Section Actions
const addSkill = async (skillName) => {
  if (!profile.value.skills.some(s => s.name === skillName)) {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/skill/add`, { skill: skillName }, { 
        headers: { 'Authorization': `Bearer ${token}` }
      });
      profile.value.skills.push({ id: response.data.skill_id, name: response.data.skill_name });
      showSuccess('Skill Added!', 'Skill added successfully', 1500);
    } catch (error) {
      showError('Error', error.response?.data?.message || 'Failed to add skill');
    }
  }
};

const removeSkill = async (index) => {
  const skill = profile.value.skills[index];
  profile.value.skills.splice(index, 1);
  try {
    const token = localStorage.getItem('auth_token');
    await axios.delete(`${import.meta.env.VITE_API_URL}/worker/skill/remove/${skill.id}`, {    
      headers: { 'Authorization': `Bearer ${token}` }
    });
  } catch (error) {
    showError('Error', error.response?.data?.message || 'Failed to remove skill');
  }
};

const addEducation = async (data) => {
  try {
    const token = localStorage.getItem('auth_token');
    const payload = {
      degree: data.degree,
      institution: data.institution,
      location: data.location || '',
      start_year: data.startYear || '',
      end_year: data.endYear || ''
    };
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/education/add`, payload, {    
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (response.data.status === 'success') {
      profile.value.education.push({ id: response.data.education_id, ...payload });
      showSuccess('Added', 'Education added successfully', 1500);
    }
  } catch (error) {
    showError('Error', 'Failed to add education');
  }
};

const removeEducation = async (index) => {
  const item = profile.value.education[index];
  profile.value.education.splice(index, 1);
  try {
    const token = localStorage.getItem('auth_token');
    await axios.delete(`${import.meta.env.VITE_API_URL}/worker/education/remove/${item.id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
  } catch (error) {
    showError('Error', 'Failed to remove education');
  }
};

const addExperience = async (data) => {
  try {
    const token = localStorage.getItem('auth_token');
    const payload = {
      title: data.title,
      company: data.company,
      location: data.location || '',
      employment_type: data.type || 'Onsite',
      start_date: data.startDate || '',
      end_date: data.endDate || '',
      description: data.pointsText
    };
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/experience/add`, payload, {   
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (response.data.status === 'success') {
      profile.value.experience.push({ id: response.data.experience_id, ...payload });
      showSuccess('Added', 'Experience added successfully', 1500);
    }
  } catch (error) {
    showError('Error', 'Failed to add experience');
  }
};

const removeExperience = async (index) => {
  const item = profile.value.experience[index];
  profile.value.experience.splice(index, 1);
  try {
    const token = localStorage.getItem('auth_token');
    await axios.delete(`${import.meta.env.VITE_API_URL}/worker/experience/remove/${item.id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
  } catch (error) {
    showError('Error', 'Failed to remove experience');
  }
};

const addProject = async (data) => {
  try {
    const token = localStorage.getItem('auth_token');
    const technologiesArray = data.technologies.split(',').map(t => t.trim()).filter(t => t);
    const pointsArray = data.pointsText.split('\n').filter(line => line.trim());

    const payload = {
      name: data.name,
      description: '',
      technologies: technologiesArray,
      points: pointsArray
    };
    
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/project/add`, payload, {  
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (response.data.status === 'success') {
      profile.value.projects.push({ id: response.data.project_id, ...payload });
      showSuccess('Added', 'Project added successfully', 1500);
    }
  } catch (error) {
    showError('Error', 'Failed to add project');
  }
};

const removeProject = async (index) => {
  const item = profile.value.projects[index];
  profile.value.projects.splice(index, 1);
  try {
    const token = localStorage.getItem('auth_token');
    await axios.delete(`${import.meta.env.VITE_API_URL}/worker/project/remove/${item.id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
  } catch (error) {
    showError('Error', 'Failed to remove project');
  }
};

const addCertification = async (data) => {
  try {
    const token = localStorage.getItem('auth_token');
    const payload = {
      name: data.name,
      issuer: data.issuer || '',
      issue_date: data.date || ''
    };
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/certification/add`, payload, {  
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (response.data.status === 'success') {
      profile.value.certifications.push({ id: response.data.certification_id, ...payload });
      showSuccess('Added', 'Certification added successfully', 1500);
    }
  } catch (error) {
    showError('Error', 'Failed to add certification');
  }
};

const removeCertification = async (index) => {
  const item = profile.value.certifications[index];
  profile.value.certifications.splice(index, 1);
  try {
    const token = localStorage.getItem('auth_token');
    await axios.delete(`${import.meta.env.VITE_API_URL}/worker/certification/remove/${item.id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
  } catch (error) {
    showError('Error', 'Failed to remove certification');
  }
};
</script>

<style scoped>
.worker-profile-wrapper {
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

.worker-profile-container {
  position: relative;
  z-index: 1;
  max-width: 2500px;
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
  .worker-profile-container {
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