// API Configuration
export const API_CONFIG = {
    baseURL: import.meta.env.VITE_API_URL, 
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json'
    }
};

// API Endpoints
export const API_ENDPOINTS = {
    // Auth endpoints
    WORKER_LOGIN: '/worker/login',
    WORKER_REGISTER: '/worker/register',
    COMPANY_LOGIN: '/company/login',
    COMPANY_REGISTER: '/company/register',
    WORKER_CVREMOVE: '/worker/cv/remove',


    // Worker endpoints
    WORKER_PROFILE: '/worker/profile',
    WORKER_JOBS: '/worker/jobs',
    WORKER_APPLICATIONS: '/worker/applications',

    // Company endpoints
    COMPANY_PROFILE: '/company/profile',
    COMPANY_POSTS: '/company/posts',
    COMPANY_POST_DETAILS: '/company/posts/:id'
};
