<template>
  <div class="tab-content">
    <div class="card shadow-sm mt-4">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">CV Upload</h3>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <h5>Upload Your CV</h5>
            <p class="text-muted">
              Upload your CV/resume (PDF, DOC, DOCX). We'll use AI to automatically extract and update your professional information.
            </p>
            <div class="mb-4">
              <div class="input-group">
                <input 
                  type="file" 
                  class="form-control" 
                  accept=".pdf,.doc,.docx" 
                  @change="handleResumeUpload"
                  ref="resumeInput"
                  id="cvUpload"
                >
                <button class="btn btn-primary" type="button" @click="uploadResume">
                  <i class="bi bi-upload"></i> Upload CV
                </button>
              </div>
              <div class="form-text">
                Maximum file size: 10MB. Supported formats: PDF, DOC, DOCX.
              </div>
            </div>
            
            <div v-if="profile.resumeName" class="alert alert-success">
              <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                  <strong>CV Uploaded Successfully!</strong>
                  <div>{{ profile.resumeName }} ({{ profile.resumeSize }})</div>
                  <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary me-2" @click="extractInfoFromCV">
                      <i class="bi bi-magic"></i> Extract Info with AI
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="removeCV">
                      <i class="bi bi-trash"></i> Remove CV
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card border-primary">
              <div class="card-header bg-light">
                <h6 class="mb-0">AI Extraction Benefits</h6>
              </div>
              <div class="card-body">
                <ul class="list-unstyled mb-0">
                  <li class="mb-2">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    <small>Auto-fill skills from your CV</small>
                  </li>
                  <li class="mb-2">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    <small>Extract work experience details</small>
                  </li>
                  <li class="mb-2">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    <small>Identify certifications</small>
                  </li>
                  <li class="mb-2">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    <small>Parse education history</small>
                  </li>
                  <li>
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    <small>Save time on manual entry</small>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import { showWarning, showConfirm, showSuccess, showDownloadingCV, closeLoading } from '../../../../utils/notifications';

const props = defineProps({
  profile: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['file-selected', 'remove-cv', 'update-profile']);

const handleResumeUpload = (event) => {
  const file = event.target.files[0];
  
  if (!file) return;
  
  // Validate file type
  const validExtensions = ['.pdf', '.doc', '.docx'];
  const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
  
  if (!validExtensions.includes(fileExtension)) {
    showWarning('Invalid File', 'Only PDF, DOC, and DOCX files are allowed.');
    event.target.value = ''; // Clear input
    return;
  }
  
  // Validate file size (10MB = 10 * 1024 * 1024)
  if (file.size > 10 * 1024 * 1024) {
    showWarning('File Too Large', 'File size should be less than 10MB.');
    event.target.value = '';
    return;
  }
  
  console.log('Valid file selected:', file.name);
  emit('file-selected', file);
};

const uploadResume = () => {
  document.getElementById('cvUpload').click();
};

const removeCV = () => {
   showConfirm(
    'Remove CV?',
    'Are you sure you want to remove your CV? This will delete the uploaded file.',
    'Yes, remove it',
    'Cancel'
  ).then((result) => {
    if (result.isConfirmed) {
       emit('remove-cv', props.profile.resumeId);
     }
  });
};


const extractInfoFromCV = async () => {
  if (!props.profile.resumeName) {
    showWarning('No CV Uploaded', 'Please upload a CV first.');
    return;
  }

  const result = await showConfirm(
    'AI Extraction',
    'This feature would parse your CV and update your profile automatically.',
    'Simulate AI Extraction',
    'Cancel'
  );

  if (!result.isConfirmed) return;

  try {
    // 1️⃣ Simulate async operations happening simultaneously
    await Promise.all([
      (async () => emit('update-profile'))(),
      (async () => showDownloadingCV(props.profile.resumeName))()
    ]);

    // 2️⃣ Wait for a short delay if needed (e.g., download simulation)
    await new Promise(resolve => setTimeout(resolve, 1500));

    // 3️⃣ Show success after both complete
    showSuccess('Saved', 'Your changes have been saved');
    closeLoading();

  } catch (error) {
    closeLoading();
    showError('Error', 'Something went wrong while saving.');
  }
};

</script>

<style scoped>
.card.border-primary {
  border-color: #4f46e5 !important;
}
</style>
