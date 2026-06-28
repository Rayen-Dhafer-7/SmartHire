<template>
  <div class="admin-login-page">
    <div class="login-container">
      <!-- Login Card -->
      <div class="login-card">
        <div class="card-header">
          <h1>Admin Panel</h1>
          <p>Secure access to management dashboard</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
          <div class="input-group">
            <div class="input-field">
              <input 
                id="adminUsername" 
                v-model="username" 
                type="text" 
                placeholder="Username"
                autocomplete="off"
                required
              >
              <label for="adminUsername" class="floating-label">Username</label>
            </div>
          </div>

          <div class="input-group">
            <div class="input-field">
              <input 
                id="adminPassword" 
                v-model="password" 
                type="password" 
                placeholder="Password"
                required
              >
              <label for="adminPassword" class="floating-label">Password</label>
            </div>
          </div>

          <button type="submit" class="login-btn" :disabled="loading">
            <span v-if="!loading">Login</span>
            <span v-else>
              <i class="fas fa-spinner fa-spin"></i>
              Logging in...
            </span>
          </button>

          <div class="back-link">
            <router-link to="/">
              <i class="fas fa-arrow-left"></i>
              Back to main site
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const router = useRouter();
const username = ref('');
const password = ref('');
const loading = ref(false);

onMounted(() => {
  const role = localStorage.getItem('user_role');
  if (role === 'admin') {
    router.replace('/admin');
  }
});

const handleLogin = async () => {
  if (username.value.trim() === 'admin' && password.value === 'admin') {
    loading.value = true;
    
    await new Promise(resolve => setTimeout(resolve, 800));
    
    localStorage.setItem('user_role', 'admin');
    localStorage.removeItem('auth_token');
    
    Swal.fire({
      icon: 'success',
      title: 'Welcome!',
      text: 'Redirecting to dashboard...',
      timer: 1500,
      showConfirmButton: false,
      background: '#fff',
      color: '#1e293b'
    });
    
    setTimeout(() => {
      router.push('/admin');
    }, 1500);
    
    return;
  }
  
  Swal.fire({
    icon: 'error',
    title: 'Access Denied',
    text: 'Invalid credentials',
    confirmButtonColor: '#3b82f6',
    background: '#fff',
    color: '#1e293b'
  });
  
  const card = document.querySelector('.login-card');
  card.classList.add('shake');
  setTimeout(() => {
    card.classList.remove('shake');
  }, 500);
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.admin-login-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #3b83f64f 0%, #3b83f679 100%);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  position: relative;
}

/* Login Container */
.login-container {
  width: 100%;
  max-width: 400px;
  padding: 1rem;
}

/* Login Card */
.login-card {
  background: white;
  border-radius: 16px;
  padding: 2.5rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
  animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Card Header */
.card-header {
  text-align: center;
  margin-bottom: 2rem;
}

.card-header h1 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.card-header p {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
}

/* Login Form */
.login-form {
  margin-top: 1.5rem;
}

.input-group {
  margin-bottom: 1.5rem;
}

.input-field {
  position: relative;
}

.input-field input {
  width: 100%;
  padding: 0.75rem 0;
  font-size: 1rem;
  border: none;
  border-bottom: 2px solid #e2e8f0;
  outline: none;
  background: transparent;
  transition: all 0.3s;
  font-family: inherit;
}

.input-field input:focus {
  border-bottom-color: #3b82f6;
}

.input-field input:focus + .floating-label,
.input-field input:not(:placeholder-shown) + .floating-label {
  transform: translateY(-1.5rem);
  font-size: 0.75rem;
  color: #3b82f6;
}

.floating-label {
  position: absolute;
  left: 0;
  top: 0.75rem;
  color: #94a3b8;
  pointer-events: none;
  transition: all 0.3s;
  font-size: 1rem;
}

/* Login Button */
.login-btn {
  width: 100%;
  padding: 0.75rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  margin-bottom: 1.5rem;
}

.login-btn:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.login-btn:active {
  transform: translateY(0);
}

.login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Back Link */
.back-link {
  text-align: center;
}

.back-link a {
  color: #64748b;
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.back-link a:hover {
  color: #3b82f6;
}

/* Shake Animation */
.shake {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-5px);
  }
  75% {
    transform: translateX(5px);
  }
}

/* Responsive */
@media (max-width: 640px) {
  .login-card {
    padding: 1.5rem;
  }
}
</style>