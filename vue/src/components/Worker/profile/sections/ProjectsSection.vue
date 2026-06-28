<template>
  <div class="projects-section">
    <div class="section-header">
      <div class="section-title-wrapper">
        <div class="section-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
          </svg>
        </div>
        <h4 class="section-title">Projects</h4>
      </div>
      <button type="button" class="add-btn" @click="toggleForm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        {{ showForm ? 'Cancel' : 'Add Project' }}
      </button>
    </div>
    
    <!-- Form appears right below the button -->
    <ProjectForm 
      v-if="showForm" 
      @save="onSave" 
      @cancel="toggleForm" 
    />
    
    <div v-if="projectsList.length === 0 && !showForm" class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
      </svg>
      <p>No projects added yet</p>
      <span>Showcase your work</span>
    </div>
    
    <div class="projects-list">
      <div v-for="(project, index) in projectsList" :key="index" class="project-card">
        <div class="project-header">
          <div class="project-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
          </div>
          <h5 class="project-title">{{ project.project_name || project.name }}</h5>
          <button type="button" class="remove-btn" @click="remove(index)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="project-tech">
          <span v-for="tech in (project.technologies || [])" :key="tech" class="tech-tag">
            {{ tech }}
          </span>
        </div>
        <div class="project-description">
          <ul>
            <li v-for="(point, pIndex) in project.points" :key="pIndex">{{ point }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ProjectForm from '../forms/ProjectForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  projectsList: { type: Array, default: () => [] }
});

const emit = defineEmits(['add', 'remove']);
const showForm = ref(false);

const toggleForm = () => {
  showForm.value = !showForm.value;
};

const onSave = (data) => {
  emit('add', data);
  showForm.value = false;
};

const remove = (index) => {
  showConfirm('Remove Project?', 'Are you sure you want to remove this project?', 'Yes, remove it', 'Cancel')
    .then((result) => {
      if (result.isConfirmed) emit('remove', index);
    });
};
</script>

<style scoped>
.projects-section {
  margin-bottom: 2rem;
  padding: 1rem 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-title-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-icon {
  width: 32px;
  height: 32px;
  background: #eef2ff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4f46e5;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.add-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 500;
  color: #4f46e5;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-btn:hover {
  background: #eef2ff;
  border-color: #4f46e5;
  transform: translateY(-1px);
}

.empty-state {
  text-align: center;
  padding: 2rem;
  background: #f8fafc;
  border-radius: 16px;
  border: 1px dashed #e2e8f0;
  margin-top: 1rem;
}

.empty-state svg {
  margin-bottom: 0.5rem;
}

.empty-state p {
  font-weight: 500;
  color: #334155;
  margin: 0;
}

.empty-state span {
  font-size: 0.75rem;
  color: #94a3b8;
}

.projects-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
}

.project-card {
  background: white;
  border-radius: 16px;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.project-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.project-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.project-icon {
  width: 32px;
  height: 32px;
  background: #eef2ff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4f46e5;
}

.project-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
  flex: 1;
}

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 0.25rem;
  border-radius: 6px;
  transition: all 0.2s;
}

.remove-btn:hover {
  color: #ef4444;
  background: #fef2f2;
}

.project-tech {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.tech-tag {
  background: #f1f5f9;
  color: #334155;
  padding: 0.25rem 0.625rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 500;
}

.project-description ul {
  margin: 0;
  padding-left: 1.25rem;
}

.project-description li {
  font-size: 0.8rem;
  color: #475569;
  margin-bottom: 0.25rem;
  line-height: 1.4;
}
</style>