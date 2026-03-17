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
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
  transition: all var(--transition-base);
  box-shadow: var(--shadow-sm);
}

.experience-item:hover {
  box-shadow: var(--shadow-md);
  border-color: transparent;
  transform: translateY(-2px);
}

.card-body {
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
  margin-bottom: 0.5rem;
}

.text-muted {
  color: var(--text-muted) !important;
  font-size: 0.9rem;
}

ul {
  margin-left: 1.5rem;
  color: var(--text-gray);
  line-height: 1.6;
}

ul li {
  margin-bottom: 0.5rem;
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

@media (max-width: 768px) {
  .card-body {
    padding: 1rem;
  }
  
  h6 {
    font-size: 0.95rem;
  }
}
</style>
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
