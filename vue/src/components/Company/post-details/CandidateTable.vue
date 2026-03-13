<template>
  <div class="table-container">
    <table class="app-table">
      <thead>
        <tr>
          <th>Rank</th>
          <th>Candidate Name</th>
          <th>Email</th>
          <th>Final Score</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <template v-for="(candidate, index) in candidates" :key="candidate.id || index">
          <!-- Main Row -->
          <tr @click="toggleDetails(candidate.id || index)">
            <td>
              <span class="badge badge-gray">#{{ index + 1 }}</span>
            </td>

            <td>
              <div class="d-flex align-items-center gap-2">
                <img
                  :src="candidate.photoUrl"
                  :alt="candidate.name"
                  class="candidate-photo-sm"
                  @error="handleImageError"
                />
                <span class="fw-bold">{{ candidate.name }}</span>
              </div>
            </td>

            <td>{{ candidate.email }}</td>

            <td>
              <div class="d-flex align-items-center">
                <span class="fw-bold me-2">{{ candidate.finalScore }}%</span>
                <div class="progress" style="width: 80px; height: 6px;">
                  <div
                    class="progress-bar"
                    :class="getScoreColor(candidate.finalScore)"
                    :style="{ width: candidate.finalScore + '%' }"
                  ></div>
                </div>
              </div>
            </td>

            <td>
              <button
                class="btn btn-sm btn-outline-primary"
                @click.stop="toggleDetails(candidate.id || index)"
              >
                {{ expandedId === (candidate.id || index) ? 'Hide Details' : 'View Details' }}
              </button>
            </td>
          </tr>

          <!-- Expanded Details -->
          <tr v-if="expandedId === (candidate.id || index)" class="bg-light">
            <td colspan="5" class="p-4">
              <CandidateDetails :candidate="candidate" />
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import CandidateDetails from './CandidateDetails.vue';

defineProps({
  candidates: {
    type: Array,
    required: true
  }
});

const expandedId = ref(null);

const toggleDetails = (id) => {
  expandedId.value = expandedId.value === id ? null : id;
};

const handleImageError = (e) => {
  e.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(e.target.alt || 'User');
};

const getScoreColor = (score) => {
  if (score >= 80) return 'bg-success';
  if (score >= 60) return 'bg-warning';
  return 'bg-danger';
};
</script>

<style scoped>
.app-table {
  width: 100%;
  border-collapse: collapse;
}

.app-table th,
.app-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #dee2e6;
}

.table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.candidate-photo-sm {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e0e0e0;
}

.progress {
  background-color: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  transition: width 0.3s ease;
}

.bg-success { background-color: #10b981 !important; }
.bg-warning { background-color: #f59e0b !important; }
.bg-danger { background-color: #ef4444 !important; }

.badge-gray {
  background-color: #f3f4f6;
  color: #374151;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.85em;
}

.gap-2 { gap: 8px; }
tr { cursor: pointer; }
</style>