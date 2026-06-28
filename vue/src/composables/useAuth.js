// Authentication composable - reusable auth logic
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { getToken, getRole, setToken, setRole, clearAuth } from '../utils/storage.js';
import { showConfirm } from '../utils/notifications.js';

export function useAuth() {
    const router = useRouter();

    // Check if user is authenticated
    const isAuthenticated = computed(() => {
        return !!getToken();
    });

    // Get current user role
    const userRole = computed(() => {
        return getRole();
    });

    /**
     * Check authentication and redirect if not authenticated
     * @param {string} requiredRole - Required role for access (optional)
     */
    const checkAuth = (requiredRole = null) => {
        const token = getToken();
        const role = getRole();

        if (!token || !role) {
            router.push('/');
            clearAuth();
            return false;
        }

        if (requiredRole && role !== requiredRole) {
            router.push('/');
            clearAuth();
            return false;
        }

        return true;
    };

    /**
     * Save authentication data
     * @param {string} token - Auth token
     * @param {string} role - User role
     */
    const saveAuth = (token, role) => {
        setToken(token);
        setRole(role);
    };

    /**
     * Logout user with confirmation
     */
    const logout = async () => {
        const result = await showConfirm(
            'Logout?',
            'Are you sure you want to logout?',
            'Yes, logout',
            'Cancel'
        );

        if (result.isConfirmed) {
            clearAuth();
            router.push('/');
        }
    };

    /**
     * Redirect to appropriate dashboard based on role
     * @param {string} role - User role
     */
    const redirectToDashboard = (role) => {
        if (role === 'worker') {
            router.push('/worker');
        } else if (role === 'company') {
            router.push('/company');
        }
    };

    return {
        isAuthenticated,
        userRole,
        checkAuth,
        saveAuth,
        logout,
        redirectToDashboard
    };
}
