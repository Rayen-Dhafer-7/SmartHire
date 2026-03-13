<template>
  <div class="card mt-3">
    <div class="card-body">
      <h6 class="mb-3">Add New Experience</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Job Title</label>
          <input type="text" class="form-control" v-model="form.title" placeholder="e.g., Frontend Developer">
        </div>
        <div class="col-md-6">
          <label class="form-label">Company</label>
          <input type="text" class="form-control" v-model="form.company" placeholder="e.g., Google">
        </div>
        <div class="col-md-6">
          <label class="form-label">Location</label>
          <input type="text" class="form-control" v-model="form.location" placeholder="e.g., New York, USA">
        </div>
        <div class="col-md-6">
          <label class="form-label">Type</label>
          <select class="form-select" v-model="form.type">
            <option value="Onsite">Onsite</option>
            <option value="Remote">Remote</option>
            <option value="Hybrid">Hybrid</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Start Date</label>
          <input type="text" class="form-control" v-model="form.startDate" placeholder="e.g., 2023/09">
        </div>
        <div class="col-md-6">
          <label class="form-label">End Date</label>
          <input type="text" class="form-control" v-model="form.endDate" placeholder="e.g., 2024/01 or Present">
        </div>
        <div class="col-12">
          <label class="form-label">Responsibilities (one per line, start with •)</label>
          <textarea 
            class="form-control" 
            rows="4" 
            v-model="form.pointsText" 
            placeholder="• Developed features using Vue.js
• Collaborated with team on UI design
• Implemented unit tests"
          ></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="button" class="btn btn-primary" @click="save">
            Save Experience
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
  title: '',
  company: '',
  location: '',
  type: 'Onsite',
  startDate: '',
  endDate: '',
  pointsText: ''
});

const save = () => {
  if (!form.value.title || !form.value.company) {
    showWarning('Missing Information', 'Please fill in all required fields.');
    return;
  }
  emit('save', { ...form.value });
  form.value = { title: '', company: '', location: '', type: 'Onsite', startDate: '', endDate: '', pointsText: '' };
};

const cancel = () => {
  emit('cancel');
  form.value = { title: '', company: '', location: '', type: 'Onsite', startDate: '', endDate: '', pointsText: '' };
};
</script>
