<template>
  <div class="timer-container">
    <div class="timer-icon" :class="{ 'timer-warning': timeRemaining < 120 }">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
    </div>
    <div class="timer-display" :class="{ 'timer-warning': timeRemaining < 120 }">
      <span class="timer-hours" v-if="hours > 0">{{ hours }}:</span>
      <span class="timer-minutes">{{ minutes }}</span>
      <span class="timer-separator">:</span>
      <span class="timer-seconds">{{ seconds }}</span>
    </div>
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

const hours = computed(() => Math.floor(props.timeRemaining / 3600));
const minutes = computed(() => Math.floor((props.timeRemaining % 3600) / 60).toString().padStart(2, '0'));
const seconds = computed(() => (props.timeRemaining % 60).toString().padStart(2, '0'));
</script>

<style scoped>
.timer-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #f8fafc;
  padding: 0.5rem 1rem;
  border-radius: 40px;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.timer-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4f46e5;
}

.timer-icon.timer-warning {
  color: #ef4444;
  animation: pulse 1s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

.timer-display {
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: 1px;
}

.timer-display.timer-warning {
  color: #ef4444;
  animation: pulse 1s ease-in-out infinite;
}

.timer-hours,
.timer-minutes,
.timer-seconds {
  font-weight: 700;
}

.timer-separator {
  margin: 0 2px;
}
</style>