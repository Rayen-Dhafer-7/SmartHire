<template>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between mb-4">
        <!-- Left: Basic Info & Links --> 
        <div>
          <div class="d-flex align-items-center gap-3 mb-3">
            <img
              :src="candidate.photoUrl"
              :alt="candidate.name"
              class="candidate-photo-lg"
              @error="handleImageError"
            />
            <div>
              <h5 class="mb-1">{{ candidate.name }}</h5>
              <p class="text-muted mb-0">{{ candidate.email }}</p>
              <p v-if="candidate.location" class="text-muted small mb-0">
                <i class="bi bi-geo-alt"></i> {{ candidate.location }}
              </p>
            </div>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <a
              v-if="candidate.cvLink && candidate.cvLink !== '#'"
              :href="candidate.cvLink"
              target="_blank"
              class="btn btn-sm btn-outline-secondary"
            >
              <i class="bi bi-file-pdf"></i> Download CV 
            </a>

            <a
              v-if="candidate.linkedin && candidate.linkedin !== '#'"
              :href="candidate.linkedin"
              target="_blank"
              class="btn btn-sm btn-outline-primary"
            >
              <i class="bi bi-linkedin"></i> 
            </a>

            <a
              v-if="candidate.github && candidate.github !== '#'"
              :href="candidate.github"
              target="_blank"
              class="btn btn-sm btn-dark"
            >
              <i class="bi bi-github"></i> GitHub 
            </a>

            <a
              v-if="candidate.website && candidate.website !== '#'"
              :href="candidate.website"
              target="_blank"
              class="btn btn-sm btn-outline-success"
            >
              <i class="bi bi-globe"></i> Website
            </a>

            <a
              v-if="candidate.gmail && candidate.gmail !== '#'"
              :href="`mailto:${candidate.email}`"
              target="_blank"
              class="btn btn-sm btn-outline-danger"
            >
              <i class="bi bi-envelope"></i> Email
            </a>

          </div>
        </div>

        <!-- Right: Score & Stats -->
        <div class="text-end">
          <div class="display-6 fw-bold" :class="getScoreTextColor(candidate.totalcore)">
            {{ candidate.totalcore }} 
          </div>

          <div class="text-muted mt-1">
            <h6>⏱ {{ candidate.timeUsed }} / {{ candidate.timeTotal }} min</h6>
          </div>

          <div class="text-muted text-uppercase small">
           
          </div>
        </div>
      </div>

      <!-- Skills Table -->
      <table class="table table-sm table-bordered">
        <thead class="table-light">
          <tr>
            <th>Technology</th>
            <th>Score</th>
            <th>Percentage</th>
            <th>Level</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="skill in candidate.skills" :key="skill.name">
            <td>{{ skill.name }}</td>
            <td>{{ skill.score }}/12</td>
            <td>{{ Math.round((skill.score / 20) * 100) }}%</td>
            <td>
              <span class="badge" :class="getLevelBadge(skill.level)">
                {{ skill.level }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  candidate: {
    type: Object,
    required: true
  }
});

const handleImageError = (e) => {
  e.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(e.target.alt || 'User');
};

const getLevelBadge = (level) => {
  const levelLower = (level || '').toLowerCase();
  switch (levelLower) {
    case 'expert': return 'bg-success';
    case 'advanced': return 'bg-primary';
    case 'intermediate': return 'bg-info text-dark';
    case 'beginner': return 'bg-secondary';
    default: return 'bg-secondary';
  }
};

const getScoreTextColor = (score) => {
  if (score >= 80) return 'text-success';
  if (score >= 60) return 'text-warning';
  return 'text-danger';
};


</script>

<style scoped>
.candidate-photo-lg {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #4f46e5;
}

.text-end {
  text-align: right;
}

.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }

.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }

.bg-success { background-color: #10b981 !important; }
.bg-primary { background-color: #4f46e5 !important; }
.bg-info { background-color: #06b6d4 !important; }
.bg-secondary { background-color: #6b7280 !important; }

.badge {
  padding: 0.5em 0.8em;
  border-radius: 4px;
  color: white;
}
</style>