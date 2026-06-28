<template>
  <div class="certifications-section">
    <div class="section-header">
      <div class="section-title-wrapper">
        <div class="section-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 15v2m-6-4h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2zm10-10v4a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2z"/>
          </svg>
        </div>
        <h4 class="section-title">Certifications</h4>
      </div>
      <button type="button" class="add-btn" @click="toggleForm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        {{ showForm ? 'Cancel' : 'Add Certification' }}
      </button>
    </div>
    
    <!-- Form appears right below the button -->
    <CertificationForm 
      v-if="showForm" 
      @save="onSave" 
      @cancel="toggleForm" 
    />
    
    <div v-if="certificationsList.length === 0 && !showForm" class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
        <path d="M12 15v2m-6-4h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2zm10-10v4a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2z"/>
      </svg>
      <p>No certifications added yet</p>
      <span>Add your professional certifications</span>
    </div>
    
    <div class="certifications-list">
      <div v-for="(cert, index) in certificationsList" :key="index" class="certification-item">
        <div class="cert-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </div>
        <div class="cert-content">
          <div class="cert-header">
            <h5 class="cert-name">{{ cert.name }}</h5>
            <button type="button" class="remove-btn" @click="remove(index)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <p class="cert-details">{{ cert.issuer }} • {{ cert.issue_date || cert.date }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import CertificationForm from '../forms/CertificationForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  certificationsList: { type: Array, default: () => [] }
});

const emit = defineEmits(['add', 'remove']);
const showForm = ref(false);

const toggleForm = () => {
  showForm.value = !showForm.value;
};

const onSave = (data) => {
  emit('add', data);
  showForm.value = false;
};

const remove = (index) => {
  showConfirm('Remove Certification?', 'Are you sure you want to remove this certification?', 'Yes, remove it', 'Cancel')
    .then((result) => {
      if (result.isConfirmed) emit('remove', index);
    });
};
</script>

<style scoped>
.certifications-section {
  margin-bottom: 2rem;
  padding: 1rem 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
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

.add-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 500;
  color: #4f46e5;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-btn:hover {
  background: #eef2ff;
  border-color: #4f46e5;
  transform: translateY(-1px);
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

.certifications-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1rem;
}

.certification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.certification-item:hover {
  border-color: #cbd5e1;
  background: #fefaf5;
}

.cert-icon {
  width: 40px;
  height: 40px;
  background: #fffbeb;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.cert-content {
  flex: 1;
}

.cert-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.25rem;
}

.cert-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 0.25rem;
  border-radius: 6px;
  transition: all 0.2s;
}

.remove-btn:hover {
  color: #ef4444;
  background: #fef2f2;
}

.cert-details {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0;
}
</style>