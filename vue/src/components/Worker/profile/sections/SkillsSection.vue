<template>
  <div class="mb-4">
    <h5 class="mb-0">Skills</h5>
    <SkillForm @add="onAdd" />
    
    <div class="skills-tags-container">
      <div 
        v-for="(skill, index) in skills" 
        :key="index" 
        class="skill-tag badge bg-primary d-inline-flex align-items-center gap-1 me-2 mb-2 p-2"
      >
        {{ skill.name }}
        <button 
          type="button" 
          class="btn-close btn-close-white btn-close-sm" 
          @click="remove(index)"
          aria-label="Remove"
        ></button>
      </div>
      <div v-if="skills.length === 0" class="text-muted">
        No skills added yet. Add some skills to showcase your expertise.
      </div>
    </div>
  </div>
</template>

<script setup>
import SkillForm from '../forms/SkillForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  skills: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['add', 'remove']);

const onAdd = (skillName) => {
  emit('add', skillName);
};

const remove = (index) => {
  showConfirm('Remove Skill?', 'Are you sure you want to remove this skill?', 'Yes, remove it', 'Cancel').then((result) => {
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

.skills-tags-container {
  min-height: 50px;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1rem;
  background-color: var(--bg-light);
  transition: all var(--transition-base);
}

.skills-tags-container:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow-sm);
}

.skill-tag {
  font-size: 0.85rem;
  padding: 8px 12px;
  border-radius: 6px;
  background: linear-gradient(135deg, rgba(13, 124, 140, 0.1) 0%, rgba(13, 124, 140, 0.05) 100%);
  color: var(--primary-color);
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-right: 0.5rem;
  margin-bottom: 0.5rem;
  transition: all var(--transition-fast);
}

.skill-tag:hover {
  background: linear-gradient(135deg, rgba(13, 124, 140, 0.15) 0%, rgba(13, 124, 140, 0.1) 100%);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.skill-tag .btn-close {
  font-size: 0.6rem;
  padding: 0.2rem;
  margin-left: 0.25rem;
  opacity: 0.7;
  transition: opacity var(--transition-fast);
}

.skill-tag .btn-close:hover {
  opacity: 1;
}

.btn-close-sm {
  width: 0.6em;
  height: 0.6em;
}

.text-muted {
  color: var(--text-muted) !important;
  font-size: 0.9rem;
}
</style>
