<!-- CreateJobPost.vue -->
<template>
  <div class="create-job-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="create-job-container">
      <div class="page-header">
        <div class="header-content">
          <div class="header-left">
            <h2 class="page-title">Create New Job Post</h2>
            <p class="page-subtitle">Fill in the details below to post a new job opportunity</p>
          </div>
        </div>
      </div>

      <div class="card">
        <JobPostForm 
          @submit="createPost" 
          @cancel="cancel" 
          :is-loading="isLoading"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { showSuccess, showError } from '../../../utils/notifications';
import JobPostForm from './forms/JobPostForm.vue';

const router = useRouter();
const isLoading = ref(false);

const createPost = async (formData) => {
  const now = new Date();
  const formattedDate = now.toISOString().split('T')[0];
  
  const postData = {
    title: formData.title,
    description: formData.description,
    deadline: formData.deadline,
    postDate: formattedDate,
    type: formData.type,
    count: parseInt(formData.count) || 1,
    skillsArray: formData.skills ? formData.skills.split(',').map(skill => skill.trim()).filter(skill => skill) : []
  };
  
  isLoading.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      throw new Error('No authentication token found. Please login again.');
    }
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/company/savePost`, { 
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(postData)
    });
    
    const data = await response.json();
    
    if (data.status === 'success') {
      await showSuccess('Post Created!', 'Your new job post is now live.', 2000);
      router.push('/company/inprogress-posts');
    } else {
      throw new Error(data.message || 'Failed to create job post');
    }
    
  } catch (error) {
    console.error('Error creating job post:', error);
    showError('Error', error.message || 'Failed to create job post. Please try again.');
  } finally {
    isLoading.value = false;
  }
};

const cancel = () => {
  router.push('/company/inprogress-posts');
};
</script>

<style scoped>
.create-job-wrapper {
  min-height: 100vh;
  position: relative;
  top: -3%;
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
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -30px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

.create-job-container {
  position: relative;
  z-index: 1;
  max-width: 100%;  /* Full width */
  margin: 0 auto;
  padding: 2rem 4rem;  /* Add horizontal padding */
}

/* Page Header */
.page-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-left {
  flex: 1;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  text-decoration: none;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  transition: all 0.3s ease;
}

.back-link:hover {
  color: #4f46e5;
  transform: translateX(-4px);
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0.5rem 0 0.5rem;
  letter-spacing: -0.5px;
}

.page-subtitle {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0;
}

/* Card */
.card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  transition: all 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .create-job-container {
    padding: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .card {
    border-radius: 16px;
  }
}
</style>