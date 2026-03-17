<template>
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Education</h5>
      <button type="button" class="btn btn-sm btn-outline-primary" @click="showForm = true">
        <i class="bi bi-plus"></i> Add Education
      </button>
    </div>
    
    <div v-if="educationList.length === 0" class="text-muted mb-3">
      No education added yet.
    </div>
    
    <div class="education-list">
      <div v-for="(edu, index) in educationList" :key="index" class="education-item card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1 d-flex justify-content-between align-items-center">
                <span class="fw-bold">{{ edu.degree }}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="fw-bold">{{ edu.start_year }} - {{ edu.end_year }}</span>
              </h6>
              <div class="text-muted">{{ edu.institution }} • {{ edu.location }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" @click="remove(index)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <EducationForm 
      v-if="showForm" 
      @save="onSave" 
      @cancel="showForm = false" 
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import EducationForm from '../forms/EducationForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  educationList: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['add', 'remove']);
const showForm = ref(false);

const onSave = (data) => {
  emit('add', data);
  showForm.value = false;
};

const remove = (index) => {
  showConfirm('Remove Education?', 'Are you sure you want to remove this education entry?', 'Yes, remove it', 'Cancel').then((result) => {
    if (result.isConfirmed) {
      emit('remove', index);
    }
  });
};
</script>

<style scoped>
.education-item {
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
  transition: all var(--transition-base);
  box-shadow: var(--shadow-sm);
}

.education-item:hover {
  box-shadow: var(--shadow-md);
  border-color: transparent;
  transform: translateY(-2px);
}

.education-item .card-body {
  padding: 1.5rem;
}

h5 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 1.5rem;
}

h6 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-main);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

h6 span:first-child {
  flex: 1;
}

h6 span:last-child {
  white-space: nowrap;
  color: var(--primary-color);
  font-weight: 700;
}

.text-muted {
  color: var(--text-muted) !important;
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

.btn-outline-primary {
  color: var(--primary-color);
  border: 1px solid var(--primary-color);
  background-color: transparent;
  transition: all var(--transition-fast);
}

.btn-outline-primary:hover {
  background-color: var(--primary-color);
  color: var(--white);
  transform: translateY(-2px);
}

.btn-outline-danger {
  color: var(--error);
  border: 1px solid var(--error);
  background-color: transparent;
  transition: all var(--transition-fast);
}

.btn-outline-danger:hover {
  background-color: var(--error);
  color: var(--white);
  transform: translateY(-2px);
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  h6 {
    flex-direction: column;
    align-items: flex-start;
  }
  
  h6 span:last-child {
    width: 100%;
  }
}
</style>
