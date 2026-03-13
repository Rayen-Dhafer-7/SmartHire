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
