<template>
  <form @submit.prevent="submit" class="job-post-form">
    <div class="form-content">
      <!-- Job Title -->
      <div class="form-group">
        <label class="form-label">
          Job Title <span class="required">*</span>
        </label>
        <div class="input-wrapper">
          <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 7h-4.18A3 3 0 0 0 16 5.18V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v1.18A3 3 0 0 0 8.18 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
            <path d="M12 11v4M9 13h6"/>
          </svg>
          <input 
            type="text" 
            class="form-control" 
            v-model="form.title" 
            placeholder="e.g. Senior Vue.js Developer" 
            required 
            :disabled="isLoading"
          />
        </div>
      </div>

      <!-- Description -->
      <div class="form-group">
        <label class="form-label">
          Description <span class="required">*</span>
        </label>
        <div class="input-wrapper">
          <svg class="input-icon textarea-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <textarea 
            class="form-control textarea" 
            rows="6" 
            v-model="form.description" 
            placeholder="Describe the role, responsibilities, requirements, and benefits..."
            required
            :disabled="isLoading"
          ></textarea>
        </div>
      </div>

      <!-- Grid Row 1: Deadline & Post Date -->
      <div class="form-grid grid-2">
        <div class="form-group">
          <label class="form-label">Deadline</label>
          <div class="input-wrapper">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <input 
              type="date" 
              class="form-control" 
              v-model="form.deadline" 
              required 
              :min="minDate"
              :disabled="isLoading"
            />
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Post Date</label>
          <div class="input-wrapper">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <input 
              type="text" 
              class="form-control bg-light" 
              :value="currentDate" 
              readonly 
              disabled
            />
          </div>
        </div>
      </div>

      <!-- Grid Row 2: Job Type, Workers Needed, Skills -->
      <div class="form-grid grid-3">
        <div class="form-group">
          <label class="form-label">Job Type</label>
          <div class="input-wrapper">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            <select class="form-control select" v-model="form.type" :disabled="isLoading">
              <option value="Onsite">🏢 Onsite</option>
              <option value="Remote">🏠 Remote</option>
              <option value="Hybrid">🔄 Hybrid</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Workers Needed</label>
          <div class="input-wrapper">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input 
              type="number" 
              class="form-control" 
              v-model="form.count" 
              min="1" 
              :disabled="isLoading"
            />
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Skills (comma separated)</label>
          <div class="input-wrapper">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            <input 
              type="text" 
              class="form-control" 
              v-model="form.skills" 
              placeholder="React, Vue, Docker, Python..." 
              :disabled="isLoading"
            />
          </div>
          <div class="form-hint">Enter skills separated by commas</div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button 
          type="button" 
          class="btn-secondary" 
          @click="$emit('cancel')"
          :disabled="isLoading"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="btn-primary"
          :disabled="isLoading"
        >
          <svg v-if="isLoading" class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 2a10 10 0 0 1 10 10"/>
          </svg>
          <span v-else>Publish Post</span>
          <svg v-if="!isLoading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
          </svg>
        </button>
      </div>
    </div>
  </form>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'cancel']);

const currentDate = computed(() => {
  const date = new Date();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const year = date.getFullYear();
  return `${month}/${day}/${year}`;
});

const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const form = ref({
  title: '',
  description: '',
  deadline: '',
  type: 'Onsite',
  count: 1,
  skills: ''
});

const submit = () => {
  emit('submit', { ...form.value });
};
</script>

<style scoped>
.job-post-form {
  padding: 2rem;
}

.form-content {
  max-width: 100%;
}

/* Form Groups */
.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.9rem;
  color: #334155;
}

.required {
  color: #ef4444;
}

/* Input Wrapper with Icon */
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
  z-index: 1;
}

.textarea-icon {
  top: 1.25rem;
  transform: none;
}

.form-control {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 2.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: white;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-control.textarea {
  resize: vertical;
  padding-top: 1rem;
  padding-bottom: 1rem;
  min-height: 140px;
}

.form-control.select {
  appearance: none;
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
}

.form-control.bg-light {
  background: #f8fafc;
  color: #64748b;
}

.form-hint {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Form Grid */
.form-grid {
  display: grid;
  gap: 1.5rem;
  margin-bottom: 0;
}

.grid-2 {
  grid-template-columns: repeat(2, 1fr);
}

.grid-3 {
  grid-template-columns: repeat(3, 1fr);
}

/* Form Actions */
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
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  color: #64748b;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-secondary:hover:not(:disabled) {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Spinner Animation */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.spinner {
  animation: spin 1s linear infinite;
}

/* Responsive */
@media (max-width: 992px) {
  .grid-3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .job-post-form {
    padding: 1.5rem;
  }
  
  .grid-2,
  .grid-3 {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .form-actions {
    flex-direction: column-reverse;
  }
  
  .btn-primary,
  .btn-secondary {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .job-post-form {
    padding: 1rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
}
</style>