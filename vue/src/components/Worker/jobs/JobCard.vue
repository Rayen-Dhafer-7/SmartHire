<template>
  <div class="card post-card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="d-flex align-items-center gap-3">
          <!-- Company Logo -->
          <div v-if="post.logoUrl" class="company-logo bg-light d-flex align-items-center justify-content-center rounded border overflow-hidden">
            <img :src="post.logoUrl" :alt="post.company" class="w-100 h-100 object-fit-cover">
          </div>
          <div v-else class="company-logo bg-light d-flex align-items-center justify-content-center rounded text-muted fw-bold border">
            {{ post.company.charAt(0) }}
          </div>
          <div>
            <h5 class="mb-0 text-primary fw-bold">{{ post.title }}</h5>
            <div class="text-muted small">
              <span class="fw-semibold text-dark">{{ post.company }}</span> • {{ post.location }}
            </div>
          </div>
        </div>
        <span class="badge" :class="getBadgeClass(post.type)">{{ post.type }} </span>
      </div>

      <p class="text-muted small mb-3">
        <i class="bi bi-clock"></i> Posted: {{ post.date }} • <i class="bi bi-calendar-check"></i> Deadline: {{ post.deadline }}
      </p>

      <p class="mb-3 text-secondary description-text">{{ truncateText(post.description, 150) }}</p>

      <div class="mb-3">
        <div class="small fw-bold text-muted mb-1">Required Skills:</div>
        <div class="d-flex flex-wrap gap-2">
            <span v-for="skill in post.skills" :key="skill" class="skill-tag">
              {{ skill }}
            </span>
        </div>
      </div>
      
      <div class="d-flex gap-3 mt-3 social-links">
        <a v-if="post.social?.instagram" :href="post.social.instagram" target="_blank" class="social-icon instagram" title="Instagram">
          <i class="bi bi-instagram"></i>
        </a>

        <a v-if="post.social?.facebook" :href="post.social.facebook" target="_blank" class="social-icon facebook" title="Facebook">
          <i class="bi bi-facebook"></i>
        </a>

        <a v-if="post.social?.twitter" :href="post.social.twitter" target="_blank" class="social-icon twitter" title="X (Twitter)">
          <i class="bi bi-twitter-x"></i>
        </a>

        <a v-if="post.social?.linkedin" :href="post.social.linkedin" target="_blank" class="social-icon linkedin" title="LinkedIn">
          <i class="bi bi-linkedin"></i>
        </a>

        <a v-if="post.social?.website" :href="post.social.website" target="_blank" class="social-icon website" title="Website">
          <i class="bi bi-globe"></i>
        </a>

        <a v-if="post.social?.email" :href="`mailto:${post.social.email}`" class="social-icon email" title="Email">
          <i class="bi bi-envelope-fill"></i>
        </a>
      </div>
      
      <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
          <div class="small fw-bold text-muted">
            <i class="bi bi-people-fill"></i> {{ post.workersNeeded }} Worker{{ post.workersNeeded > 1 ? 's' : '' }} Needed
          </div>
          <button class="btn btn-primary btn-sm px-4" @click="$emit('apply', post)">
            Apply Now
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
  }
});

defineEmits(['apply']);

const getBadgeClass = (type) => {
  switch (type) {
    case 'Remote': return 'badge-green';
    case 'Onsite': return 'badge-blue';
    case 'Hybrid': return 'badge-gray';
    default: return 'badge-gray';
  }
};

const truncateText = (text, length) => {
  if (!text) return '';
  if (text.length <= length) return text;
  return text.substring(0, length) + '...';
};
</script>

<style scoped>
.post-card {
  transition: all var(--transition-base);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  background: var(--white);
}

.post-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: transparent;
}

.card-body {
  padding: 20px !important;
}

.company-logo {
  width: 56px;
  height: 56px;
  min-width: 56px;
  font-size: 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  color: var(--text-gray);
}

.object-fit-cover {
  object-fit: cover;
}

/* Typography */
.mb-0 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-main);
  transition: color var(--transition-fast);
}

.post-card:hover .mb-0 {
  color: var(--primary-color);
}

.text-muted {
  color: var(--text-muted) !important;
  font-size: 0.9rem;
}

.text-dark {
  color: var(--text-main) !important;
}

.text-secondary {
  color: var(--text-gray) !important;
  line-height: 1.6;
}

.description-text {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  font-size: 0.95rem;
  line-height: 1.5;
}

/* Skills Section */
.skill-tag {
  display: inline-block;
  padding: 6px 12px;
  background: linear-gradient(135deg, rgba(13, 124, 140, 0.1) 0%, rgba(13, 124, 140, 0.05) 100%);
  color: var(--primary-color);
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  margin-right: 8px;
  margin-bottom: 8px;
  transition: all var(--transition-fast);
  cursor: pointer;
}

.skill-tag:hover {
  background: linear-gradient(135deg, rgba(13, 124, 140, 0.15) 0%, rgba(13, 124, 140, 0.1) 100%);
  transform: translateY(-2px);
}

/* Badge - Status */
.badge {
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.badge-green { 
  background: var(--success-light);
  color: #065f46;
}

.badge-blue { 
  background: var(--info-light);
  color: #0c5577;
}

.badge-gray { 
  background: var(--bg-light);
  color: var(--text-gray);
}

/* Social Links */
.social-links {
  border-top: 1px solid var(--border-color);
  padding-top: 16px;
}

.social-icon {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
  text-decoration: none;
  transition: all var(--transition-fast);
  opacity: 0.85;
  font-weight: 500;
}

.social-icon:hover {
  transform: translateY(-3px);
  opacity: 1;
  box-shadow: var(--shadow-md);
}

.instagram { background: #E4405F; }
.facebook  { background: #1877F2; }
.twitter   { background: #000000; }
.linkedin  { background: #0A66C2; }
.website   { background: #6b7280; }
.email     { background: #dc2626; }

/* Button */
.btn-primary {
  background-color: var(--primary-color);
  border: none;
  color: white;
  font-weight: 600;
  padding: 10px 20px;
  border-radius: 8px;
  transition: all var(--transition-fast);
  cursor: pointer;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.btn-primary:active {
  transform: translateY(0);
}

/* Icons */
.bi {
  margin-right: 4px;
}

/* Spacing Utilities */
.gap-3 {
  gap: 1rem !important;
}

.gap-2 {
  gap: 0.5rem !important;
}

.d-flex {
  display: flex;
}

.justify-content-between {
  justify-content: space-between;
}

.align-items-start {
  align-items: flex-start;
}

.align-items-center {
  align-items: center;
}

.flex-wrap {
  flex-wrap: wrap;
}

.border-top {
  border-top: 1px solid var(--border-color);
}

/* Responsive */
@media (max-width: 768px) {
  .card-body {
    padding: 16px !important;
  }
  
  .company-logo {
    width: 48px;
    height: 48px;
  }
}
</style>
