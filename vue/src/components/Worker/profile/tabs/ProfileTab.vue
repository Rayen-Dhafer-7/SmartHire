<template>
  <div class="tab-content">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">General Information</h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="$emit('save-profile')">
          <!-- Photo and Basic Info -->
          <div class="row mb-4">
            <div class="col-md-3 text-center mb-3 mb-md-0">
              <div class="position-relative d-inline-block">
                <img :src="logoPreview || profile.photo || 'https://via.placeholder.com/150'" 
                     class="img-thumbnail rounded-circle mb-2" 
                     style="width: 150px; height: 150px; object-fit: cover;">
                <div class="mt-2">
                  <label for="photoUpload" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-camera"></i> Change Photo
                  </label>
                  <input type="file" id="photoUpload" class="d-none" accept="image/*" @change="handlePhotoUpload" name="profile">
                </div>
              </div>
            </div>
            <div class="col-md-9">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" v-model="profile.fullName" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" v-model="profile.email" disabled>
                <small class="text-muted">Email cannot be changed</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" v-model="profile.location" placeholder="e.g. New York, USA">
              </div>
            </div>
          </div>

          <!-- Professional Info -->
          <h5 class="mb-3 border-bottom pb-2">Professional Details</h5>
          
          <!-- Current CV Download -->
          <div class="mb-4">
            <h5 class="mb-0">Current CV</h5>
            <div v-if="profile.resumeName" class="alert alert-info d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark-pdf fs-4 me-3 text-danger"></i>
                <div>
                  <strong>{{ profile.resumeName }}</strong>
                  <div class="small">Uploaded on {{ profile.resumeDate || 'recently' }}</div>
                  <div class="small">Size {{ profile.resumeSize || 'recently' }}</div>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" @click="$emit('download-cv')">
                <i class="bi bi-download"></i> Download CV
              </button>
            </div>
            <div v-else class="text-muted">
              No CV uploaded yet. Upload your CV in the CV Upload tab.
            </div>
          </div>

          <div class="mb-3">
            <h5 class="mb-0">Bio / Description</h5>
            <textarea class="form-control" rows="4" v-model="profile.bio" placeholder="Tell us about yourself..."></textarea>
          </div>

          <!-- Component Sections -->
          <EducationSection 
            :educationList="profile.education" 
            @add="$emit('add-education', $event)" 
            @remove="$emit('remove-education', $event)" 
          />
          <br><br>

          <ExperienceSection 
            :experienceList="profile.experience" 
            @add="$emit('add-experience', $event)" 
            @remove="$emit('remove-experience', $event)" 
          />
          <br><br>

          <ProjectsSection 
            :projectsList="profile.projects" 
            @add="$emit('add-project', $event)" 
            @remove="$emit('remove-project', $event)" 
          />
          <br><br>

          <SkillsSection 
            :skills="profile.skills" 
            @add="$emit('add-skill', $event)" 
            @remove="$emit('remove-skill', $event)" 
          />
          <br><br>

          <CertificationsSection 
            :certificationsList="profile.certifications" 
            @add="$emit('add-certification', $event)" 
            @remove="$emit('remove-certification', $event)" 
          />
          <br><br>

          <div class="row">
            <div class="col-md-6 mb-3">
              <h5 class="mb-0">LinkedIn URL</h5>
              <input type="url" class="form-control" v-model="profile.linkedin">
            </div>
            <div class="col-md-6 mb-3">
              <h5 class="mb-0">GitHub URL</h5>
              <input type="url" class="form-control" v-model="profile.github">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <h5 class="mb-0">Website URL</h5>
              <input type="url" class="form-control" v-model="profile.website">
            </div>
            <div class="col-md-6 mb-3">
              <h5 class="mb-0">Mail URL</h5>
              <input type="url" class="form-control" v-model="profile.gmail">
            </div>
          </div>
          <br><br><br>

          <!-- Save Buttons -->
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" @click="$emit('reset-form')">Reset</button>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-lg"></i> Save Profile
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import EducationSection from '../sections/EducationSection.vue';
import ExperienceSection from '../sections/ExperienceSection.vue';
import ProjectsSection from '../sections/ProjectsSection.vue';
import SkillsSection from '../sections/SkillsSection.vue';
import CertificationsSection from '../sections/CertificationsSection.vue';

defineProps({
  profile: {
    type: Object,
    required: true
  },
  logoPreview: {
    type: String,
    default: null
  }
});

const emit = defineEmits([
  'save-profile', 
  'reset-form', 
  'download-cv', 
  'photo-selected',
  'add-education', 'remove-education',
  'add-experience', 'remove-experience',
  'add-project', 'remove-project',
  'add-skill', 'remove-skill',
  'add-certification', 'remove-certification'
]);

const handlePhotoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    emit('photo-selected', file);
  }
};
</script>

<style scoped>
.img-thumbnail {
  border-radius: 50%;
  border: 4px solid #fff;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style>