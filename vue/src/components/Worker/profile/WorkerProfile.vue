<template>
  <div>
    <!-- Tabs Navigation -->
    <div class="card mb-4">
      <div class="card-body p-0">
        <ul class="nav nav-tabs nav-fill">
          <li class="nav-item">
            <button 
              class="nav-link" 
              :class="{ active: activeTab === 'cv' }"
              @click="activeTab = 'cv'"
            >
              <i class="bi bi-file-earmark-arrow-up me-2"></i> CV Upload
            </button>
          </li>
          <li class="nav-item">
            <button 
              class="nav-link" 
              :class="{ active: activeTab === 'profile' }"
              @click="activeTab = 'profile'"
            >
              <i class="bi bi-person-circle me-2"></i> General Information
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
    </div>

    <!-- CV Upload Tab -->
    <CVUploadTab 
      v-if="activeTab === 'cv'" 
      :profile="profile" 
      @file-selected="handleResumeSelected"
      @remove-cv="removeCV"
      @update-profile="mergeProfileData"
    />

    <!-- General Information Tab (Main Profile) -->
    <ProfileTab 
      v-if="activeTab === 'profile'" 
      :profile="profile" 
      :logoPreview="logoPreview"
      @save-profile="saveProfile"
      @reset-form="resetForm"
      @photo-selected="handlePhotoSelected"
      @download-cv="downloadCV"
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { showError, showSuccess, showConfirm } from '../../../utils/notifications';

// Import Subcomponents
import CVUploadTab from './tabs/CVUploadTab.vue';
import ProfileTab from './tabs/ProfileTab.vue';
import PasswordTab from './tabs/PasswordTab.vue';
import { setProfile, getProfile } from '../../../utils/storage.js';

// Active tab state
const activeTab = ref('profile');

// Profile data structure
const profile = ref({
  fullName: '',
  email: 'worker@example.com',
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
  photoFile: null // Added to track photo file
});

const logoPreview = ref(null);
const selectedResumeFile = ref(null);

onMounted(async () => {

  if (!sessionStorage.getItem('profilePageReloaded')) {
    sessionStorage.setItem('profilePageReloaded', 'true');
    window.location.reload();
    return;
  }


  profile.value = getProfile();
  console.log('Loaded profile:', profile.value);
  
  //await fetchCompanyInfo();
  //await loadProfileData();
  
  logoPreview.value = profile.value.photo;
});

// ===================================
// DATA LOADING
// ===================================
const fetchCompanyInfo = async () => {
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
      
      // Store photo in both logoPreview and profile.photo
      logoPreview.value = data.worker.photoUrl;
      profile.value.photo = data.worker.photoUrl;
      console.log('Fetched worker data:', data);
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
    console.log('Loaded profile data:', response.data.data);
  } catch (error) {
    console.error('Error loading profile data:', error);
  }
};

// ===================================
// FILE HANDLING - UPDATED
// ===================================
const handlePhotoSelected = (file) => {
  const reader = new FileReader();
  reader.onload = (e) => {
    // Update both logoPreview and profile.photo
    logoPreview.value = e.target.result;
    profile.value.photo = e.target.result; // This will update TopNav immediately
  };
  reader.readAsDataURL(file);
  
  // Store the file for upload
  profile.value.photoFile = file;
};

const handleResumeSelected = async (file) => {
  try {
    // Validate file before processing
    if (!file || !(file instanceof File)) {
      showError('Invalid File', 'Please select a valid file.');
      return;
    }
    
    selectedResumeFile.value = file;
    profile.value.resumeName = file.name;
    profile.value.resumeSize = formatFileSize(file.size);
    profile.value.resumeDate = new Date().toLocaleDateString('en-US', {
      year: 'numeric', month: 'long', day: 'numeric'
    });

    const token = localStorage.getItem('auth_token');
    const formData = new FormData();
    formData.append('cv', file, file.name);

    console.log('Uploading CV:', file.name, file.size, file.type);
    
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/cv/upload`, formData, {  
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });

    console.log('Upload response:', response.data);

    if (response.data.status === 'success') {
      showSuccess('Success', 'CV uploaded successfully.', 2000);
      await fetchCompanyInfo();
      selectedResumeFile.value = null;
    }

  } catch (error) {
    console.error('CV upload error:', error);
    
    let errorMessage = 'Failed to upload CV';
    if (error.response?.status === 422) {
      errorMessage = error.response.data?.message || 'Invalid file format or size';
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    }
    
    showError('Upload Failed', errorMessage);
    
    // Reset UI on error
    selectedResumeFile.value = null;
    profile.value.resumeName = '';
    profile.value.resumeSize = '';
    profile.value.resumeDate = '';
  }
};

const removeCV = async (cvId) => {
  try {
    const token = localStorage.getItem('auth_token');

    await axios.delete(`${import.meta.env.VITE_API_URL}/worker/cv/remove/${cvId}`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });

    // Reset UI state
    profile.value.resumeName = '';
    profile.value.resumeSize = '';
    profile.value.resumeDate = '';
    selectedResumeFile.value = null;

  } catch (error) {
    console.error('Failed to remove CV:', error.response?.data || error);
  }
};

const downloadCV = () => {
  const cvUrl = profile.value.resumePath;
  const link = document.createElement('a');
  link.href = cvUrl;
  link.download = 'CV.pdf'; 
  link.target = '_blank';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const mergeProfileData = async () => {
  const formData = new FormData();
  const token = localStorage.getItem('auth_token');

  formData.append('path', profile.value.resumePath);

  const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/cv/text`, formData, {  
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'multipart/form-data'
    }
  });

  if(response.data.status === 'success'){
    await fetchCompanyInfo();
    activeTab.value = 'profile';
    await loadProfileData();
    showSuccess('Success', 'Profile data merged successfully.', 2000);
  }
};

// ===================================
// PROFILE SAVE - UPDATED
// ===================================
const saveProfile = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const formData = new FormData();
    formData.append('fullName', profile.value.fullName);
    formData.append('email', profile.value.email);
    formData.append('location', profile.value.location);
    formData.append('industry', profile.value.bio);

    if(profile.value.linkedin) formData.append('url_linkedin', profile.value.linkedin);
    if(profile.value.github) formData.append('url_github', profile.value.github);
    if(profile.value.website) formData.append('url_website', profile.value.website);
    if(profile.value.gmail) formData.append('url_gmail', profile.value.gmail);

    // Use photoFile if it exists (new upload)
    if (profile.value.photoFile instanceof File) {
      formData.append('profile', profile.value.photoFile);
    }
    
    if (selectedResumeFile.value) {
      formData.append('cv', selectedResumeFile.value);
    }

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/update`, formData, {  
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });
    
    if (response.data.status === 'success') {
      setProfile(profile.value);
      showSuccess('Profile Saved!', 'Your profile has been updated successfully.');
      await fetchCompanyInfo();
      selectedResumeFile.value = null;
      profile.value.photoFile = null; // Clear photo file after upload
    } else {
      throw new Error(response.data.message || 'Failed to update profile');
    }
  } catch (error) {
    console.log(error.response.data)   
  console.log('Errors:', JSON.stringify(error.response.data, null, 2))  // ← add this

    showError('Error', error.response?.data?.message || 'Failed to update profile');
  }
};

const resetForm = () => {
  showConfirm('Reset Form?', 'Discard unsaved changes?', 'Yes', 'Cancel')
    .then(res => { if(res.isConfirmed) {
      profile.value.photoFile = null; // Clear photo file on reset
      loadProfileData(); 
    }});
}

// ===================================
// SECTION ACTIONS (API CALLS)
// ===================================

// SKILLS
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
    const response = await axios.delete(`${import.meta.env.VITE_API_URL}/worker/skill/remove/${skill.id}`, {    
      headers: { 'Authorization': `Bearer ${token}` }
    });
  } catch (error) {
    showError('Error', error.response?.data?.message || 'Failed to remove skill');
  }
};

// EDUCATION
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
    const response = await axios.delete(`${import.meta.env.VITE_API_URL}/worker/education/remove/${item.id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
  } catch (error) {
    showError('Error', 'Failed to remove education');
  }
};

// EXPERIENCE
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

// PROJECTS
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

// CERTIFICATIONS
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
.nav-tabs {
  border-bottom: 2px solid #dee2e6;
}
.nav-tabs .nav-link {
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
</style>