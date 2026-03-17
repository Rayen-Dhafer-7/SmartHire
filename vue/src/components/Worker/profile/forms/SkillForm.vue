<template>
  <div class="skills-input-container mb-3">
    <div class="input-group">
      <input 
        type="text" 
        class="form-control" 
        v-model="skill" 
        placeholder="Add a skill (e.g., Vue.js) and press Enter"
        @keydown.enter.prevent="add"
      >
      <button class="btn btn-outline-primary" type="button" @click="add">
        <i class="bi bi-plus-lg"></i> Add
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const emit = defineEmits(['add']);

const skill = ref('');

const add = () => {
  if (skill.value.trim()) {
    emit('add', skill.value.trim());
    skill.value = '';
  }
};
</script>

<style scoped>
.skills-input-container {
  margin-bottom: 1.5rem;
}

.input-group {
  display: flex;
  gap: 0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.form-control {
  border: 1px solid var(--border-color);
  color: var(--text-main);
  font-size: 0.95rem;
  padding: 12px 14px;
  transition: all var(--transition-fast);
}

.form-control::placeholder {
  color: var(--text-muted);
}

.form-control:hover {
  border-color: var(--border-light);
  background-color: var(--bg-lighter);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: none;
  background-color: var(--white);
}

.btn {
  padding: 12px 20px;
  font-weight: 600;
  font-size: 0.95rem;
  border: none;
  transition: all var(--transition-fast);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-outline-primary {
  background-color: var(--primary-color);
  color: var(--white);
  border: 1px solid var(--primary-color);
}

.btn-outline-primary:hover {
  background-color: var(--primary-dark);
  border-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.btn-outline-primary:active {
  transform: translateY(0);
}

.bi {
  font-size: 1rem;
}
</style>
