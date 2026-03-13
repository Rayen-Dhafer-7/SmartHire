<template>
  <div class="mb-5">
    <div class="d-flex align-items-center gap-2 mb-4">
        <h3 class="fw-bold text-primary mb-0">{{ section.skill }}</h3>
        <div class="flex-grow-1 border-bottom ms-3"></div>
    </div>

    <!-- MCQs -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-light fw-bold py-3">Part 1: Multiple Choice</div>
        <div class="card-body">
            <div v-for="(q, qIndex) in section.mcqs" :key="'mcq-'+qIndex" class="mb-4 last-mb-0">
                <h6 class="fw-bold mb-3">{{ qIndex + 1 }}. {{ q.text }}</h6>
                <div class="d-flex flex-column gap-2">
                    <label v-for="(opt, oIndex) in q.options" :key="oIndex" class="option-label p-3 rounded border d-flex align-items-center gap-3 cursor-pointer">
                        <input type="radio" 
                               :name="`mcq-${section.skill}-${qIndex}`" 
                               :value="oIndex" 
                               :checked="answers.mcq[qIndex] === oIndex"
                               @change="updateAnswer('mcq', qIndex, oIndex)"
                               class="form-check-input mt-0">
                        <span>{{ opt }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Debugging -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-light fw-bold py-3">Part 2: Debugging & Reasoning</div>
        <div class="card-body">
            <div v-for="(q, qIndex) in section.debugging" :key="'debug-'+qIndex" class="mb-4 last-mb-0">
                <h6 class="fw-bold mb-3">{{ qIndex + 1 }}. {{ q.text }}</h6>
                <div class="bg-dark text-white p-3 rounded mb-3 font-monospace small" v-if="q.code">
                    <pre class="mb-0">{{ q.code }}</pre>
                </div>
                <textarea class="form-control" rows="3" 
                          :value="answers.debug[qIndex]"
                          @input="updateAnswer('debug', qIndex, $event.target.value)"
                          placeholder="Explain the issue or your reasoning..."></textarea>
            </div>
        </div>
    </div>

    <!-- Scenario -->
    <div class="card mb-5 border-0 shadow-sm">
        <div class="card-header bg-light fw-bold py-3">Part 3: Scenario Based</div>
        <div class="card-body">
            <div v-for="(q, qIndex) in section.scenario" :key="'scen-'+qIndex" class="mb-4 last-mb-0">
                <h6 class="fw-bold mb-3">{{ qIndex + 1 }}. {{ q.text }}</h6>
                <textarea class="form-control" rows="4" 
                          :value="answers.scenario[qIndex]"
                          @input="updateAnswer('scenario', qIndex, $event.target.value)"
                          placeholder="Describe your approach..."></textarea>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  section: {
    type: Object,
    required: true
  },
  answers: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update-answer']);

const updateAnswer = (type, qIndex, value) => {
    // type: 'mcq' | 'debug' | 'scenario'
    // This emits an event so parent can update the state
    // Or we rely on the object reference passed in props (Vue allow mutation of object internals, ensuring reactivity)
    // For cleaner data flow, we emit.
    emit('update-answer', { type, qIndex, value });
};
</script>

<style scoped>
.option-label {
    background-color: white;
    transition: all 0.2s;
    cursor: pointer;
}

.option-label:hover {
    background-color: #f3f4f6;
    border-color: #4f46e5 !important;
}

.option-label:has(input:checked) {
    background-color: #eef2ff;
    border-color: #4f46e5 !important;
    box-shadow: 0 0 0 1px #4f46e5;
}

.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.last-mb-0:last-child {
    margin-bottom: 0 !important;
}
</style>
