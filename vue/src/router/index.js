import { createRouter, createWebHistory } from 'vue-router';
import LandingDashboard from '../components/LandingDashboard.vue';
import AuthPage from '../components/AuthPage.vue';
import AdminLogin from '../components/Admin/AdminLogin.vue';
import AdminDashboard from '../components/Admin/AdminDashboard.vue';

// Lazy load components for better performance
const WorkerDashboard = () => import('../components/Worker/dashboard/WorkerDashboard.vue');
const WorkerProfile = () => import('../components/Worker/profile/WorkerProfile.vue');
const WorkerHome = () => import('../components/Worker/jobs/WorkerHome.vue');
const ResetPassword = () => import('../components/Worker/password/ResetPassword.vue');
const WorkerTests = () => import('../components/Worker/applications/WorkerTests.vue');
const TestPage = () => import('../components/Worker/test/TestPage.vue');

const CompanyDashboard = () => import('../components/Company/dashboard/CompanyDashboard.vue');
const CompanyProfile = () => import('../components/Company/profile/CompanyProfile.vue');
const OldHirePosts = () => import('../components/Company/posts/OldHirePosts.vue');
const InProgressPosts = () => import('../components/Company/posts/InProgressPosts.vue');
const AddPost = () => import('../components/Company/add-post/AddPost.vue');
const PostDetails = () => import('../components/Company/post-details/PostDetails.vue');
const WorkerProfileView = () => import('../components/Company/worker-profile/WorkerPublicProfile.vue');

const routes = [
    {
        path: '/',
        name: 'Landing',
        component: LandingDashboard
    },
    {
        path: '/login',
        name: 'Login',
        component: AuthPage
    },
    {
        path: '/signup',
        name: 'Signup',
        component: AuthPage
    },
    {
        path: '/admin/login',
        name: 'AdminLogin',
        component: AdminLogin
    },
    {
        path: '/admin',
        name: 'AdminDashboard',
        component: AdminDashboard
    },
    // MOVE RESET PASSWORD OUTSIDE OF NESTED ROUTES - Make them standalone
    {
        path: '/worker/reset-password',
        name: 'ResetPasswordWorker',
        component: ResetPassword,
        meta: { requiresAuth: false, layout: 'empty' } // No layout needed
    },
    {
        path: '/company/reset-password',
        name: 'ResetPasswordCompany',
        component: ResetPassword,
        meta: { requiresAuth: false, layout: 'empty' }
    },
    {
        path: '/worker',
        name: 'WorkerDashboard',
        component: WorkerDashboard,
        redirect: '/worker/profile',
        meta: { requiresAuth: true },
        children: [
            {
                path: 'profile',
                name: 'WorkerProfile',
                component: WorkerProfile,
                meta: { requiresAuth: true }
            },
            {
                path: 'jobs',
                name: 'WorkerHome',
                component: WorkerHome,
                meta: { requiresAuth: true }
            },
            {
                path: 'applications',
                name: 'WorkerTests',
                component: WorkerTests,
                meta: { requiresAuth: true }
            },
            {
                path: 'test-application/:id',
                name: 'TestPage',
                component: TestPage,
                meta: { requiresAuth: true }
            }
            // REMOVED reset-password from here
        ]
    },
    {
        path: '/company',
        name: 'CompanyDashboard',
        component: CompanyDashboard,
        redirect: '/company/profile',
        meta: { requiresAuth: true },
        children: [
            {
                path: 'profile',
                name: 'CompanyProfile',
                component: CompanyProfile,
                meta: { requiresAuth: true }
            },
            {
                path: 'old-posts',
                name: 'OldHirePosts',
                component: OldHirePosts,
                meta: { requiresAuth: true }
            },
            {
                path: 'inprogress-posts',
                name: 'InProgressPosts',
                component: InProgressPosts,
                meta: { requiresAuth: true }
            },
            {
                path: 'add-post',
                name: 'AddPost',
                component: AddPost,
                meta: { requiresAuth: true }
            },
            {
                path: 'post-details/:id',
                name: 'PostDetails',
                component: PostDetails,
                meta: { requiresAuth: true }
            },
            {
                path: 'worker-profile/:id',
                name: 'WorkerProfileView',
                component: WorkerProfileView,
                meta: { requiresAuth: true }
            }
            // REMOVED reset-password from here
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Optional: Add navigation guard to protect routes
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    const role = localStorage.getItem('user_role');
    
    // Check if route requires authentication
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            // Not authenticated, redirect to login
            next({ name: 'Login' });
        } else {
            // Check role-based access
            if (to.path.startsWith('/worker') && role !== 'worker') {
                next({ name: 'Login' });
            } else if (to.path.startsWith('/company') && role !== 'company') {
                next({ name: 'Login' });
            } else {
                next();
            }
        }
    } else {
        next();
    }
});

export default router;