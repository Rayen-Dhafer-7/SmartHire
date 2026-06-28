<template>
  <div class="form-card">
    <div class="form-header">
      <div class="form-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
        </svg>
      </div>
      <h6 class="form-title">Add Project</h6>
    </div>
    <div class="form-body">
      <div class="form-grid">
        <div class="form-field full-width">
          <label class="field-label">Project Name *</label>
          <input type="text" class="field-input" v-model="form.name" placeholder="e.g., E-commerce Platform">
        </div>
        <div class="form-field full-width">
          <label class="field-label">Technologies Used</label>
          <input type="text" class="field-input" v-model="form.technologies" placeholder="e.g., Vue.js, Node.js, MongoDB">
        </div>
        <div class="form-field full-width">
          <label class="field-label">Description (one bullet point per line, start with •)</label>
          <textarea class="field-textarea" rows="5" v-model="form.pointsText" placeholder="• Developed a full-stack e-commerce platform&#10;• Implemented payment gateway integration&#10;• Created responsive UI with Vue.js"></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn-cancel" @click="cancel">Cancel</button>
        <button type="button" class="btn-save" @click="save">Save Project</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { showWarning } from '../../../../utils/notifications';

const emit = defineEmits(['save', 'cancel']);

const form = ref({
  name: '',
  technologies: '',
  pointsText: ''
});

const save = () => {
  if (!form.value.name) {
    showWarning('Missing Information', 'Please enter a project name.');
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
  form.value = { name: '', technologies: '', pointsText: '' };
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
  display: flex;
  flex-direction: column;
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

.field-input, .field-textarea {
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

.field-input:focus, .field-textarea:focus {
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
</style>