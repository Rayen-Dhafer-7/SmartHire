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
  border-left: 4px solid #4f46e5;
}
.education-item .card-body {
  padding: 1rem;
}
</style>
