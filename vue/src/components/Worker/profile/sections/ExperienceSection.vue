<template>
  <div class="experience-section">
    <div class="section-header">
      <div class="section-title-wrapper">
        <div class="section-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
        </div>
        <h4 class="section-title">Work Experience</h4>
      </div>
      <button type="button" class="add-btn" @click="toggleForm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        {{ showForm ? 'Cancel' : 'Add Experience' }}
      </button>
    </div>
    
    <!-- Form appears right below the button -->
    <ExperienceForm 
      v-if="showForm" 
      @save="onSave" 
      @cancel="toggleForm" 
    />
    
    <div v-if="experienceList.length === 0 && !showForm" class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
      </svg>
      <p>No experience added yet</p>
      <span>Add your work experience</span>
    </div>
    
    <div class="experience-list">
      <div v-for="(exp, index) in experienceList" :key="index" class="experience-item">
        <div class="timeline-line"></div>
        <div class="item-content">
          <div class="item-header">
            <div>
              <h5 class="item-title">{{ exp.title }}</h5>
              <p class="item-company">{{ exp.company }} • {{ exp.location }}</p>
              <p class="item-date">{{ exp.start_date }} — {{ exp.end_date || 'Present' }}</p>
            </div>
            <button type="button" class="remove-btn" @click="remove(index)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="item-description" v-if="exp.description">
            <ul>
              <li v-for="(point, pIndex) in exp.description.split('\n')" :key="pIndex">
                {{ point.replace(/^•\s*/, '') }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ExperienceForm from '../forms/ExperienceForm.vue';
import { showConfirm } from '../../../../utils/notifications';

defineProps({
  experienceList: { type: Array, default: () => [] }
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
  showConfirm('Remove Experience?', 'Are you sure you want to remove this experience?', 'Yes, remove it', 'Cancel')
    .then((result) => {
      if (result.isConfirmed) emit('remove', index);
    });
};
</script>

<style scoped>
.experience-section {
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

.experience-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-top: 1rem;
}

.experience-item {
  display: flex;
  gap: 1.5rem;
  position: relative;
  padding-left: 1.5rem;
}

.timeline-line {
  position: absolute;
  left: 0;
  top: 8px;
  bottom: -24px;
  width: 2px;
  background: linear-gradient(180deg, #4f46e5, #cbd5e1);
}

.experience-item:last-child .timeline-line {
  display: none;
}

.experience-item::before {
  content: '';
  position: absolute;
  left: -4px;
  top: 8px;
  width: 10px;
  height: 10px;
  background: #4f46e5;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 0 2px #eef2ff;
}

.item-content {
  flex: 1;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.item-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 0.25rem;
}

.item-company {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0 0 0.25rem;
}

.item-date {
  font-size: 0.7rem;
  color: #94a3b8;
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

.item-description {
  margin-top: 0.75rem;
  padding-left: 0;
}

.item-description ul {
  margin: 0;
  padding-left: 1.25rem;
}

.item-description li {
  font-size: 0.8rem;
  color: #475569;
  margin-bottom: 0.25rem;
  line-height: 1.4;
}
</style>