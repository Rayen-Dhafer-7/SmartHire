<template>
  <div class="test-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Preparing your assessment...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.5">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <h3>Job Post Not Found</h3>
      <p>The job post you're looking for doesn't exist or has been removed.</p>
      <router-link to="/worker/jobs" class="btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Jobs
      </router-link>
    </div>

    <!-- Intro Screen -->
    <div v-else-if="!testStarted" class="intro-screen">
      <div class="intro-card">
        <div class="intro-header">
          <!-- Updated Company Logo with hover effect like CompanyInfoForm -->
          <div class="company-logo-container">
            <div class="company-logo-preview">
              <img v-if="jobData?.logoUrl" :src="jobData.logoUrl" :alt="jobData.company" class="logo-image" style="width: 100%; height: 100%; object-fit: contain;"/>
              <div v-else class="logo-placeholder">
                <span>{{ jobData?.company?.charAt(0) || 'J' }}</span>
              </div>
 
            </div>
          </div>
          <div class="company-info">
            <h1 class="job-title">{{ jobData?.title || 'Loading...' }}</h1>
            <p class="company-name">{{ jobData?.company || '' }} • {{ jobData?.location || '' }}</p>
          </div>
        </div>

        <div class="intro-body">
          <div class="section">
            <h3 class="section-title">Job Description</h3>
            <p class="description">{{ truncateText(jobData?.description || '', 300) }}</p>
          </div>

          <div class="section">
            <h3 class="section-title">Required Skills Assessment</h3>
            <div class="skills-list">
              <span v-for="skill in jobData?.skills || []" :key="skill" class="skill-badge">
                {{ skill }}
              </span>
            </div>
            <div class="info-box">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <div>
                <strong>Assessment Structure per Skill:</strong>
                <ul>
                  <li>4 Multiple Choice Questions</li>
                  <li>4 Debugging / Reasoning Questions</li>
                  <li>4 Scenario-based Questions</li>
                  <li><span class="highlight">Time: 10 minutes per skill</span></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="stats-grid">
            <div class="stat-card">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
              <div class="stat-info">
                <span class="stat-label">Total Time</span>
                <span class="stat-value">{{ formatTime(totalTime) }}</span>
                <span class="stat-hint">{{ jobData?.skills?.length || 0 }} skill{{ jobData?.skills?.length > 1 ? 's' : '' }} • 10min each</span>
              </div>
            </div>
            <div class="stat-card">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
              </svg>
              <div class="stat-info">
                <span class="stat-label">Total Questions</span>
                <span class="stat-value">{{ totalQuestions }}</span>
                <span class="stat-hint">12 questions per skill</span>
              </div>
            </div>
          </div>

          <button class="start-btn" @click="startTest" :disabled="generatingQuestions || !jobData?.id">
            <span v-if="generatingQuestions" class="spinner-sm"></span>
            <span v-else>Start Assessment</span>
            <svg v-if="!generatingQuestions" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <line x1="5" y1="12" x2="19" y2="12"/>
              <polyline points="12 5 19 12 12 19"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Test Interface -->
    <div v-else>
      <!-- Fixed Header -->
      <div class="fixed-header">
        <div class="header-content">
          <div class="header-left">
            <div class="header-info">
              <h4 class="header-title">{{ jobData?.title || '' }}</h4>
              <p class="header-subtitle">{{ jobData?.company || '' }} • {{ jobData?.skills?.length || 0 }} skills</p>
            </div>
          </div>
          <div class="header-right">
            <TestTimer :timeRemaining="timeRemaining" />
            <button class="finish-btn" @click="finishTest">Finish Test</button>
          </div>
        </div>
      </div>
      
      <!-- Fixed Progress Bar -->
      <div class="fixed-progress">
        <div class="progress-bar-fill" :style="{ width: progressPercentage + '%' }"></div>
      </div>
      
      <!-- Main Content with Top Padding -->
      <div class="test-content-with-padding">
        <div class="test-content">
          <form @submit.prevent="finishTest">
            <SkillAssessment 
              v-for="(section, sIndex) in questions" 
              :key="sIndex"
              :section="section"
              :answers="answers[section?.skill]"
              @update-answer="handleUpdateAnswer(section?.skill, $event)"
            />
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router';
import { showWarning, showConfirm, showSuccess, showLoading, closeLoading, showError } from '../../../utils/notifications';
import TestTimer from './TestTimer.vue';
import SkillAssessment from './SkillAssessment.vue';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const error = ref(false);
const testStarted = ref(false);
const timeRemaining = ref(0);
const generatingQuestions = ref(false);
const isSubmitting = ref(false);
let timerInterval = null;

const TIME_PER_SKILL = 10;

const jobData = ref({
  id: '',
  company: '',
  title: '',
  location: '',
  description: '',
  skills: [],
  deadline: '',
  workersNeeded: 0,
  type: '',
  social: {},
  logoUrl: null
});

const questions = ref([]);
const answers = ref({});

const totalTime = computed(() => {
  return (jobData.value?.skills?.length || 0) * TIME_PER_SKILL * 60;
});

const totalQuestions = computed(() => {
  return (jobData.value?.skills?.length || 0) * 12;
});

const progressPercentage = computed(() => {
  const elapsed = totalTime.value - timeRemaining.value;
  return totalTime.value > 0 ? (elapsed / totalTime.value) * 100 : 0;
});

const handleUpdateAnswer = (skill, { type, qIndex, value }) => {
  if (!skill) return;
  if (!answers.value[skill]) {
    answers.value[skill] = { mcq: {}, debug: {}, scenario: {} };
  }
  answers.value[skill][type][qIndex] = value;
};

const hideParentDashboard = () => {
  document.body.classList.add('test-active');
  const topNav = document.querySelector('.top-navbar');
  if (topNav) {
    topNav.dataset.originalDisplay = topNav.style.display || '';
    topNav.style.display = 'none';
  }
  const animatedBg = document.querySelector('.dashboard-container > .animated-bg');
  if (animatedBg) {
    animatedBg.dataset.originalDisplay = animatedBg.style.display || '';
    animatedBg.style.display = 'none';
  }
};

const showParentDashboard = () => {
  document.body.classList.remove('test-active');
  const topNav = document.querySelector('.top-navbar');
  if (topNav) {
    topNav.style.display = topNav.dataset.originalDisplay || '';
    delete topNav.dataset.originalDisplay;
  }
  const animatedBg = document.querySelector('.dashboard-container > .animated-bg');
  if (animatedBg) {
    animatedBg.style.display = animatedBg.dataset.originalDisplay || '';
    delete animatedBg.dataset.originalDisplay;
  }
};

const fetchTestQuestions = async () => {
  if (!jobData.value?.id) {
    showError('Error', 'No job data found');
    return false;
  }
  
  generatingQuestions.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) throw new Error('No authentication token found');
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/worker/geretest`, { 
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        post_id: jobData.value.id,
        title: jobData.value.title,
        description: jobData.value.description,
        skills: jobData.value.skills
      })
    });

    const data = await response.json();
    
    if (data.status === 'success') {
      questions.value = data.questions || [];
      
      if (jobData.value.skills) {
        jobData.value.skills.forEach(skill => {
          answers.value[skill] = { mcq: {}, debug: {}, scenario: {} };
        });
      }
      return true;
    } else {
      throw new Error(data.message || 'Failed to generate test questions');
    }
  } catch (error) {
    console.error('Error fetching test questions:', error);
    showError('Error', error.message || 'Failed to generate test. Please try again.');
    return false;
  } finally {
    generatingQuestions.value = false;
  }
};

const saveTestResults = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) throw new Error('No authentication token found');
    
    const timeElapsed = totalTime.value - timeRemaining.value;
    const minutesElapsed = Math.floor(timeElapsed / 60);
    const secondsElapsed = timeElapsed % 60;
    const totalMinutes = Math.floor(totalTime.value / 60);
    
    const timeFormatted = `${minutesElapsed.toString().padStart(2, '0')}:${secondsElapsed.toString().padStart(2, '0')}/${totalMinutes.toString().padStart(2, '0')}:00`;
    
    const requestData = {
      post_id: jobData.value.id,
      time_formatted: timeFormatted,
      answers: answers.value,
      questions: questions.value
    };
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/worker/savetest`, {  
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(requestData)
    });
    
    const data = await response.json();
    
    if (data.status === 'success') {
      return data;
    } else {
      throw new Error(data.message || 'Failed to save test results');
    }
  } catch (error) {
    console.error('Error saving test results:', error);
    throw error;
  } finally {
    isSubmitting.value = false;
  }
};

const startTest = async () => {
  if (!jobData.value?.id) {
    showError('Error', 'No job data found');
    return;
  }
  
  const success = await fetchTestQuestions();
  
  if (success) {
    hideParentDashboard();
    testStarted.value = true;
    timeRemaining.value = totalTime.value;
    
    timerInterval = setInterval(() => {
      timeRemaining.value--;
      if (timeRemaining.value <= 0) {
        clearInterval(timerInterval);
        handleTimeUp();
      }
    }, 1000);
  }
};

const handleTimeUp = () => {
  showWarning("Time's Up!", "Your test time has ended. Submitting your answers now.").then(() => {
    submitTest();
  });
};

const finishTest = () => {
  if (isSubmitting.value) return;
  
  showConfirm('Finish Test?', "Are you sure you want to finish the test? You cannot change your answers after submission.", 'Yes, Submit', 'Cancel').then((result) => {
    if (result.isConfirmed) {
      clearInterval(timerInterval);
      submitTest();
    }
  });
};

const submitTest = async () => {
  if (isSubmitting.value) return;
  
  showLoading('Submitting...', 'Calculating your score with AI...');
  
  try {
    const result = await saveTestResults();
    closeLoading();
    testStarted.value = false;
    
    if (result.skill_results) {
      const avgScore = Math.round(result.skill_results.reduce((acc, curr) => acc + curr.percentage, 0) / result.skill_results.length);
      showSuccess('Test Submitted!', `Your score: ${avgScore}%. Check "My Applications" for details.`);
    } else if (result.overall_score) {
      showSuccess('Test Submitted!', `Your score: ${Math.round(result.overall_score)}%. Check "My Applications" for details.`);
    } else {
      showSuccess('Test Submitted!', 'Your assessment has been recorded. Check "My Applications" for your score.');
    }
    
    showParentDashboard();
    router.push('/worker/applications');
  } catch (error) {
    closeLoading();
    showError('Error', error.message || 'Failed to submit test. Please try again.');
  }
};

onMounted(() => {
  if (route.query.post) {
    try {
      const postData = JSON.parse(route.query.post);
      jobData.value = {
        id: postData.id || '',
        company: postData.company || '',
        title: postData.title || '',
        location: postData.location || '',
        description: postData.description || '',
        skills: postData.skills || [],
        deadline: postData.deadline || '',
        workersNeeded: postData.workersNeeded || 0,
        type: postData.type || '',
        social: postData.social || {},
        logoUrl: postData.logoUrl || null
      };
      
      if (!jobData.value.skills || jobData.value.skills.length === 0) {
        error.value = true;
      }
    } catch (e) {
      console.error('Error parsing job data:', e);
      error.value = true;
    }
  } else {
    error.value = true;
  }
  
  loading.value = false;
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  window.removeEventListener('beforeunload', handleBeforeUnload);
  showParentDashboard();
});

onBeforeRouteLeave((to, from, next) => {
  if (!testStarted.value || isSubmitting.value) {
    next();
    return;
  }

  showConfirm('Leave Test?', 'Leaving this page will terminate your test and submit your answers.', 'Leave & Submit', 'Stay').then((result) => {
    if (result.isConfirmed) {
      clearInterval(timerInterval);
      submitTest();
      next(false);
    } else {
      next(false);
    }
  });
});

const handleBeforeUnload = (event) => {
  if (!testStarted.value || isSubmitting.value) return;
  event.preventDefault();
  event.returnValue = '';
  submitTest();
};

const formatTime = (seconds) => {
  const min = Math.floor(Math.max(seconds, 0) / 60);
  const sec = Math.max(seconds, 0) % 60;
  return `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
};

const truncateText = (text, length) => {
  if (!text) return '';
  if (text.length <= length) return text;
  return text.substring(0, length) + '...';
};
</script>

<style scoped>
/* Add the company logo styles */
.company-logo-container {
  flex-shrink: 0;
}

.company-logo-preview {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  overflow: hidden;
  cursor: pointer;
  position: relative;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
  background: linear-gradient(135deg, #f8fafc, #ffffff);
}

 

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.logo-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  font-size: 2rem;
  font-weight: 700;
}

.logo-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  opacity: 0;
  transition: opacity 0.3s ease;
  color: white;
  font-size: 0.7rem;
  font-weight: 500;
}

.company-logo-preview:hover .logo-overlay {
  opacity: 1;
}

/* Keep existing styles */
.test-wrapper {
   top:-50px; 
  min-height: 100vh;
  position: relative;
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

.loading-state, .error-state {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2rem;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 3px solid #e2e8f0;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state svg {
  margin-bottom: 1rem;
}

.error-state h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.error-state p {
  color: #64748b;
  margin-bottom: 1.5rem;
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
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.intro-screen {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.intro-card {
  max-width: 800px;
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

.intro-header {
  display: flex;
  gap: 1.5rem;
  padding: 2rem;
  background: linear-gradient(135deg, #f8fafc, #ffffff);
  border-bottom: 1px solid #e2e8f0;
}

.company-info {
  flex: 1;
}

.job-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem;
}

.company-name {
  color: #64748b;
  margin: 0;
}

.intro-body {
  padding: 2rem;
}

.section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-title::before {
  content: '';
  width: 4px;
  height: 20px;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border-radius: 2px;
}

.description {
  color: #475569;
  line-height: 1.6;
}

.skills-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.skill-badge {
  background: #eef2ff;
  color: #4f46e5;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
}

.info-box {
  background: #f8fafc;
  border-radius: 16px;
  padding: 1rem;
  display: flex;
  gap: 1rem;
  border: 1px solid #e2e8f0;
}

.info-box svg {
  flex-shrink: 0;
  color: #4f46e5;
}

.info-box ul {
  margin: 0.5rem 0 0;
  padding-left: 1.25rem;
}

.info-box li {
  font-size: 0.875rem;
  color: #475569;
  margin-bottom: 0.25rem;
}

.highlight {
  color: #4f46e5;
  font-weight: 600;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #f8fafc;
  border-radius: 16px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border: 1px solid #e2e8f0;
}

.stat-card svg {
  width: 32px;
  height: 32px;
  color: #4f46e5;
}

.stat-info {
  flex: 1;
}

.stat-label {
  display: block;
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
}

.stat-hint {
  font-size: 0.7rem;
  color: #94a3b8;
}

.start-btn {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.start-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.start-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner-sm {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

.fixed-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10001;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  padding: 1rem 2rem;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.fixed-progress {
  position: fixed;
  top: 145px;
  left: 0;
  right: 0;
  z-index: 9999;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #4f46e5, #6366f1);
  transition: width 0.3s ease;
}

.test-content-with-padding {
  padding-top: 190px;
}

.test-content {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 2rem 2rem;
}

.header-left {
  flex: 1;
}

.header-info {
  text-align: left;
}

.header-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.header-subtitle {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.finish-btn {
  padding: 0.5rem 1.25rem;
  background: linear-gradient(135deg, #10b981, #34d399);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.finish-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

@media (max-width: 768px) {
  .intro-header {
    flex-direction: column;
    text-align: center;
  }
  
  .company-logo-container {
    align-self: center;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .fixed-header {
    padding: 0.75rem 1rem;
  }
  
  .fixed-progress {
    top: 67px;
  }
  
  .test-content-with-padding {
    padding-top: 100px;
  }
  
  .test-content {
    padding: 0 1rem 1rem;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .header-right {
    width: 100%;
    justify-content: space-between;
  }
  
  .finish-btn {
    flex: 1;
    text-align: center;
  }
}
</style>