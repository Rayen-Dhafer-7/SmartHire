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
.project-item {
  border-left: 4px solid #4f46e5;
}
.project-item .card-body {
  padding: 1rem;
}
.project-item ul {
  padding-left: 1.5rem;
  margin-bottom: 0;
}
.project-item li {
  margin-bottom: 0.25rem;
  white-space: pre-line;
}
</style>
