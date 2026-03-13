<template>
  <div>
    <div class="page-header d-flex justify-content-between align-items-center">
      <h2 class="page-title">In Progress Posts</h2>
    </div>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="posts.length === 0" class="text-center py-5 text-muted">
      <p>No active job posts found.</p>
    </div>

    <div v-else class="d-flex flex-column gap-3">
      <PostCard 
        v-for="post in posts" 
        :key="post.id" 
        :post="formatPost(post)" 
        variant="active"
        @click="viewPost(post.id)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { showError } from '../../../utils/notifications';
import PostCard from './shared/PostCard.vue';

const router = useRouter();
const posts = ref([]);
const isLoading = ref(true);

const viewPost = (id) => {
  router.push(`/company/post-details/${id}`);
};

const formatPost = (post) => {
  return {
    id: post.id,
    title: post.title,
    description: post.description,
    date: formatDate(post.post_date),
    deadline: formatDate(post.deadline),
    skills: post.skills || [],
    applicants: post.applicants 
  };
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  });
};

const fetchPosts = async () => {
  isLoading.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      throw new Error('No authentication token found');
    }
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/company/getPosts`, {  
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (data.status === 'success') {
      posts.value = data.posts || [];
    } else {
      throw new Error(data.message || 'Failed to fetch posts');
    }
    
  } catch (error) {
    console.error('Error fetching posts:', error);
    showError('Error', error.message || 'Failed to load job posts');
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchPosts();
});
</script>

<style scoped>
.gap-3 { gap: 12px; }
</style>