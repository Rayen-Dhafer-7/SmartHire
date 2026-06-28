<template>
  <div class="cv-tab">

    <!-- AI Extraction Loading Overlay -->
    <Transition name="fade-overlay">
      <div v-if="isExtracting" class="extraction-overlay">
        <div class="extraction-modal">
          <div class="extraction-spinner">
            <svg class="spinner-ring" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
              <circle class="spinner-track" cx="25" cy="25" r="20" fill="none" stroke-width="4"/>
              <circle class="spinner-head" cx="25" cy="25" r="20" fill="none" stroke-width="4"/>
            </svg>
          </div>
          <div class="extraction-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M13 2L3 14h6l-2 8 10-12h-6l2-8z"/>
            </svg>
          </div>
          <h3 class="extraction-title">Extracting with AI</h3>
          <p class="extraction-subtitle">Analyzing your CV and updating your profile&hellip;</p>
          <div class="extraction-steps">
            <div class="step step-active">
              <span class="step-dot"></span>
              <span>Reading CV content</span>
            </div>
            <div class="step step-active" style="animation-delay: 0.6s">
              <span class="step-dot"></span>
              <span>Extracting skills &amp; experience</span>
            </div>
            <div class="step step-active" style="animation-delay: 1.2s">
              <span class="step-dot"></span>
              <span>Updating your profile</span>
            </div>
          </div>
        </div>
      </div>
    </Transition>
    <div class="profile-card">
      <div class="card-header-section">
        <div class="header-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
        </div>
        <h3 class="card-title">CV Upload</h3>
      </div>
      
      <div class="card-content">
        <div class="upload-grid">
          <div class="upload-section">
            <h4 class="section-title">Upload Your CV</h4>
            <p class="section-description">Upload your CV/resume (PDF, DOC, DOCX). We'll use AI to automatically extract and update your professional information.</p>
            
<div class="upload-area">
  <div class="upload-icon">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
      <polyline points="17 8 12 3 7 8"/>
      <line x1="12" y1="3" x2="12" y2="15"/>
    </svg>
  </div>
  <p class="upload-text">Drag and drop your CV here, or click to browse</p>
  <p class="upload-hint">Maximum file size: 10MB. Supported formats: PDF, DOC, DOCX.</p>
  <input 
    type="file" 
    ref="fileInput"
    class="file-input" 
    accept=".pdf,.doc,.docx" 
    @change="handleResumeUpload"
    :disabled="isUploading"
  >
  <button class="btn-upload" @click="triggerFileUpload" :disabled="isUploading">
    <span v-if="isUploading" class="spinner"></span>
    <span v-else>Browse Files</span>
  </button>
</div>
            
            <!-- Uploaded CV Info -->
            <div v-if="profile.resumeName && !isUploading" class="uploaded-cv">
              <div class="cv-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                  <path d="M20 6L9 17l-5-5"/>
                </svg>
                <span>CV Uploaded Successfully!</span>
              </div>
              <div class="cv-details">
                <div class="cv-name">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                  </svg>
                  {{ profile.resumeName }}
                </div>
                <div class="cv-size">{{ profile.resumeSize }}</div>
              </div>
              <div class="cv-actions">
                <button class="btn-extract" @click="extractInfoFromCV" :disabled="isExtracting">
                  <span v-if="isExtracting" class="btn-spinner"></span>
                  <template v-else>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M13 2L3 14h6l-2 8 10-12h-6l2-8z"/>
                    </svg>
                  </template>
                  {{ isExtracting ? 'Extracting...' : 'Extract Info with AI' }}
                </button>
                <button class="btn-remove" @click="removeCV">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                  Remove CV
                </button>
              </div>
            </div>
          </div>
          
          <div class="benefits-section">
            <div class="benefits-card">
              <h4 class="benefits-title">AI Extraction Benefits</h4>
              <ul class="benefits-list">
                <li>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Auto-fill skills from your CV
                </li>
                <li>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Extract work experience details
                </li>
                <li>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Identify certifications
                </li>
                <li>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Parse education history
                </li>
                <li>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Save time on manual entry
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { showWarning, showConfirm, showSuccess } from '../../../../utils/notifications';

const props = defineProps({
  profile: { type: Object, required: true },
  isExtracting: { type: Boolean, default: false }
});

const emit = defineEmits(['file-selected', 'remove-cv', 'update-profile']);
const isUploading = ref(false);
const fileInput = ref(null);

const triggerFileUpload = () => {
  fileInput.value?.click();
};

const handleResumeUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const validExtensions = ['.pdf', '.doc', '.docx'];
  const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
  
  if (!validExtensions.includes(fileExtension)) {
    showWarning('Invalid File', 'Only PDF, DOC, and DOCX files are allowed.');
    event.target.value = '';
    return;
  }
  
  if (file.size > 10 * 1024 * 1024) {
    showWarning('File Too Large', 'File size should be less than 10MB.');
    event.target.value = '';
    return;
  }
  
  isUploading.value = true;
  try {
    await emit('file-selected', file);
  } catch (error) {
    console.error('Upload failed:', error);
  } finally {
    isUploading.value = false;
    event.target.value = '';
  }
};

const removeCV = () => {
  showConfirm('Remove CV?', 'Are you sure you want to remove your CV?', 'Yes, remove it', 'Cancel')
    .then((result) => {
      if (result.isConfirmed) emit('remove-cv', props.profile.resumeId);
    });
};

const extractInfoFromCV = async () => {
  if (!props.profile.resumeName) {
    showWarning('No CV Uploaded', 'Please upload a CV first.');
    return;
  }

  const result = await showConfirm(
    'AI Extraction',
    'This will parse your CV and update your profile automatically.',
    'Extract Info',
    'Cancel'
  );

  if (result.isConfirmed) {
    emit('update-profile');
  }
};
</script>

<style scoped>
/* ── AI Extraction Loading Overlay ── */
.extraction-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.extraction-modal {
  background: white;
  border-radius: 24px;
  padding: 2.5rem 3rem;
  text-align: center;
  max-width: 380px;
  width: 90%;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
  position: relative;
}

.extraction-spinner {
  position: relative;
  width: 72px;
  height: 72px;
  margin: 0 auto 1.25rem;
}

.spinner-ring {
  width: 72px;
  height: 72px;
  animation: rotate 1.4s linear infinite;
}

.spinner-track {
  stroke: #e2e8f0;
}

.spinner-head {
  stroke: #4f46e5;
  stroke-linecap: round;
  stroke-dasharray: 90 180;
  stroke-dashoffset: 0;
  animation: dash 1.4s ease-in-out infinite;
}

@keyframes rotate {
  to { transform: rotate(360deg); }
}

@keyframes dash {
  0%   { stroke-dasharray: 1 180;  stroke-dashoffset: 0; }
  50%  { stroke-dasharray: 100 180; stroke-dashoffset: -35; }
  100% { stroke-dasharray: 100 180; stroke-dashoffset: -125; }
}

.extraction-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #4f46e5;
}

.extraction-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.5rem;
}

.extraction-subtitle {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0 0 1.5rem;
}

.extraction-steps {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  text-align: left;
}

.step {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.8125rem;
  color: #475569;
  animation: stepFadeIn 0.5s ease forwards;
  opacity: 0;
}

.step-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #4f46e5;
  flex-shrink: 0;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes stepFadeIn {
  to { opacity: 1; }
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.8); }
}

/* Overlay transition */
.fade-overlay-enter-active,
.fade-overlay-leave-active {
  transition: opacity 0.3s ease;
}
.fade-overlay-enter-from,
.fade-overlay-leave-to {
  opacity: 0;
}

/* Extract button spinner */
.btn-spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  flex-shrink: 0;
}

.btn-extract:disabled {
  opacity: 0.75;
  cursor: not-allowed;
  transform: none !important;
}

.cv-tab {
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

.upload-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.section-description {
  color: #64748b;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.upload-area {
  border: 2px dashed #e2e8f0;
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  position: relative;
  transition: all 0.3s ease;
  background: #fafbfc;
}

.upload-area:hover {
  border-color: #4f46e5;
  background: #fefefe;
}

.upload-area.has-file {
  border-color: #10b981;
  background: #f0fdf4;
}

.upload-icon {
  color: #94a3b8;
  margin-bottom: 1rem;
}

.upload-text {
  color: #334155;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.upload-hint {
  color: #94a3b8;
  font-size: 0.75rem;
  margin-bottom: 1.5rem;
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.btn-upload {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border: none;
  padding: 0.625rem 1.5rem;
  border-radius: 10px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-upload:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-upload:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid white;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.uploaded-cv {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #f0fdf4;
  border-radius: 12px;
  border: 1px solid #bbf7d0;
}

.cv-success {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #10b981;
  font-weight: 500;
  margin-bottom: 0.75rem;
}

.cv-details {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 0.5rem;
  background: white;
  border-radius: 8px;
}

.cv-name {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #334155;
  flex: 1;
}

.cv-size {
  font-size: 0.75rem;
  color: #64748b;
}

.cv-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-extract, .btn-remove {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-extract {
  background: #4f46e5;
  color: white;
  border: none;
}

.btn-extract:hover {
  background: #6366f1;
  transform: translateY(-1px);
}

.btn-remove {
  background: white;
  border: 1px solid #e2e8f0;
  color: #ef4444;
}

.btn-remove:hover {
  background: #fef2f2;
  border-color: #ef4444;
}

.benefits-card {
  background: linear-gradient(135deg, #f8fafc, #ffffff);
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
}

.benefits-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 1rem;
}

.benefits-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.benefits-list li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
  color: #475569;
  font-size: 0.875rem;
}

.benefits-list svg {
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .card-content {
    padding: 1rem;
  }
  
  .upload-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .cv-actions {
    flex-direction: column;
  }
  
  .btn-extract, .btn-remove {
    width: 100%;
    justify-content: center;
  }
}
</style>