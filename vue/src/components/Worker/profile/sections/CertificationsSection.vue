<template>
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Certifications</h5>
      <button type="button" class="btn btn-sm btn-outline-primary" @click="showForm = true">
        <i class="bi bi-plus"></i> Add Certification
      </button>
    </div>
    
    <div v-if="certificationsList.length === 0" class="text-muted mb-3">
      No certifications added yet.
    </div>
    
    <div class="certifications-list">
      <div v-for="(cert, index) in certificationsList" :key="index" class="certification-item card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-1">{{ cert.name }}</h6>
              <div class="text-muted">{{ cert.issue_date || cert.date }} <br>• {{ cert.issuer }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" @click="remove(index)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <CertificationForm 
      v-if="showForm" 
      @save="onSave" 
      @cancel="showForm = false" 
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import CertificationForm from '../forms/CertificationForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  certificationsList: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['add', 'remove']);
const showForm = ref(false);

const onSave = (data) => {
  emit('add', data);
  showForm.value = false;
};

const remove = (index) => {
  showConfirm('Remove Certification?', 'Are you sure you want to remove this certification?', 'Yes, remove it', 'Cancel').then((result) => {
    if (result.isConfirmed) {
      emit('remove', index);
    }
  });
};
</script>

<style scoped>
.certification-item {
  border-left: 4px solid #4f46e5;
}
.certification-item .card-body {
  padding: 1rem;
}
</style>
