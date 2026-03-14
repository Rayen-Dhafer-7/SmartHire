<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { showSuccess, showError } from '../utils/notifications';
import axios from 'axios';

// Import CSS
import '../assets/styles/colors.css';
import '../assets/styles/layout.css';
import '../assets/styles/forms.css';

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
  location: '',
  profile: null
});

// Photo preview for both company and worker
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
    location: '',
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
    
    // Reset the forgot password state after 3 seconds
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
  console.log('Form submission triggered');
  console.log('Mode:', isLoginMode.value ? 'Login' : 'Signup');
  console.log('User Type:', userType.value);
  console.log('Form Data:', formData.value);

  if (isLoginMode.value) {
    // LOGIN LOGIC - Use Laravel API
    console.log('Attempting login via API...');
    
    try {
      const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/login`, {
        email: formData.value.email,
        password: formData.value.password
      }, {
        headers: {
          'Content-Type': 'application/json'
        }
      });
      
      console.log('Login response:', response.data);
      
      // Save token to localStorage
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user_role', response.data.role);
  
      // Login Success
      showSuccess('Login Successful!', `Welcome back! Redirecting to ${response.data.role} dashboard...`, 1500);
      
      // Redirect based on role
      if (response.data.role === 'worker') {
        console.log('Redirecting to worker dashboard');
        setTimeout(() => {
          router.push('/worker');
        }, 1500);
      } else if (response.data.role === 'company') {
        console.log('Redirecting to company dashboard');
        setTimeout(() => {
          router.push('/company/inprogress-posts');
        }, 1500);
      }
      
    } catch (error) {
      console.error('Login error:', error.response?.data || error.message);
      
      showError('Login Failed', error.response?.data?.error || error.response?.data?.message || 'Invalid email or password');
    }
    
  } else {
    // SIGNUP LOGIC
    console.log('Attempting signup...');
    
    try {
      if (userType.value === 'worker') {
        // Worker Signup
        console.log('Creating worker account...');
        
        const workerData = new FormData();
        workerData.append('fullName', formData.value.fullName);
        workerData.append('email', formData.value.email);
        workerData.append('password', formData.value.password);
        
        if (formData.value.profile) {
          workerData.append('profile', formData.value.profile);
          console.log('Profile photo attached');
        }
        
        const response = await axios.post(`${import.meta.env.VITE_API_URL}/worker/register`, workerData, { 
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        console.log('Worker signup response:', response.data);
        
        showSuccess('Account Created!', 'Your worker account has been created successfully.').then(() => {
          toggleMode(); // Switch to login mode
        });
        
      } else if (userType.value === 'company') {
        // Company Signup
        console.log('Creating company account...');
        
        const companyData = new FormData();
        companyData.append('companyName', formData.value.companyName);
        companyData.append('email', formData.value.email);
        companyData.append('password', formData.value.password);
        companyData.append('location', formData.value.location);
        companyData.append('industry', formData.value.industry);
        
        if (formData.value.profile) {
          companyData.append('logo', formData.value.profile);
          console.log('Company logo attached');
        }
        
        const response = await axios.post(`${import.meta.env.VITE_API_URL}/company/register`, companyData, {  
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        console.log('Company signup response:', response.data);
        
        showSuccess('Account Created!', 'Your company account has been created successfully.').then(() => {
          toggleMode(); // Switch to login mode
        });
      }
    } catch (error) {
      console.error('Signup error:', error.response?.data || error.message);
      
      showError('Signup Failed', error.response?.data?.error || error.response?.data?.message || 'An error occurred during signup');
    }
  }
};

// Optional: Check if already logged in
import { onMounted } from 'vue';
onMounted(() => {
  const token = localStorage.getItem('auth_token');
  const role = localStorage.getItem('user_role');
  
  if (token && role) {
    // Redirect to appropriate dashboard
    if (role === 'worker') {
      router.push('/worker');
    } else if (role === 'company') {
      router.push('/company');
    }
  }
});
</script>

<template>
  <div class="auth-container" :class="{ 'is-login': isLoginMode }">
    
    <!-- Left Side - Image & Branding -->
    <div class="side-image">
      <div class="image-content">
        <video autoplay muted loop playsinline class="bg-video">
          <source src="https://cdn.dribbble.com/userupload/3268137/file/original-440c7f02ce4c74a5bca6a50ec833e590.mp4" type="video/mp4">
          Your browser does not support the video tag.
        </video>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div class="side-form">
      <div class="form-content">
        
        <!-- Headers -->
        <div class="header-section">
          <h1 v-if="isLoginMode && !showForgotPassword">Welcome Back</h1>
          <h1 v-else-if="!isLoginMode">Create Account</h1>
          <h1 v-else-if="showForgotPassword">Reset Password</h1>
          
          <p v-if="isLoginMode && !showForgotPassword" class="subtitle">
            Sign in to your account to continue
          </p>
          <p v-else-if="!isLoginMode" class="subtitle">
            Create a new account to get started
          </p>
          <p v-else-if="showForgotPassword" class="subtitle">
            Enter your email to receive a password reset link
          </p>
        </div>

        <!-- Signup Tabs (Only in Signup Mode) -->
        <div v-if="!isLoginMode" class="tabs">
          <button 
            class="tab-btn" 
            :class="{ active: userType === 'company' }" 
            @click="setType('company')"
          >
            Company
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: userType === 'worker' }" 
            @click="setType('worker')"
          >
            Worker
          </button>
        </div>

        <!-- Forgot Password Section -->
        <div v-if="showForgotPassword" class="forgot-password-section">
          <form @submit.prevent="handleForgotPassword">
            <div class="form-group">
              <label>Email Address</label>
              <input 
                type="email" 
                v-model="formData.email" 
                class="form-control" 
                placeholder="name@example.com" 
                required 
              />
            </div>
            
            <button type="submit" class="btn-primary">
              Send Reset Link
            </button>
            
            <div class="toggle-link">
              <span>Remember your password? <a @click="showForgotPassword = false">Back to login</a></span>
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
                <input 
                  type="email" 
                  id="email"
                  name="email"
                  v-model="formData.email" 
                  class="form-control" 
                  placeholder="name@example.com" 
                  required 
                />
              </div>
              <div class="form-group">
                <div class="password-header">
                  <label>Password</label>

                </div>
                <input 
                  type="password" 
                  id="password"
                  name="password"
                  v-model="formData.password" 
                  class="form-control" 
                  placeholder="••••••••" 
                  required 
                />
              </div>
            </div>

            <!-- SIGNUP MODE FIELDS -->
            <div v-else>
              
              <!-- Profile Upload for BOTH company and worker -->
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
                    <span class="upload-icon">📷</span>
                    <span class="upload-text">
                      {{ userType === 'company' ? 'Company Logo' : 'Profile Photo' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- COMPANY Specific -->
              <div v-if="userType === 'company'">
                <div class="form-group">
                  <label>Company Name</label>
                  <input 
                    type="text" 
                    v-model="formData.companyName" 
                    class="form-control" 
                    placeholder="e.g. Acme Corp" 
                    required 
                  />
                </div>
                <div class="form-group">
                  <label>Business Email</label>
                  <input 
                    type="email" 
                    v-model="formData.email" 
                    class="form-control" 
                    placeholder="hr@company.com" 
                    required 
                  />
                </div>
                <div class="form-group">
                  <label>Location</label>
                  <input 
                    type="text" 
                    v-model="formData.location" 
                    class="form-control" 
                    placeholder="Monastir, Tunisia" 
                    required 
                  />
                </div>
                
                <div class="form-group">
                  <label>Industry</label>
                  <textarea 
                    v-model="formData.industry" 
                    class="form-control" 
                    placeholder="Enter industry details here..."
                    rows="3"
                    required
                  ></textarea>
                </div>
              </div>

              <!-- WORKER Specific -->
              <div v-else>
                <div class="form-group">
                  <label>Full Name</label>
                  <input 
                    type="text" 
                    v-model="formData.fullName" 
                    class="form-control" 
                    placeholder="John Doe" 
                    required 
                  />
                </div>
                <div class="form-group">
                  <label>Work Email</label>
                  <input 
                    type="email" 
                    v-model="formData.email" 
                    class="form-control" 
                    placeholder="john@work.com" 
                    required 
                  />
                </div>
              </div>

              <!-- Password for Signup (Common) -->
              <div class="form-group">
                <label>Set Password</label>
                <input 
                  type="password" 
                  v-model="formData.password" 
                  class="form-control" 
                  placeholder="Create a strong password" 
                  required 
                />
              </div>

            </div>

            <!-- Submit Button -->
            <button type="submit" id="login" name="login" class="btn-primary">
              {{ isLoginMode ? 'Sign In' : 'Get Started' }}
            </button>

            <!-- Toggle Link -->
            <div class="toggle-link">
              <span v-if="isLoginMode">
                Don't have an account? <a @click="toggleMode">Sign up</a>
                <br><br>
                forgot-password-link? <a @click="showForgotPassword = true">Send mail</a>
 
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
</template>

<style scoped>
* {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

/* Profile Upload Styles */
.profile-upload-container {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.profile-upload-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background-color: #f3f4f6;
  border: 2px dashed #d1d5db;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.3s ease;
  position: relative;
}

.profile-upload-circle:hover {
  border-color: var(--primary-color, #4f46e5);
  background-color: #eef2ff;
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
  color: #6b7280;
  text-align: center;
}

.upload-icon {
  font-size: 24px;
  margin-bottom: 4px;
}

.upload-text {
  font-size: 10px;
  font-weight: 500;
}

.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
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
  font-size: 18px;
}

.bg-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  animation: video-breathe 10s ease-in-out infinite;
}

@keyframes video-breathe {
  0%   { transform: scale(1.05) rotate(0deg); }
  50%  { transform: scale(1.08) rotate(0.3deg); }
  100% { transform: scale(1.05) rotate(0deg); }
}

/* Forgot Password Link */
.password-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.forgot-password-link {
  font-size: 0.875rem;
  color: var(--primary-color, #4f46e5);
  text-decoration: none;
  cursor: pointer;
  transition: color 0.2s ease;
}

.forgot-password-link:hover {
  color: var(--primary-dark, #4338ca);
  text-decoration: underline;
}

/* Forgot Password Section */
.forgot-password-section {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .password-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .forgot-password-link {
    align-self: flex-end;
  }
}
</style>