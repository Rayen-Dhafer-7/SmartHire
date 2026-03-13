<template>
  <div class="card mt-3">
    <div class="card-body">
      <h6 class="mb-3">Add New Certification</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Certification Name</label>
          <input type="text" class="form-control" v-model="form.name" placeholder="e.g., AWS Certified Developer">
        </div>
        <div class="col-md-6">
          <label class="form-label">Issuer</label>
          <input type="text" class="form-control" v-model="form.issuer" placeholder="e.g., Amazon Web Services">
        </div>
        <div class="col-md-6">
          <label class="form-label">Date</label>
          <input type="text" class="form-control" v-model="form.date" placeholder="e.g., 2024/09">
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="button" class="btn btn-primary" @click="save">
            Save Certification
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
  issuer: '',
  date: ''
});

const save = () => {
  if (!form.value.name || !form.value.issuer) {
    showWarning('Missing Information', 'Please fill in all required fields.');
    return;
  }
  emit('save', { ...form.value });
  form.value = { name: '', issuer: '', date: '' };
};

const cancel = () => {
  emit('cancel');
  form.value = { name: '', issuer: '', date: '' };
};
</script>
