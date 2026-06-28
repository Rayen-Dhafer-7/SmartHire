<template>
  <div class="reset-password-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="reset-password-container">
      <div class="reset-card">
        <div class="card-header-section">
          <div class="header-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
          <h2 class="card-title">Reset Password</h2>
          <p class="card-subtitle">Create a new secure password for your account</p>
        </div>

        <!-- Add loading state -->
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading...</p>
        </div>

        <!-- Show token error if missing -->
        <div v-else-if="!resetToken" class="error-state">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ef4444">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <h3>Invalid Reset Link</h3>
          <p>The password reset link is invalid or has expired.</p>
          <button @click="goToLogin" class="btn-primary">Back to Login</button>
        </div>

        <!-- Show form only if token exists -->
        <div v-else class="card-body">
          <form @submit.prevent="resetPassword">
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
                  :type="showPassword ? 'text' : 'password'"
                  @input="checkPasswordStrength"
                />
                <button 
                  type="button" 
                  class="password-toggle" 
                  @click="showPassword = !showPassword"
                >
                  <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                </button>
              </div>
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
                  <svg v-if="!showConfirmPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
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
              <button type="submit" class="btn-primary" :disabled="submitting">
                <svg v-if="!submitting" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                  <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                </svg>
                <span v-else class="spinner-small"></span>
                {{ submitting ? 'Resetting...' : 'Reset Password' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { showSuccess, showError } from '../../../utils/notifications';

const router = useRouter();
const route = useRoute();
const password = ref({
  newPassword: '',
  confirmPassword: ''
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const strengthPercentage = ref(0);
const strengthText = ref('');
const strengthClass = ref('');
const resetToken = ref('');
const loading = ref(true);
const submitting = ref(false);

const getTokenFromURL = () => {
  // Try to get token from route query or window location
  const token = route.query.token || new URLSearchParams(window.location.search).get('token');
  const role = route.query.role || new URLSearchParams(window.location.search).get('role');
  
  console.log('Token from URL:', token);
  console.log('Role from URL:', role);
  console.log('Full URL:', window.location.href);
  
  return token;
};

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

const goToLogin = () => {
  router.push('/login');
};

onMounted(() => {
  // Small delay to ensure route is fully loaded
  setTimeout(() => {
    resetToken.value = getTokenFromURL();
    loading.value = false;
    
    if (!resetToken.value) {
      console.error('No token found in URL');
      showError('Error', 'Invalid or missing reset token. Please request a new password reset link.');
    } else {
      console.log('Token found:', resetToken.value);
    }
  }, 100);
});

const resetPassword = async () => {
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
  
  submitting.value = true;
  
  try {
    console.log('Sending reset request with token:', resetToken.value);
    console.log('API URL:', `${import.meta.env.VITE_API_URL}/worker/reset-password`);
    
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/reset-password`, 
    {
        password: password.value.newPassword
    }, 
    {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${resetToken.value}`
        }
    });
    
    console.log('Reset response:', response.data);
    
    if (response.data.status === 'success') {
      showSuccess('Success', 'Password reset successfully', 2000);
      
      password.value.newPassword = '';
      password.value.confirmPassword = '';
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user_role', response.data.role);
  
      setTimeout(() => {
        if(response.data.role === 'company'){
          router.push('/company/profile');
        } else {
          router.push('/worker/profile');
        }
      }, 2000);
    }
  } catch (error) {
    console.error('Reset password error:', error);
    console.error('Error response:', error.response?.data);
    showError('Error', error.response?.data?.message || 'Failed to reset password');
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
/* Add these new styles */
.loading-state, .error-state {
  text-align: center;
  padding: 3rem 2rem;
}

.loading-state .spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

.spinner-small {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state svg {
  margin-bottom: 1rem;
}

.error-state h3 {
  color: #ef4444;
  margin-bottom: 0.5rem;
}

.error-state p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Rest of your existing styles remain the same */
.reset-password-wrapper {
  min-height: 100vh;
  position: relative;
  background: #f7f8fb;
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

.reset-password-container {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.reset-card {
  max-width: 500px;
  width: 100%;
  background: white;
  border-radius: 24px;
  box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  animation: slideUp 0.5s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card-header-section {
  text-align: center;
  padding: 2rem 2rem 1rem;
  background: linear-gradient(135deg, #f8fafc, #ffffff);
  border-bottom: 1px solid #e2e8f0;
}

.header-icon {
  width: 64px;
  height: 64px;
  background: #eef2ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  color: #4f46e5;
}

.card-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.5rem;
  letter-spacing: -0.5px;
}

.card-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.card-body {
  padding: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
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

.requirements-section {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.requirements-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: #334155;
  margin: 0 0 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
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
  font-size: 0.7rem;
  color: #64748b;
  transition: color 0.2s ease;
}

.requirement.met {
  color: #10b981;
}

.requirement svg {
  flex-shrink: 0;
}

.form-actions {
  display: flex;
  justify-content: center;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 2rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  justify-content: center;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.btn-primary:active {
  transform: translateY(0);
}

@media (max-width: 768px) {
  .reset-password-container {
    padding: 1rem;
  }
  
  .card-header-section {
    padding: 1.5rem;
  }
  
  .card-title {
    font-size: 1.5rem;
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .requirements-grid {
    grid-template-columns: 1fr;
  }
}
</style>