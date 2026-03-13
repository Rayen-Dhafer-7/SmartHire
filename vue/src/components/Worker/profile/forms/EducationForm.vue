<template>
  <div class="card mt-3">
    <div class="card-body">
      <h6 class="mb-3">Add New Education</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Degree/Diploma</label>
          <input type="text" class="form-control" v-model="form.degree" placeholder="e.g., Bachelor of Computer Science">
        </div>
        <div class="col-md-6">
          <label class="form-label">Institution</label>
          <input type="text" class="form-control" v-model="form.institution" placeholder="e.g., University of Technology">
        </div>
        <div class="col-md-6">
          <label class="form-label">Location</label>
          <input type="text" class="form-control" v-model="form.location" placeholder="e.g., New York, USA">
        </div>
        <div class="col-md-3">
          <label class="form-label">Start Year</label>
          <input type="text" class="form-control" v-model="form.startYear" placeholder="e.g., 2020">
        </div>
        <div class="col-md-3">
          <label class="form-label">End Year</label>
          <input type="text" class="form-control" v-model="form.endYear" placeholder="e.g., 2024 or Present">
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="button" class="btn btn-primary" @click="save">
            Save Education
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
  degree: '',
  institution: '',
  location: '',
  startYear: '',
  endYear: ''
});

const save = () => {
  if (!form.value.degree || !form.value.institution) {
    showWarning('Missing Information', 'Please fill in all required fields.');
    return;
  }
  emit('save', { ...form.value });
  form.value = { degree: '', institution: '', location: '', startYear: '', endYear: '' };
};

const cancel = () => {
  emit('cancel');
  form.value = { degree: '', institution: '', location: '', startYear: '', endYear: '' };
};
</script>
