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
        <p class="card-subtitle">Update your password to keep your account secure</p>
      </div>
      
      <div class="card-content">
        <form @submit.prevent="changePassword">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Current Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input 
                  type="password" 
                  class="form-control" 
                  v-model="password.oldPassword" 
                  placeholder="Enter current password"
                  :type="showCurrentPassword ? 'text' : 'password'"
                />
                <button 
                  type="button" 
                  class="password-toggle" 
                  @click="showCurrentPassword = !showCurrentPassword"
                >
                  <svg v-if="!showCurrentPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                </button>
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
                  :type="showNewPassword ? 'text' : 'password'"
                  @input="checkPasswordStrength"
                />
                <button 
                  type="button" 
                  class="password-toggle" 
                  @click="showNewPassword = !showNewPassword"
                >
                  <svg v-if="!showNewPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                </button>
              </div>
              <!-- Password Strength Indicator -->
              <div v-if="password.newPassword" class="password-strength">
                <div class="strength-bar">
                  <div class="strength-fill" :class="strengthClass" :style="{ width: strengthPercentage + '%' }"></div>
                </div>
                <span class="strength-text" :class="strengthClass">{{ strengthText }}</span>
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
                  :type="showConfirmPassword ? 'text' : 'password'"
                />
                <button 
                  type="button" 
                  class="password-toggle" 
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  <svg v-if="!showConfirmPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                </button>
              </div>
              <div v-if="password.confirmPassword && password.newPassword !== password.confirmPassword" class="error-hint">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ef4444">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Passwords do not match
              </div>
            </div>
          </div>

          <!-- Password Requirements -->
          <div class="requirements-section">
            <h4 class="requirements-title">Password Requirements:</h4>
            <div class="requirements-grid">
              <div class="requirement" :class="{ met: password.newPassword.length >= 6 }">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="password.newPassword.length >= 6 ? '#10b981' : '#94a3b8'">
                  <polyline v-if="password.newPassword.length >= 6" points="20 6 9 17 4 12"/>
                  <circle v-else cx="12" cy="12" r="10"/>
                </svg>
                <span>At least 6 characters</span>
              </div>
              <div class="requirement" :class="{ met: /[A-Z]/.test(password.newPassword) }">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="/[A-Z]/.test(password.newPassword) ? '#10b981' : '#94a3b8'">
                  <polyline v-if="/[A-Z]/.test(password.newPassword)" points="20 6 9 17 4 12"/>
                  <circle v-else cx="12" cy="12" r="10"/>
                </svg>
                <span>At least one uppercase letter</span>
              </div>
              <div class="requirement" :class="{ met: /[a-z]/.test(password.newPassword) }">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="/[a-z]/.test(password.newPassword) ? '#10b981' : '#94a3b8'">
                  <polyline v-if="/[a-z]/.test(password.newPassword)" points="20 6 9 17 4 12"/>
                  <circle v-else cx="12" cy="12" r="10"/>
                </svg>
                <span>At least one lowercase letter</span>
              </div>
              <div class="requirement" :class="{ met: /[0-9]/.test(password.newPassword) }">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="/[0-9]/.test(password.newPassword) ? '#10b981' : '#94a3b8'">
                  <polyline v-if="/[0-9]/.test(password.newPassword)" points="20 6 9 17 4 12"/>
                  <circle v-else cx="12" cy="12" r="10"/>
                </svg>
                <span>At least one number</span>
              </div>
            </div>
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

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const strengthPercentage = ref(0);
const strengthText = ref('');
const strengthClass = ref('');

const checkPasswordStrength = () => {
  const pwd = password.value.newPassword;
  let strength = 0;
  
  if (pwd.length >= 6) strength += 25;
  if (/[A-Z]/.test(pwd)) strength += 25;
  if (/[a-z]/.test(pwd)) strength += 25;
  if (/[0-9]/.test(pwd)) strength += 25;
  
  strengthPercentage.value = strength;
  
  if (strength === 0) {
    strengthText.value = '';
    strengthClass.value = '';
  } else if (strength <= 25) {
    strengthText.value = 'Weak';
    strengthClass.value = 'weak';
  } else if (strength <= 50) {
    strengthText.value = 'Fair';
    strengthClass.value = 'fair';
  } else if (strength <= 75) {
    strengthText.value = 'Good';
    strengthClass.value = 'good';
  } else {
    strengthText.value = 'Strong';
    strengthClass.value = 'strong';
  }
};

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
      
      // Reset form
      password.value.oldPassword = '';
      password.value.newPassword = '';
      password.value.confirmPassword = '';
      strengthPercentage.value = 0;
      strengthText.value = '';
      strengthClass.value = '';
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
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #f8fafc, #ffffff);
  border-bottom: 1px solid #e2e8f0;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: #eef2ff;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  color: #4f46e5;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem;
  letter-spacing: -0.3px;
}

.card-subtitle {
  font-size: 0.875rem;
  color: #64748b;
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
  font-weight: 600;
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
  z-index: 1;
}

.form-control {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 2.5rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  background: white;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.password-toggle {
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
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
  z-index: 1;
}

.password-toggle:hover {
  color: #4f46e5;
}

/* Password Strength */
.password-strength {
  margin-top: 0.5rem;
}

.strength-bar {
  height: 4px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.25rem;
}

.strength-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.strength-fill.weak {
  background: #ef4444;
}

.strength-fill.fair {
  background: #f59e0b;
}

.strength-fill.good {
  background: #10b981;
}

.strength-fill.strong {
  background: #10b981;
}

.strength-text {
  font-size: 0.7rem;
  font-weight: 500;
}

.strength-text.weak {
  color: #ef4444;
}

.strength-text.fair {
  color: #f59e0b;
}

.strength-text.good {
  color: #10b981;
}

.strength-text.strong {
  color: #10b981;
}

.error-hint {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-top: 0.5rem;
  font-size: 0.7rem;
  color: #ef4444;
}

/* Requirements Section */
.requirements-section {
  background: #f8fafc;
  border-radius: 16px;
  padding: 1.25rem;
  margin-bottom: 2rem;
}

.requirements-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
  margin: 0 0 0.75rem;
}

.requirements-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

.requirement {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #64748b;
  transition: color 0.2s ease;
}

.requirement.met {
  color: #10b981;
}

.requirement svg {
  flex-shrink: 0;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
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

.btn-primary:active {
  transform: translateY(0);
}

/* Responsive */
@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .requirements-grid {
    grid-template-columns: 1fr;
  }
  
  .card-content {
    padding: 1.5rem;
  }
}

@media (max-width: 768px) {
  .card-header-section {
    padding: 1rem;
  }
  
  .card-content {
    padding: 1rem;
  }
  
  .form-actions {
    justify-content: stretch;
  }
  
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
}
</style>