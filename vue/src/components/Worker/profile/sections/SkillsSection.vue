<template>
  <div class="skills-section">
    <div class="section-header">
      <div class="section-title-wrapper">
        <div class="section-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
          </svg>
        </div>
        <h4 class="section-title">Skills</h4>
      </div>
    </div>
    
    <!-- Skill Form appears right below the header -->
    <SkillForm @add="onAdd" />
    
    <div v-if="skills.length === 0" class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
      </svg>
      <p>No skills added yet</p>
      <span>Add your technical skills</span>
    </div>
    
    <div class="skills-grid">
      <div v-for="(skill, index) in skills" :key="index" class="skill-card">
        <span class="skill-name">{{ skill.name }}</span>
        <button type="button" class="remove-skill" @click="remove(index)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import SkillForm from '../forms/SkillForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  skills: { type: Array, default: () => [] }
});

const emit = defineEmits(['add', 'remove']);

const onAdd = (skillName) => {
  emit('add', skillName);
};

const remove = (index) => {
  showConfirm('Remove Skill?', 'Are you sure you want to remove this skill?', 'Yes, remove it', 'Cancel')
    .then((result) => {
      if (result.isConfirmed) emit('remove', index);
    });
};
</script>

<style scoped>
.skills-section {
  margin-bottom: 2rem;
  padding: 1rem 0;
}

.section-header {
  display: flex;
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

.skills-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 1rem;
}

.skill-card {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #eef2ff, #ffffff);
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.5rem 0.75rem;
  transition: all 0.2s ease;
}

.skill-card:hover {
  border-color: #4f46e5;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1);
}

.skill-name {
  font-size: 0.85rem;
  font-weight: 500;
  color: #334155;
}

.remove-skill {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 0;
  display: flex;
  align-items: center;
  transition: color 0.2s;
}

.remove-skill:hover {
  color: #ef4444;
}
</style>