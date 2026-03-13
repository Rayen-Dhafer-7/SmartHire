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
.skills-tags-container {
  min-height: 50px;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  padding: 0.75rem;
  background-color: #f8f9fa;
}

.skill-tag {
  font-size: 0.875rem;
  padding: 0.4rem 0.75rem;
  border-radius: 0.375rem;
}

.skill-tag .btn-close {
  font-size: 0.5rem;
  padding: 0.3rem;
  margin-left: 0.25rem;
}

.btn-close-sm {
  width: 0.5em;
  height: 0.5em;
}
</style>
