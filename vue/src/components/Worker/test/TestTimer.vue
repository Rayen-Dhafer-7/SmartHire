<template>
  <div class="timer-display" :class="{ 'text-danger': timeRemaining < 120 }">
    <i class="bi bi-clock-history me-2"></i>
    <span class="fs-4 fw-bold font-monospace">{{ formattedTime }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  timeRemaining: {
    type: Number,
    required: true
  }
});

const formattedTime = computed(() => {
    const min = Math.floor(props.timeRemaining / 60);
    const sec = props.timeRemaining % 60;
    return `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
});
</script>

<style scoped>
.timer-display {
    color: #1e293b;
    transition: color 0.3s;
}
.text-danger {
    color: #dc3545 !important;
}
</style>
