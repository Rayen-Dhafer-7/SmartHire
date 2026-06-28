<template>
  <div class="jobs-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="jobs-container">
      <div class="page-header">
        <div class="header-content">
          <div>
            <h2 class="page-title">Available Jobs</h2>
            <p class="page-subtitle">Discover opportunities that match your skills</p>
          </div>
          <div class="stats-badge">
            <span class="badge-count">{{ filteredPosts.length }}</span>
            <span>Jobs Available</span>
          </div>
        </div>
      </div>

      <!-- Search & Filter Section -->
      <JobFilters 
        :filters="filters" 
        :showMatchedOnly="showMatchedOnly"
        @update:showMatchedOnly="showMatchedOnly = $event"
        @toggle-change="handleToggleChange"
      />

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading available jobs...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredPosts.length === 0" class="empty-state">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
          <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
          <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          <circle cx="12" cy="12" r="2"/>
        </svg>
        <h4>No jobs found matching your criteria</h4>
        <p>Try adjusting your filters or search terms</p>
      </div>

      <!-- Job Listings -->
      <div v-else class="jobs-grid">
        <JobCard 
          v-for="post in filteredPosts" 
          :key="post.id" 
          :post="post" 
          @apply="applyForJob"
        />
      </div>

      <VoiceAssistant 
        :onSearchJobs="handleVoiceSearch"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { showError } from '../../../utils/notifications';

import JobFilters from './JobFilters.vue';
import JobCard from './JobCard.vue';
import VoiceAssistant from './VoiceAssistant.vue';

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

    const locationMatch = !filters.value.location || (() => {
      const searchLocations = filters.value.location
        .toLowerCase()
        .split(',')
        .map(s => s.trim())
        .filter(s => s.length > 0);
      return searchLocations.some(searchLoc =>
        post.location.toLowerCase().includes(searchLoc)
      );
    })();

    const typeMatch = !filters.value.type || post.type === filters.value.type;

    const skillMatch = !filters.value.skill || (() => {
      const searchSkills = filters.value.skill
        .toLowerCase()
        .split(',')
        .map(s => s.trim())
        .filter(s => s.length > 0);
      return searchSkills.every(searchSkill =>
        post.skills.some(postSkill =>
          postSkill.toLowerCase().includes(searchSkill)
        )
      );
    })();

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
  if (matchedOnly) {
    fetchMatchedJobs();
  } else {
    fetchAllJobs();
  }
};

const fetchAllJobs = async () => {
  isLoading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) throw new Error('No authentication token found');
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/worker/getPosts`, {
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
    console.error('Error fetching all jobs:', error);
    showError('Error', error.message || 'Failed to load job listings');
  } finally {
    isLoading.value = false;
  }
};

const fetchMatchedJobs = async () => {
  isLoading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) throw new Error('No authentication token found');
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/worker/getPostsMatche`, {
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
      throw new Error(data.message || 'Failed to fetch matched job posts');
    }
  } catch (error) {
    console.error('Error fetching matched jobs:', error);
    showError('Error', error.message || 'Failed to load matched job listings');
  } finally {
    isLoading.value = false;
  }
};

const fetchVoiceMatchedJobs = async (audioBlob) => {
  isLoading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) throw new Error('No authentication token found');
    
    const formData = new FormData();
    formData.append('audio', audioBlob, 'recording.webm');
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/worker/getPostsMatcheVoice`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: formData
    });
    
    const data = await response.json();
    
    if (data.status === 'success') {
      posts.value = data.posts || [];
      showMatchedOnly.value = true;
      if (data.count === 0) {
        showError('No matches', 'No jobs found matching your voice search');
      }
    } else {
      throw new Error(data.message || 'Failed to fetch voice-matched job posts');
    }
  } catch (error) {
    console.error('Error fetching voice-matched jobs:', error);
    showError('Error', error.message || 'Failed to load voice-matched job listings');
  } finally {
    isLoading.value = false;
  }
};

const handleVoiceSearch = (audioBlob) => {
  if (!audioBlob) {
    showError('Error', 'No recording found. Please try again.');
    return;
  }
  fetchVoiceMatchedJobs(audioBlob);
};

onMounted(() => {
  fetchAllJobs();
});
</script>

<style scoped>
.jobs-wrapper {
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

.jobs-container {
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

.jobs-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (max-width: 768px) {
  .jobs-container {
    padding: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>