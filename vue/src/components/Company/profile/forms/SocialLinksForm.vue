<template>
  <div>
    <div class="form-section-title mt-4">Social Presence</div>
    <div class="grid-3">
      <div class="mb-3">
        <label class="form-label">Website</label>
        <input type="url" class="form-control" v-model="urls.website" placeholder="https://" />
      </div>
      <div class="mb-3">
        <label class="form-label">LinkedIn</label>
        <input type="url" class="form-control" v-model="urls.linkedin" placeholder="https://linkedin.com/in/..." />
      </div>
      <div class="mb-3">
        <label class="form-label">Twitter / X</label>
        <input type="url" class="form-control" v-model="urls.twitter" placeholder="https://x.com/..." />
      </div>
      <div class="mb-3">
        <label class="form-label">Facebook</label>
        <input type="url" class="form-control" v-model="urls.facebook" placeholder="https://facebook.com/..." />
      </div>
      <div class="mb-3">
        <label class="form-label">Instagram</label>
        <input type="url" class="form-control" v-model="urls.instagram" placeholder="https://instagram.com/..." />
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input 
          type="email" 
          class="form-control" 
          v-model="urls.mail" 
          placeholder="example@gmail.com" 
        />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  urls: {
    type: Object,
    required: true
  }
});
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

.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

@media (max-width: 992px) {
  .grid-3 {
    grid-template-columns: 1fr;
  }
}
</style>
