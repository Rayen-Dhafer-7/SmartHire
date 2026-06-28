<template>
  <div class="company-info-form">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Company Name</label>
        <div class="input-wrapper">
          <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="4" y="8" width="16" height="12" rx="2"/>
            <path d="M8 4v4M16 4v4"/>
          </svg>
          <input type="text" class="form-control" v-model="profile.name" placeholder="e.g., Acme Corp" />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="input-wrapper">
          <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          <input type="text" class="form-control" v-model="profile.email" placeholder="hr@company.com" />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Location</label>
        <div class="input-wrapper">
          <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          <input type="text" class="form-control" v-model="profile.location" placeholder="San Francisco, CA" />
        </div>
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Company Logo</label>
        <div class="logo-upload-container">
          <input 
            type="file" 
            id="logo-upload" 
            accept="image/*" 
            class="hidden-input" 
            @change="$emit('logo-change', $event)"
            style="display: none;"
          />
          <div class="logo-preview" @click="$emit('trigger-logo-upload')">
            <img :src="logoPreview" alt="Company Logo" class="logo-image" style="width: 100%; height: 100%; object-fit: contain;"/>
            <div class="logo-overlay">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
              </svg>
              <span>Change Logo</span>
            </div>
          </div>
          <p class="logo-hint">Click to upload or change logo (PNG, JPG, max 2MB)</p>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <div class="input-wrapper">
          <svg class="input-icon textarea-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          <textarea class="form-control textarea" rows="5" v-model="profile.description" placeholder="Describe your company, mission, values, and what you're looking for..."></textarea>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  profile: {
    type: Object,
    required: true
  },
  logoPreview: {
    type: [String, Object],
    default: 'https://via.placeholder.com/150'
  }
});

defineEmits(['logo-change', 'trigger-logo-upload']);
</script>

<style scoped>
.company-info-form {
  padding: 1rem 2rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.form-group {
  margin-bottom: 0;
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

/* Logo Upload */
.logo-upload-container {
  text-align: center;
}

.logo-preview {
  width: 120px;
  height: 120px;
  margin: 0 auto 0.75rem;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
  position: relative;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
}

.logo-preview:hover {
  border-color: #4f46e5;
  transform: scale(1.02);
}

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
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

.logo-preview:hover .logo-overlay {
  opacity: 1;
}

.logo-hint {
  font-size: 0.7rem;
  color: #94a3b8;
  text-align: center;
  margin: 0;
}

@media (max-width: 992px) {
  .company-info-form {
    padding: 1rem;
  }
  
  .form-grid,
  .form-grid-2 {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>