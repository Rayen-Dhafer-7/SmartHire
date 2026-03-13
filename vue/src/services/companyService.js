// Company service - handles company-related API calls
import apiClient from './api.js';
import { API_ENDPOINTS } from '../config/api.js';

/**
 * Get company profile
 * @returns {Promise} Company profile data
 */
export const getProfile = async () => {
    const response = await apiClient.get(API_ENDPOINTS.COMPANY_PROFILE);
    return response.data;
};

/**
 * Update company profile
 * @param {Object} profileData - Updated profile data
 * @returns {Promise} API response
 */
export const updateProfile = async (profileData) => {
    const response = await apiClient.put(API_ENDPOINTS.COMPANY_PROFILE, profileData);
    return response.data;
};

/**
 * Get all company posts
 * @returns {Promise} List of posts
 */
export const getPosts = async () => {
    const response = await apiClient.get(API_ENDPOINTS.COMPANY_POSTS);
    return response.data;
};

/**
 * Get post details by ID
 * @param {string|number} postId - Post ID
 * @returns {Promise} Post details
 */
export const getPostDetails = async (postId) => {
    const endpoint = API_ENDPOINTS.COMPANY_POST_DETAILS.replace(':id', postId);
    const response = await apiClient.get(endpoint);
    return response.data;
};

// Add more company-related API methods as needed
