<template>
  <div>
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
      <h2 class="page-title">Available Jobs</h2>
    </div>

    <!-- Search & Filter Section with Toggle -->
    <JobFilters 
      :filters="filters" 
      :showMatchedOnly="showMatchedOnly"
      @update:showMatchedOnly="showMatchedOnly = $event"
      @toggle-change="handleToggleChange"
    />

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading available jobs...</p>
    </div>

    <!-- Job Listings -->
    <div v-else-if="filteredPosts.length === 0" class="text-center py-5 text-muted">
      <i class="bi bi-emoji-frown fs-1 d-block mb-3"></i>
      <h5>No jobs found matching your criteria</h5>
      <p>Try adjusting your filters or search terms.</p>
    </div>

    <div v-else class="d-flex flex-column gap-3">
       <JobCard 
         v-for="post in filteredPosts" 
         :key="post.id" 
         :post="post" 
         @apply="applyForJob"
       />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { showError } from '../../../utils/notifications';
import JobFilters from './JobFilters.vue';
import JobCard from './JobCard.vue';

const router = useRouter();
const posts = ref([]);
const isLoading = ref(true);
const showMatchedOnly = ref(false);

const filters = ref({
  keyword: '',
  location: '',
  type: '',
  skill: ''
});

const filteredPosts = computed(() => {
  return posts.value.filter(post => {
    const keywordMatch = !filters.value.keyword || 
                         post.title.toLowerCase().includes(filters.value.keyword.toLowerCase()) || 
                         post.company.toLowerCase().includes(filters.value.keyword.toLowerCase());
    
    const locationMatch = !filters.value.location || 
                          post.location.toLowerCase().includes(filters.value.location.toLowerCase());
    
    const typeMatch = !filters.value.type || post.type === filters.value.type;

    const skillMatch = !filters.value.skill || 
                       post.skills.some(skill => skill.toLowerCase().includes(filters.value.skill.toLowerCase()));

    return keywordMatch && locationMatch && typeMatch && skillMatch;
  });
});

const applyForJob = (post) => {
  router.push({
    path: `/worker/test-application/${post.id}`,
    query: { 
      post: JSON.stringify(post)
    }
  });
};

const handleToggleChange = (matchedOnly) => {
  showMatchedOnly.value = matchedOnly;
  fetchPosts(); // Refetch when toggle changes
};

const fetchPosts = async () => {
  isLoading.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      throw new Error('No authentication token found. Please login again.');
    }
    
    // Choose endpoint based on toggle
    const endpoint = showMatchedOnly.value 
      ? `${import.meta.env.VITE_API_URL}/worker/getPostsMatche` 
      : `${import.meta.env.VITE_API_URL}/worker/getPosts`;
    
    const response = await fetch(endpoint, {
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
      throw new Error(data.message || 'Failed to fetch job posts');
    }
    
  } catch (error) {
    console.error('Error fetching job posts:', error);
    showError('Error', error.message || 'Failed to load job listings');
  } finally {
    isLoading.value = false;
  }
};

// Watch for changes in the toggle
watch(showMatchedOnly, () => {
  fetchPosts();
});

onMounted(() => {
  fetchPosts();
});
</script>

<style scoped>
.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}
</style>