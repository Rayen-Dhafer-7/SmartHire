<template>
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <!-- Filter Row with Toggle Integrated -->
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small fw-bold">Job Title / Company</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" v-model="filters.keyword" placeholder="Search by title or company...">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold">Location</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
            <input type="text" class="form-control" v-model="filters.location" placeholder="City, Country...">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold">Job Type</label>
          <select class="form-select" v-model="filters.type">
            <option value="">All Types</option>
            <option value="Onsite">Onsite</option>
            <option value="Remote">Remote</option>
            <option value="Hybrid">Hybrid</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold">Skills</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-code-slash"></i></span>
            <input type="text" class="form-control" v-model="filters.skill" placeholder="e.g. Vue.js">
          </div>
        </div>
        <!-- Toggle Switch Column -->
        <div class="col-md-2">
          <label class="form-label small fw-bold d-none d-md-block">&nbsp;</label>
          <div class="d-flex align-items-center justify-content-end">
            <span class="me-2 text-muted" :class="{ 'fw-bold text-primary': !localShowMatchedOnly }">All Jobs</span>
            <div class="form-check form-switch">
              <input 
                class="form-check-input" 
                type="checkbox" 
                role="switch" 
                id="matchToggle"
                v-model="localShowMatchedOnly"
                @change="handleToggleChange"
              >
            </div>
            <span class="ms-2 text-muted" :class="{ 'fw-bold text-primary': localShowMatchedOnly }">Matched</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  filters: {
    type: Object,
    required: true
  },
  showMatchedOnly: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:showMatchedOnly', 'toggle-change']);

// Create a local copy of the prop
const localShowMatchedOnly = ref(props.showMatchedOnly);

// Watch for prop changes
watch(() => props.showMatchedOnly, (newValue) => {
  localShowMatchedOnly.value = newValue;
});

// Handle toggle change
const handleToggleChange = () => {
  emit('update:showMatchedOnly', localShowMatchedOnly.value);
  emit('toggle-change', localShowMatchedOnly.value);
};
</script>

<style scoped>
/* Modern Filter Card */
.card {
  background: var(--white);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-base);
}

.card:hover {
  box-shadow: var(--shadow-md);
  border-color: transparent;
}

.card-body {
  padding: 1.5rem !important;
}

/* Input Groups */
.input-group-text {
  border-right: none;
  color: var(--text-muted);
  background-color: var(--white);
  border: 1px solid var(--border-color);
  transition: all var(--transition-fast);
}

.input-group:focus-within .input-group-text {
  border-color: var(--primary-color);
  color: var(--primary-color);
}

.form-control {
  border: 1px solid var(--border-color);
  border-left: none;
  color: var(--text-main);
  font-size: 0.95rem;
  transition: all var(--transition-fast);
}

.form-control::placeholder {
  color: var(--text-muted);
}

.form-control:hover {
  border-color: var(--border-light);
  background-color: var(--bg-lighter);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(13, 124, 140, 0.1);
  background-color: var(--white);
}

/* Form Select */
.form-select {
  border: 1px solid var(--border-color);
  color: var(--text-main);
  font-size: 0.95rem;
  transition: all var(--transition-fast);
}

.form-select:hover {
  border-color: var(--border-light);
  background-color: var(--bg-lighter);
}

.form-select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(13, 124, 140, 0.1);
}

.form-select option {
  color: var(--text-main);
}

/* Form Label */
.form-label {
  color: var(--text-main);
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

/* Toggle Switch */
.form-check-input {
  width: 44px;
  height: 24px;
  border: 2px solid var(--border-color);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.form-check-input:checked {
  background-color: var(--primary-color);
  border-color: var(--primary-color);
}

.form-check-input:hover {
  border-color: var(--primary-color);
}

.form-check-input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(13, 124, 140, 0.2);
}

/* Toggle Labels */
.text-muted {
  color: var(--text-muted) !important;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all var(--transition-fast);
}

.text-primary {
  color: var(--primary-color) !important;
  font-weight: 600;
}

/* Icons */
.bi {
  margin-right: 4px;
}

/* Responsive */
@media (max-width: 768px) {
  .card-body {
    padding: 1rem !important;
  }
  
  .row {
    gap: 0.75rem !important;
  }
  
  .form-label {
    font-size: 0.8rem;
  }
}
</style>
