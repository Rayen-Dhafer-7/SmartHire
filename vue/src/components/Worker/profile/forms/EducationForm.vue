<template>
  <div class="form-card">
    <div class="form-header">
      <div class="form-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
          <path d="M6 12v5c3 2 6 2 9 0v-5"/>
        </svg>
      </div>
      <h6 class="form-title">Add Education</h6>
    </div>
    <div class="form-body">
      <div class="form-grid">
        <div class="form-field">
          <label class="field-label">Degree/Diploma *</label>
          <input type="text" class="field-input" v-model="form.degree" placeholder="e.g., Bachelor of Computer Science">
        </div>
        <div class="form-field">
          <label class="field-label">Institution *</label>
          <input type="text" class="field-input" v-model="form.institution" placeholder="e.g., University of Technology">
        </div>
        <div class="form-field">
          <label class="field-label">Location</label>
          <input type="text" class="field-input" v-model="form.location" placeholder="e.g., New York, USA">
        </div>
        <div class="form-field">
          <label class="field-label">Start Year</label>
          <input type="text" class="field-input" v-model="form.startYear" placeholder="e.g., 2020">
        </div>
        <div class="form-field">
          <label class="field-label">End Year</label>
          <input type="text" class="field-input" v-model="form.endYear" placeholder="e.g., 2024 or Present">
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn-cancel" @click="cancel">Cancel</button>
        <button type="button" class="btn-save" @click="save">Save Education</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { showWarning } from '../../../../utils/notifications';

const emit = defineEmits(['save', 'cancel']);

const form = ref({
  degree: '',
  institution: '',
  location: '',
  startYear: '',
  endYear: ''
});

const save = () => {
  if (!form.value.degree || !form.value.institution) {
    showWarning('Missing Information', 'Please fill in all required fields.');
    return;
  }
  emit('save', { ...form.value });
  resetForm();
};

const cancel = () => {
  emit('cancel');
  resetForm();
};

const resetForm = () => {
  form.value = { degree: '', institution: '', location: '', startYear: '', endYear: '' };
};
</script>

<style scoped>
.form-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  margin-top: 1rem;
  overflow: hidden;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 1.25rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.form-icon {
  width: 28px;
  height: 28px;
  background: #eef2ff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4f46e5;
}

.form-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.form-body {
  padding: 1.25rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.field-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.field-input {
  padding: 0.5rem 0.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.8rem;
  transition: all 0.2s;
}

.field-input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn-cancel, .btn-save {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel {
  background: white;
  border: 1.5px solid #e2e8f0;
  color: #64748b;
}

.btn-cancel:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.btn-save {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border: none;
  color: white;
}

.btn-save:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>