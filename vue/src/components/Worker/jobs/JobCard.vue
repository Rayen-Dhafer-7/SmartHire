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
  transition: transform 0.2s, box-shadow 0.2s;
  border-left: 4px solid #4f46e5;
  border-radius: 8px;
  overflow: hidden;
}

.post-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.company-logo {
  width: 48px;
  height: 48px;
  min-width: 48px;
  font-size: 1.5rem;
}

.object-fit-cover {
  object-fit: cover;
}

.description-text {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.skill-tag {
  display: inline-block;
  padding: 4px 10px;
  background: #eef2ff;
  color: #4f46e5;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
}

.badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-green { background: #dcfce7; color: #166534; }
.badge-blue { background: #dbeafe; color: #1e40af; }
.badge-gray { background: #f3f4f6; color: #374151; }

.social-icon {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 10px;
  text-decoration: none;
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.social-icon:hover {
  transform: scale(1.1);
  opacity: 0.85;
}

.instagram { background: #E4405F; }
.facebook  { background: #1877F2; }
.twitter   { background: #000000; }
.linkedin  { background: #0A66C2; }
.website   { background: #6c757d; }
.email     { background: #dc3545; }
</style>