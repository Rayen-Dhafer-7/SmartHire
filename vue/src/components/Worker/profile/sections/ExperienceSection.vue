<template>
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Experience</h5>
      <button type="button" class="btn btn-sm btn-outline-primary" @click="showForm = true">
        <i class="bi bi-plus"></i> Add Experience
      </button>
    </div>
    
    <div v-if="experienceList.length === 0" class="text-muted mb-3">
      No experience added yet.
    </div>
    
    <div class="experience-list">
      <div v-for="(exp, index) in experienceList" :key="index" class="experience-item card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <h6 class="mb-1 fw-bold">{{ exp.title }}</h6>
              <div class="text-muted">
                {{ exp.company }} • {{ exp.location }} • {{ exp.employment_type }}
              </div>
              <div class="text-muted">
                {{ exp.start_date }} - {{ exp.end_date }}
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" @click="remove(index)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
          <ul class="mb-0">
            <li v-for="(point, pIndex) in (exp.description || '').split('\n')" :key="pIndex">
              {{ point.replace(/^•\s*/, '') }}
            </li>
          </ul>
        </div>
      </div>
    </div>
    
    <ExperienceForm 
      v-if="showForm" 
      @save="onSave" 
      @cancel="showForm = false" 
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ExperienceForm from '../forms/ExperienceForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  experienceList: {
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
  showConfirm('Remove Experience?', 'Are you sure you want to remove this experience?', 'Yes, remove it', 'Cancel').then((result) => {
    if (result.isConfirmed) {
      emit('remove', index);
    }
  });
};
</script>

<style scoped>
.experience-item {
  border-left: 4px solid #4f46e5;
}
.experience-item .card-body {
  padding: 1rem;
}
.experience-item ul {
  padding-left: 1.5rem;
  margin-bottom: 0;
}
.experience-item li {
  margin-bottom: 0.25rem;
  white-space: pre-line;
}
</style>
