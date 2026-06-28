<template>
  <div class="applications-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="applications-container">
      <div class="page-header">
        <div class="header-content">
          <div>
            <h2 class="page-title">My Applications</h2>
            <p class="page-subtitle">Track and manage all your job applications</p>
          </div>
          <div class="stats-badge">
            <span class="badge-count">{{ filteredPosts.length }}</span>
            <span>Applications</span>
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
            placeholder="Search applications by job title..."
          />
          <button v-if="searchQuery" @click="clearSearch" class="clear-search-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading your applications...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="empty-state error-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h4>Failed to load applications</h4>
        <p>{{ errorMessage }}</p>
        <button class="btn-retry" @click="fetchTakenPosts">Try Again</button>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredPosts.length === 0" class="empty-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        <h4 v-if="searchQuery">No applications found matching "{{ searchQuery }}"</h4>
        <h4 v-else>You haven't applied to any jobs yet</h4>
        <p v-if="!searchQuery">Start your job search and apply to opportunities that match your skills</p>
        <button v-if="!searchQuery" class="btn-browse" @click="$router.push('/worker/jobs')">
          Browse Jobs
        </button>
        <button v-else class="btn-clear" @click="clearSearch">Clear Search</button>
      </div>

      <!-- Application Listings -->
      <div v-else class="applications-grid">
        <ApplicationCard 
          v-for="post in filteredPosts" 
          :key="post.id" 
          :post="formatPost(post)" 
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { showError } from '../../../utils/notifications';
import ApplicationCard from './ApplicationCard.vue';

const router = useRouter();
const posts = ref([]);
const isLoading = ref(true);
const error = ref(false);
const errorMessage = ref('');
const searchQuery = ref('');

const filteredPosts = computed(() => {
  if (!searchQuery.value.trim()) return posts.value;
  const query = searchQuery.value.toLowerCase().trim();
  return posts.value.filter(post => 
    post.title && post.title.toLowerCase().includes(query)
  );
});

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

const clearSearch = () => {
  searchQuery.value = '';
};

const fetchTakenPosts = async () => {
  isLoading.value = true;
  error.value = false;
  errorMessage.value = '';
  
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) throw new Error('No authentication token found');
    
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
.applications-wrapper {
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

.applications-container {
  position: relative;
  z-index: 1;
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

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
  color: #4f46e5;
  display: block;
  line-height: 1;
}

.stats-badge span:last-child {
  font-size: 0.75rem;
  color: #94a3b8;
}

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
  padding: 0.875rem 2.5rem 0.875rem 2.75rem;
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

.loading-state {
  text-align: center;
  padding: 4rem;
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

.empty-state {
  text-align: center;
  padding: 4rem;
  background: white;
  border-radius: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-state.error-state {
  background: #fef2f2;
}

.empty-state h4 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #94a3b8;
  margin-bottom: 1.5rem;
}

.btn-retry, .btn-browse, .btn-clear {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-retry, .btn-browse {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
}

.btn-retry:hover, .btn-browse:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.btn-clear {
  background: white;
  border: 1.5px solid #e2e8f0;
  color: #64748b;
}

.btn-clear:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.applications-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (max-width: 768px) {
  .applications-container {
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