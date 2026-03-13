// File upload composable - reusable file upload logic
import { ref } from 'vue';

export function useFileUpload() {
    const photoPreview = ref(null);
    const selectedFile = ref(null);

    /**
     * Trigger file input click
     * @param {string} inputId - ID of the file input element
     */
    const triggerFileInput = (inputId = 'profile-upload') => {
        const input = document.getElementById(inputId);
        if (input) {
            input.click();
        }
    };

    /**
     * Handle file change event
     * @param {Event} event - File input change event
     */
    const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (file) {
            selectedFile.value = file;

            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                photoPreview.value = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    };

    /**
     * Clear file selection and preview
     */
    const clearFile = () => {
        selectedFile.value = null;
        photoPreview.value = null;
    };

    return {
        photoPreview,
        selectedFile,
        triggerFileInput,
        handleFileChange,
        clearFile
    };
}
