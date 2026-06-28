<template>
  <div class="password-tab">
    <div class="profile-card">
      <div class="card-header-section">
        <div class="header-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
        </div>
        <h3 class="card-title">Change Password</h3>
      </div>
      
      <div class="card-content">
        <form @submit.prevent="changePassword">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Current Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input 
                  type="password" 
                  class="form-control" 
                  v-model="password.oldPassword" 
                  placeholder="Enter current password"
                />
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">New Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input 
                  type="password" 
                  class="form-control" 
                  v-model="password.newPassword" 
                  placeholder="Enter new password"
                />
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">Confirm New Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input 
                  type="password" 
                  class="form-control" 
                  v-model="password.confirmPassword" 
                  placeholder="Confirm new password"
                />
              </div>
            </div>
          </div>
          
          <div class="password-requirements">
            <p class="requirements-title">Password Requirements:</p>
            <ul>
              <li>At least 6 characters long</li>
              <li>Contains letters and numbers</li>
              <li>Special characters recommended for better security</li>
            </ul>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
              </svg>
              Update Password
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
    
    const response = await axios.put(`${import.meta.env.VITE_API_URL}/company/update-password`, { 
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
.password-tab {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.profile-card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.card-header-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #f8fafc, #ffffff);
  border-bottom: 1px solid #e2e8f0;
}

.header-icon {
  width: 40px;
  height: 40px;
  background: #eef2ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4f46e5;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.card-content {
  padding: 2rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 0;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  color: #334155;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.password-requirements {
  background: #f8fafc;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin-bottom: 2rem;
}

.requirements-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0.5rem;
}

.password-requirements ul {
  margin: 0;
  padding-left: 1.25rem;
  font-size: 0.75rem;
  color: #64748b;
}

.password-requirements li {
  margin-bottom: 0.25rem;
}

.form-actions {
  text-align: right;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .card-content {
    padding: 1rem;
  }
  
  .form-actions {
    text-align: center;
  }
  
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
}
</style>