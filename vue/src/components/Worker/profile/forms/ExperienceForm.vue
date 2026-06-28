<template>
  <div class="form-card">
    <div class="form-header">
      <div class="form-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
          <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
        </svg>
      </div>
      <h6 class="form-title">Add Experience</h6>
    </div>
    <div class="form-body">
      <div class="form-grid">
        <div class="form-field">
          <label class="field-label">Job Title *</label>
          <input type="text" class="field-input" v-model="form.title" placeholder="e.g., Frontend Developer">
        </div>
        <div class="form-field">
          <label class="field-label">Company *</label>
          <input type="text" class="field-input" v-model="form.company" placeholder="e.g., Google">
        </div>
        <div class="form-field">
          <label class="field-label">Location</label>
          <input type="text" class="field-input" v-model="form.location" placeholder="e.g., New York, USA">
        </div>
        <div class="form-field">
          <label class="field-label">Employment Type</label>
          <select class="field-select" v-model="form.type">
            <option value="Onsite">Onsite</option>
            <option value="Remote">Remote</option>
            <option value="Hybrid">Hybrid</option>
          </select>
        </div>
        <div class="form-field">
          <label class="field-label">Start Date</label>
          <input type="text" class="field-input" v-model="form.startDate" placeholder="e.g., 2023/09">
        </div>
        <div class="form-field">
          <label class="field-label">End Date</label>
          <input type="text" class="field-input" v-model="form.endDate" placeholder="e.g., 2024/01 or Present">
        </div>
        <div class="form-field full-width">
          <label class="field-label">Responsibilities (one per line, start with •)</label>
          <textarea class="field-textarea" rows="4" v-model="form.pointsText" placeholder="• Developed features using Vue.js&#10;• Collaborated with team on UI design&#10;• Implemented unit tests"></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn-cancel" @click="cancel">Cancel</button>
        <button type="button" class="btn-save" @click="save">Save Experience</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { showWarning } from '../../../../utils/notifications';

const emit = defineEmits(['save', 'cancel']);

const form = ref({
  title: '',
  company: '',
  location: '',
  type: 'Onsite',
  startDate: '',
  endDate: '',
  pointsText: ''
});

const save = () => {
  if (!form.value.title || !form.value.company) {
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
  form.value = { title: '', company: '', location: '', type: 'Onsite', startDate: '', endDate: '', pointsText: '' };
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
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
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

.form-field.full-width {
  grid-column: span 2;
}

.field-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.field-input, .field-select, .field-textarea {
  padding: 0.5rem 0.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.8rem;
  transition: all 0.2s;
  font-family: inherit;
}

.field-textarea {
  resize: vertical;
  line-height: 1.4;
}

.field-input:focus, .field-select:focus, .field-textarea:focus {
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
  
  .form-field.full-width {
    grid-column: span 1;
  }
}
</style>