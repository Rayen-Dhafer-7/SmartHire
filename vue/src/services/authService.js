// Authentication service - handles all auth-related API calls
import axios from 'axios';
import { API_ENDPOINTS } from '../config/api.js';

/**
 * Login user (worker or company)
 * @param {string} email - User email
 * @param {string} password - User password
 * @returns {Promise} API response with token and role
 */
export const login = async (email, password) => {
    const response = await axios.post(`${import.meta.env.VITE_API_URL}${API_ENDPOINTS.WORKER_LOGIN}`, {   
        email,  
        password
    }, {
        headers: {
            'Content-Type': 'application/json'
        }
    });
    return response.data;
};

/**
 * Register a new worker
 * @param {Object} workerData - Worker registration data
 * @returns {Promise} API response
 */
export const registerWorker = async (workerData) => {
    const formData = new FormData();
    formData.append('fullName', workerData.fullName);
    formData.append('email', workerData.email);
    formData.append('password', workerData.password);

    if (workerData.profile) {
        formData.append('profile', workerData.profile);
    }

    const response = await axios.post(`${import.meta.env.VITE_API_URL}${API_ENDPOINTS.WORKER_REGISTER}`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
    return response.data;
};

/**
 * Register a new company
 * @param {Object} companyData - Company registration data
 * @returns {Promise} API response
 */
export const registerCompany = async (companyData) => {
    const formData = new FormData();
    formData.append('companyName', companyData.companyName);
    formData.append('email', companyData.email);
    formData.append('password', companyData.password);
    formData.append('location', companyData.location);
    formData.append('industry', companyData.industry);

    if (companyData.logo) {
        formData.append('logo', companyData.logo);
    }

    const response = await axios.post(`${import.meta.env.VITE_API_URL}${API_ENDPOINTS.COMPANY_REGISTER}`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
    return response.data;
};
