<template>
  <div class="tab-content">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">Reset Password</h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="resetPassword">
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input 
              type="password" 
              class="form-control" 
              v-model="password.newPassword" 
              placeholder="Enter new password" 
            />
          </div>
          
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input 
              type="password" 
              class="form-control" 
              v-model="password.confirmPassword" 
              placeholder="Confirm new password" 
            />
          </div>
          
          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-lg"></i> Reset Password
            </button>      
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { showSuccess, showError } from '../../../utils/notifications';

const router = useRouter();
const password = ref({
  newPassword: '',
  confirmPassword: ''
});

// Extract token from URL
const getTokenFromURL = () => {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get('token');
};

const resetToken = ref('');

onMounted(() => {
  resetToken.value = getTokenFromURL();
  console.log(resetToken.value);
  if (!resetToken.value) {
    showError('Error', 'Invalid or missing reset token');
    // Optionally redirect immediately if no token
    // setTimeout(() => router.push('/login'), 3000);
  }
});

const resetPassword = async () => {
  // Validation
  if (!password.value.newPassword || !password.value.confirmPassword) {
    showError('Error', 'Please fill in all password fields.');
    return;
  }
  
  if (password.value.newPassword !== password.value.confirmPassword) {
    showError('Error', 'New password and confirmation do not match.');
    return;
  }
  
  if (password.value.newPassword.length < 6) {
    showError('Error', 'New password must be at least 6 characters.');
    return;
  }
  
  if (!resetToken.value) {
    showError('Error', 'Invalid reset token. Please request a new password reset.');
    return;
  }
  
  try {
    
    
    const token = localStorage.getItem('auth_token');

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/reset-password`, 
    {
        password: password.value.newPassword
    }, 
    {
        headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${resetToken.value}`
        }
    }
    );
    console.log(response.data)
    if (response.data.status === 'success') {
      showSuccess('Success', 'Password reset successfully', 2000);
      
      // Reset password fields
      password.value.newPassword = '';
      password.value.confirmPassword = '';
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user_role', response.data.role);
  
       
      if(response.data.role == 'company '){
        setTimeout(() => {
          router.push('/company/profile');
        }, 2000);
      }else{
        setTimeout(() => {
          router.push('/worker/profile');
        }, 2000);
      }
    }
  } catch (error) {
    showError('Error', error.response?.data?.message || 'Failed to reset password');
  }
};
</script>

<style scoped>
.tab-content {
  min-height: calc(100vh - 200px); /* Adjust based on your layout */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.card {
  max-width: 800px;
  width: 100%;
  margin: 0 auto;
}

.card-header {
  text-align: center;
}

.form-label {
  font-weight: 500;
  margin-bottom: 8px;
}

.form-control {
  padding: 10px 15px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  transition: border-color 0.3s ease;
}

.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-success {
  padding: 10px 30px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

@media (max-width: 576px) {
  .card-body {
    padding: 1.5rem;
  }
  
  .btn-success {
    width: 100%;
    padding: 12px;
  }
}
</style>