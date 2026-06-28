<template>
  <div class="worker-profile-wrapper">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="worker-profile-container">
      <div class="mb-4">
        <button class="back-button" @click="goBack">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
          </svg>
          Back
        </button>
      </div>

      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading profile...</p>
      </div>

      <div v-else-if="error" class="empty-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h4>Failed to load profile</h4>
        <p>Please try again later</p>
      </div>

      <div v-else>
        <!-- Header Card -->
        <div class="profile-card header-card">
          <div class="profile-header">
            <div class="profile-avatar">
              <img
                :src="worker.photoUrl || avatarUrl"
                :alt="worker.fullname"
                class="avatar-image"
                @error="handleImageError"
              />
              <div class="avatar-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>
            </div>
            <div class="profile-info">
              <h2 class="profile-name">{{ worker.fullname }}</h2>
              <p class="profile-email">{{ worker.email }}</p>
              <div class="profile-details">
                <span v-if="worker.location" class="detail-item">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                  </svg>
                  {{ worker.location }}
                </span>
                <span v-if="worker.industry" class="detail-item">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                  </svg>
                  {{ worker.industry }}
                </span>
              </div>
            </div>
          </div>

          <!-- Social Links -->
          <div class="social-links">
            <a v-if="urls.url_linkedin" :href="urls.url_linkedin" target="_blank" class="social-btn linkedin">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                <rect x="2" y="9" width="4" height="12"/>
                <circle cx="4" cy="4" r="2"/>
              </svg>
              LinkedIn
            </a>
            <a v-if="urls.url_github" :href="urls.url_github" target="_blank" class="social-btn github">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48c-1.5-.42-3.1-.42-4.6 0C8.67.65 7.49 1 7.49 1A5.07 5.07 0 0 0 7.4 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 11 18.13V22"/>
              </svg>
              GitHub
            </a>
            <a v-if="urls.url_website" :href="urls.url_website" target="_blank" class="social-btn website">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
              </svg>
              Website
            </a>
            <a v-if="cv" :href="cv.file_path" target="_blank" class="social-btn cv">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
              Download CV
            </a>
          </div>
        </div>

        <!-- Education -->
        <div v-if="profileData.education?.length" class="info-card">
          <div class="card-header">
            <div class="header-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c3 2 6 2 9 0v-5"/>
              </svg>
            </div>
            <h3 class="card-title">Education</h3>
          </div>
          <div class="card-content">
            <div v-for="edu in profileData.education" :key="edu.id" class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <h4 class="item-title">{{ edu.degree }}</h4>
                <p class="item-subtitle">{{ edu.institution }} • {{ edu.location }}</p>
                <p class="item-date">{{ edu.start_year }} — {{ edu.end_year }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Skills -->
        <div v-if="profileData.skills?.length" class="info-card">
          <div class="card-header">
            <div class="header-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
              </svg>
            </div>
            <h3 class="card-title">Skills</h3>
          </div>
          <div class="card-content">
            <div class="skills-grid">
              <span
                v-for="skill in profileData.skills"
                :key="skill.id"
                class="skill-badge"
              >
                {{ skill.skill_name }}
              </span>
            </div>
          </div>
        </div>

        <!-- Experience -->
        <div v-if="profileData.experience?.length" class="info-card">
          <div class="card-header">
            <div class="header-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
              </svg>
            </div>
            <h3 class="card-title">Experience</h3>
          </div>
          <div class="card-content">
            <div v-for="exp in profileData.experience" :key="exp.id" class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <h4 class="item-title">{{ exp.title }}</h4>
                <p class="item-subtitle">{{ exp.company }} • {{ exp.location }}</p>
                <p class="item-date">{{ exp.start_date }} — {{ exp.end_date || 'Present' }}</p>
                <p v-if="exp.description" class="item-description">{{ exp.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Certifications -->
        <div v-if="profileData.certifications?.length" class="info-card">
          <div class="card-header">
            <div class="header-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 15v2m-6-4h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2zm10-10v4a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2z"/>
              </svg>
            </div>
            <h3 class="card-title">Certifications</h3>
          </div>
          <div class="card-content">
            <div v-for="cert in profileData.certifications" :key="cert.id" class="cert-item">
              <div class="cert-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
              </div>
              <div class="cert-content">
                <h4 class="cert-name">{{ cert.name }}</h4>
                <p class="cert-details">{{ cert.issuer }} • {{ cert.issue_date }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Projects -->
        <div v-if="profileData.projects?.length" class="info-card">
          <div class="card-header">
            <div class="header-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
              </svg>
            </div>
            <h3 class="card-title">Projects</h3>
          </div>
          <div class="card-content">
            <div v-for="project in profileData.projects" :key="project.id" class="project-card">
              <h4 class="project-title">{{ project.project_name }}</h4>
              <div class="project-tech">
                <span v-for="tech in project.technologies" :key="tech" class="tech-badge">
                  {{ tech }}
                </span>
              </div>
              <ul class="project-points">
                <li v-for="point in project.points" :key="point">{{ point }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const worker = ref({});
const urls = ref({});
const cv = ref(null);
const profileData = ref({});
const isLoading = ref(true);
const error = ref(false);

const avatarUrl = computed(() =>
  `https://ui-avatars.com/api/?name=${encodeURIComponent(worker.value.fullname || 'User')}&background=4f46e5&color=fff&size=80&bold=true`
);

const handleImageError = (e) => {
  e.target.src = avatarUrl.value;
};

const goBack = () => {
  router.back();
};

const fetchWorkerProfile = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const workerId = route.params.id;

    const res = await fetch(`${import.meta.env.VITE_API_URL}/worker/public-profile/${workerId}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });

    const data = await res.json();

    if (data.status === 'success') {
      worker.value = data.worker;
      urls.value = data.urls || {};
      cv.value = data.cv;
      profileData.value = {
        skills: data.skills,
        experience: data.experience,
        education: data.education,
        certifications: data.certifications,
        projects: data.projects
      };
    } else {
      error.value = true;
    }

  } catch (err) {
    console.error('Error fetching worker profile:', err);
    error.value = true;
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchWorkerProfile();
});
</script>

<style scoped>
.worker-profile-wrapper {
  min-height: 100vh;
  position: relative;

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

.worker-profile-container {
  position: relative;
  z-index: 1;
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Back Button */
.back-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-button:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: translateX(-4px);
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem;
  background: white;
  border-radius: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-state h4 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin-top: 1rem;
}

.empty-state p {
  color: #94a3b8;
}

/* Cards */
.profile-card, .info-card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
  overflow: hidden;
  transition: all 0.3s ease;
}

.profile-card:hover, .info-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Header Card */
.header-card {
  padding: 2rem;
}

.profile-header {
  display: flex;
  gap: 2rem;
  align-items: center;
  flex-wrap: wrap;
}

.profile-avatar {
  position: relative;
}

.avatar-image {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #4f46e5;
  box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);
}

.avatar-badge {
  position: absolute;
  bottom: 4px;
  right: 4px;
  background: #4f46e5;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
  color: white;
}

.profile-name {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem;
  letter-spacing: -0.5px;
}

.profile-email {
  color: #64748b;
  margin: 0 0 0.5rem;
}

.profile-details {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.detail-item {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.875rem;
  color: #475569;
}

/* Social Links */
.social-links {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.social-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.3s ease;
  background: white;
  border: 1.5px solid #e2e8f0;
  color: #475569;
}

.social-btn:hover {
  transform: translateY(-2px);
}

.social-btn.linkedin:hover {
  background: #0077b5;
  border-color: #0077b5;
  color: white;
}

.social-btn.github:hover {
  background: #1e293b;
  border-color: #1e293b;
  color: white;
}

.social-btn.website:hover {
  background: #10b981;
  border-color: #10b981;
  color: white;
}

.social-btn.cv:hover {
  background: #ef4444;
  border-color: #ef4444;
  color: white;
}

/* Info Cards */
.card-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem 1.5rem 0 1.5rem;
}

.header-icon {
  width: 36px;
  height: 36px;
  background: #eef2ff;
  border-radius: 10px;
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
  padding: 1.5rem;
}

/* Timeline Items */
.timeline-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  position: relative;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-dot {
  width: 10px;
  height: 10px;
  background: #4f46e5;
  border-radius: 50%;
  margin-top: 0.25rem;
  flex-shrink: 0;
}

.timeline-content {
  flex: 1;
}

.item-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 0.25rem;
}

.item-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 0.25rem;
}

.item-date {
  font-size: 0.75rem;
  color: #94a3b8;
  margin: 0 0 0.5rem;
}

.item-description {
  font-size: 0.875rem;
  color: #475569;
  margin: 0;
  line-height: 1.5;
}

/* Skills Grid */
.skills-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.skill-badge {
  background: #eef2ff;
  color: #4f46e5;
  padding: 0.375rem 0.875rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.skill-badge:hover {
  transform: translateY(-2px);
  background: #4f46e5;
  color: white;
}

/* Certifications */
.cert-item {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.cert-item:last-child {
  margin-bottom: 0;
}

.cert-icon {
  width: 32px;
  height: 32px;
  background: #f1f5f9;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f59e0b;
  flex-shrink: 0;
}

.cert-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 0.25rem;
}

.cert-details {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0;
}

/* Projects */
.project-card {
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.project-card:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.project-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 0.5rem;
}

.project-tech {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.tech-badge {
  background: #f1f5f9;
  color: #334155;
  padding: 0.25rem 0.625rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 500;
}

.project-points {
  margin: 0;
  padding-left: 1.25rem;
  font-size: 0.875rem;
  color: #475569;
  line-height: 1.5;
}

.project-points li {
  margin-bottom: 0.25rem;
}

/* Responsive */
@media (max-width: 768px) {
  .worker-profile-container {
    padding: 1rem;
  }
  
  .header-card {
    padding: 1.5rem;
  }
  
  .profile-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .profile-details {
    justify-content: center;
  }
  
  .social-links {
    justify-content: center;
  }
  
  .card-header {
    padding: 1rem 1rem 0 1rem;
  }
  
  .card-content {
    padding: 1rem;
  }
}
</style>