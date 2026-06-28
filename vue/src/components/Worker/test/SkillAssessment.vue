<template>
  <div class="skill-assessment">
    <div class="skill-header">
      <div class="skill-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
      </div>
      <h2 class="skill-title">{{ section.skill }}</h2>
    </div>

    <!-- MCQs Section -->
    <div class="question-section">
      <div class="section-header">
        <div class="section-badge">Part 1</div>
        <h3 class="section-title">Multiple Choice</h3>
      </div>
      
      <div class="questions-list">
        <div v-for="(q, qIndex) in section.mcqs" :key="'mcq-'+qIndex" class="question-card">
          <div class="question-number">{{ qIndex + 1 }}</div>
          <div class="question-content">
            <p class="question-text">{{ q.text }}</p>
            <div class="options-list">
              <label v-for="(opt, oIndex) in q.options" :key="oIndex" class="option-item">
                <input type="radio" 
                       :name="`mcq-${section.skill}-${qIndex}`" 
                       :value="oIndex" 
                       :checked="answers.mcq[qIndex] === oIndex"
                       @change="updateAnswer('mcq', qIndex, oIndex)">
                <span class="option-marker">{{ String.fromCharCode(65 + oIndex) }}</span>
                <span class="option-text">{{ opt }}</span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Debugging Section -->
    <div class="question-section">
      <div class="section-header">
        <div class="section-badge">Part 2</div>
        <h3 class="section-title">Debugging & Reasoning</h3>
      </div>
      
      <div class="questions-list">
        <div v-for="(q, qIndex) in section.debugging" :key="'debug-'+qIndex" class="question-card">
          <div class="question-number">{{ qIndex + 1 }}</div>
          <div class="question-content">
            <p class="question-text">{{ q.text }}</p>
            <div v-if="q.code" class="code-block">
              <pre><code>{{ q.code }}</code></pre>
            </div>
            <textarea class="answer-input" 
                      rows="4"
                      :value="answers.debug[qIndex]"
                      @input="updateAnswer('debug', qIndex, $event.target.value)"
                      placeholder="Explain the issue or your reasoning..."></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Scenario Section -->
    <div class="question-section">
      <div class="section-header">
        <div class="section-badge">Part 3</div>
        <h3 class="section-title">Scenario Based</h3>
      </div>
      
      <div class="questions-list">
        <div v-for="(q, qIndex) in section.scenario" :key="'scen-'+qIndex" class="question-card">
          <div class="question-number">{{ qIndex + 1 }}</div>
          <div class="question-content">
            <p class="question-text">{{ q.text }}</p>
            <textarea class="answer-input" 
                      rows="5"
                      :value="answers.scenario[qIndex]"
                      @input="updateAnswer('scenario', qIndex, $event.target.value)"
                      placeholder="Describe your approach..."></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  section: { type: Object, required: true },
  answers: { type: Object, required: true }
});

const emit = defineEmits(['update-answer']);

const updateAnswer = (type, qIndex, value) => {
  emit('update-answer', { type, qIndex, value });
};
</script>

<style scoped>
.skill-assessment {
  margin-bottom: 3rem;
}

.skill-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e2e8f0;
}

.skill-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #eef2ff, #ffffff);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4f46e5;
  border: 1px solid #e2e8f0;
}

.skill-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.5px;
}

.question-section {
  margin-bottom: 2.5rem;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.section-badge {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #334155;
  margin: 0;
}

.questions-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.question-card {
  display: flex;
  gap: 1rem;
  background: white;
  border-radius: 16px;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.question-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.question-number {
  width: 32px;
  height: 32px;
  background: #eef2ff;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #4f46e5;
  flex-shrink: 0;
}

.question-content {
  flex: 1;
}

.question-text {
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 1rem;
  line-height: 1.5;
}

.options-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.option-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: white;
}

.option-item:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.option-item:has(input:checked) {
  background: #eef2ff;
  border-color: #4f46e5;
}

.option-marker {
  width: 28px;
  height: 28px;
  background: #f1f5f9;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  color: #475569;
  transition: all 0.2s ease;
}

.option-item:has(input:checked) .option-marker {
  background: #4f46e5;
  color: white;
}

.option-text {
  flex: 1;
  color: #334155;
  font-size: 0.875rem;
}

input[type="radio"] {
  display: none;
}

.code-block {
  background: #1e293b;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1rem;
  overflow-x: auto;
}

.code-block pre {
  margin: 0;
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 0.8rem;
  color: #e2e8f0;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.answer-input {
  width: 100%;
  padding: 0.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  font-family: inherit;
  resize: vertical;
  transition: all 0.2s ease;
}

.answer-input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

@media (max-width: 768px) {
  .skill-title {
    font-size: 1.25rem;
  }
  
  .question-card {
    flex-direction: column;
  }
  
  .question-number {
    align-self: flex-start;
  }
}
</style>