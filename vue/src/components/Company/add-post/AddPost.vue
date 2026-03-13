<template>
  <div>
    <div class="page-header">
      <h2 class="page-title">Create New Job Post</h2>
    </div>

    <div class="card">
      <JobPostForm 
        @submit="createPost" 
        @cancel="cancel" 
        :is-loading="isLoading"
      />
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
  // Format date as YYYY-MM-DD (without time)
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
  
  console.log('Job Post Data:', postData);
  
  isLoading.value = true;
  
  try {
    // Get token from localStorage
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      throw new Error('No authentication token found. Please login again.');
    }
    
    // Make API call to your backend
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