<template>
  <div class="old-posts-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="old-posts-container">
      <div class="page-header">
        <div class="header-content">
          <div>
            <h2 class="page-title">Old Hire Posts</h2>
            <p class="page-subtitle">View and manage your expired job postings</p>
          </div>
          <div class="stats-badge">
            <span class="badge-count">{{ filteredPosts.length }}</span>
            <span>Total Expired</span>
          </div>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="search-container">
        <div class="search-wrapper">
          <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input 
            type="text" 
            v-model="searchQuery" 
            class="search-input" 
            placeholder="Search by post title..."
            @input="handleSearch"
          />
          <button 
            v-if="searchQuery" 
            @click="clearSearch" 
            class="clear-search-btn"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </div>

      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading posts...</p>
      </div>

      <div v-else-if="filteredPosts.length === 0" class="empty-state">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <h4 v-if="searchQuery">No posts found matching "{{ searchQuery }}"</h4>
        <h4 v-else>No old job posts found</h4>
        <p>Expired posts will appear here</p>
      </div>

      <div v-else class="posts-grid">
        <PostCard 
          v-for="post in filteredPosts" 
          :key="post.id" 
          :post="formatPost(post)" 
          variant="expired"
          @click="viewPost(post.id)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { showError } from '../../../utils/notifications';
import PostCard from './shared/PostCard.vue';

const router = useRouter();
const posts = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');

const filteredPosts = computed(() => {
  if (!searchQuery.value.trim()) {
    return posts.value;
  }
  const query = searchQuery.value.toLowerCase().trim();
  return posts.value.filter(post => 
    post.title && post.title.toLowerCase().includes(query)
  );
});

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

const handleSearch = () => {};

const clearSearch = () => {
  searchQuery.value = '';
};

const fetchOldPosts = async () => {
  isLoading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) throw new Error('No authentication token found');
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/company/getOld`, {  
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
      throw new Error(data.message || 'Failed to fetch old posts');
    }
  } catch (error) {
    console.error('Error fetching old posts:', error);
    showError('Error', error.message || 'Failed to load old job posts');
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchOldPosts();
});
</script>

<style scoped>
.old-posts-wrapper {
  min-height: 100vh;
  position: relative;
}

/* Animated Background */
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
  animation-delay: 0s;
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

.old-posts-container {
  position: relative;
  z-index: 1;
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Page Header */
.page-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 1rem;
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

.stats-badge {
  background: white;
  padding: 0.75rem 1.5rem;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stats-badge .badge-count {
  font-size: 1.5rem;
  font-weight: 700;
  color: #64748b;
  display: block;
  line-height: 1;
}

.stats-badge span:last-child {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Search */
.search-container {
  margin-bottom: 2rem;
}

.search-wrapper {
  position: relative;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 2.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: white;
}

.search-input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.clear-search-btn {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 0;
  display: flex;
  transition: color 0.2s ease;
}

.clear-search-btn:hover {
  color: #ef4444;
}

/* Posts Grid */
.posts-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-state svg {
  margin-bottom: 1rem;
}

.empty-state h4 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
  .old-posts-container {
    padding: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .search-wrapper {
    max-width: 100%;
  }
}
</style>