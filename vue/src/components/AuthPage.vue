<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { showSuccess, showError } from '../utils/notifications';
import axios from 'axios';

// State Management
const isLoginMode = ref(true);
const userType = ref('company');
const showForgotPassword = ref(false);

// Form Data
const formData = ref({
  email: '',
  password: '',
  fullName: '',
  companyName: '',
  jobTitle: '',
  industry: '',
  profile: null
});

// Photo preview
const photoPreview = ref(null);

// Toggle Functions
const toggleMode = () => {
  isLoginMode.value = !isLoginMode.value;
  formData.value = {
    email: '',
    password: '',
    fullName: '',
    companyName: '',
    jobTitle: '',
    industry: '',
    profile: null
  };
  photoPreview.value = null;
  showForgotPassword.value = false;
};

const setType = (type) => {
  userType.value = type;
};

// File Upload Functions
const triggerFileInput = () => {
  document.getElementById('profile-upload').click();
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    formData.value.profile = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Forgot Password Function
const handleForgotPassword = async () => {
  if (!formData.value.email) {
    showError('Error', 'Please enter your email address');
    return;
  }

  try {
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/company/send-reset-mail`, { 
      email: formData.value.email
    }, {
      headers: {
        'Content-Type': 'application/json'
      }
    });

    console.log('Reset email response:', response.data);
    
    showSuccess('Success', 'Check your email for password reset instructions!');
    
    setTimeout(() => {
      showForgotPassword.value = false;
      formData.value.email = '';
    }, 3000);

  } catch (error) {
    console.error('Forgot password error:', error.response?.data || error.message);
    
    const errorMessage = error.response?.data?.error || 
                        error.response?.data?.message || 
                        'Failed to send reset email. Please try again.';
    
    showError('Error', errorMessage);
  }
};

// Router and Submit
const router = useRouter();

const handleSubmit = async () => {
  if (isLoginMode.value) {
    try {
      const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/login`, {
        email: formData.value.email,
        password: formData.value.password
      }, {
        headers: {
          'Content-Type': 'application/json'
        }
      });
      
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user_role', response.data.role);
  
      showSuccess('Login Successful!', `Welcome back! Redirecting to ${response.data.role} dashboard...`, 1500);
      
      if (response.data.role === 'worker') {
        setTimeout(() => {
          router.push('/worker');
        }, 1500);
      } else if (response.data.role === 'company') {
        setTimeout(() => {
          router.push('/company/inprogress-posts');
        }, 1500);
      }
      
    } catch (error) {
      console.error('Login error:', error.response?.data || error.message);
      showError('Login Failed', error.response?.data?.error || error.response?.data?.message || 'Invalid email or password');
    }
    
  } else {
    try {
      if (userType.value === 'worker') {
        const workerData = new FormData();
        workerData.append('fullName', formData.value.fullName);
        workerData.append('email', formData.value.email);
        workerData.append('password', formData.value.password);
        
        if (formData.value.profile) {
          workerData.append('profile', formData.value.profile);
        }
        
        const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/register`, workerData, { 
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        showSuccess('Account Created!', 'Your worker account has been created successfully.').then(() => {
          toggleMode();
        });
        
      } else if (userType.value === 'company') {
        const companyData = new FormData();
        companyData.append('companyName', formData.value.companyName);
        companyData.append('email', formData.value.email);
        companyData.append('password', formData.value.password);
        companyData.append('industry', formData.value.industry);
        
        if (formData.value.profile) {
          companyData.append('logo', formData.value.profile);
        }
        
        const response = await axios.post(`${import.meta.env.VITE_API_URL}/company/register`, companyData, {  
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        showSuccess('Account Created!', 'Your company account has been created successfully.').then(() => {
          toggleMode();
        });
      }
    } catch (error) {
      console.error('Signup error:', error.response?.data || error.message);
      showError('Signup Failed', error.response?.data?.error || error.response?.data?.message || 'An error occurred during signup');
    }
  }
};

import { onMounted } from 'vue';
onMounted(() => {
  const token = localStorage.getItem('auth_token');
  const role = localStorage.getItem('user_role');
  
  if (token && role) {
    if (role === 'worker') {
      router.push('/worker');
    } else if (role === 'company') {
      router.push('/company');
    } else if (role === 'admin') {
      router.push('/admin');
    }
  }
});
</script>

<template>
  <div class="auth-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="auth-container" :class="{ 'is-login': isLoginMode }">
      
      <!-- Left Side - Image & Branding -->
      <div class="side-image">
        <div class="image-content">
          <img 
            src="/l1.png" 
            alt="SmartHire Background" 
            class="bg-image"
          />

        </div>
      </div>

      <!-- Right Side - Form -->
      <div class="side-form">
        <div class="form-container">
          <!-- Logo -->
          <div class="logo-section">
            <div class="logo-container">
              <svg viewBox="0 0 100 100" width="48" height="48" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="2" width="96" height="96" rx="18" ry="18" fill="#1B74E4"/>
                <text 
                  x="50" y="72"
                  font-family="Arial Black, Arial, sans-serif" 
                  font-weight="900" 
                  font-size="58" 
                  fill="white" 
                  text-anchor="middle"
                  letter-spacing="-2"
                >Sh</text>
              </svg>
              <span class="brand-name">SmartHire</span>
            </div>
          </div>

          <div class="form-content">
            <!-- Headers -->
            <div class="header-section">
              <h1 v-if="isLoginMode && !showForgotPassword">Welcome Back</h1>
              <h1 v-else-if="!isLoginMode">Create Account</h1>
              <h1 v-else-if="showForgotPassword">Reset Password</h1>
              
              <p v-if="isLoginMode && !showForgotPassword" class="subtitle">
                Sign in to access your dashboard
              </p>
              <p v-else-if="!isLoginMode" class="subtitle">
                Join the future of hiring
              </p>
              <p v-else-if="showForgotPassword" class="subtitle">
                We'll send you instructions to reset your password
              </p>
            </div>

            <!-- Signup Tabs -->
            <div v-if="!isLoginMode" class="tabs">
              <button 
                class="tab-btn" 
                :class="{ active: userType === 'company' }" 
                @click="setType('company')"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="4" y="8" width="16" height="12" rx="2"/>
                  <path d="M8 4v4M16 4v4M12 12v4M8 16h8"/>
                </svg>
                Company
              </button>
              <button 
                class="tab-btn" 
                :class="{ active: userType === 'worker' }" 
                @click="setType('worker')"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                Worker
              </button>
            </div>

            <!-- Forgot Password Section -->
            <div v-if="showForgotPassword" class="forgot-password-section">
              <form @submit.prevent="handleForgotPassword">
                <div class="form-group">
                  <label>Email Address</label>
                  <div class="input-wrapper">
                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                      <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <input 
                      type="email" 
                      v-model="formData.email" 
                      class="form-control" 
                      placeholder="name@example.com" 
                      required 
                    />
                  </div>
                </div>
                
                <button type="submit" class="btn-primary">
                  <span>Send Reset Link</span>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                  </svg>
                </button>
                
                <div class="toggle-link">
                  <a @click="showForgotPassword = false">← Back to login</a>
                </div>
              </form>
            </div>

            <!-- Normal Login/Signup Section -->
            <div v-else>
              <form @submit.prevent="handleSubmit">
                
                <!-- LOGIN MODE FIELDS -->
                <div v-if="isLoginMode">
                  <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                      <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                      </svg>
                      <input 
                        type="email" 
                        v-model="formData.email" 
                        class="form-control" 
                        placeholder="name@example.com" 
                        required 
                      />
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <div class="password-header">
                      <label>Password</label>
                      <a class="forgot-password-link" @click="showForgotPassword = true">Forgot password?</a>
                    </div>
                    <div class="input-wrapper">
                      <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                      </svg>
                      <input 
                        type="password" 
                        v-model="formData.password" 
                        class="form-control" 
                        placeholder="••••••••" 
                        required 
                      />
                    </div>
                  </div>
                </div>

                <!-- SIGNUP MODE FIELDS -->
                <div v-else>
                  
                  <!-- Profile Upload -->
                  <div class="profile-upload-container">
                    <input 
                      type="file" 
                      id="profile-upload" 
                      accept="image/*" 
                      class="hidden-input" 
                      @change="handleFileChange"
                      style="display: none;"
                    />
                    <div class="profile-upload-circle" @click="triggerFileInput">
                      <div v-if="photoPreview">
                        <img :src="photoPreview" alt="Profile Preview" class="profile-preview" />
                        <div class="upload-overlay">
                          <span class="edit-icon">✎</span>
                        </div>
                      </div>
                      <div v-else class="upload-placeholder">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                          <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                          <circle cx="12" cy="13" r="4"/>
                        </svg>
                        <span class="upload-text">
                          {{ userType === 'company' ? 'Upload Logo' : 'Upload Photo' }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- COMPANY Specific -->
<div v-if="userType === 'company'">
  <div class="form-group">
    <label>Company Name</label>
    <div class="input-wrapper">
      <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <rect x="4" y="8" width="16" height="12" rx="2"/>
        <path d="M8 4v4M16 4v4"/>
      </svg>
      <input 
        type="text" 
        v-model="formData.companyName" 
        class="form-control" 
        placeholder="Acme Corp" 
        required 
      />
    </div>
  </div>
  
  <div class="form-group">
    <label>Business Email</label>
    <div class="input-wrapper">
      <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
        <polyline points="22,6 12,13 2,6"/>
      </svg>
      <input 
        type="email" 
        v-model="formData.email" 
        class="form-control" 
        placeholder="hr@company.com" 
        required 
      />
    </div>
  </div>
  
  <!-- Industry field REMOVED -->
</div>

                  <!-- WORKER Specific -->
                  <div v-else>
                    <div class="form-group">
                      <label>Full Name</label>
                      <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                          <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <input 
                          type="text" 
                          v-model="formData.fullName" 
                          class="form-control" 
                          placeholder="John Doe" 
                          required 
                        />
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label>Work Email</label>
                      <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                          <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input 
                          type="email" 
                          v-model="formData.email" 
                          class="form-control" 
                          placeholder="john@work.com" 
                          required 
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Password for Signup -->
                  <div class="form-group">
                    <label>Set Password</label>
                    <div class="input-wrapper">
                      <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                      </svg>
                      <input 
                        type="password" 
                        v-model="formData.password" 
                        class="form-control" 
                        placeholder="Create a strong password" 
                        required 
                      />
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary">
                  <span>{{ isLoginMode ? 'Sign In' : 'Create Account' }}</span>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                  </svg>
                </button>

                <!-- Toggle Link -->
                <div class="toggle-link">
                  <span v-if="isLoginMode">
                    Don't have an account? <a @click="toggleMode">Sign up</a>
                  </span>
                  <span v-else>
                    Already have an account? <a @click="toggleMode">Log in</a>
                  </span>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
* {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
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

.auth-wrapper {
  min-height: 100vh;
  position: relative;
  background: #f7f8fb;
}

.auth-container {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
  background: transparent;
}

/* Left Side - Image */
.side-image {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
}

.image-content {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.bg-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  animation: videoBreathe 10s ease-in-out infinite;
}

@keyframes videoBreathe {
  0% { transform: scale(1.05) rotate(0deg); }
  50% { transform: scale(1.08) rotate(0.3deg); }
  100% { transform: scale(1.05) rotate(0deg); }
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: transparent;  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 3rem;
}

.bg-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.overlay-content {
  text-align: center;
  color: white;
  animation: fadeUp 0.8s ease-out;
}

.overlay-content h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  letter-spacing: -0.5px;
}

.overlay-content p {
  font-size: 1.1rem;
  opacity: 0.9;
  max-width: 300px;
  margin: 0 auto;
  line-height: 1.5;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Right Side - Form */
.side-form {
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  backdrop-filter: blur(0);
  position: relative;
}

.form-container {
  width: 100%;
  max-width: 480px;
  padding: 2.5rem;
}

.logo-section {
  position: absolute;
  top: 30px;
  right: 30px;
  z-index: 10;
  margin-bottom: 0;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.3s ease;
}

.logo-container:hover {
  transform: translateY(-2px);
}

.brand-name {
  font-size: 24px;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.5px;
}

/* Form Content */
.form-content {
  animation: fadeUp 0.6s ease-out;
}

.header-section {
  text-align: center;
  margin-bottom: 2rem;
}

.header-section h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.5rem;
  letter-spacing: -0.5px;
}

.header-section .subtitle {
  color: #64748b;
  font-size: 0.95rem;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  background: #f1f5f9;
  padding: 0.25rem;
  border-radius: 999px;
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  border-radius: 999px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tab-btn svg {
  transition: all 0.3s ease;
}

.tab-btn.active {
  background: white;
  color: #4f46e5;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.tab-btn.active svg {
  stroke: #4f46e5;
}

.tab-btn:hover:not(.active) {
  color: #334155;
}

/* Form Groups */
.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.9rem;
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
  padding: 0.875rem 1rem 0.875rem 2.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: white;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-control.textarea {
  resize: vertical;
  padding-top: 0.875rem;
  padding-bottom: 0.875rem;
  min-height: 80px;
}

.textarea .input-icon {
  top: 1.25rem;
  transform: none;
}

/* Password Header */
.password-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.forgot-password-link {
  font-size: 0.85rem;
  color: #4f46e5;
  cursor: pointer;
  text-decoration: none;
  transition: color 0.2s ease;
}

.forgot-password-link:hover {
  color: #4338ca;
  text-decoration: underline;
}

/* Profile Upload */
.profile-upload-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.profile-upload-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  border: 2px dashed #cbd5e1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.3s ease;
  position: relative;
}

.profile-upload-circle:hover {
  border-color: #4f46e5;
  transform: scale(1.02);
}

.profile-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  color: #64748b;
  text-align: center;
}

.upload-placeholder svg {
  stroke: #94a3b8;
}

.upload-text {
  font-size: 0.7rem;
  font-weight: 500;
}

.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.profile-upload-circle:hover .upload-overlay {
  opacity: 1;
}

.edit-icon {
  color: white;
  font-size: 1.5rem;
  font-weight: bold;
}

/* Button */
.btn-primary {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 1.5rem;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.btn-primary:active {
  transform: translateY(0);
}

/* Toggle Link */
.toggle-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #64748b;
  font-size: 0.9rem;
}

.toggle-link a {
  color: #4f46e5;
  cursor: pointer;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}

.toggle-link a:hover {
  color: #4338ca;
  text-decoration: underline;
}

/* Forgot Password Section */
.forgot-password-section {
  animation: fadeUp 0.3s ease;
}

/* Responsive */
@media (max-width: 1024px) {
  .auth-container {
    grid-template-columns: 1fr;
  }
  
  .side-image {
    display: none;
  }
  
  .side-form {
    min-height: 100vh;
  }
  
  .form-container {
    padding: 2rem;
  }
}

@media (max-width: 640px) {
  .form-container {
    padding: 1.5rem;
  }
  
  .header-section h1 {
    font-size: 1.75rem;
  }
  
  .tabs {
    margin-bottom: 1.5rem;
  }
  
  .tab-btn {
    padding: 0.6rem 0.75rem;
    font-size: 0.85rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
}
</style>