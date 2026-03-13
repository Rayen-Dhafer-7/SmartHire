<template>
  <div class="card post-card" :class="{ 'expired': variant === 'expired' }" @click="$emit('click')">
    <div class="d-flex justify-content-between mb-2">
      <h4 class="mb-0" :class="variant === 'active' ? 'text-primary' : 'text-secondary'">
        {{ post.title }}
      </h4>
      <span class="badge" :class="variant === 'active' ? 'badge-green' : 'badge-gray'">
        {{ variant === 'active' ? 'Active' : 'Expired' }}
      </span>
    </div>
    <p class="text-muted small mb-3">Posted on: {{ post.date }} • Deadline: {{ post.deadline }}</p>
    <p class="mb-3 text-secondary">{{ post.description }}</p>
    
    <div class="d-flex gap-2 flex-wrap">
       <span 
         v-for="skill in post.skills" 
         :key="skill" 
         class="skill-tag"
         :style="variant === 'expired' ? 'background: #f3f4f6; color: #6b7280;' : ''"
       >
         {{ skill }}
       </span>
    </div>

    <div  class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
      <div class="small fw-bold">{{ post.applicants || 0 }} Applicants</div>
      <button class="btn btn-sm btn-outline-primary">View Rankings</button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  post: {
    type: Object,
    required: true
  },
  variant: {
    type: String,
    default: 'active',
    validator: (value) => ['active', 'expired'].includes(value)
  }
});

defineEmits(['click']);
</script>

<style scoped>
.post-card {
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
  border-left: 4px solid transparent;
}

/* Active Variant */
.post-card:not(.expired) {
  border-left-color: #4f46e5;
}

.post-card:not(.expired):hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Expired Variant */
.post-card.expired {
  background-color: #fafafa;
}

.post-card.expired:hover {
  opacity: 0.8;
}

.badge-green {
  background-color: #d1fae5;
  color: #065f46;
}

.badge-gray {
  background-color: #f3f4f6;
  color: #374151;
}

.skill-tag {
  background-color: #eef2ff;
  color: #4f46e5;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
}

.flex-wrap {
  flex-wrap: wrap;
}
</style>