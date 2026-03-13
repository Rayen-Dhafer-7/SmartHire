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

      <!-- Ranking Table -->
      <CandidateTable :candidates="applicants" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { showError } from '../../../utils/notifications';
import CandidateTable from './CandidateTable.vue';

const route = useRoute();
const router = useRouter();
const post = ref({});
const applicants = ref([]);
const isLoading = ref(true);
const error = ref(false);
const errorMessage = ref('');

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

onMounted(() => {
  fetchPostDetails();
});
</script>

<style scoped>
.badge-green {
  background-color: #d1fae5;
  color: #065f46;
  padding: 0.5em 0.8em;
  border-radius: 4px;
}

.badge-gray {
  background-color: #f3f4f6;
  color: #374151;
  padding: 0.5em 0.8em;
  border-radius: 4px;
}

.btn-link {
  color: #6c757d;
}

.btn-link:hover {
  color: #0d6efd;
}

.back-button {
  background-color: #ffffff;
  border: 1px solid #9ca6af;
  color: #495057;
  transition: all 0.2s ease;
}

.back-button:hover {
  background-color: #e9ecef;
  border-color: #ced4da;
  color: #212529;
}
</style>