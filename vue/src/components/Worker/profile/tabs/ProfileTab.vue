<template>
  <div class="profile-tab">
    <div class="profile-card">
      <div class="card-header-section">
        <div class="header-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <h3 class="card-title">General Information</h3>
      </div>
      
      <div class="card-content">
        <form @submit.prevent="$emit('save-profile')">
          <!-- Photo and Basic Info -->
          <div class="profile-header-row">
            <div class="photo-section">
              <div class="photo-container">
                <img :src="logoPreview || profile.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(profile.fullName || 'User') + '&background=4f46e5&color=fff&size=150'" 
                     class="profile-photo" 
                     alt="Profile Photo">
                <div class="photo-overlay">
                  <label for="photoUpload" class="change-photo-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                      <circle cx="12" cy="13" r="4"/>
                    </svg>
                    
                  </label>
                  <input type="file" id="photoUpload" class="d-none" accept="image/*" @change="handlePhotoUpload">
                </div>
              </div>
            </div>
            
            <div class="info-section">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                  <input type="text" class="form-control" v-model="profile.fullName" required>
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">Email</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                  <input type="email" class="form-control" v-model="profile.email" disabled>
                </div>
                <small class="form-hint">Email cannot be changed</small>
              </div>
              
              <div class="form-group">
                <label class="form-label">Location</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                  </svg>
                  <input type="text" class="form-control" v-model="profile.location" placeholder="e.g. New York, USA">
                </div>
              </div>
            </div>
          </div>

          <!-- CV Section -->
          <div class="cv-section">
            <h4 class="section-title">Current CV</h4>
            <div v-if="profile.resumeName" class="cv-card">
              <div class="cv-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
              </div>
              <div class="cv-info">
                <strong>{{ profile.resumeName }}</strong>
                <div class="cv-meta">Uploaded on {{ profile.resumeDate }} • {{ profile.resumeSize }}</div>
              </div>
              <div class="cv-actions">
                <button type="button" class="btn-icon" @click="$emit('download-cv')" title="Download CV">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                  </svg>
                </button>
              </div>
            </div>
            <div v-else class="cv-empty">
              <p>No CV uploaded yet.</p>
              <button type="button" class="btn-generate" :disabled="isGeneratingCV" @click="$emit('generate-cv')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M13 2L3 14h6l-2 8 10-12h-6l2-8z"/>
                </svg>
                <span v-if="isGeneratingCV">Generating...</span>
                <span v-else>Generate CV from Profile</span>
              </button>
            </div>
          </div>

          <!-- Bio Section -->
          <div class="form-group">
            <label class="form-label">Bio / Description</label>
            <div class="input-wrapper">
              <svg class="input-icon textarea-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
              </svg>
              <textarea class="form-control textarea" rows="4" v-model="profile.bio" placeholder="Tell us about yourself..."></textarea>
            </div>
          </div>

          <!-- Sections -->
          <EducationSection 
            :educationList="profile.education" 
            @add="$emit('add-education', $event)" 
            @remove="$emit('remove-education', $event)" 
          />
          
          <ExperienceSection 
            :experienceList="profile.experience" 
            @add="$emit('add-experience', $event)" 
            @remove="$emit('remove-experience', $event)" 
          />
          
          <ProjectsSection 
            :projectsList="profile.projects" 
            @add="$emit('add-project', $event)" 
            @remove="$emit('remove-project', $event)" 
          />
          
          <SkillsSection 
            :skills="profile.skills" 
            @add="$emit('add-skill', $event)" 
            @remove="$emit('remove-skill', $event)" 
          />
          
          <CertificationsSection 
            :certificationsList="profile.certifications" 
            @add="$emit('add-certification', $event)" 
            @remove="$emit('remove-certification', $event)" 
          />

          <!-- Social Links -->
          <div class="social-section">
            <h4 class="section-title">Social Links</h4>
            <div class="social-grid">
              <div class="form-group">
                <label class="form-label">LinkedIn</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                    <rect x="2" y="9" width="4" height="12"/>
                    <circle cx="4" cy="4" r="2"/>
                  </svg>
                  <input type="url" class="form-control" v-model="profile.linkedin" placeholder="https://linkedin.com/in/...">
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">GitHub</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48c-1.5-.42-3.1-.42-4.6 0C8.67.65 7.49 1 7.49 1A5.07 5.07 0 0 0 7.4 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 11 18.13V22"/>
                  </svg>
                  <input type="url" class="form-control" v-model="profile.github" placeholder="https://github.com/...">
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">Website</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                  </svg>
                  <input type="url" class="form-control" v-model="profile.website" placeholder="https://...">
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">Email (Public)</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                  <input type="email" class="form-control" v-model="profile.gmail" placeholder="contact@example.com">
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="form-actions">
            <button type="button" class="btn-secondary" @click="$emit('reset-form')">Reset</button>
            <button type="submit" class="btn-primary">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
              </svg>
              Save Profile
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Floating Scroll Buttons -->
    <div class="scroll-fab-group">
      <button v-show="showScrollTop" class="scroll-fab" @click="scrollToTop">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="18 15 12 9 6 15"/>
        </svg>
      </button>
      <button v-show="showScrollBottom" class="scroll-fab" @click="scrollToBottom">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import EducationSection from '../sections/EducationSection.vue';
import ExperienceSection from '../sections/ExperienceSection.vue';
import ProjectsSection from '../sections/ProjectsSection.vue';
import SkillsSection from '../sections/SkillsSection.vue';
import CertificationsSection from '../sections/CertificationsSection.vue';

defineProps({
  profile: { type: Object, required: true },
  logoPreview: { type: String, default: null },
  isGeneratingCV: { type: Boolean, default: false }
});

const emit = defineEmits([
  'save-profile', 'reset-form', 'download-cv', 'generate-cv', 'photo-selected',
  'add-education', 'remove-education', 'add-experience', 'remove-experience',
  'add-project', 'remove-project', 'add-skill', 'remove-skill',
  'add-certification', 'remove-certification'
]);

const showScrollTop = ref(false);
const showScrollBottom = ref(true);

const handlePhotoUpload = (event) => {
  const file = event.target.files[0];
  if (file) emit('photo-selected', file);
};

const onScroll = () => {
  const scrollY = window.scrollY;
  const windowH = window.innerHeight;
  const docH = document.documentElement.scrollHeight;
  showScrollTop.value = scrollY > 300;
  showScrollBottom.value = scrollY + windowH < docH - 150;
};

const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });
const scrollToBottom = () => {
  const el = document.querySelector('.form-actions');
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  else window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
};

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
});

onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<style scoped>
.profile-tab {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.profile-card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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

.profile-header-row {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e2e8f0;
}

.photo-section {
  text-align: center;
}

.photo-container {
  position: relative;
  width: 150px;
  height: 150px;
  margin: 0 auto;
}

.profile-photo {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #4f46e5;
  box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);
}

.photo-overlay {
  position: absolute;
  bottom: 0;
  right: 0;
}

.change-photo-btn {
  background: #4f46e5;
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid white;
}

.change-photo-btn:hover {
  transform: scale(1.1);
  background: #6366f1;
}

.info-section {
  flex: 1;
}

.form-group {
  margin-bottom: 1rem;
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

.textarea-icon {
  top: 1rem;
  transform: none;
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.form-control.textarea {
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
  resize: vertical;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-hint {
  font-size: 0.7rem;
  color: #94a3b8;
  margin-top: 0.25rem;
  display: block;
}

.cv-section {
  background: #f8fafc;
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 1rem;
}

.cv-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.cv-icon {
  width: 48px;
  height: 48px;
  background: #fee2e2;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ef4444;
}

.cv-info {
  flex: 1;
}

.cv-meta {
  font-size: 0.7rem;
  color: #64748b;
}

.cv-actions .btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  color: #64748b;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.2s;
}

.cv-actions .btn-icon:hover {
  background: #f1f5f9;
  color: #4f46e5;
}

.cv-empty {
  text-align: center;
  padding: 1.5rem;
}

.cv-empty p {
  color: #64748b;
  margin-bottom: 1rem;
}

.btn-generate {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-generate:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.social-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e2e8f0;
}

.social-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
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
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.scroll-fab-group {
  position: fixed;
  bottom: 2rem;
  right: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  z-index: 1050;
}

.scroll-fab {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: none;
  background: #4f46e5;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
}

.scroll-fab:hover {
  transform: scale(1.1);
}

@media (max-width: 768px) {
  .card-content {
    padding: 1rem;
  }
  
  .profile-header-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .social-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn-primary, .btn-secondary {
    width: 100%;
    justify-content: center;
  }
}
</style>