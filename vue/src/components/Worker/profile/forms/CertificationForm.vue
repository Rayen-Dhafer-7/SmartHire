<template>
  <div class="form-card">
    <div class="form-header">
      <div class="form-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
      </div>
      <h6 class="form-title">Add Certification</h6>
    </div>
    <div class="form-body">
      <div class="form-grid">
        <div class="form-field">
          <label class="field-label">Certification Name *</label>
          <input type="text" class="field-input" v-model="form.name" placeholder="e.g., AWS Certified Developer">
        </div>
        <div class="form-field">
          <label class="field-label">Issuer *</label>
          <input type="text" class="field-input" v-model="form.issuer" placeholder="e.g., Amazon Web Services">
        </div>
        <div class="form-field">
          <label class="field-label">Date</label>
          <input type="text" class="field-input" v-model="form.date" placeholder="e.g., 2024/09">
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn-cancel" @click="cancel">Cancel</button>
        <button type="button" class="btn-save" @click="save">Save Certification</button>
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
  issuer: '',
  date: ''
});

const save = () => {
  if (!form.value.name || !form.value.issuer) {
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
  form.value = { name: '', issuer: '', date: '' };
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
  background: #fffbeb;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f59e0b;
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
  border-color: #f59e0b;
  box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.1);
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
}
</style>