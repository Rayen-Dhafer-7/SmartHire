<template>
  <div class="info-tab">
    <div class="profile-card">
      <div class="card-header-section">
        <div class="header-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
        </div>
        <h3 class="card-title">General Information</h3>
      </div>
      
      <form @submit.prevent="$emit('save-profile')">
        <CompanyInfoForm 
          :profile="profile" 
          :logoPreview="logoPreview"
          @logo-change="$emit('logo-change', $event)"
          @trigger-logo-upload="$emit('trigger-logo-upload')"
        />

        <div class="divider"></div>

        <SocialLinksForm 
          :urls="profile.urls"
        />

        <div class="form-actions">
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
</template>

<script setup>
import CompanyInfoForm from '../forms/CompanyInfoForm.vue';
import SocialLinksForm from '../forms/SocialLinksForm.vue';

defineProps({
  profile: {
    type: Object,
    required: true
  },
  logoPreview: {
    type: [String, Object],
    default: null
  }
});

defineEmits(['save-profile', 'logo-change', 'trigger-logo-upload']);
</script>

<style scoped>
.info-tab {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.profile-card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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

.divider {
  height: 1px;
  background: linear-gradient(90deg, #e2e8f0, transparent);
  margin: 1.5rem 0;
}

.form-actions {
  padding: 1.5rem 2rem;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  text-align: right;
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
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

@media (max-width: 768px) {
  .card-header-section {
    padding: 1rem;
  }
  
  .form-actions {
    padding: 1rem;
  }
  
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
}
</style>