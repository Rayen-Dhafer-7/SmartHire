<template>
  <div class="social-links-form">
    <div class="section-header">
      <div class="header-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
      </div>
      <h4 class="section-title">Social Presence</h4>
      <p class="section-subtitle">Connect your company's social media profiles</p>
    </div>

    <div class="social-grid">
      <div class="social-item">
        <div class="social-icon website">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
          </svg>
        </div>
        <div class="social-field">
          <label class="form-label">Website</label>
          <input type="url" class="form-control" v-model="urls.website" placeholder="https://yourcompany.com" />
        </div>
      </div>

      <div class="social-item">
        <div class="social-icon linkedin">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
            <rect x="2" y="9" width="4" height="12"/>
            <circle cx="4" cy="4" r="2"/>
          </svg>
        </div>
        <div class="social-field">
          <label class="form-label">LinkedIn</label>
          <input type="url" class="form-control" v-model="urls.linkedin" placeholder="https://linkedin.com/company/..." />
        </div>
      </div>

      <div class="social-item">
        <div class="social-icon twitter">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
          </svg>
        </div>
        <div class="social-field">
          <label class="form-label">Twitter / X</label>
          <input type="url" class="form-control" v-model="urls.twitter" placeholder="https://x.com/..." />
        </div>
      </div>

      <div class="social-item">
        <div class="social-icon facebook">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
          </svg>
        </div>
        <div class="social-field">
          <label class="form-label">Facebook</label>
          <input type="url" class="form-control" v-model="urls.facebook" placeholder="https://facebook.com/..." />
        </div>
      </div>

      <div class="social-item">
        <div class="social-icon instagram">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
          </svg>
        </div>
        <div class="social-field">
          <label class="form-label">Instagram</label>
          <input type="url" class="form-control" v-model="urls.instagram" placeholder="https://instagram.com/..." />
        </div>
      </div>

      <div class="social-item">
        <div class="social-icon email">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
        </div>
        <div class="social-field">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" v-model="urls.mail" placeholder="contact@company.com" />
        </div>
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
.social-links-form {
  padding: 0 2rem 1rem 2rem;
}

.section-header {
  margin-bottom: 1.5rem;
  text-align: center;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #eef2ff, #ffffff);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.75rem;
  color: #4f46e5;
  border: 1px solid #e2e8f0;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 0.25rem;
}

.section-subtitle {
  font-size: 0.75rem;
  color: #94a3b8;
  margin: 0;
}

.social-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.social-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.social-item:hover {
  background: #f1f5f9;
}

.social-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.social-icon.website { background: #eef2ff; color: #4f46e5; }
.social-icon.linkedin { background: #eef2ff; color: #0077b5; }
.social-icon.twitter { background: #eef2ff; color: #1da1f2; }
.social-icon.facebook { background: #eef2ff; color: #1877f2; }
.social-icon.instagram { background: #eef2ff; color: #e4405f; }
.social-icon.email { background: #eef2ff; color: #ea4335; }

.social-field {
  flex: 1;
}

.social-field .form-label {
  display: block;
  margin-bottom: 0.25rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-control {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.8rem;
  transition: all 0.3s ease;
  background: white;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

@media (max-width: 992px) {
  .social-links-form {
    padding: 0 1rem 1rem 1rem;
  }
  
  .social-grid {
    grid-template-columns: 1fr;
  }
}
</style>