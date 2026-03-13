<template>
  <div class="test-page-container">
    
    <!-- Loading State -->
    <div v-if="loading" class="d-flex flex-column align-items-center justify-content-center" style="min-height: 80vh;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Preparing your assessment...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow-lg border-0">
            <div class="card-body text-center py-5">
              <i class="bi bi-exclamation-triangle text-warning fs-1 mb-3"></i>
              <h3 class="mb-3">Job Post Not Found</h3>
              <p class="text-muted mb-4">The job post you're looking for doesn't exist or has been removed.</p>
              <router-link to="/worker/jobs" class="btn btn-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Jobs
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Intro Screen -->
    <div v-else-if="!testStarted" class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow-lg border-0 overflow-hidden">
            <div class="card-header bg-primary text-white p-4">
              <div class="d-flex align-items-center gap-3">
                <!-- Company Logo with safe navigation -->
                <div v-if="jobData?.logoUrl" class="company-logo-lg bg-white d-flex align-items-center justify-content-center rounded overflow-hidden border">
                  <img :src="jobData.logoUrl" :alt="jobData.company || 'Company'" class="w-100 h-100 object-fit-cover" />
                </div>
                <div v-else class="company-logo-lg bg-white text-primary d-flex align-items-center justify-content-center rounded fw-bold border">
                  {{ jobData?.company?.charAt(0) || 'J' }}
                </div>
                <div>
                  <h2 class="mb-1">{{ jobData?.title || 'Loading...' }}</h2>
                  <div class="opacity-75">{{ jobData?.company || '' }} • {{ jobData?.location || '' }}</div>
                </div>
              </div>
            </div>
            
            <div class="card-body p-4 p-md-5">
              <div class="mb-4">
                <h5 class="fw-bold text-dark mb-3">Job Description</h5>
                <p class="text-muted">{{ truncateText(jobData?.description || '', 300) }}</p>
              </div>

              <div class="mb-4">
                <h5 class="fw-bold text-dark mb-3">Required Skills Assessment</h5>
                <div class="d-flex flex-wrap gap-2 mb-3">
                  <span v-for="skill in jobData?.skills || []" :key="skill" class="badge bg-light text-primary border border-primary px-3 py-2">
                    {{ skill }}
                  </span>
                </div>
                <div class="alert alert-info d-flex align-items-start gap-3">
                   <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
                   <div>
                     <strong>Assessment Structure per Skill:</strong>
                     <ul class="mb-0 ps-3 mt-1 small">
                       <li>4 Multiple Choice Questions</li>
                       <li>4 Debugging / Reasoning Questions</li>
                       <li>4 Scenario-based Questions</li>
                       <li><span class="fw-bold text-primary">Time: 10 minutes per skill</span></li>
                     </ul>
                   </div>
                </div>
              </div>

              <div class="d-flex align-items-center justify-content-between bg-light p-4 rounded-3 mb-4">
                 <div>
                   <small class="text-muted d-block text-uppercase fw-bold mb-1">Total Time</small>
                   <div class="fs-4 fw-bold text-dark">
                     <i class="bi bi-stopwatch"></i> {{ formatTime(totalTime) }}
                   </div>
                   <small class="text-muted">{{ jobData?.skills?.length || 0 }} skill{{ jobData?.skills?.length > 1 ? 's' : '' }} • 10min each</small>
                 </div>
                 <div>
                    <small class="text-muted d-block text-uppercase fw-bold mb-1">Total Questions</small>
                    <div class="fs-4 fw-bold text-dark">
                      <i class="bi bi-list-check"></i> {{ totalQuestions }}
                    </div>
                    <small class="text-muted">12 questions per skill</small>
                 </div>
              </div>

              <div class="d-grid">
                <button class="btn btn-primary btn-lg" @click="startTest" :disabled="generatingQuestions || !jobData?.id">
                  <span v-if="generatingQuestions" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  <span v-if="generatingQuestions">Generating Test...</span>
                  <span v-else>Start Assessment <i class="bi bi-arrow-right ms-2"></i></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Test Interface -->
    <div v-else class="test-interface">
      <!-- Sticky Header -->
      <header class="test-header bg-white shadow-sm sticky-top py-3 px-4">
        <div class="container d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0 text-truncate" style="max-width: 300px;">{{ jobData?.title || '' }}</h5>
            <small class="text-muted">{{ jobData?.company || '' }} • {{ jobData?.skills?.length || 0 }} skills</small>
          </div>
          
          <div class="d-flex align-items-center gap-4">
             <TestTimer :timeRemaining="timeRemaining" />
             <button class="btn btn-success px-4" @click="finishTest">
               Finish Test
             </button>
          </div>
        </div>
        <div class="progress mt-3" style="height: 4px;">
           <div class="progress-bar bg-primary transition-all" :style="{ width: progressPercentage + '%' }"></div>
        </div>
      </header>
      
      <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form @submit.prevent="finishTest">
                    <!-- Iterate over Skills using SkillAssessment Component -->
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

// Time per skill in minutes
const TIME_PER_SKILL = 10;

// Job Data - Initialize with default values
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

// Questions Structure
const questions = ref([]);
const answers = ref({});

// Computed
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

// Fetch test questions from API
const fetchTestQuestions = async () => {
  if (!jobData.value?.id) {
    showError('Error', 'No job data found');
    return false;
  }
  
  generatingQuestions.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      throw new Error('No authentication token found');
    }
    
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
      
      // Initialize answers object for each skill
      if (jobData.value.skills) {
        jobData.value.skills.forEach(skill => {
          answers.value[skill] = {
            mcq: {},
            debug: {},
            scenario: {}
          };
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

// Save test results to API
const saveTestResults = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      throw new Error('No authentication token found');
    }
    
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

onMounted(() => {
  // Parse job data from query parameters
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
});

const startTest = async () => {
  if (!jobData.value?.id) {
    showError('Error', 'No job data found');
    return;
  }
  
  const success = await fetchTestQuestions();
  
  if (success) {
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
      
      router.push('/worker/applications');
      
    } catch (error) {
      closeLoading();
      showError('Error', error.message || 'Failed to submit test. Please try again.');
    }
};

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

// Utilities
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
.test-page-container {
    background-color: #f8f9fa;
    min-height: 100vh;
}
.company-logo-lg {
  width: 64px;
  height: 64px;
  min-width: 64px;
  font-size: 2rem;
}
.object-fit-cover {
  object-fit: cover;
}
.test-header {
    z-index: 1000;
}
</style>