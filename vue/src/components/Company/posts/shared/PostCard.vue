<template>
  <div class="card post-card" :class="{ 'expired': variant === 'expired' }" @click="$emit('click')">
    <div class="card-content">
      <div class="card-header">
        <div class="title-section">
          <div class="title-icon" :class="variant === 'active' ? 'active-icon' : 'expired-icon'">
            <svg v-if="variant === 'active'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
<svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
  <circle cx="12" cy="12" r="10"/>
  <line x1="12" y1="8" x2="12" y2="12"/>
  <line x1="12" y1="16" x2="12.01" y2="16"/>
</svg>
            
          </div>
          <h4 class="post-title" :class="variant === 'active' ? 'text-primary' : 'text-secondary'">
            {{ post.title }}
          </h4>
        </div>
        <span class="badge" :class="variant === 'active' ? 'badge-active' : 'badge-expired'">
          {{ variant === 'active' ? 'Active' : 'Expired' }}
        </span>
      </div>

      <div class="post-meta">
        <span class="meta-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Posted: {{ post.date }}
        </span>
        <span class="meta-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Deadline: {{ post.deadline }}
        </span>
      </div>

      <p class="post-description">{{ truncateText(post.description, 120) }}</p>
      
      <div class="skills-section">
        <span 
          v-for="skill in post.skills.slice(0, 5)" 
          :key="skill" 
          class="skill-tag"
          :class="{ 'skill-tag-expired': variant === 'expired' }"
        >
          {{ skill }}
        </span>
        <span v-if="post.skills.length > 5" class="skill-tag more-tag">
          +{{ post.skills.length - 5 }} more
        </span>
      </div>

      <div class="card-footer">
        <div class="applicants-count">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span class="count-number">{{ post.applicants || 0 }}</span>
          <span class="count-label">Applicants</span>
        </div>
        <button class="btn-view">
          View Rankings
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
          </svg>
        </button>
      </div>
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

const truncateText = (text, length) => {
  if (!text) return '';
  if (text.length <= length) return text;
  return text.substring(0, length) + '...';
};
</script>

 <style scoped>
.post-card {
  background: white;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.post-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
  border-color: #cbd5e1;
}

.post-card.expired {
  background: white;
  opacity: 1;
}

.post-card.expired:hover {
  opacity: 1;
  transform: translateY(-4px);
}

.card-content {
  padding: 1.5rem;
}

/* Card Header */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.title-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.title-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #f1f5f9;
}

.active-icon {
  background: #eef2ff;
  color: #4f46e5;
}

.expired-icon {
  background: #fee2e2;
  color: #ef4444;
}

.post-title {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0;
  letter-spacing: -0.3px;
}

.text-primary {
  color: #0f172a;
}

.text-secondary {
  color: #0f172a;
}

/* Badges */
.badge {
  padding: 0.375rem 0.875rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}

.badge-active {
  background: #d1fae5;
  color: #065f46;
}

.badge-expired {
  background: #fee2e2;
  color: #991b1b;
}

/* Post Meta */
.post-meta {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  color: #64748b;
}

.meta-item svg {
  color: #94a3b8;
}

/* Description */
.post-description {
  color: #475569;
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Skills Section */
.skills-section {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.skill-tag {
  background: #f1f5f9;
  color: #334155;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.skill-tag-expired {
  background: #f1f5f9;
  color: #334155;
}

.more-tag {
  background: #eef2ff;
  color: #4f46e5;
  font-weight: 600;
}

/* Card Footer */
.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.applicants-count {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.applicants-count svg {
  color: #94a3b8;
}

.count-number {
  font-weight: 700;
  font-size: 1rem;
  color: #0f172a;
}

.count-label {
  font-size: 0.75rem;
  color: #64748b;
}

.btn-view {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #4e46e5e5;
  border-color: #4e46e5d3;
  border: 1.5px solid #e2e8f0;
  color: white;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-view:hover {
  background: #4f46e5;
  border-color: #4f46e5;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
}

/* Responsive */
@media (max-width: 768px) {
  .card-content {
    padding: 1rem;
  }
  
  .card-header {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .badge {
    align-self: flex-start;
  }
  
  .post-meta {
    gap: 1rem;
  }
  
  .card-footer {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .btn-view {
    justify-content: center;
  }
}
</style>