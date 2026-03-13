// Notification utilities using SweetAlert2
import Swal from 'sweetalert2';

const PRIMARY_COLOR = '#4f46e5';

/**
 * Show success notification
 * @param {string} title - The title of the notification
 * @param {string} text - The message text
 * @param {number} timer - Auto-close timer in ms (optional)
 * @param {string} fileName - Name of the CV file

 */


export const showDownloadingCV = (fileName) => {
    return Swal.fire({
        title: 'Extract CV...',
        text: `Preparing to download ${fileName}`,
        icon: 'info',
        showConfirmButton: false,
         timer: 15500,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

 



export const showSuccess = (title, text, timer = null) => {
    return Swal.fire({
        icon: 'success',
        title,
        text,
        confirmButtonColor: PRIMARY_COLOR,
        timer,
        showConfirmButton: !timer
    });
};

/**
 * Show error notification
 * @param {string} title - The title of the notification
 * @param {string} text - The error message
 */
export const showError = (title, text) => {
    return Swal.fire({
        icon: 'error',
        title,
        text,
        confirmButtonColor: PRIMARY_COLOR
    });
};

/**
 * Show confirmation dialog
 * @param {string} title - The title of the dialog
 * @param {string} text - The message text
 * @param {string} confirmButtonText - Text for confirm button
 * @param {string} cancelButtonText - Text for cancel button
 */
export const showConfirm = (title, text, confirmButtonText = 'Yes', cancelButtonText = 'Cancel') => {
    return Swal.fire({
        title,
        text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        confirmButtonColor: PRIMARY_COLOR
    });
};

/**
 * Show info notification
 * @param {string} title - The title of the notification
 * @param {string} text - The message text
 */
export const showInfo = (title, text) => {
    return Swal.fire({
        icon: 'info',
        title,
        text,
        confirmButtonColor: PRIMARY_COLOR
    });
};

/**
 * Show warning notification
 * @param {string} title - The title of the notification
 * @param {string} text - The warning message
 */
export const showWarning = (title, text) => {
    return Swal.fire({
        icon: 'warning',
        title,
        text,
        confirmButtonColor: PRIMARY_COLOR
    });
};

/**
 * Show loading notification
 * @param {string} title - The title of the loading notification
 * @param {string} text - Optional loading message
 */
export const showLoading = (title = 'Loading...', text = 'Please wait') => {
    return Swal.fire({
        title,
        text,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

/**
 * Close any open Swal notification
 */
export const closeLoading = () => {
    Swal.close();
};
