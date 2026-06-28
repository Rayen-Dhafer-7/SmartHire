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

            <td @click.stop>
              <button
                class="btn btn-sm btn-outline-primary"
                @click.stop="toggleDetails(candidate.id || index)"
              >
                {{ expandedId === (candidate.id || index) ? 'Hide Details' : 'View Details' }}
              </button>
              &nbsp;&nbsp;
              <button
                class="btn btn-sm btn-outline-secondary"
                @click.stop="viewProfile(candidate.worker_id)"
              >
                <i class="bi bi-person"></i> View Profile
              </button>
            </td>
          </tr>

          <!-- Expanded Details -->
          <tr v-if="expandedId === (candidate.id || index)" class="bg-light">
            <td colspan="5" class="p-4">
              <CandidateDetails 
                :candidate="candidate" 
                :profile="profile"
                :logoPreview="logoPreview"
              />
            </td>
          </tr>
          
        </template>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CandidateDetails from './CandidateDetails.vue';
import { getProfile } from '../../../utils/storage';

const router = useRouter();

const props = defineProps({
  candidates: {
    type: Array,
    required: true
  }
});

const expandedId = ref(null);
const profile = ref({});
const logoPreview = ref('');

const toggleDetails = (id) => {
  expandedId.value = expandedId.value === id ? null : id;
};

const viewProfile = (workerId) => {
    console.log('viewProfile clicked, workerId:', workerId);
    router.push(`/company/worker-profile/${workerId}`);
};

const handleImageError = (e) => {
  e.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(e.target.alt || 'User');
};

const getScoreColor = (score) => {
  if (score >= 80) return 'bg-success';
  if (score >= 60) return 'bg-warning';
  return 'bg-danger';
};

// Load company profile data when component mounts
onMounted(() => {
  // Load from storage
  const storedProfile = getProfile();
  if (storedProfile) {
    profile.value = storedProfile;
    logoPreview.value = storedProfile.logoDataUrl || storedProfile.logoUrl;
    console.log('Company profile loaded in CandidateTable:', profile.value);
  } else {
    // Try to load from localStorage directly
    try {
      const localStorageProfile = localStorage.getItem('profile');
      if (localStorageProfile) {
        const parsedProfile = JSON.parse(localStorageProfile);
        profile.value = parsedProfile;
        logoPreview.value = parsedProfile.logoDataUrl || parsedProfile.logoUrl;
        console.log('Company profile loaded from localStorage:', profile.value);
      }
    } catch (e) {
      console.error('Error loading profile from localStorage:', e);
    }
  }
});
</script>

<style scoped>
.table-container {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  transition: all 0.3s ease;
}

.app-table {
  width: 100%;
  border-collapse: collapse;
}

.app-table th {
  background: #f8fafc;
  padding: 1rem 1.25rem;
  font-weight: 600;
  font-size: 0.875rem;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e2e8f0;
}

.app-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.app-table tbody tr {
  transition: all 0.2s ease;
}

.app-table tbody tr:hover {
  background: #faf9ff;
  cursor: pointer;
}

.candidate-photo-sm {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Progress bar styling */
.progress {
  background-color: #f1f5f9;
  border-radius: 999px;
  height: 8px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  transition: width 0.3s ease;
  border-radius: 999px;
}

.bg-success { background: linear-gradient(90deg, #10b981, #34d399); }
.bg-warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.bg-danger { background: linear-gradient(90deg, #ef4444, #f87171); }

/* Button styles */
.btn-outline-primary {
  background: transparent;
  border: 1.5px solid #e2e8f0;
  color: #4f46e5;
  padding: 0.5rem 1rem;
  border-radius: 10px;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.btn-outline-primary:hover {
  background: #4f46e5;
  border-color: #4f46e5;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-outline-secondary {
  background: transparent;
  border: 1.5px solid #e2e8f0;
  color: #64748b;
  padding: 0.5rem 1rem;
  border-radius: 10px;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.badge-gray {
  background: #f1f5f9;
  color: #475569;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.875rem;
}
</style>