<template>
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Projects</h5>
      <button type="button" class="btn btn-sm btn-outline-primary" @click="showForm = true">
        <i class="bi bi-plus"></i> Add Project
      </button>
    </div>
    
    <div v-if="projectsList.length === 0" class="text-muted mb-3">
      No projects added yet.
    </div>
    
    <div class="projects-list">
      <div v-for="(project, index) in projectsList" :key="index" class="project-item card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <h6 class="mb-1 fw-bold">{{ project.project_name || project.name }}</h6>
              <div class="text-muted">Technologies: {{ Array.isArray(project.technologies) ? project.technologies.join(', ') : project.technologies }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" @click="remove(index)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
          <ul class="mb-0">
            <li v-for="(point, pointIndex) in project.points" :key="pointIndex">{{ point }}</li>
          </ul>
        </div>
      </div>
    </div>
    
    <ProjectForm 
      v-if="showForm" 
      @save="onSave" 
      @cancel="showForm = false" 
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ProjectForm from '../forms/ProjectForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  projectsList: {
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
  showConfirm('Remove Project?', 'Are you sure you want to remove this project?', 'Yes, remove it', 'Cancel').then((result) => {
    if (result.isConfirmed) {
      emit('remove', index);
    }
  });
};
</script>

<style scoped>
h5 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 1.5rem;
}

.project-item {
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
  transition: all var(--transition-base);
  box-shadow: var(--shadow-sm);
}

.project-item:hover {
  box-shadow: var(--shadow-md);
  border-color: transparent;
  transform: translateY(-2px);
}

.project-item .card-body {
  padding: 1.5rem;
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
  margin-bottom: 1rem;
}

.project-item ul {
  padding-left: 1.5rem;
  margin-bottom: 0;
  color: var(--text-gray);
  line-height: 1.6;
}

.project-item ul li {
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

.btn-sm {
  padding: 6px 12px;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .project-item .card-body {
    padding: 1rem;
  }
}
.project-item li {
  margin-bottom: 0.25rem;
  white-space: pre-line;
}
</style>
