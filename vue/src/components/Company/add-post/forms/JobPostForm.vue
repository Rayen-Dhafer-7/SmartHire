<template>
  <form @submit.prevent="submit">
    <div class="mb-3">
      <label class="form-label">Job Title <span class="text-danger">*</span></label>
      <input 
        type="text" 
        class="form-control" 
        v-model="form.title" 
        placeholder="e.g. Senior Vue.js Developer" 
        required 
        :disabled="isLoading"
      />
    </div>

    <div class="mb-3">
      <label class="form-label">Description <span class="text-danger">*</span></label>
      <textarea 
        class="form-control" 
        rows="5" 
        v-model="form.description" 
        required
        :disabled="isLoading"
      ></textarea>
    </div>

    <div class="grid-2">
      <div class="mb-3">
          <label class="form-label">Deadline</label>
          <input 
            type="date" 
            class="form-control" 
            v-model="form.deadline" 
            required 
            :min="minDate"
            :disabled="isLoading"
          />
      </div>
      <div class="mb-3">
          <label class="form-label">Post Date</label>
          <input 
            type="text" 
            class="form-control bg-light" 
            :value="currentDate" 
            readonly 
          />
      </div>
    </div>

    <div class="grid-3">
      <div class="mb-3">
        <label class="form-label">Job Type</label>
        <select class="form-select" v-model="form.type" :disabled="isLoading">
          <option value="Onsite">Onsite</option>
          <option value="Remote">Remote</option>
          <option value="Hybrid">Hybrid</option>
        </select>
      </div>
      <div class="mb-3">
          <label class="form-label">Workers Needed</label>
          <input 
            type="number" 
            class="form-control" 
            v-model="form.count" 
            min="1" 
            :disabled="isLoading"
          />
      </div>
      <div class="mb-3">
          <label class="form-label">Skills (comma separated)</label>
          <input 
            type="text" 
            class="form-control" 
            v-model="form.skills" 
            placeholder="React, Vue, Docker..." 
            :disabled="isLoading"
          />
      </div>
    </div>

    <div class="mt-4 text-end">
        <button 
          type="button" 
          class="btn btn-link text-muted me-2" 
          @click="$emit('cancel')"
          :disabled="isLoading"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="btn btn-primary"
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
          {{ isLoading ? 'Publishing...' : 'Publish Post' }}
        </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'cancel']);

const currentDate = computed(() => {
  const date = new Date();
  const month = date.getMonth() + 1;
  const day = date.getDate();
  const year = date.getFullYear();
  return `${month}/${day}/${year}`;
});

const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const form = ref({
  title: '',
  description: '',
  deadline: '',
  type: 'Onsite',
  count: 1,
  skills: ''
});

const submit = () => {
    emit('submit', { ...form.value });
};
</script>

<style scoped>
.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.text-end {
  text-align: right;
}

@media (max-width: 992px) {
  .grid-2,
  .grid-3 {
    grid-template-columns: 1fr;
  }
}
</style>