<template>
  <div class="job-card">
    <div class="card-left-border"></div>
    
    <div class="card-content">
      <div class="card-header">
        <div class="company-info">
          <div class="company-logo">
            <img v-if="post.logoUrl" :src="post.logoUrl" :alt="post.company" style="width: 100%; height: 100%; object-fit: contain;" />
            <span v-else class="logo-placeholder">{{ post.company?.charAt(0) || 'C' }}</span>
          </div>
          <div>
            <h3 class="job-title">{{ post.title }}</h3>
            <div class="company-name">{{ post.company }} • {{ post.location }}</div>
          </div>
        </div>
        <span class="job-badge" :class="getBadgeClass(post.type)">
          {{ post.type }}
        </span>
      </div>

      <div class="job-meta">
        <div class="meta-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          <span>Posted: {{ post.date }}</span>
        </div>
        <div class="meta-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          <span>Deadline: {{ post.deadline }}</span>
        </div>
        <div class="meta-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>{{ post.workersNeeded }} Worker{{ post.workersNeeded > 1 ? 's' : '' }} Needed</span>
        </div>
      </div>

      <p class="job-description">{{ truncateText(post.description, 150) }}</p>

      <div class="skills-section">
        <span class="skills-label">Required Skills:</span>
        <div class="skills-list">
          <span v-for="skill in post.skills.slice(0, 5)" :key="skill" class="skill-tag">
            {{ skill }}
          </span>
          <span v-if="post.skills.length > 5" class="skill-tag more-tag">
            +{{ post.skills.length - 5 }}
          </span>
        </div>
      </div>

      <div class="social-links">
        <a v-if="post.social?.instagram" :href="post.social.instagram" target="_blank" class="social-icon instagram" title="Instagram">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
          </svg>
        </a>
        <a v-if="post.social?.facebook" :href="post.social.facebook" target="_blank" class="social-icon facebook" title="Facebook">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
          </svg>
        </a>
        <a v-if="post.social?.linkedin" :href="post.social.linkedin" target="_blank" class="social-icon linkedin" title="LinkedIn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
            <rect x="2" y="9" width="4" height="12"/>
            <circle cx="4" cy="4" r="2"/>
          </svg>
        </a>
        <a v-if="post.social?.website" :href="post.social.website" target="_blank" class="social-icon website" title="Website">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
          </svg>
        </a>
        <a v-if="post.social?.email" :href="`mailto:${post.social.email}`" class="social-icon email" title="Email">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
        </a>
      </div>

      <div class="card-footer">
        <div class="applicants-info">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>{{ post.applicants || 0 }} applicants so far</span>
        </div>
        <button class="apply-btn" @click="$emit('apply', post)">
          Apply Now
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
  post: { type: Object, required: true }
});

defineEmits(['apply']);

const getBadgeClass = (type) => {
  switch (type) {
    case 'Remote': return 'badge-remote';
    case 'Onsite': return 'badge-onsite';
    case 'Hybrid': return 'badge-hybrid';
    default: return 'badge-default';
  }
};

const truncateText = (text, length) => {
  if (!text) return '';
  if (text.length <= length) return text;
  return text.substring(0, length) + '...';
};
</script>

<style scoped>
.job-card {
  background: white;
  border-radius: 20px;
  position: relative;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.job-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
}

.card-left-border {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
}

.card-content {
  padding: 1.5rem;
  margin-left: 4px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.company-info {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.company-logo {
  width: 56px;
  height: 56px;
  background: #f8fafc;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.company-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.logo-placeholder {
  font-size: 1.5rem;
  font-weight: 700;
  color: #4f46e5;
}

.job-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem;
  letter-spacing: -0.3px;
}

.company-name {
  font-size: 0.875rem;
  color: #64748b;
}

.job-badge {
  padding: 0.375rem 0.875rem;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-remote {
  background: #d1fae5;
  color: #065f46;
}

.badge-onsite {
  background: #dbeafe;
  color: #1e40af;
}

.badge-hybrid {
  background: #fef3c7;
  color: #b45309;
}

.badge-default {
  background: #f1f5f9;
  color: #475569;
}

.job-meta {
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

.job-description {
  color: #475569;
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.skills-section {
  margin-bottom: 1rem;
}

.skills-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
  margin-bottom: 0.5rem;
}

.skills-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.skill-tag {
  background: #f1f5f9;
  color: #334155;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
}

.more-tag {
  background: #eef2ff;
  color: #4f46e5;
  font-weight: 600;
}

.social-links {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.social-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  background: #f1f5f9;
  color: #64748b;
}

.social-icon:hover {
  transform: translateY(-2px);
}

.social-icon.instagram:hover {
  background: #e4405f;
  color: white;
}

.social-icon.facebook:hover {
  background: #1877f2;
  color: white;
}

.social-icon.linkedin:hover {
  background: #0a66c2;
  color: white;
}

.social-icon.website:hover {
  background: #10b981;
  color: white;
}

.social-icon.email:hover {
  background: #ef4444;
  color: white;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
  flex-wrap: wrap;
  gap: 1rem;
}

.applicants-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #64748b;
}

.apply-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1.25rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.apply-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

@media (max-width: 768px) {
  .card-content {
    padding: 1rem;
  }
  
  .card-header {
    flex-direction: column;
  }
  
  .company-info {
    width: 100%;
  }
  
  .job-badge {
    align-self: flex-start;
  }
  
  .card-footer {
    flex-direction: column;
    align-items: stretch;
  }
  
  .apply-btn {
    justify-content: center;
  }
}
</style>