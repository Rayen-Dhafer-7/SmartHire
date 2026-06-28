<template>
  <div class="landing-wrap">
    <!-- Animated background elements -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <nav class="navbar">
      <div class="navbar-brand">
        <svg viewBox="0 0 100 100" width="40" height="40" xmlns="http://www.w3.org/2000/svg">
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
        <span>SmartHire</span>
      </div>
      <div class="navbar-actions">
        <router-link to="/login" class="btn-link">Sign in</router-link>
        <router-link to="/signup" class="btn-signup">Create account</router-link>
      </div>
    </nav>

    <section class="hero-area">
      <div class="hero-left">
        <h1 class="animate-fade-up">Hire confidently and grow faster</h1>
        <p class="animate-fade-up delay-1">From sourcing talent to onboarding and tracking applications, SmartHire gives companies and workers one workflow.
          <br />
          Build your next career move with enterprise-level insights.</p>

        <div class="hero-ctas animate-fade-up delay-2">
          <router-link to="/signup" class="btn btn-primary">Get started</router-link>
         </div>

        <div class="dash-cards animate-fade-up delay-3">
          <div class="dash-card">
            <span class="counter" ref="usersCounter">0</span>
            <small>Total users</small>
          </div>
          <div class="dash-card">
            <span class="counter" ref="companiesCounter">0</span>
            <small>Companies</small>
          </div>
          <div class="dash-card">
            <span class="counter" ref="workersCounter">0</span>
            <small>Workers</small>
          </div>
        </div>
        <div class="stats-feedback">
          <span v-if="isLoadingStats">Loading statistics...</span>
          <span v-else-if="statsError" class="text-error">{{ statsError }}</span>
        </div>
      </div>

      <div class="hero-right animate-fade-up delay-2">
        <div class="mock-dashboard">
          <div class="md-header">
            <span>Overview</span>
            <span>Today</span>
          </div>
          <div class="md-body">
            <div class="md-row"><strong class="counter" ref="activeJobsCounter">0</strong><span>Jobs in progress</span></div>
            <div class="md-row"><strong class="counter" ref="newCandidatesCounter">0</strong><span>Candidates</span></div>
            <div class="md-row"><strong class="counter" ref="jobsExpiredCounterOverview">0</strong><span>Jobs Expired</span></div>
            <div class="md-progress">
              <div class="label">Application Rate: <span ref="applicationRateDisplay">0</span></div>
              <div class="track"><div class="fill" :style="{ width: applicationRateValue + '%' }" ref="progressFill"></div></div>
              <div class="rate-description">Applications per job (%)</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="features-area">
      <h2 class="animate-on-scroll">Why choose SmartHire</h2>
      <div class="features-grid">
        <article class="animate-on-scroll delay-1">
          <h3>Centralized hiring hub</h3>
          <p>Keep roles, candidates, and reports organized in one single dashboard.</p>
        </article>
        <article class="animate-on-scroll delay-2">
          <h3>Smart analytics</h3>
          <p>Actionable insights per job post, tracking progress and candidate activity in real-time.</p>
        </article>
        <article class="animate-on-scroll delay-3">
          <h3>Automated workflows</h3>
          <p>From posting to offer, accelerate hiring with intelligent notifications and seamless processes.</p>
        </article>
        <article class="animate-on-scroll delay-4">
          <h3>Enterprise-ready</h3>
          <p>Built for startups and scales with enterprise organizations, handling growth effortlessly.</p>
        </article>
      </div>
    </section>

    <section class="showcase-area">
      <div class="showcase-content">
        <div class="animate-on-scroll">
          <h2>AI-Powered Job Matching</h2>
          <p>Our intelligent algorithm analyzes your CV and profile to recommend the most relevant job opportunities based on your unique skills and experience.</p>
          <ul>
            <li>🤖 AI-powered CV analysis</li>
            <li>📊 Personalized job recommendations</li>
            <li>🎯 Match score for each opportunity</li>
          </ul>
        </div>
        <img src="/page1.png" alt="AI Job Matching" class="animate-on-scroll delay-2" />
      </div>
    </section>

    <section class="showcase-area">
      <div class="showcase-content">
        <img src="/page2.png" alt="Skill Assessment" class="animate-on-scroll delay-2" />

        <div class="animate-on-scroll">
          <h2>Skill Tests & Talent Ranking</h2>
          <p>Validate your skills through specialized tests. Companies filter and rank candidates based on test performance, putting top talent first.</p>
          <ul>
            <li>📝 Skill validation tests</li>
            <li>🏆 Competency-based ranking</li>
            <li>⭐ Stand out to recruiters</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="showcase-area">
      <div class="showcase-content">
        <div class="animate-on-scroll">
          <h2>✨ Smart Profile & CV Builder</h2>
          <p>Create and manage your professional profile seamlessly. Upload your existing CV or let our AI build one automatically from your profile information.</p>
          <ul>
            <li>📎 Upload existing CV or auto-generate new one</li>
            <li>🤖 AI-powered CV generation from profile data</li>
            <li>📝 Profile information automatically updates your CV</li>
          </ul>
        </div>
        <img src="/page3.png" alt="Profile Management" class="animate-on-scroll delay-2" />
      </div>
    </section>

    <section class="deep-stats-area">
      <h2 class="animate-on-scroll">Deep insights</h2>
      <p class="animate-on-scroll delay-1">Real-time analytics and key metrics to track your hiring performance and job market trends.</p>
      <div class="deep-stats-grid">
        <div class="deep-card animate-on-scroll delay-2">
          <h4>📊 Total Jobs</h4>
          <p class="counter" ref="totalJobsCounter">0</p>
          <small>Active + Expired</small>
        </div>
        <div class="deep-card animate-on-scroll delay-3">
          <h4>✅ Jobs In Progress</h4>
          <p class="counter" ref="jobsInProgressCounter">0</p>
          <small>Deadline today or future</small>
        </div>
        <div class="deep-card animate-on-scroll delay-4">
          <h4>⏰ Jobs Expired</h4>
          <p class="counter" ref="jobsExpiredCounter">0</p>
          <small>Past deadline</small>
        </div>
        <div class="deep-card animate-on-scroll delay-5">
          <h4>📝 Applications</h4>
          <p class="counter" ref="applicationsCounterDeep">0</p>
          <small>Total test attempts</small>
        </div>
        <div class="deep-card animate-on-scroll delay-2">
          <h4>📈 Application Rate</h4>
          <p class="counter" ref="applicationRateCounter">0</p>
          <small>Applications per job (%)</small>
        </div>
        <div class="deep-card animate-on-scroll delay-3">
          <h4>🔥 Active Jobs Ratio</h4>
          <p class="counter" ref="activeRatioCounter">0</p>
          <small>Active vs Total (%)</small>
        </div>
        <div class="deep-card animate-on-scroll delay-4">
          <h4>👥 Total Users</h4>
          <p class="counter" ref="totalUsersCounterDeep">0</p>
          <small>Workers + Companies</small>
        </div>
        <div class="deep-card animate-on-scroll delay-3">
          <h4>📊 Jobs per Company</h4>
          <p class="counter" ref="jobsPerCompanyCounter">0</p>
          <small>Average jobs/company</small>
        </div>

      </div>
    </section>

    <section class="workflow-area">
      <h2 class="animate-on-scroll">How it works</h2>
      <div class="workflow-grid">
        <div class="animate-on-scroll delay-1">
          <h3>1. Create your company or worker profile</h3>
          <p>Sign up with a single account and start your dedicated dashboard.</p>
        </div>
        <div class="animate-on-scroll delay-2">
          <h3>2. Publish jobs & browse talent</h3>
          <p>Post jobs or search for candidates with intelligent filters and instant matching.</p>
        </div>
        <div class="animate-on-scroll delay-3">
          <h3>3. Track and close faster</h3>
          <p>Use built-in analytics to reduce hiring time and make better decisions.</p>
        </div>
      </div>
    </section>

    <footer class="landing-footer">
      <p>Need help? <a href="#">Contact support</a> | <router-link to="/login">Sign in</router-link></p>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

 



const stats = ref({
  users: 0,
  companies: 0,
  workers: 0,
  jobs: 0,
  jobs_in_progress: 0,
  jobs_expired: 0,
  applications: 0,
  application_rate: 0,
  active_ratio: 0
});

// Refs for counter elements
const usersCounter = ref(null);
const companiesCounter = ref(null);
const workersCounter = ref(null);
const activeJobsCounter = ref(null);
const newCandidatesCounter = ref(null);
const jobsExpiredCounterOverview = ref(null);
const applicationRateDisplay = ref(null);
const progressFill = ref(null);
const applicationRateValue = ref(0);

// Deep stats counters
const totalJobsCounter = ref(null);
const jobsInProgressCounter = ref(null);
const jobsExpiredCounter = ref(null);
const applicationsCounterDeep = ref(null);
const applicationRateCounter = ref(null);
const activeRatioCounter = ref(null);
const totalUsersCounterDeep = ref(null);
const companiesCounterDeep = ref(null);
const workersCounterDeep = ref(null);
const jobsPerCompanyCounter = ref(null);
const appsPerJobCounter = ref(null);
const successRateCounter = ref(null);

const isLoadingStats = ref(true);
const statsError = ref(null);

// Animation counter function
const animateNumber = (element, start, end, duration = 1500, suffix = '') => {
  if (!element) return;
  const startTime = performance.now();
  const updateNumber = (currentTime) => {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
    const current = Math.floor(start + (end - start) * easeOutQuart);
    element.textContent = current + suffix;
    if (progress < 1) {
      requestAnimationFrame(updateNumber);
    } else {
      element.textContent = end + suffix;
    }
  };
  requestAnimationFrame(updateNumber);
};

// Animate progress fill
const animateProgressFill = (targetPercentage, duration = 1500) => {
  if (!progressFill.value) return;
  const startTime = performance.now();
  const startWidth = parseFloat(progressFill.value.style.width) || 0;
  const animate = (currentTime) => {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
    const currentWidth = startWidth + (targetPercentage - startWidth) * easeOutQuart;
    progressFill.value.style.width = currentWidth + '%';
    if (progress < 1) {
      requestAnimationFrame(animate);
    }
  };
  requestAnimationFrame(animate);
};

// Start all animations with stats data
const startAnimations = () => {
  if (stats.value.users) {
    // Main counters
    animateNumber(usersCounter.value, 0, stats.value.users, 1500);
    animateNumber(companiesCounter.value, 0, stats.value.companies, 1500);
    animateNumber(workersCounter.value, 0, stats.value.workers, 1500);
    animateNumber(activeJobsCounter.value, 0, stats.value.jobs_in_progress || 0, 1500);
    animateNumber(newCandidatesCounter.value, 0, stats.value.applications || 0, 1500);
    animateNumber(jobsExpiredCounterOverview.value, 0, stats.value.jobs_expired || 0, 1500);
    
    // Application Rate for Overview
    const appRate = stats.value.application_rate || 0;
    applicationRateValue.value = appRate;
    animateNumber(applicationRateDisplay.value, 0, appRate, 1500, '%');
    animateProgressFill(appRate, 1500);
    
    // Deep stats animations
    animateNumber(totalJobsCounter.value, 0, stats.value.jobs || 0, 1500);
    animateNumber(jobsInProgressCounter.value, 0, stats.value.jobs_in_progress || 0, 1500);
    animateNumber(jobsExpiredCounter.value, 0, stats.value.jobs_expired || 0, 1500);
    animateNumber(applicationsCounterDeep.value, 0, stats.value.applications || 0, 1500);
    animateNumber(applicationRateCounter.value, 0, stats.value.application_rate || 0, 1500, '%');
    animateNumber(activeRatioCounter.value, 0, stats.value.active_ratio || 0, 1500, '%');
    animateNumber(totalUsersCounterDeep.value, 0, stats.value.users || 0, 1500);
    animateNumber(companiesCounterDeep.value, 0, stats.value.companies || 0, 1500);
    animateNumber(workersCounterDeep.value, 0, stats.value.workers || 0, 1500);
    
    // Calculate derived stats
    const jobsPerCompany = stats.value.companies > 0 ? (stats.value.jobs / stats.value.companies).toFixed(1) : 0;
    const appsPerJob = stats.value.jobs > 0 ? (stats.value.applications / stats.value.jobs).toFixed(1) : 0;
    const successRate = stats.value.jobs_in_progress > 0 ? (stats.value.applications / stats.value.jobs_in_progress).toFixed(1) : 0;
    
    animateNumber(jobsPerCompanyCounter.value, 0, jobsPerCompany, 1500);
    animateNumber(appsPerJobCounter.value, 0, appsPerJob, 1500);
    animateNumber(successRateCounter.value, 0, successRate, 1500);
    
  } else {
    // Fallback animations
    animateNumber(usersCounter.value, 0, 12580, 1500);
    animateNumber(companiesCounter.value, 0, 342, 1500);
    animateNumber(workersCounter.value, 0, 12238, 1500);
    animateNumber(activeJobsCounter.value, 0, 59, 1500);
    animateNumber(newCandidatesCounter.value, 0, 245, 1500);
    animateNumber(jobsExpiredCounterOverview.value, 0, 30, 1500);
    
    // Fallback application rate
    const fallbackRate = 33.3;
    applicationRateValue.value = fallbackRate;
    animateNumber(applicationRateDisplay.value, 0, fallbackRate, 1500, '%');
    animateProgressFill(fallbackRate, 1500);
    
    // Deep stats fallback
    animateNumber(totalJobsCounter.value, 0, 89, 1500);
    animateNumber(jobsInProgressCounter.value, 0, 59, 1500);
    animateNumber(jobsExpiredCounter.value, 0, 30, 1500);
    animateNumber(applicationsCounterDeep.value, 0, 245, 1500);
    animateNumber(applicationRateCounter.value, 0, 275, 1500, '%');
    animateNumber(activeRatioCounter.value, 0, 66, 1500, '%');
    animateNumber(totalUsersCounterDeep.value, 0, 12580, 1500);
    animateNumber(companiesCounterDeep.value, 0, 342, 1500);
    animateNumber(workersCounterDeep.value, 0, 12238, 1500);
    animateNumber(jobsPerCompanyCounter.value, 0, 3.8, 1500);
    animateNumber(appsPerJobCounter.value, 0, 2.8, 1500);
    animateNumber(successRateCounter.value, 0, 4.2, 1500);
  }
};

const fetchStats = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/platform/stats`);
    if (response.data && response.data.success) {
      stats.value = {
        users: response.data.data.users || 0,
        companies: response.data.data.companies || 0,
        workers: response.data.data.workers || 0,
        jobs: response.data.data.jobs || 0,
        jobs_in_progress: response.data.data.jobs_in_progress || 0,
        jobs_expired: response.data.data.jobs_expired || 0,
        applications: response.data.data.applications || 0,
        application_rate: response.data.data.application_rate || 0,
        active_ratio: response.data.data.active_ratio || 0
      };
      startAnimations();
    } else {
      statsError.value = 'Failed to load stats';
      console.warn('Stats API response error', response.data);
      startAnimations();
    }
  } catch (error) {
    statsError.value = error.response?.data?.error || error.message || 'Could not fetch stats';
    console.error('Stats API request failed:', statsError.value);
    startAnimations();
  } finally {
    isLoadingStats.value = false;
  }
};

// Intersection Observer for scroll animations
onMounted(() => {
  fetchStats();
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1 });
  
  document.querySelectorAll('.animate-on-scroll').forEach(el => {
    observer.observe(el);
  });
});
</script>


<style scoped>
.landing-wrap {
  min-height: 100vh;
  font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
  color: #0f172a;
  background: #f7f8fb;
  position: relative;
  overflow-x: hidden;
  font-size: 100%; /* Base font size */
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
  opacity: 0.5;
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
  animation: gridMove 20s linear infinite;
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

@keyframes gridMove {
  0% {
    transform: translate(0, 0);
  }
  100% {
    transform: translate(50px, 50px);
  }
}

/* Animation classes */
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

.animate-fade-up {
  animation: fadeUp 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
  opacity: 0;
}

.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.3s; }
.delay-3 { animation-delay: 0.5s; }
.delay-4 { animation-delay: 0.7s; }
.delay-5 { animation-delay: 0.9s; }

.animate-on-scroll {
  opacity: 0;
  transform: translateY(40px);
  transition: all 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1);
}

.animate-on-scroll.visible {
  opacity: 1;
  transform: translateY(0);
}

/* Counter styles */
.counter {
  display: inline-block;
  transition: all 0.3s ease;
}

/* Navbar */
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: rgba(255, 255, 255, 0.86);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #e2e8f0;
}
.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  font-size: 1.2rem;
  font-weight: 700;
  color: #334155;
}
.logo {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: linear-gradient(135deg, #6366f1, #a78bfa);
  box-shadow: 0 3px 10px rgba(99, 102, 241, 0.32);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.9;
  }
}

.navbar-actions {
  display: flex;
  gap: 0.75rem;
}
.btn-link,
.btn-signup {
  padding: 0.55rem 1.2rem;
  border-radius: 999px;
  border: 1px solid transparent;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}
.btn-link {
  color: #475569;
  background: #e2e8f0;
}
.btn-link:hover {
  background: #cbd5e1;
  transform: translateY(-2px);
}
.btn-signup {
  color: #ffffff;
  background: linear-gradient(150deg, #6366f1 0%, #4f46e5 100%);
}
.btn-signup:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

/* Hero area */
.hero-area {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 2rem;
  align-items: start;
  padding: 5rem 2rem 3rem;
  max-width: 1200px;
  margin: 0 auto;
}
.hero-left h1 {
  font-size: clamp(2rem, 4vw, 3.5rem);
  line-height: 1.2;
  margin-bottom: 1rem;
  color: #0f172a;
}
.hero-left p {
  color: #64748b;
  max-width: 640px;
  margin-bottom: 1.5rem;
  font-size: 1.1rem;
  line-height: 1.5;
}
.hero-ctas {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}
.btn {
  font-weight: 600;
  padding: 0.9rem 1.8rem;
  border-radius: 999px;
  border: none;
  transition: all 0.22s ease;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  font-size: 1rem;
}
.btn-primary {
  background: linear-gradient(130deg, #6366f1, #4f46e5);
  color: #fff;
}
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(99, 102, 241, 0.18);
}
.dash-cards {
  display: grid;
  grid-template-columns: repeat(3, minmax(120px, 1fr));
  gap: 1rem;
}
.dash-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  padding: 1rem;
  text-align: center;
  transition: all 0.3s ease;
}
.dash-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(99, 102, 241, 0.12);
}
.dash-card span {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  color: #111827;
}
.dash-card small {
  display: block;
  margin-top: 0.3rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.7rem;
}
.stats-feedback {
  margin-top: 0.8rem;
  font-size: 0.9rem;
  color: #475569;
}
.stats-feedback .text-error {
  color: #b91c1c;
}
.hero-right {
  display: flex;
  justify-content: center;
}
.mock-dashboard {
  width: 100%;
  max-width: 420px;
  border-radius: 16px;
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 60%);
  border: 1px solid rgba(255, 255, 255, 0.16);
  color: #fff;
  box-shadow: 0 24px 48px rgba(15, 23, 42, 0.28);
  overflow: hidden;
  transition: all 0.3s ease;
}
.mock-dashboard:hover {
  transform: translateY(-8px);
  box-shadow: 0 32px 64px rgba(99, 102, 241, 0.2);
}
.md-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.2rem;
  background: rgba(15, 23, 42, 0.88);
  font-size: 1rem;
  font-weight: 600;
}
.md-body {
  padding: 1.2rem;
}
.md-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}
.md-row strong {
  font-size: 1.2rem;
  color: #f8fafc;
}
.md-row span {
  color: #cbd5e1;
  font-size: 0.95rem;
}
.md-progress {
  margin-top: 1.2rem;
}
.label {
  color: #cbd5e1;
  font-size: 0.8rem;
  margin-bottom: 0.5rem;
}
.track {
  width: 100%;
  height: 8px;
  border-radius: 999px;
  background: rgba(148, 163, 184, 0.34);
  overflow: hidden;
}
.fill {
  width: 0%;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #0ea5e9, #6366f1);
  transition: width 0.1s linear;
}
.rate-description {
  font-size: 0.7rem;
  color: #94a3b8;
  text-align: center;
  margin-top: 0.5rem;
  letter-spacing: 0.3px;
}

/* Features area */
.features-area {
  position: relative;
  z-index: 1;
  max-width: 1200px;
  margin: 3rem auto 3rem;
  padding: 0 2rem;
}
.features-area h2 {
  font-size: clamp(1.8rem, 3vw, 2.2rem);
  margin-bottom: 1.5rem;
  color: #0f172a;
}
.features-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(280px, 1fr));
  gap: 1.5rem;
}
.features-grid article {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.5rem;
  transition: all 0.3s ease;
}
.features-grid article:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 32px rgba(99, 102, 241, 0.12);
  border-color: rgba(99, 102, 241, 0.3);
}
.features-grid h3 {
  margin-bottom: 0.8rem;
  color: #1e293b;
  font-size: 1.2rem;
}
.features-grid p {
  margin: 0;
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.5;
}

/* Showcase area */
.showcase-area {
  position: relative;
  z-index: 1;
  padding: 3rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
}
.showcase-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
}
.showcase-content img {
  width: 100%;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
  transition: all 0.5s ease;
}
.showcase-content img:hover {
  transform: scale(1.02);
  box-shadow: 0 24px 48px rgba(99, 102, 241, 0.15);
}
.showcase-content h2 {
  margin-top: 0;
  font-size: clamp(1.5rem, 2.5vw, 2rem);
  color: #0f172a;
  margin-bottom: 1rem;
}
.showcase-content p {
  color: #475569;
  line-height: 1.6;
  font-size: 1rem;
}
.showcase-content ul {
  margin: 1rem 0 0;
  padding-left: 1.5rem;
  color: #475569;
}
.showcase-content li {
  margin-bottom: 0.6rem;
  font-size: 0.95rem;
}

/* Deep stats area */
.deep-stats-area {
  position: relative;
  z-index: 1;
  padding: 3rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
}
.deep-stats-area h2 {
  font-size: clamp(1.8rem, 3vw, 2.2rem);
  margin-bottom: 0.5rem;
}
.deep-stats-area p {
  margin-bottom: 2rem;
  font-size: 1rem;
}
.deep-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(220px, 1fr));
  gap: 1.5rem;
}
.deep-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.5rem;
  text-align: center;
  transition: all 0.3s ease;
}
.deep-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(99, 102, 241, 0.12);
  border-color: rgba(99, 102, 241, 0.3);
}
.deep-card h4 {
  margin: 0;
  color: #334155;
  font-size: 0.95rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.deep-card p {
  margin: 0.8rem 0 0.4rem;
  color: #0f172a;
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.deep-card small {
  font-size: 0.75rem;
  color: #64748b;
  display: block;
}

/* Workflow area */
.workflow-area {
  position: relative;
  z-index: 1;
  padding: 3rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
}
.workflow-area h2 {
  font-size: clamp(1.8rem, 3vw, 2.2rem);
  margin-bottom: 2rem;
}
.workflow-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(260px, 1fr));
  gap: 1.5rem;
}
.workflow-grid div {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
}
.workflow-grid div:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 32px rgba(99, 102, 241, 0.12);
}
.workflow-grid h3 {
  margin: 0 0 0.8rem;
  font-size: 1.1rem;
}
.workflow-grid p {
  margin: 0;
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.5;
}

/* Footer */
.landing-footer {
  position: relative;
  z-index: 1;
  border-top: 1px solid #e2e8f0;
  text-align: center;
  padding: 1.5rem;
  font-size: 0.9rem;
  color: #64748b;
  background: rgba(255, 255, 255, 0.9);
}
.landing-footer a {
  color: #4f46e5;
  text-decoration: none;
}
.landing-footer a:hover {
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 1080px) {
  .hero-area {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  .features-grid {
    grid-template-columns: 1fr;
  }
  .dash-cards {
    grid-template-columns: repeat(3, minmax(100px, 1fr));
  }
  .deep-stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .workflow-grid {
    grid-template-columns: 1fr;
  }
  .showcase-content {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

@media (max-width: 768px) {
  .hero-area {
    padding: 3rem 1.5rem 2rem;
  }
  .dash-cards {
    grid-template-columns: 1fr;
  }
  .deep-stats-grid {
    grid-template-columns: 1fr;
  }
  .navbar {
    padding: 0.8rem 1rem;
  }
  .hero-left p {
    font-size: 1rem;
  }
  .btn {
    padding: 0.7rem 1.5rem;
  }
}

/* Support for zoom (100% to 110%) */
@media (min-resolution: 1.1dppx) {
  .hero-left h1 {
    font-size: clamp(2rem, 3.8vw, 3.3rem);
  }
  .deep-card p {
    font-size: 1.8rem;
  }
  .dash-card span {
    font-size: 1.6rem;
  }
}
</style>