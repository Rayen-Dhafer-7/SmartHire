<template>
  <div>
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
      <h2 class="page-title">My Applications</h2>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading your applications...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-5 text-muted">
      <i class="bi bi-exclamation-triangle text-warning fs-1 d-block mb-3"></i>
      <h5>Failed to load applications</h5>
      <p class="text-muted">{{ errorMessage }}</p>
      <button class="btn btn-primary mt-3" @click="fetchTakenPosts">
        Try Again
      </button>
    </div>

    <!-- Application Listings -->
    <div v-else-if="posts.length === 0" class="text-center py-5 text-muted">
      <i class="bi bi-file-earmark-text fs-1 d-block mb-3"></i>
      <h5>You haven't applied to any jobs yet.</h5>
      <button class="btn btn-primary mt-3" @click="$router.push('/worker/jobs')">
        Browse Jobs
      </button>
    </div>

    <div v-else class="d-flex flex-column gap-3">
       <ApplicationCard 
         v-for="post in posts" 
         :key="post.id" 
         :post="formatPost(post)" 
       />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { showError } from '../../../utils/notifications';
import ApplicationCard from './ApplicationCard.vue';

const router = useRouter();
const posts = ref([]);
const isLoading = ref(true);
const error = ref(false);
const errorMessage = ref('');

// Format post data from API to match component props
const formatPost = (post) => {
  return {
    id: post.id,
    company: post.companyName || post.company,
    location: post.location,
    title: post.title,
    description: post.description,
    appliedDate: post.test?.test_date || formatDate(post.post_date) || formatDate(new Date()),
    myScore: Math.round(post.test?.final_score || 0),
    type: post.job_type,
    skills: post.skills || [],
    social: post.social || {},
    test: post.test,
    logoUrl: post.logoUrl || null,
    deadline_message: post.deadline_message,
    deadline_formatted: post.deadline_formatted,
    post_date_formatted: post.post_date_formatted
  };
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  });
};

// Fetch taken posts from API
const fetchTakenPosts = async () => {
  isLoading.value = true;
  error.value = false;
  errorMessage.value = '';
  
  try {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      throw new Error('No authentication token found');
    }
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/worker/getTakenPosts`, {  
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (data.status === 'success') {
      posts.value = data.posts || [];
    } else {
      throw new Error(data.message || 'Failed to fetch applications');
    }
    
  } catch (err) {
    console.error('Error fetching taken posts:', err);
    error.value = true;
    errorMessage.value = err.message || 'Failed to load your applications. Please try again.';
    showError('Error', errorMessage.value);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchTakenPosts();
});
</script>

<style scoped>
.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}
</style>