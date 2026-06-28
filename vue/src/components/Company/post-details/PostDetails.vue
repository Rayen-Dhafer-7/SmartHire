<template>
  <div>
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading post details...</p>
    </div>

    <div v-else-if="error" class="text-center py-5 text-muted">
      <i class="bi bi-exclamation-triangle text-warning fs-1 d-block mb-3"></i>
      <h5>Failed to load post details</h5>
      <p class="text-muted">{{ errorMessage }}</p>
      <button class="btn btn-primary mt-3" @click="fetchPostDetails">
        Try Again
      </button>
    </div>

    <div v-else>
      <!-- Back Button -->
      <div class="mb-3">
        <button class="btn back-button" @click="goBack">
          <i class="bi bi-arrow-left me-1"></i>
          Back
        </button>
      </div>

      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h2 class="page-title">{{ post.title }}</h2>
          <p class="text-muted">
            Posted on: {{ post.posted_date }} • Deadline: {{ post.deadline }}
          </p>
        </div>
        <div>
          <span class="badge" :class="post.status_badge">{{ post.status_text }}</span>
        </div>
      </div>

      <!-- Ranking Table with profile data -->
      <CandidateTable 
        :candidates="applicants" 
        :profile="companyProfile"
        :logoPreview="logoPreview"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { showError } from '../../../utils/notifications';
import CandidateTable from './CandidateTable.vue';
import { getProfile } from '../../../utils/storage';

const route = useRoute();
const router = useRouter();
const post = ref({});
const applicants = ref([]);
const isLoading = ref(true);
const error = ref(false);
const errorMessage = ref('');
const companyProfile = ref({});
const logoPreview = ref('');

const goBack = () => {
  router.push('/company/inprogress-posts');
};

const fetchPostDetails = async () => {
  isLoading.value = true;
  error.value = false;
  errorMessage.value = '';
  
  try {
    const token = localStorage.getItem('auth_token');
    const postId = route.params.id;
    
    if (!token) {
      throw new Error('No authentication token found');
    }
    
    if (!postId) {
      throw new Error('Post ID not found');
    }
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/company/getpostdetails`, {  
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        post_id: postId
      })
    });
    
    const data = await response.json();
   
    console.log(data);
    if (data.status === 'success') {
      post.value = data.post;
      applicants.value = data.applicants || [];
    } else {
      throw new Error(data.message || 'Failed to fetch post details');
    }
    
  } catch (err) {
    console.error('Error fetching post details:', err);
    error.value = true;
    errorMessage.value = err.message || 'Failed to load post details. Please try again.';
    showError('Error', errorMessage.value);
  } finally {
    isLoading.value = false;
  }
};

// Load company profile
const loadCompanyProfile = () => {
  const storedProfile = getProfile();
  if (storedProfile) {
    companyProfile.value = storedProfile;
    logoPreview.value = storedProfile.logoDataUrl || storedProfile.logoUrl;
    console.log('Company profile loaded in PostDetails:', companyProfile.value);
  } else {
    // Try to load from localStorage directly
    try {
      const localStorageProfile = localStorage.getItem('profile');
      if (localStorageProfile) {
        const parsedProfile = JSON.parse(localStorageProfile);
        companyProfile.value = parsedProfile;
        logoPreview.value = parsedProfile.logoDataUrl || parsedProfile.logoUrl;
        console.log('Company profile loaded from localStorage:', companyProfile.value);
      }
    } catch (e) {
      console.error('Error loading profile from localStorage:', e);
    }
  }
};

onMounted(() => {
  loadCompanyProfile();
  fetchPostDetails();
});
</script>

<style scoped>
.create-job-wrapper {
  min-height: 100vh;
  position: relative;
  background: #f7f8fb;
}

.post-details-container {
  position: relative;
  z-index: 1;
  max-width: 1400px;  /* Wider container */
  margin: 0 auto;
  padding: 2rem;
}

/* Add animated background classes (same as create job page) */
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

/* Update back button */
.back-button {
  background: white;
  border: 1.5px solid #e2e8f0;
  color: #64748b;
  padding: 0.6rem 1.2rem;
  border-radius: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.back-button:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: translateX(-4px);
}

/* Page header */
.page-header {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.5px;
}

/* Badge styles */
.badge-green, .badge-gray {
  padding: 0.5rem 1rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.875rem;
}

.badge-green {
  background: #d1fae5;
  color: #065f46;
}

.badge-gray {
  background: #f1f5f9;
  color: #475569;
}
</style>