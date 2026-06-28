<template>
  <div class="admin-dashboard">
    <!-- Sidebar -->
    <aside class="admin-sidebar" :class="{ 'collapsed': sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="logo" v-if="!sidebarCollapsed">
          <span>AdminPanel</span>
        </div>
        <div class="logo-icon" v-else>
          
        </div>

      </div>

      <div class="sidebar-content">
        <div class="admin-info" v-if="!sidebarCollapsed">

          <div class="admin-details">
            <h4>Admin</h4>
            <span>Administrator</span>
          </div>
        </div>

        <nav class="sidebar-nav">
          <a 
            v-for="item in tabs" 
            :key="item.key"
            href="#"
            class="nav-item"
            :class="{ active: activeTab === item.key }"
            @click.prevent="activeTab = item.key"
          >
            <i :class="item.icon"></i>
            <span v-if="!sidebarCollapsed">{{ item.label }}</span>
            <span class="badge" v-if="item.badge && !sidebarCollapsed">{{ item.badge }}</span>
          </a>
        </nav>

        <div class="sidebar-footer">
          <button class="logout-btn" @click="logout">
            <i class="fas fa-sign-out-alt"></i>
            <span v-if="!sidebarCollapsed">Logout</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main" :class="{ 'expanded': sidebarCollapsed }">
      <div class="main-header">
        <div class="header-left">
          <h1>{{ currentTabLabel }}</h1>
          <p class="text-muted">Manage your platform data and monitoring</p>
        </div>
        <div class="header-right">
          <div class="date-time">
            <i class="far fa-calendar-alt"></i>
            <span>{{ currentDateTime }}</span>
          </div>

        </div>
      </div>

      <div class="main-content">
        <div v-if="loading" class="loading-container">
          <div class="spinner"></div>
          <p>Loading dashboard data...</p>
        </div>

        <div v-else>
          <!-- Stats Cards with Enhanced Display -->
          <div v-if="activeTab === 'stats'" class="stats-container">
            <!-- Main Stats Grid -->
            <div class="stats-grid">
              <div v-for="(value, key) in mainStats" :key="key" class="stat-card" :class="getStatCardClass(key)">
                <div class="stat-icon" :class="getIconClass(key)">
                  <i :class="getStatIcon(key)"></i>
                </div>
                <div class="stat-info">
                  <h3>{{ formatNumber(value) }}</h3>
                  <p>{{ formatStatLabel(key) }}</p>
                </div>
                <div class="stat-trend" v-if="getTrend(key)">
                  <i :class="getTrendIcon(key)"></i>
                  <span>{{ getTrend(key) }}</span>
                </div>
              </div>
            </div>

            <!-- Jobs Status Section with Visual Charts -->
            <div class="jobs-status-section">
              <div class="section-header">
                <h3>
                  <i class="fas fa-briefcase"></i>
                  Jobs Overview
                </h3>
                <div class="header-actions">
                  <span class="date-badge">
                    <i class="far fa-calendar-alt"></i>
                    {{ currentDate }}
                  </span>
                </div>
              </div>
              
              <div class="jobs-status-grid">
                <!-- Jobs In Progress Card -->
                <div class="status-card in-progress" @click="showJobsDetails('in_progress')">
                  <div class="status-header">
                    <div class="status-icon">
                      <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="status-badge">Active</div>
                  </div>
                  <div class="status-content">
                    <div class="status-number">{{ formatNumber(stats.jobs_in_progress || 0) }}</div>
                    <div class="status-label">Jobs In Progress</div>
                    <div class="status-description">Deadline today or in future</div>
                  </div>
                  <div class="status-footer">
                    <div class="progress-ring">
                      <svg width="80" height="80" viewBox="0 0 80 80">
                        <circle cx="40" cy="40" r="35" fill="none" stroke="#e2e8f0" stroke-width="4"/>
                        <circle cx="40" cy="40" r="35" fill="none" stroke="#48bb78" stroke-width="4"
                                :stroke-dasharray="getProgressRingDasharray(stats.jobs_in_progress, stats.jobs)"
                                stroke-dashoffset="0" stroke-linecap="round"/>
                      </svg>
                      <span class="percentage">{{ getPercentage(stats.jobs_in_progress, stats.jobs) }}%</span>
                    </div>
                  </div>
                </div>

                <!-- Jobs Expired Card -->
                <div class="status-card expired" @click="showJobsDetails('expired')">
                  <div class="status-header">
                    <div class="status-icon">
                      <i class="fas fa-stop-circle"></i>
                    </div>
                    <div class="status-badge">Expired</div>
                  </div>
                  <div class="status-content">
                    <div class="status-number">{{ formatNumber(stats.jobs_expired || 0) }}</div>
                    <div class="status-label">Jobs Expired</div>
                    <div class="status-description">Past deadline</div>
                  </div>
                  <div class="status-footer">
                    <div class="trend-indicator down">
                      <i class="fas fa-arrow-down"></i>
                      <span>No longer accepting</span>
                    </div>
                  </div>
                </div>

                <!-- Applications Card -->
                <div class="status-card applications" @click="showApplicationsDetails">
                  <div class="status-header">
                    <div class="status-icon">
                      <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="status-badge">Applications</div>
                  </div>
                  <div class="status-content">
                    <div class="status-number">{{ formatNumber(stats.applications || 0) }}</div>
                    <div class="status-label">Total Applications</div>
                    <div class="status-description">Application rate: {{ stats.application_rate || 0 }}%</div>
                  </div>
                  <div class="status-footer">
                    <div class="rate-bar">
                      <div class="rate-fill" :style="{ width: (stats.application_rate || 0) + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Jobs Details Modal (Normal Page Display) -->
            <div v-if="showJobsModal" class="jobs-details-modal-overlay" @click="closeJobsModal">
              <div class="jobs-details-modal-content" @click.stop>
                <div class="modal-header">
                  <h3>
                    <i :class="modalType === 'in_progress' ? 'fas fa-play-circle' : 'fas fa-stop-circle'"></i>
                    {{ modalType === 'in_progress' ? 'Jobs In Progress' : 'Jobs Expired' }}
                  </h3>
                  <button class="close-btn" @click="closeJobsModal">×</button>
                </div>
                <div class="modal-body">
                  <div class="detail-stats">
                    <div class="stat-item">
                      <span class="stat-label">Total {{ modalType === 'in_progress' ? 'Active' : 'Expired' }} Jobs:</span>
                      <span class="stat-value">{{ modalType === 'in_progress' ? stats.jobs_in_progress : stats.jobs_expired }}</span>
                    </div>
                    <div class="stat-item">
                      <span class="stat-label">Percentage:</span>
                      <span class="stat-value">{{ getPercentage(modalType === 'in_progress' ? stats.jobs_in_progress : stats.jobs_expired, stats.jobs) }}%</span>
                    </div>
                    <div class="info-message" :class="modalType === 'in_progress' ? 'info' : 'warning'">
                      <i :class="modalType === 'in_progress' ? 'fas fa-info-circle' : 'fas fa-exclamation-triangle'"></i>
                      {{ modalType === 'in_progress' ? 'These jobs are currently accepting applications' : 'These jobs have passed their deadline' }}
                    </div>
                    
                    <!-- Additional details can be added here -->
                    <div class="additional-info">
                      <h4>Job Distribution</h4>
                      <div class="distribution-bar-large">
                        <div class="distribution-segment active" :style="{ width: getPercentage(stats.jobs_in_progress, stats.jobs) + '%' }">
                          <span v-if="getPercentage(stats.jobs_in_progress, stats.jobs) > 10">Active: {{ getPercentage(stats.jobs_in_progress, stats.jobs) }}%</span>
                        </div>
                        <div class="distribution-segment expired" :style="{ width: getPercentage(stats.jobs_expired, stats.jobs) + '%' }">
                          <span v-if="getPercentage(stats.jobs_expired, stats.jobs) > 10">Expired: {{ getPercentage(stats.jobs_expired, stats.jobs) }}%</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button class="btn-secondary" @click="closeJobsModal">Close</button>
                </div>
              </div>
            </div>

            <!-- Applications Details Modal (Normal Page Display) -->
            <div v-if="showApplicationsModal" class="jobs-details-modal-overlay" @click="closeApplicationsModal">
              <div class="jobs-details-modal-content" @click.stop>
                <div class="modal-header">
                  <h3>
                    <i class="fas fa-file-alt"></i>
                    Applications Overview
                  </h3>
                  <button class="close-btn" @click="closeApplicationsModal">×</button>
                </div>
                <div class="modal-body">
                  <div class="detail-stats">
                    <div class="stat-item">
                      <span class="stat-label">Total Applications:</span>
                      <span class="stat-value">{{ stats.applications }}</span>
                    </div>
                    <div class="stat-item">
                      <span class="stat-label">Application Rate:</span>
                      <span class="stat-value">{{ stats.application_rate || 0 }}%</span>
                    </div>
                    <div class="stat-item">
                      <span class="stat-label">Jobs Per Application:</span>
                      <span class="stat-value">{{ stats.jobs > 0 ? (stats.applications / stats.jobs).toFixed(1) : 0 }}</span>
                    </div>
                    <div class="stat-item">
                      <span class="stat-label">Average Applications per Job:</span>
                      <span class="stat-value">{{ stats.jobs > 0 ? (stats.applications / stats.jobs).toFixed(1) : 0 }}</span>
                    </div>
                    
                    <div class="additional-info">
                      <h4>Application Trends</h4>
                      <div class="trend-info">
                        <div class="trend-item">
                          <span class="trend-label">Active Jobs:</span>
                          <span class="trend-value">{{ stats.jobs_in_progress || 0 }}</span>
                        </div>
                        <div class="trend-item">
                          <span class="trend-label">Applications per Active Job:</span>
                          <span class="trend-value">{{ stats.jobs_in_progress > 0 ? (stats.applications / stats.jobs_in_progress).toFixed(1) : 0 }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button class="btn-secondary" @click="closeApplicationsModal">Close</button>
                </div>
              </div>
            </div>

            <!-- Additional Charts Section -->
            <div class="charts-section">
              <div class="chart-card">
                <div class="chart-header">
                  <h4><i class="fas fa-chart-pie"></i> Jobs Distribution</h4>
                </div>
                <div class="chart-body">
                  <canvas ref="jobsChart" id="jobsChart"></canvas>
                </div>
              </div>
              
              <div class="chart-card">
                <div class="chart-header">
                  <h4><i class="fas fa-chart-line"></i> Activity Overview</h4>
                </div>
                <div class="chart-body">
                  <canvas ref="activityChart" id="activityChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Companies Table -->
          <div v-if="activeTab === 'companies'" class="data-section">
            <div class="section-header">
              <h3>Companies Directory</h3>
              <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  placeholder="Search companies..."
                  class="form-control"
                >
              </div>
            </div>
            
            <div class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Logo</th>
                    <th>Company Name</th>
                    <th>Email</th>
                    <th>Location</th>
                    <th>Industry</th>

                  </tr>
                </thead>
                <tbody>
                  <tr v-for="company in filteredCompanies" :key="company.id">
                    <td class="logo-cell">
                      <img 
                        v-if="company.logoUrl" 
                        :src="company.logoUrl" 
                        :alt="company.companyName"
                        class="company-logo"
                        @click="showImagePreview(company.logoUrl, company.companyName)"
                         style="object-fit: contain; cursor: pointer"
                      >
                      <div v-else class="placeholder-logo">
                        <i class="fas fa-building"></i>
                      </div>
                    </td>
                    <td class="company-name">{{ company.companyName }}</td>
                    <td>{{ company.email }}</td>
                    <td>
                      <i class="fas fa-map-marker-alt"></i>
                      {{ company.location || 'N/A' }}
                    </td>
                    <td>
                      <span class="industry-badge">{{ company.industry || 'N/A' }}</span>
                    </td>

                  </tr>
                  <tr v-if="filteredCompanies.length === 0">
                    <td colspan="6" class="empty-state">
                      <i class="fas fa-building"></i>
                      <p>No companies found</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Workers Table -->
          <div v-if="activeTab === 'workers'" class="data-section">
            <div class="section-header">
              <h3>Workers Directory</h3>
              <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  placeholder="Search workers..."
                  class="form-control"
                >
              </div>
            </div>
            
            <div class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Location</th>
                    <th>Industry</th>
    
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="worker in filteredWorkers" :key="worker.id">
                    <td class="avatar-cell">
                      <img 
                        v-if="worker.photoUrl" 
                        :src="worker.photoUrl" 
                        :alt="worker.fullName"
                        class="worker-avatar"
                        @click="showImagePreview(worker.photoUrl, worker.fullName)"
                        style="cursor: pointer"
                      >
                      <div v-else class="placeholder-avatar">
                        <i class="fas fa-user"></i>
                      </div>
                    </td>
                    <td class="worker-name">{{ worker.fullName }}</td>
                    <td>{{ worker.email }}</td>
                    <td>
                      <i class="fas fa-map-marker-alt"></i>
                      {{ worker.location || 'N/A' }}
                    </td>
                    <td>
                      <span class="industry-badge">{{ worker.industry || 'N/A' }}</span>
                    </td>

                  </tr>
                  <tr v-if="filteredWorkers.length === 0">
                    <td colspan="7" class="empty-state">
                      <i class="fas fa-users"></i>
                      <p>No workers found</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Sentry Issues -->
          <div v-if="activeTab === 'sentryFront' || activeTab === 'sentryBack'" class="data-section">
            <div class="section-header">
              <h3>
                <i class="fas fa-bug"></i>
                {{ activeTab === 'sentryFront' ? 'Frontend Issues' : 'Backend Issues' }}
              </h3>
              <div class="stats-summary">
                <span class="stat-badge total">
                  <i class="fas fa-chart-line"></i>
                  Total: {{ (activeTab === 'sentryFront' ? sentryFront : sentryBack).length }}
                </span>
                <span class="stat-badge unresolved">
                  <i class="fas fa-exclamation-circle"></i>
                  Unresolved
                </span>
              </div>
            </div>

            <div class="issues-grid">
              <div 
                v-for="issue in activeTab === 'sentryFront' ? sentryFront : sentryBack" 
                :key="issue.id"
                class="issue-card"
                :class="getIssueLevelClass(issue.level)"
              >
                <div class="issue-header">
                  <div class="issue-id">
                    <span class="badge">{{ issue.shortId }}</span>
                    <span class="level-badge" :class="issue.level">
                      <i :class="getLevelIcon(issue.level)"></i>
                      {{ issue.level || 'info' }}
                    </span>
                  </div>
                  <div class="issue-count">
                    <i class="fas fa-chart-simple"></i>
                    {{ issue.count }} events
                  </div>
                </div>
                
                <div class="issue-title">
                  {{ issue.title || issue.metadata?.value || 'Unknown issue' }}
                </div>
                
                <div class="issue-details">
                  <div class="detail-item">
                    <i class="fas fa-code-branch"></i>
                    {{ issue.culprit || 'Unknown location' }}
                  </div>
                  <div class="detail-item">
                    <i class="far fa-clock"></i>
                    First seen: {{ formatDate(issue.firstSeen) }}
                  </div>
                  <div class="detail-item">
                    <i class="fas fa-history"></i>
                    Last seen: {{ formatDate(issue.lastSeen) }}
                  </div>
                </div>
              </div>
              
              <div v-if="(activeTab === 'sentryFront' ? sentryFront : sentryBack).length === 0" class="empty-state-large">
                <i class="fas fa-check-circle"></i>
                <h4>No issues found</h4>
                <p>All systems are operational</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';

const router = useRouter();
const activeTab = ref('companies');
const sidebarCollapsed = ref(false);
const searchQuery = ref('');
const loading = ref(true);
const modalVisible = ref(false);
const modalData = ref({});

// New modal states for jobs and applications
const showJobsModal = ref(false);
const showApplicationsModal = ref(false);
const modalType = ref('in_progress');

const companies = ref([]);
const workers = ref([]);
const stats = ref({});
const sentryFront = ref([]);
const sentryBack = ref([]);
const currentDateTime = ref('');
const currentDate = ref('');

// Chart refs
const jobsChart = ref(null);
const activityChart = ref(null);
let jobsChartInstance = null;
let activityChartInstance = null;

const tabs = [
  { key: 'companies', label: 'Companies', icon: 'fas fa-building' },
  { key: 'workers', label: 'Workers', icon: 'fas fa-users' },
  { key: 'stats', label: 'Stats', icon: 'fas fa-chart-line' },
  { key: 'sentryFront', label: 'Sentry Front', icon: 'fas fa-laptop-code' },
  { key: 'sentryBack', label: 'Sentry Back', icon: 'fas fa-server' }
];

const currentTabLabel = computed(() => {
  const tab = tabs.find(t => t.key === activeTab.value);
  return tab ? tab.label : 'Dashboard';
});

const filteredCompanies = computed(() => {
  if (!searchQuery.value) return companies.value;
  const query = searchQuery.value.toLowerCase();
  return companies.value.filter(c => 
    c.companyName?.toLowerCase().includes(query) ||
    c.email?.toLowerCase().includes(query) ||
    c.location?.toLowerCase().includes(query) ||
    c.industry?.toLowerCase().includes(query)
  );
});

const filteredWorkers = computed(() => {
  if (!searchQuery.value) return workers.value;
  const query = searchQuery.value.toLowerCase();
  return workers.value.filter(w => 
    w.fullName?.toLowerCase().includes(query) ||
    w.email?.toLowerCase().includes(query) ||
    w.industry?.toLowerCase().includes(query) ||
    w.location?.toLowerCase().includes(query)
  );
});

// Computed for main stats (filter out job-specific ones from main grid)
const mainStats = computed(() => {
  const { jobs_in_progress, jobs_expired, application_rate, active_ratio, ...mainStatsData } = stats.value;
  return mainStatsData;
});

const fetchAdminData = async () => {
  loading.value = true;
  try {
    const base = import.meta.env.VITE_API_URL;
    const [companiesResp, workersResp, statsResp, sentryFrontResp, sentryBackResp] = await Promise.all([
      axios.get(`${base}/platform/getAllCompanies`),
      axios.get(`${base}/platform/getAllWorkers`),
      axios.get(`${base}/platform/stats`),
      axios.get(`${base}/platform/getsentryFront`),
      axios.get(`${base}/platform/getsentryBack`)
    ]);

    companies.value = companiesResp.data?.data || [];
    workers.value = workersResp.data?.data || [];
    stats.value = statsResp.data?.data || {};
    sentryFront.value = Array.isArray(sentryFrontResp.data) ? sentryFrontResp.data : [];
    sentryBack.value = Array.isArray(sentryBackResp.data) ? sentryBackResp.data : [];
  } catch (err) {
    console.error('Admin data fetch failed', err);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load admin data',
      confirmButtonColor: '#667eea'
    });
  } finally {
    loading.value = false;
  }
};

const updateDateTime = () => {
  const now = new Date();
  currentDateTime.value = now.toLocaleString('en-US', { 
    weekday: 'short', 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const updateDate = () => {
  const now = new Date();
  currentDate.value = now.toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric'
  });
};

const logout = () => {
  Swal.fire({
    title: 'Logout',
    text: 'Are you sure you want to logout?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#f56565',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, logout'
  }).then((result) => {
    if (result.isConfirmed) {
      localStorage.removeItem('user_role');
      localStorage.removeItem('auth_token');
      router.push('/admin/login');
    }
  });
};

const showImagePreview = (imageUrl, name) => {
  if (!imageUrl) return;
  
  Swal.fire({
    title: name || 'Image Preview',
    imageUrl: imageUrl,
    imageAlt: name || 'Image',
    imageWidth: '100%',
    imageHeight: 'auto',
    showCloseButton: true,
    showConfirmButton: false,
    background: '#1a1a2e',
    customClass: {
      popup: 'image-preview-modal',
      title: 'text-white'
    }
  });
};

const viewDetails = (item, type) => {
  modalData.value = {
    name: type === 'company' ? item.companyName : item.fullName,
    email: item.email,
    location: item.location,
    industry: item.industry,
    created_at: item.created_at
  };
  modalVisible.value = true;
};

const closeModal = () => {
  modalVisible.value = false;
};

const formatNumber = (num) => {
  if (!num && num !== 0) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};

const formatStatLabel = (key) => {
  const labels = {
    users: 'Total Users',
    companies: 'Companies',
    workers: 'Workers',
    jobs: 'Total Jobs',
    applications: 'Applications'
  };
  return labels[key] || key.replace(/_/g, ' ').replace(/([A-Z])/g, ' $1').toLowerCase();
};

const getStatCardClass = (key) => {
  const classes = {
    users: 'gradient-blue',
    companies: 'gradient-green',
    workers: 'gradient-orange',
    jobs: 'gradient-purple',
    applications: 'gradient-pink'
  };
  return classes[key] || '';
};

const getStatIcon = (key) => {
  const icons = {
    users: 'fas fa-users',
    companies: 'fas fa-building',
    workers: 'fas fa-user',
    jobs: 'fas fa-briefcase',
    applications: 'fas fa-file-alt'
  };
  return icons[key] || 'fas fa-chart-simple';
};

const getIconClass = (key) => {
  const classes = {
    users: 'blue',
    companies: 'green',
    workers: 'orange',
    jobs: 'purple',
    applications: 'pink'
  };
  return classes[key] || 'gray';
};

const getTrend = (key) => {
  const trends = {
    users: '+12%',
    companies: '+5%',
    workers: '+8%',
    jobs: '+15%',
    applications: '+23%'
  };
  return trends[key] || null;
};

const getTrendIcon = (key) => {
  return 'fas fa-arrow-up';
};

const getPercentage = (value, total) => {
  if (!total || total === 0) return 0;
  return Math.round((value / total) * 100);
};

const getProgressRingDasharray = (value, total) => {
  const percentage = getPercentage(value, total);
  const circumference = 2 * Math.PI * 35;
  const dash = (percentage / 100) * circumference;
  return `${dash} ${circumference}`;
};

// Updated functions to show normal modals instead of SweetAlert
const showJobsDetails = (type) => {
  modalType.value = type;
  showJobsModal.value = true;
};

const closeJobsModal = () => {
  showJobsModal.value = false;
};

const showApplicationsDetails = () => {
  showApplicationsModal.value = true;
};

const closeApplicationsModal = () => {
  showApplicationsModal.value = false;
};

const getIssueLevelClass = (level) => {
  const classes = {
    error: 'error',
    fatal: 'error',
    warning: 'warning',
    info: 'info'
  };
  return classes[level] || 'info';
};

const getLevelIcon = (level) => {
  const icons = {
    error: 'fas fa-times-circle',
    fatal: 'fas fa-skull-crossbones',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle'
  };
  return icons[level] || 'fas fa-circle-info';
};

// Initialize charts
const initCharts = () => {
  nextTick(() => {
    // Jobs Distribution Chart (Pie)
    const jobsCtx = document.getElementById('jobsChart')?.getContext('2d');
    if (jobsCtx && jobsChartInstance) {
      jobsChartInstance.destroy();
    }
    if (jobsCtx && (stats.value.jobs_in_progress !== undefined || stats.value.jobs_expired !== undefined)) {
      jobsChartInstance = new Chart(jobsCtx, {
        type: 'doughnut',
        data: {
          labels: ['Jobs In Progress', 'Jobs Expired'],
          datasets: [{
            data: [stats.value.jobs_in_progress || 0, stats.value.jobs_expired || 0],
            backgroundColor: ['#48bb78', '#f56565'],
            borderWidth: 0,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                font: {
                  size: 12
                }
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.raw || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                  return `${label}: ${value} (${percentage}%)`;
                }
              }
            }
          }
        }
      });
    }

    // Activity Overview Chart (Bar)
    const activityCtx = document.getElementById('activityChart')?.getContext('2d');
    if (activityCtx && activityChartInstance) {
      activityChartInstance.destroy();
    }
    if (activityCtx) {
      activityChartInstance = new Chart(activityCtx, {
        type: 'bar',
        data: {
          labels: ['Companies', 'Workers', 'Jobs', 'Applications'],
          datasets: [{
            label: 'Count',
            data: [
              stats.value.companies || 0,
              stats.value.workers || 0,
              stats.value.jobs || 0,
              stats.value.applications || 0
            ],
            backgroundColor: [
              'rgba(102, 126, 234, 0.8)',
              'rgba(72, 187, 120, 0.8)',
              'rgba(237, 137, 54, 0.8)',
              'rgba(246, 135, 179, 0.8)'
            ],
            borderRadius: 8,
            barPercentage: 0.6,
            categoryPercentage: 0.8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return `${context.raw.toLocaleString()} items`;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                  if (value >= 1000) return (value / 1000).toFixed(1) + 'K';
                  return value;
                }
              },
              grid: {
                color: '#e2e8f0'
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        }
      });
    }
  });
};

// Watch for stats changes to update charts
watch(() => stats.value, () => {
  if (activeTab.value === 'stats') {
    initCharts();
  }
}, { deep: true });

// Watch for active tab changes to initialize charts
watch(activeTab, (newTab) => {
  if (newTab === 'stats') {
    setTimeout(initCharts, 100);
  }
});

onMounted(() => {
  const role = localStorage.getItem('user_role');
  if (role !== 'admin') {
    router.replace('/admin/login');
    return;
  }
  fetchAdminData();
  updateDateTime();
  updateDate();
  setInterval(updateDateTime, 60000);
});
</script>

<style scoped>
/* Add these styles for image preview modal */
:deep(.image-preview-modal) {
  background: rgba(26, 26, 46, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  max-width: 90vw;
  width: auto !important;
}

:deep(.image-preview-modal .swal2-title) {
  color: white;
  font-size: 1.25rem;
}

:deep(.image-preview-modal .swal2-image) {
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  max-height: 80vh;
  object-fit: contain;
}

:deep(.image-preview-modal .swal2-close) {
  color: white;
  font-size: 2rem;
}

:deep(.image-preview-modal .swal2-close:hover) {
  color: #f56565;
}

.admin-dashboard {
  display: flex;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Sidebar Styles */
.admin-sidebar {
  width: 280px;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  z-index: 100;
}

.admin-sidebar.collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  position: relative;
}

.logo, .logo-icon {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #667eea;
}

.logo i, .logo-icon i {
  font-size: 1.5rem;
}

.toggle-btn {
  background: #f0f0f0;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  color: #666;
}

.toggle-btn:hover {
  background: #e0e0e0;
  transform: scale(1.05);
}

.admin-info {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.admin-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
}

.admin-details h4 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
}

.admin-details span {
  font-size: 0.8rem;
  color: #666;
}

.sidebar-nav {
  flex: 1;
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  text-decoration: none;
  color: #4a5568;
  transition: all 0.3s;
  cursor: pointer;
  position: relative;
}

.nav-item i {
  width: 24px;
  font-size: 1.1rem;
}

.nav-item span:not(.badge) {
  flex: 1;
}

.nav-item .badge {
  background: #667eea;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
}

.nav-item:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.nav-item.active {
  background: linear-gradient(135deg, #667eeac9 0%, #667eea  100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.logout-btn {
  width: 100%;
  padding: 0.75rem;
  background: #f56565b4;
  border: none;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 500;
}

.logout-btn:hover {
  background: #e53e3e;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(245, 101, 101, 0.3);
}

/* Main Content */
.admin-main {
  margin-left: 280px;
  flex: 1;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: #f7fafc;
}

.admin-main.expanded {
  margin-left: 80px;
}

.main-header {
  background: white;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-left h1 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.header-left p {
  margin: 0.25rem 0 0;
  font-size: 0.875rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.date-time {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #4a5568;
  font-size: 0.875rem;
}

.refresh-btn {
  width: 36px;
  height: 36px;
  background: #f0f0f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
}

.refresh-btn:hover {
  background: #e0e0e0;
  transform: rotate(180deg);
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.main-content {
  padding: 2rem;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.stat-card.gradient-blue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-card.gradient-green {
  background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
  color: white;
}

.stat-card.gradient-orange {
  background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
  color: white;
}

.stat-card.gradient-purple {
  background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);
  color: white;
}

.stat-card.gradient-pink {
  background: linear-gradient(135deg, #f687b3 0%, #ed64a6 100%);
  color: white;
}

.stat-card .stat-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
}

.stat-card .stat-info h3 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
}

.stat-card .stat-info p {
  margin: 0.25rem 0 0;
  opacity: 0.9;
  font-size: 0.875rem;
}

.stat-trend {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
}

/* Jobs Status Section */
.jobs-status-section {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.jobs-status-section .section-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.jobs-status-section .section-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-badge {
  background: #f7fafc;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  color: #4a5568;
}

.jobs-status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.status-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.status-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border-color: transparent;
}

.status-card.in-progress:hover {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.status-card.expired:hover {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

.status-card.applications:hover {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.status-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.status-card.in-progress .status-icon {
  background: #dcfce7;
  color: #48bb78;
}

.status-card.expired .status-icon {
  background: #fee2e2;
  color: #f56565;
}

.status-card.applications .status-icon {
  background: #dbeafe;
  color: #667eea;
}

.status-badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-weight: 600;
}

.status-card.in-progress .status-badge {
  background: #48bb78;
  color: white;
}

.status-card.expired .status-badge {
  background: #f56565;
  color: white;
}

.status-card.applications .status-badge {
  background: #667eea;
  color: white;
}

.status-content {
  margin-bottom: 1rem;
}

.status-number {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2d3748;
}

.status-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4a5568;
  margin-top: 0.25rem;
}

.status-description {
  font-size: 0.75rem;
  color: #718096;
  margin-top: 0.5rem;
}

.status-footer {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.progress-ring {
  position: relative;
  width: 80px;
  height: 80px;
}

.progress-ring svg {
  transform: rotate(-90deg);
}

.progress-ring .percentage {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 1rem;
  font-weight: 700;
  color: #48bb78;
}

.trend-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.trend-indicator.down {
  color: #f56565;
}

.rate-bar {
  width: 100%;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.rate-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  border-radius: 4px;
  transition: width 1s ease;
}

/* Jobs Details Modal Styles */
.jobs-details-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.jobs-details-modal-content {
  background: white;
  border-radius: 20px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow: auto;
  animation: slideUp 0.3s ease;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    transform: translateY(50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 20px 20px 0 0;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.modal-header .close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.modal-header .close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
}

.btn-secondary {
  padding: 0.5rem 1.5rem;
  background: #e2e8f0;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #cbd5e0;
  transform: translateY(-1px);
}

.detail-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 12px;
}

.stat-label {
  font-weight: 600;
  color: #4a5568;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #667eea;
}

.info-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
}

.info-message.info {
  background: #e6f7ff;
  color: #1890ff;
}

.info-message.warning {
  background: #fff7e6;
  color: #fa8c16;
}

.additional-info {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.additional-info h4 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #2d3748;
}

.distribution-bar-large {
  display: flex;
  height: 40px;
  border-radius: 8px;
  overflow: hidden;
  margin-top: 0.5rem;
}

.distribution-segment {
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  transition: all 0.3s;
}

.distribution-segment.active {
  background: #48bb78;
}

.distribution-segment.expired {
  background: #f56565;
}

.trend-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.trend-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: #f7fafc;
  border-radius: 8px;
}

.trend-label {
  font-weight: 500;
  color: #4a5568;
}

.trend-value {
  font-weight: 600;
  color: #667eea;
}

/* Charts Section */
.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.chart-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.chart-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.chart-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.chart-body {
  padding: 1.5rem;
  height: 300px;
  position: relative;
}

/* Data Section */
.data-section {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.section-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.section-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.search-box {
  position: relative;
  width: 300px;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #a0aec0;
}

.search-box input {
  width: 100%;
  padding: 0.5rem 0.5rem 0.5rem 2rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
}

/* Table Styles */
.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: #f7fafc;
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  color: #4a5568;
  border-bottom: 2px solid #e2e8f0;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.875rem;
}

.company-logo, .worker-avatar {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  object-fit: cover;
  cursor: pointer;
  transition: transform 0.2s;
}

.company-logo:hover, .worker-avatar:hover {
  transform: scale(1.1);
}

.worker-avatar {
  border-radius: 50%;
}

.placeholder-logo, .placeholder-avatar {
  width: 40px;
  height: 40px;
  background: #e2e8f0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a0aec0;
}

.placeholder-avatar {
  border-radius: 50%;
}

.industry-badge, .job-badge {
  background: #e6f7ff;
  color: #1890ff;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  display: inline-block;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.3s;
  color: #667eea;
}

.icon-btn:hover {
  background: #f0f0f0;
  transform: scale(1.1);
}

/* Issues Grid */
.issues-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1rem;
  padding: 1.5rem;
}

.issue-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  border-left: 4px solid;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.issue-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.issue-card.error { border-left-color: #f56565; }
.issue-card.warning { border-left-color: #ed8936; }
.issue-card.info { border-left-color: #4299e1; }

.issue-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.issue-id {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.badge {
  background: #e2e8f0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  font-family: monospace;
}

.level-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
  text-transform: uppercase;
}

.level-badge.error { background: #fed7d7; color: #c53030; }
.level-badge.warning { background: #feebc8; color: #c05621; }
.level-badge.info { background: #bee3f8; color: #2c5282; }

.issue-title {
  font-weight: 600;
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}

.issue-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #718096;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stats-summary {
  display: flex;
  gap: 1rem;
}

.stat-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
}

.stat-badge.total {
  background: #e6f7ff;
  color: #1890ff;
}

.stat-badge.unresolved {
  background: #fff1f0;
  color: #f5222d;
}

/* Loading */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  gap: 1rem;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Empty States */
.empty-state, .empty-state-large {
  text-align: center;
  padding: 3rem;
  color: #a0aec0;
}

.empty-state i, .empty-state-large i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state-large i {
  font-size: 4rem;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
  }
  
  .admin-sidebar.collapsed {
    transform: translateX(0);
    width: 280px;
  }
  
  .admin-main {
    margin-left: 0;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .jobs-status-grid {
    grid-template-columns: 1fr;
  }
  
  .charts-section {
    grid-template-columns: 1fr;
  }
  
  .chart-body {
    height: 250px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-box {
    width: 100%;
  }
  
  .issues-grid {
    grid-template-columns: 1fr;
  }
  
  .jobs-details-modal-content {
    width: 95%;
    margin: 1rem;
  }
}
</style>