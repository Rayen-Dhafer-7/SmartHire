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
        @generate-cv="openCvModelPicker"
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
    <!-- CV Model Picker Modal -->
    <Transition name="fade-overlay">
      <div v-if="showCvModelModal" class="cv-modal-overlay" @click.self="showCvModelModal = false">
        <div class="cv-modal">
          <button class="cv-modal-close" @click="showCvModelModal = false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>

          <div class="cv-modal-header">
            <div class="cv-modal-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
            </div>
            <h3>Choose a CV Template</h3>
            <p>Select the style that matches your target market</p>
          </div>

          <div class="cv-model-grid">
            <!-- Canadian -->
            <div
              class="cv-model-card"
              :class="{ selected: selectedCvTemplate === 'canadian' }"
              @click="selectedCvTemplate = 'canadian'"
            >
              <div class="cv-model-preview canadian-preview">
                <div class="prev-header-bar"></div>
                <div class="prev-line long"></div>
                <div class="prev-line medium"></div>
                <div class="prev-divider"></div>
                <div class="prev-line short"></div>
                <div class="prev-line long"></div>
                <div class="prev-line medium"></div>
                <div class="prev-divider"></div>
                <div class="prev-badges"><span></span><span></span><span></span></div>
              </div>
              <div class="cv-model-info">
                <span class="cv-model-flag">🇨🇦</span>
                <strong>CV Canadien</strong>
                <p>Concise, 1–2 pages, skills-focused, without photo</p>
              </div>
              <div v-if="selectedCvTemplate === 'canadian'" class="cv-model-check">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
            </div>

            <!-- French -->
            <div
              class="cv-model-card"
              :class="{ selected: selectedCvTemplate === 'french' }"
              @click="selectedCvTemplate = 'french'"
            >
              <div class="cv-model-preview french-preview">
                <div class="prev-sidebar">
                  <div class="prev-avatar"></div>
                  <div class="prev-line short" style="margin-top:6px"></div>
                  <div class="prev-line short"></div>
                </div>
                <div class="prev-content">
                  <div class="prev-line long"></div>
                  <div class="prev-line medium"></div>
                  <div class="prev-divider"></div>
                  <div class="prev-line short"></div>
                  <div class="prev-line long"></div>
                  <div class="prev-line medium"></div>
                </div>
              </div>
              <div class="cv-model-info">
                <span class="cv-model-flag">🇫🇷</span>
                <strong>CV Français</strong>
                <p>Two-column layout, with photo, modern design</p>
              </div>
              <div v-if="selectedCvTemplate === 'french'" class="cv-model-check">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
            </div>

            <!-- American -->
            <div
              class="cv-model-card"
              :class="{ selected: selectedCvTemplate === 'american' }"
              @click="selectedCvTemplate = 'american'"
            >
              <div class="cv-model-preview american-preview">
                <div class="prev-name-bar">
                  <div class="prev-line long" style="height:10px;border-radius:3px"></div>
                  <div class="prev-line medium" style="margin-top:4px"></div>
                </div>
                <div class="prev-divider accent"></div>
                <div class="prev-line short bold-label"></div>
                <div class="prev-line long"></div>
                <div class="prev-line medium"></div>
                <div class="prev-divider accent"></div>
                <div class="prev-badges"><span></span><span></span><span></span><span></span></div>
              </div>
              <div class="cv-model-info">
                <span class="cv-model-flag">🇺🇸</span>
                <strong>CV Américain (Resume)</strong>
                <p>Maximum 1 page, professional summary at the top, ATS-friendly</p>
              </div>
              <div v-if="selectedCvTemplate === 'american'" class="cv-model-check">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="cv-modal-actions">
            <button class="btn-cancel" @click="showCvModelModal = false">Cancel</button>
            <button
              class="btn-generate-confirm"
              :disabled="!selectedCvTemplate"
              @click="generateCV"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M13 2L3 14h6l-2 8 10-12h-6l2-8z"/>
              </svg>
              Generate CV
            </button>
          </div>
        </div>
      </div>
    </Transition>

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
const showCvModelModal = ref(false);
const selectedCvTemplate = ref(null);

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

const openCvModelPicker = () => {
  selectedCvTemplate.value = null;
  showCvModelModal.value = true;
};

const generateCV = async () => {
  if (!selectedCvTemplate.value) return;
  showCvModelModal.value = false;
  try {
    isGeneratingCV.value = true;
    const token = localStorage.getItem('auth_token');

    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/worker/cv/generate`,
      { template: selectedCvTemplate.value },
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
/* ── CV Model Picker Modal ── */
.cv-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.cv-modal {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  width: 100%;
  max-width: 760px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 30px 70px rgba(0,0,0,0.2);
  position: relative;
}

.cv-modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #f1f5f9;
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}
.cv-modal-close:hover { background: #e2e8f0; color: #0f172a; }

.cv-modal-header {
  text-align: center;
  margin-bottom: 2rem;
}
.cv-modal-icon {
  width: 56px;
  height: 56px;
  background: #eef2ff;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4f46e5;
  margin: 0 auto 1rem;
}
.cv-modal-header h3 {
  font-size: 1.4rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.375rem;
}
.cv-modal-header p {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
}

.cv-model-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 1.75rem;
}

.cv-model-card {
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.25s;
  position: relative;
  background: #fafbfc;
}
.cv-model-card:hover { border-color: #a5b4fc; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,70,229,0.1); }
.cv-model-card.selected { border-color: #4f46e5; background: #fafeff; box-shadow: 0 0 0 3px rgba(79,70,229,0.15); }

/* Mini CV previews */
.cv-model-preview {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  height: 130px;
  margin-bottom: 0.875rem;
  padding: 8px;
  overflow: hidden;
  position: relative;
}

/* Canadian preview: top bar + lines */
.canadian-preview .prev-header-bar {
  height: 8px; background: #4f46e5; border-radius: 3px; margin-bottom: 6px;
}
/* French preview: two-column with avatar */
.french-preview { display: flex; gap: 6px; padding: 6px; }
.prev-sidebar { width: 30%; display: flex; flex-direction: column; align-items: center; background: #f3f4f6; border-radius: 4px; padding: 6px 4px; }
.prev-avatar { width: 28px; height: 28px; border-radius: 50%; background: #c7d2fe; }
.prev-content { flex: 1; }
/* American preview: name block + accent dividers */
.american-preview .prev-name-bar { margin-bottom: 4px; }
.prev-divider.accent { background: #4f46e5; height: 2px; border-radius: 1px; margin: 5px 0; }
.bold-label { background: #374151; height: 6px; width: 40%; border-radius: 2px; }

/* Shared preview elements */
.prev-line { height: 5px; background: #e2e8f0; border-radius: 3px; margin-bottom: 4px; }
.prev-line.long { width: 90%; }
.prev-line.medium { width: 65%; }
.prev-line.short { width: 40%; }
.prev-divider { height: 1px; background: #e2e8f0; margin: 5px 0; }
.prev-badges { display: flex; gap: 4px; flex-wrap: wrap; }
.prev-badges span { height: 12px; width: 30px; background: #ede9fe; border-radius: 10px; }

.cv-model-info { text-align: center; }
.cv-model-flag { font-size: 1.5rem; display: block; margin-bottom: 0.25rem; }
.cv-model-info strong { display: block; font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.25rem; }
.cv-model-info p { font-size: 0.7rem; color: #64748b; line-height: 1.4; margin: 0; }

.cv-model-check {
  position: absolute;
  top: 0.625rem;
  right: 0.625rem;
  width: 24px;
  height: 24px;
  background: #4f46e5;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cv-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-cancel:hover { background: #f8fafc; border-color: #cbd5e1; }

.btn-generate-confirm {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.75rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}
.btn-generate-confirm:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(79,70,229,0.3); }
.btn-generate-confirm:disabled { opacity: 0.45; cursor: not-allowed; }

/* Overlay transition */
.fade-overlay-enter-active, .fade-overlay-leave-active { transition: opacity 0.3s ease; }
.fade-overlay-enter-from, .fade-overlay-leave-to { opacity: 0; }

@media (max-width: 640px) {
  .cv-model-grid { grid-template-columns: 1fr; }
}

</style>