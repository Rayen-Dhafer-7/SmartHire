<template>
  <div class="tab-content">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">Change Password</h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="changePassword">
          <div class="grid-3">
            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <input type="password" class="form-control" v-model="password.oldPassword" placeholder="Enter current password" />
            </div>
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" class="form-control" v-model="password.newPassword" placeholder="Enter new password" />
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" v-model="password.confirmPassword" placeholder="Confirm new password" />
            </div>
          </div>
          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-lg"></i> Update Password
            </button>      
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { showSuccess, showError } from '../../../../utils/notifications';

const password = ref({
  oldPassword: '',
  newPassword: '',
  confirmPassword: ''
});

const changePassword = async () => {
  if (!password.value.oldPassword || !password.value.newPassword || !password.value.confirmPassword) {
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
  
  try {
    const token = localStorage.getItem('auth_token');
    
    const response = await axios.put(`${import.meta.env.VITE_API_URL}/worker/update-password`, {  
      oldPassword: password.value.oldPassword,
      newPassword: password.value.newPassword
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    if (response.data.status === 'success') {
      showSuccess('Success', 'Password updated successfully', 2000);
      
      // Reset password fields
      password.value.oldPassword = '';
      password.value.newPassword = '';
      password.value.confirmPassword = '';
    }
  } catch (error) {
    showError('Error', error.response?.data?.message || 'Failed to update password');
  }
};
</script>

<style scoped>
.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

@media (max-width: 992px) {
  .grid-3 {
    grid-template-columns: 1fr;
  }
}
</style>
