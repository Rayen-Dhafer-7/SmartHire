<template>
  <div>
    <div class="form-section-title">General Information 33</div>
    <div class="grid-3 mb-4">
      <div class="mb-3">
        <label class="form-label">Company Name</label>
        <input type="text" class="form-control" id="company_name" name="company_name" v-model="profile.name" />

      </div>
      <div class="mb-3">
        <label class="form-label">Location</label>
        <input type="text" class="form-control" id="location" name="location" v-model="profile.location" />

      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="text" class="form-control" id="email" name="email" v-model="profile.email" />
      </div>

      
    </div>

    <!-- Second Row: Image and Description -->
    <div class="grid-2 mb-4">
      <div class="mb-3">
        <label class="form-label">Profile Picture</label>
        <div class="profile-upload-container">
          <input 
            type="file" 
            id="logo-upload" 
            accept="image/*" 
            class="hidden-input" 
            @change="$emit('logo-change', $event)"
            style="display: none;"
          />
          <div class="profile-upload-circle" @click="$emit('trigger-logo-upload')">
            <div>
              <!-- Use the computed logoPreview from parent -->
              <img :src="logoPreview" alt="Company Logo" class="profile-preview" />
              <div class="upload-overlay">
                <span class="edit-icon">✎</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
                <textarea class="form-control" id="industry_description" name="industry_description" rows="6" v-model="profile.description"></textarea>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  profile: {
    type: Object,
    required: true
  },
  logoPreview: {
    type: [String, Object],
    default: 'https://via.placeholder.com/150'
  }
});

defineEmits(['logo-change', 'trigger-logo-upload']);
</script>

<style scoped>
 
.form-section-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #f0f0f0;
}

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

/* Profile Upload Styles */
.profile-upload-container {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.profile-upload-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background-color: #f8f9fa;
  border: 2px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.3s ease;
  position: relative;
}

.profile-upload-circle:hover {
  border-color: #4f46e5;
  background-color: #eef2ff;
}

.profile-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.profile-upload-circle:hover .upload-overlay {
  opacity: 1;
}

.edit-icon {
  color: white;
  font-size: 20px;
}

@media (max-width: 992px) {
  .grid-2,
  .grid-3 {
    grid-template-columns: 1fr;
  }
}
</style>