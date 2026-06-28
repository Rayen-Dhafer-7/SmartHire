<template>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between mb-4">
        <!-- Left: Basic Info & Links -->
        <div class="flex-grow-1">
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

          <!-- Social Links Row -->
          <div class="d-flex gap-2 flex-wrap mb-3">
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
              <i class="bi bi-linkedin"></i> LinkedIn
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

          <!-- Send Meeting Button - Separate Row -->
          <div class="meeting-button-wrapper">
            <button 
              @click="openMeetingModal"
              class="btn btn-primary meeting-btn"
              :disabled="sending"
            >
              <i class="bi bi-calendar-plus"></i> 
              {{ sending ? 'Sending...' : 'Send Meeting Invitation' }}
            </button>
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

    <!-- Meeting Modal -->
    <div class="modal fade" id="meetingModal" tabindex="-1" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-calendar-event"></i> Schedule Meeting with {{ candidate.name }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="sendMeetingInvitation">
              <div class="mb-3">
                <label class="form-label">Candidate Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="meetingForm.candidateName" 
                  readonly
                />
              </div>

              <div class="mb-3">
                <label class="form-label">Candidate Email</label>
                <input 
                  type="email" 
                  class="form-control" 
                  v-model="meetingForm.candidateEmail" 
                  readonly
                />
              </div>

              <div class="mb-3">
                <label class="form-label">Meeting Date & Time *</label>
                <input 
                  type="datetime-local" 
                  class="form-control" 
                  v-model="meetingForm.meetingDateTime"
                  required
                  :min="minDateTime"
                />
                <small class="text-muted">Please select a date and time for the meeting</small>
              </div>

              <!-- Meeting Type Selection -->
              <div class="mb-3">
                <label class="form-label">Meeting Type *</label>
                <div class="btn-group w-100" role="group">
                  <input 
                    type="radio" 
                    class="btn-check" 
                    name="meetingType" 
                    id="online" 
                    value="online"
                    v-model="meetingForm.meetingType"
                    autocomplete="off"
                  />
                  <label class="btn btn-outline-primary" for="online">
                    <i class="bi bi-camera-video"></i> Online Meeting
                  </label>

                  <input 
                    type="radio" 
                    class="btn-check" 
                    name="meetingType" 
                    id="onsite" 
                    value="onsite"
                    v-model="meetingForm.meetingType"
                    autocomplete="off"
                  />
                  <label class="btn btn-outline-primary" for="onsite">
                    <i class="bi bi-building"></i> On-site Meeting
                  </label>
                </div>
              </div>

              <!-- Online Meeting Fields -->
              <div v-if="meetingForm.meetingType === 'online'" class="mb-3">
                <label class="form-label">Meeting Link *</label>
                <input 
                  type="url" 
                  class="form-control" 
                  v-model="meetingForm.meetingLink"
                  placeholder="https://meet.google.com/xxx or Zoom link"
                  :required="meetingForm.meetingType === 'online'"
                />
                <small class="text-muted">Provide the video conference link for the meeting</small>
              </div>

              <!-- On-site Meeting Fields -->
              <div v-if="meetingForm.meetingType === 'onsite'" class="mb-3">
                <label class="form-label">Company Location *</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="meetingForm.location"
                    placeholder="Enter full address"
                    :required="meetingForm.meetingType === 'onsite'"
                  />
                </div>
                <small class="text-muted">Provide the full address where the meeting will take place</small>
                
                <!-- Display company location from profile -->
                <div v-if="companyLocation" class="mt-2">
                  <button 
                    type="button" 
                    class="btn btn-sm btn-link p-0"
                    @click="useCompanyLocation"
                  >
                    <i class="bi bi-arrow-repeat"></i> Use company location
                  </button>
                </div>
                <div v-else class="mt-2">
                  <div class="alert alert-warning p-2 mb-2">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>No company location found!</strong> Please enter a location.
                  </div>
                  <small class="text-muted">
                    You can update your company location in the 
                    <router-link to="/company/profile">Company Profile</router-link> page.
                  </small>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Additional Notes (Optional)</label>
                <textarea 
                  class="form-control" 
                  v-model="meetingForm.notes"
                  rows="3"
                  placeholder="Any additional information for the candidate..."
                ></textarea>
              </div>

              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Note:</strong> The candidate will receive an email with all meeting details.
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
              type="button" 
              class="btn btn-primary" 
              @click="sendMeetingInvitation"
              :disabled="sending || !isFormValid"
            >
              <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
              {{ sending ? 'Sending...' : 'Send Invitation' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import { showSuccess, showError } from '../../../utils/notifications';

const props = defineProps({
  candidate: {
    type: Object,
    required: true
  },
  profile: {
    type: Object,
    default: () => ({})
  },
  logoPreview: {
    type: [String, Object],
    default: 'https://via.placeholder.com/150'
  }
});

const sending = ref(false);
let modal = null;

// Meeting form data
const meetingForm = ref({
  candidateName: '',
  candidateEmail: '',
  meetingDateTime: '',
  meetingType: 'online',
  meetingLink: '',
  location: '',
  notes: ''
});

// Get company location from multiple sources
const companyLocation = computed(() => {
  let location = '';
  
  if (props.profile && props.profile.location) {
    location = props.profile.location;
  } else {
    try {
      const storedProfile = localStorage.getItem('profile');
      if (storedProfile) {
        const parsedProfile = JSON.parse(storedProfile);
        if (parsedProfile && parsedProfile.location) {
          location = parsedProfile.location;
        }
      }
    } catch (e) {
      console.error('Error reading from localStorage:', e);
    }
  }
  
  if (!location) {
    try {
      const sessionProfile = sessionStorage.getItem('profile');
      if (sessionProfile) {
        const parsedProfile = JSON.parse(sessionProfile);
        if (parsedProfile && parsedProfile.location) {
          location = parsedProfile.location;
        }
      }
    } catch (e) {
      console.error('Error reading from sessionStorage:', e);
    }
  }
  
  return location;
});

// Get company name from multiple sources
const companyName = computed(() => {
  let name = '';
  
  if (props.profile && props.profile.name) {
    name = props.profile.name;
  } else {
    try {
      const storedProfile = localStorage.getItem('profile');
      if (storedProfile) {
        const parsedProfile = JSON.parse(storedProfile);
        if (parsedProfile && parsedProfile.name) {
          name = parsedProfile.name;
        }
      }
    } catch (e) {
      console.error('Error reading from localStorage:', e);
    }
  }
  
  return name || localStorage.getItem('company_name') || 'Company';
});

// Minimum date and time
const minDateTime = computed(() => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day}T${hours}:${minutes}`;
});

// Form validation
const isFormValid = computed(() => {
  if (!meetingForm.value.meetingDateTime || 
      !meetingForm.value.candidateName || 
      !meetingForm.value.candidateEmail ||
      !meetingForm.value.meetingType) {
    return false;
  }
  
  if (meetingForm.value.meetingType === 'online') {
    return meetingForm.value.meetingLink && meetingForm.value.meetingLink.trim() !== '';
  } else if (meetingForm.value.meetingType === 'onsite') {
    return meetingForm.value.location && meetingForm.value.location.trim() !== '';
  }
  
  return false;
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

const useCompanyLocation = () => {
  if (companyLocation.value) {
    meetingForm.value.location = companyLocation.value;
  }
};

const openMeetingModal = () => {
  meetingForm.value.candidateName = props.candidate.name;
  meetingForm.value.candidateEmail = props.candidate.email;
  meetingForm.value.meetingDateTime = '';
  meetingForm.value.meetingType = 'online';
  meetingForm.value.meetingLink = '';
  meetingForm.value.location = '';
  meetingForm.value.notes = '';
  
  if (modal) {
    modal.show();
  }
};

const sendMeetingInvitation = async () => {
  if (!isFormValid.value) {
    showError('Validation Error', 'Please fill in all required fields');
    return;
  }

  sending.value = true;

  try {
    const formData = new FormData();
    formData.append('candidateName', meetingForm.value.candidateName);
    formData.append('candidateEmail', meetingForm.value.candidateEmail);
    formData.append('meetingDateTime', meetingForm.value.meetingDateTime);
    formData.append('meetingType', meetingForm.value.meetingType);
    
    if (meetingForm.value.meetingType === 'online') {
      formData.append('meetingLink', meetingForm.value.meetingLink);
      formData.append('location', '');
    } else {
      formData.append('meetingLink', '');
      formData.append('location', meetingForm.value.location);
    }
    
    formData.append('notes', meetingForm.value.notes || '');
    formData.append('companyName', companyName.value);
    
    if (companyLocation.value) {
      formData.append('companyLocation', companyLocation.value);
    }

    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      throw new Error('Authentication token not found');
    }

    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/company/sendmail`, 
      formData, 
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      }
    );

    if (response.data && response.data.success !== false) {
      await showSuccess('Meeting Invitation Sent!');
      
      if (modal) {
        modal.hide();
      }
      
      meetingForm.value = {
        candidateName: '',
        candidateEmail: '',
        meetingDateTime: '',
        meetingType: 'online',
        meetingLink: '',
        location: '',
        notes: ''
      };
    } else {
      throw new Error(response.data.message || 'Failed to send invitation');
    }
    
  } catch (error) {
    console.error('Error sending meeting invitation:', error);
    
    let errorMessage = 'Failed to send meeting invitation. Please try again.';
    
    if (error.response) {
      if (error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (error.response.status === 401) {
        errorMessage = 'Authentication failed. Please login again.';
      } else if (error.response.status === 403) {
        errorMessage = 'You don\'t have permission to send invitations.';
      }
    } else if (error.request) {
      errorMessage = 'Network error. Please check your internet connection.';
    } else if (error.message) {
      errorMessage = error.message;
    }
    
    showError('Error', errorMessage);
  } finally {
    sending.value = false;
  }
};

const formatDateTime = (dateTimeStr) => {
  if (!dateTimeStr) return '';
  const date = new Date(dateTimeStr);
  return date.toLocaleString();
};

onMounted(() => {
  const modalElement = document.getElementById('meetingModal');
  if (modalElement) {
    modal = new Modal(modalElement);
  }
});

onUnmounted(() => {
  if (modal) {
    modal.dispose();
  }
});
</script>

<style scoped>
.card {
  background: white;
  border-radius: 20px !important;
  border: none !important;
  overflow: hidden;
  margin-top: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

.card-body {
  padding: 1.5rem;
}

.candidate-photo-lg {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #4f46e5;
  box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);
}

/* Button group */
.d-flex.gap-2 {
  gap: 0.75rem;
}

.btn-sm {
  padding: 0.5rem 1rem;
  border-radius: 10px;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

/* Outline buttons - Fixed hover effects */
.btn-outline-secondary {
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #475569;
}

.btn-outline-secondary:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  color: #1e293b;
  transform: translateY(-2px);
}

.btn-outline-primary {
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #4f46e5;
}

.btn-outline-primary:hover {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border-color: #4f46e5;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-outline-danger {
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #ef4444;
}

.btn-outline-danger:hover {
  background: #ef4444;
  border-color: #ef4444;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-outline-success {
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #10b981;
}

.btn-outline-success:hover {
  background: #10b981;
  border-color: #10b981;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-dark {
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #1e293b;
}

.btn-dark:hover {
  background: #1e293b;
  border-color: #1e293b;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Meeting button wrapper */
.meeting-button-wrapper {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e2e8f0;
}

.meeting-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.875rem;
  color: white;
  transition: all 0.3s ease;
}

.meeting-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
}

.meeting-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Score display */
.display-6 {
  font-size: 2.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }

/* Skills table */
.table {
  margin-top: 1.5rem;
}

.table-bordered {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}

.table-light {
  background: #f8fafc;
}

.table th {
  padding: 0.875rem;
  font-weight: 600;
  color: #475569;
  border-bottom: 1px solid #e2e8f0;
}

.table td {
  padding: 0.875rem;
  vertical-align: middle;
}

/* Badge levels */
.badge {
  padding: 0.375rem 0.75rem;
  border-radius: 999px;
  font-weight: 500;
  font-size: 0.75rem;
}

.bg-success { background: #10b981 !important; }
.bg-primary { background: #4f46e5 !important; }
.bg-info { background: #06b6d4 !important; color: white !important; }
.bg-secondary { background: #64748b !important; }

/* Modal styling */
.modal-content {
  border-radius: 20px;
  border: none;
  overflow: hidden;
}

.modal-header {
  background: linear-gradient(135deg, #f8fafc, #ffffff);
  border-bottom: 1px solid #e2e8f0;
  padding: 1.25rem 1.5rem;
}

.modal-header h5 {
  font-weight: 700;
  color: #0f172a;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  padding: 1rem 1.5rem;
}

/* Form controls */
.form-control, .form-select {
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.625rem 1rem;
  transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  outline: none;
}

.input-group-text {
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px 0 0 12px;
}

/* Button group for meeting type */
.btn-group .btn {
  padding: 0.625rem 1rem;
  border-radius: 10px;
}

.btn-group .btn-check:checked + .btn-outline-primary {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border-color: #4f46e5;
  color: white;
}

/* Alerts */
.alert-info {
  background: #eef2ff;
  border: 1px solid #c7d2fe;
  border-radius: 12px;
  color: #4338ca;
}

.alert-warning {
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 12px;
  color: #92400e;
}

/* Responsive */
@media (max-width: 768px) {
  .card-body {
    padding: 1rem;
  }
  
  .d-flex.justify-content-between {
    flex-direction: column;
    gap: 1rem;
  }
  
  .text-end {
    text-align: left;
  }
  
  .meeting-btn {
    width: 100%;
    justify-content: center;
  }
  
  .display-6 {
    font-size: 2rem;
  }
  
  .candidate-photo-lg {
    width: 60px;
    height: 60px;
  }
}
</style>