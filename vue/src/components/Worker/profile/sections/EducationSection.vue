<template>
  <div class="education-section">
    <div class="section-header">
      <div class="section-title-wrapper">
        <div class="section-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 2 6 2 9 0v-5"/>
          </svg>
        </div>
        <h4 class="section-title">Education</h4>
      </div>
      <button type="button" class="add-btn" @click="toggleForm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        {{ showForm ? 'Cancel' : 'Add Education' }}
      </button>
    </div>
    
    <!-- Form appears right below the button -->
    <EducationForm 
      v-if="showForm" 
      @save="onSave" 
      @cancel="toggleForm" 
    />
    
    <div v-if="educationList.length === 0 && !showForm" class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
        <path d="M6 12v5c3 2 6 2 9 0v-5"/>
      </svg>
      <p>No education added yet</p>
      <span>Add your educational background</span>
    </div>
    
    <div class="education-list">
      <div v-for="(edu, index) in educationList" :key="index" class="education-item">
        <div class="item-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="1.5">
            <path d="M12 3L3 8l9 5 9-5-9-5z"/>
            <path d="M3 13l9 5 9-5"/>
          </svg>
        </div>
        <div class="item-content">
          <div class="item-header">
            <h5 class="item-title">{{ edu.degree }}</h5>
            <button type="button" class="remove-btn" @click="remove(index)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <p class="item-subtitle">{{ edu.institution }} • {{ edu.location }}</p>
          <p class="item-date">{{ edu.start_year }} — {{ edu.end_year || 'Present' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import EducationForm from '../forms/EducationForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  educationList: { type: Array, default: () => [] }
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
  showConfirm('Remove Education?', 'Are you sure you want to remove this education entry?', 'Yes, remove it', 'Cancel')
    .then((result) => {
      if (result.isConfirmed) emit('remove', index);
    });
};
</script>

<style scoped>
.education-section {
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
  color: #94a3b8;
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

.education-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
}

.education-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.education-item:hover {
  border-color: #cbd5e1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.item-icon {
  width: 48px;
  height: 48px;
  background: #eef2ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.item-content {
  flex: 1;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.25rem;
}

.item-title {
  font-size: 0.95rem;
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

.item-subtitle {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0 0 0.25rem;
}

.item-date {
  font-size: 0.7rem;
  color: #94a3b8;
  margin: 0;
}
</style>