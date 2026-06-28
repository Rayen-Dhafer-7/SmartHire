<template>
  <div class="filters-card">
    <div class="filters-header">
      <div class="header-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="22 3 2 3 10 13 10 21 14 18 14 13 22 3"/>
        </svg>
      </div>
      <h4 class="filters-title">Find Your Next Opportunity</h4>
    </div>
    
    <div class="filters-grid">
      <div class="filter-group">
        <label class="filter-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          Job Title / Company
        </label>
        <input 
          type="text" 
          class="filter-input" 
          v-model="filters.keyword" 
          placeholder="Search by title or company..."
        />
      </div>
      
      <div class="filter-group">
        <label class="filter-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          Location
        </label>
        <input 
          type="text" 
          class="filter-input" 
          v-model="filters.location" 
          placeholder="City, Country..."
        />
      </div>
      
      <div class="filter-group">
        <label class="filter-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
          Job Type
        </label>
        <select class="filter-select" v-model="filters.type">
          <option value="">All Types</option>
          <option value="Onsite">🏢 Onsite</option>
          <option value="Remote">🏠 Remote</option>
          <option value="Hybrid">🔄 Hybrid</option>
        </select>
      </div>
      
      <div class="filter-group">
        <label class="filter-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
          </svg>
          Skills
        </label>
        <input 
          type="text" 
          class="filter-input" 
          v-model="filters.skill" 
          placeholder="e.g., Vue.js, Python..."
        />
      </div>
    </div>
    
    <div class="toggle-section">
      <div class="toggle-label">
        <span>Show only jobs that match my profile</span>
        <span class="match-badge" v-if="localShowMatchedOnly">AI Matched</span>
      </div>
      <div class="toggle-switch">
        <span class="toggle-text" :class="{ active: !localShowMatchedOnly }">All Jobs</span>
        <label class="switch">
          <input 
            type="checkbox" 
            v-model="localShowMatchedOnly"
            @change="handleToggleChange"
          >
          <span class="slider round"></span>
        </label>
        <span class="toggle-text" :class="{ active: localShowMatchedOnly }">Matched</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  filters: { type: Object, required: true },
  showMatchedOnly: { type: Boolean, default: false }
});

const emit = defineEmits(['update:showMatchedOnly', 'toggle-change']);

const localShowMatchedOnly = ref(props.showMatchedOnly);

watch(() => props.showMatchedOnly, (newValue) => {
  localShowMatchedOnly.value = newValue;
});

const handleToggleChange = () => {
  emit('update:showMatchedOnly', localShowMatchedOnly.value);
  emit('toggle-change', localShowMatchedOnly.value);
};
</script>

<style scoped>
.filters-card {
  background: white;
  border-radius: 20px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.filters-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border-color: #cbd5e1;
}

.filters-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.header-icon {
  width: 32px;
  height: 32px;
  background: #eef2ff;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4f46e5;
}

.filters-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.filter-label svg {
  stroke-width: 1.5;
}

.filter-input, .filter-select {
  padding: 0.625rem 0.875rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  background: white;
  font-family: inherit;
}

.filter-input:focus, .filter-select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.filter-select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.875rem center;
  padding-right: 2rem;
}

.toggle-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
  flex-wrap: wrap;
  gap: 1rem;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: #334155;
  font-weight: 500;
}

.match-badge {
  background: #eef2ff;
  color: #4f46e5;
  padding: 0.25rem 0.625rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
}

.toggle-switch {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.toggle-text {
  font-size: 0.8rem;
  color: #94a3b8;
  transition: color 0.2s ease;
}

.toggle-text.active {
  color: #4f46e5;
  font-weight: 600;
}

.switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 28px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #e2e8f0;
  transition: 0.3s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

input:checked + .slider {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
}

input:checked + .slider:before {
  transform: translateX(24px);
}

.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

@media (max-width: 992px) {
  .filters-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .filters-card {
    padding: 1rem;
  }
  
  .filters-grid {
    grid-template-columns: 1fr;
  }
  
  .toggle-section {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>