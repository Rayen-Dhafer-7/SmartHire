<template>
  <div class="tab-content">
    <div class="card mb-4">
      <div class="card-body">
        <form @submit.prevent="$emit('save-profile')">
          <CompanyInfoForm 
            :profile="profile" 
            :logoPreview="logoPreview"
            @logo-change="$emit('logo-change', $event)"
            @trigger-logo-upload="$emit('trigger-logo-upload')"
          />

          <SocialLinksForm 
            :urls="profile.urls"
          />

          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary">Save Profile</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import CompanyInfoForm from '../forms/CompanyInfoForm.vue';
import SocialLinksForm from '../forms/SocialLinksForm.vue';

defineProps({
  profile: {
    type: Object,
    required: true
  },
  logoPreview: {
    type: [String, Object],
    default: null
  }
});

defineEmits(['save-profile', 'logo-change', 'trigger-logo-upload']);
</script>

<style scoped>
.text-end {
  text-align: right;
}

.btn {
  padding: 8px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background-color: #4f46e5;
  border: 1px solid #4f46e5;
  color: white;
}

.btn-primary:hover {
  background-color: #4338ca;
}

.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 24px;
  margin-bottom: 20px;
}
</style>