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
.input-group-text {
  border-right: none;
  color: #6c757d;
}

.form-control:focus {
  box-shadow: none;
  border-color: #4f46e5;
}

.form-control {
  border-left: none;
}

/* Fix for left border on focus when grouped */
.input-group .form-control:focus {
    z-index: 3;
    border-left: 1px solid #4f46e5;
    margin-left: -1px;
}

/* Toggle switch styling */
.form-switch .form-check-input {
  width: 2.5em;
  height: 1.25em;
  cursor: pointer;
  margin-top: 0;
}

.form-switch .form-check-input:checked {
  background-color: #4f46e5;
  border-color: #4f46e5;
}

.form-switch .form-check-input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
}

.text-primary {
  color: #4f46e5 !important;
}

/* Adjust column widths for better alignment */
.col-md-3:last-child {
  display: flex;
  justify-content: flex-end;
}

/* Ensure toggle aligns properly on mobile */
@media (max-width: 767px) {
  .col-md-2:last-child {
    margin-top: 10px;
  }
  
  .d-flex.align-items-center.justify-content-end {
    justify-content: flex-start !important;
  }
}
</style>