<template>
  <div class="card mt-3">
    <div class="card-body">
      <h6 class="mb-3">Add New Project</h6>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Project Name</label>
          <input type="text" class="form-control" v-model="form.name" placeholder="e.g., E-commerce Platform">
        </div>
        <div class="col-12">
          <label class="form-label">Technologies Used</label>
          <input type="text" class="form-control" v-model="form.technologies" placeholder="e.g., Vue.js, Node.js, MongoDB">
        </div>
        <div class="col-12">
          <label class="form-label">Description (one bullet point per line, start with •)</label>
          <textarea 
            class="form-control" 
            rows="4" 
            v-model="form.pointsText" 
            placeholder="• Developed a full-stack e-commerce platform
• Implemented payment gateway integration
• Created responsive UI with Vue.js"
          ></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="button" class="btn btn-primary" @click="save">
            Save Project
          </button>
          <button type="button" class="btn btn-secondary" @click="cancel">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { showWarning } from '../../../../utils/notifications';

const emit = defineEmits(['save', 'cancel']);

const form = ref({
  name: '',
  technologies: '',
  pointsText: ''
});

const save = () => {
  if (!form.value.name) {
    showWarning('Missing Information', 'Please enter a project name.');
    return;
  }
  emit('save', { ...form.value });
  form.value = { name: '', technologies: '', pointsText: '' };
};

const cancel = () => {
  emit('cancel');
  form.value = { name: '', technologies: '', pointsText: '' };
};
</script>
