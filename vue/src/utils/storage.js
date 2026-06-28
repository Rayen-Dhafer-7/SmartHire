// LocalStorage utility functions for authentication

const AUTH_TOKEN_KEY = 'auth_token';
const USER_ROLE_KEY = 'user_role';

import { reactive } from 'vue';

export const profile = reactive({});

export const setProfile = (data) => {
    Object.assign(profile, data); // update reactive object
    console.log('profile storage 1', profile);
};


export const getProfile = () => {
    return profile;
};



/**
 * Get authentication token from localStorage
 * @returns {string|null} The auth token or null if not found
 */
export const getToken = () => {
    return localStorage.getItem(AUTH_TOKEN_KEY);
};

/**
 * Set authentication token in localStorage
 * @param {string} token - The auth token to store
 */
export const setToken = (token) => {
    localStorage.setItem(AUTH_TOKEN_KEY, token);
};

/**
 * Get user role from localStorage
 * @returns {string|null} The user role or null if not found
 */
export const getRole = () => {
    return localStorage.getItem(USER_ROLE_KEY);
};

/**
 * Set user role in localStorage
 * @param {string} role - The user role to store
 */
export const setRole = (role) => {
    localStorage.setItem(USER_ROLE_KEY, role);
};

/**
 * Clear all authentication data from localStorage
 */
export const clearAuth = () => {
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(USER_ROLE_KEY);
};

/**
 * Check if user is authenticated
 * @returns {boolean} True if user has a valid token
 */
export const isAuthenticated = () => {
    return !!getToken();
};
