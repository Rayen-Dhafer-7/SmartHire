// Worker service - handles worker-related API calls
import apiClient from './api.js';
import { API_ENDPOINTS } from '../config/api.js';

/**
 * Get worker profile
 * @returns {Promise} Worker profile data
 */
export const getProfile = async () => {
    const response = await apiClient.get(API_ENDPOINTS.WORKER_PROFILE);
    return response.data;
};

/**
 * Update worker profile
 * @param {Object} profileData - Updated profile data
 * @returns {Promise} API response
 */
export const updateProfile = async (profileData) => {
    const response = await apiClient.put(API_ENDPOINTS.WORKER_PROFILE, profileData);
    return response.data;
};

/**
 * Get available jobs
 * @returns {Promise} List of available jobs
 */
export const getJobs = async () => {
    const response = await apiClient.get(API_ENDPOINTS.WORKER_JOBS);
    return response.data;
};

/**
 * Get worker applications
 * @returns {Promise} List of applications
 */
export const getApplications = async () => {
    const response = await apiClient.get(API_ENDPOINTS.WORKER_APPLICATIONS);
    return response.data;
};

// Add more worker-related API methods as needed
