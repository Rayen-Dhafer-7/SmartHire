import { createRouter, createWebHistory } from 'vue-router';
import AuthPage from '../components/AuthPage.vue';
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

const routes = [
    {
        path: '/',
        name: 'Auth',
        component: AuthPage,
        alias: '/login' // This makes '/login' also show the AuthPage
    },
    {
        path: '/worker',
        name: 'WorkerDashboard',
        component: WorkerDashboard,
        redirect: '/worker/profile',
        children: [
            {
                path: 'profile',
                name: 'WorkerProfile',
                component: WorkerProfile
            },
            {
                path: 'jobs',
                name: 'WorkerHome',
                component: WorkerHome
            },
            {
                path: 'applications',
                name: 'WorkerTests',
                component: WorkerTests
            },
            {
                path: 'test-application/:id',
                name: 'TestPage',
                component: TestPage
            },
            {
                path: 'reset-password',
                name: 'ResetPasswordW',
                component: ResetPassword
            }
        ]
    },
    {
        path: '/company',
        name: 'CompanyDashboard',
        component: CompanyDashboard,
        redirect: '/company/profile',
        children: [
            {
                path: 'profile',
                name: 'CompanyProfile',
                component: CompanyProfile
            },
            {
                path: 'old-posts',
                name: 'OldHirePosts',
                component: OldHirePosts
            },
            {
                path: 'inprogress-posts',
                name: 'InProgressPosts',
                component: InProgressPosts
            },
            {
                path: 'add-post',
                name: 'AddPost',
                component: AddPost
            },
            {
                path: 'post-details/:id',
                name: 'PostDetails',
                component: PostDetails
            },
            {
                path: 'reset-password',
                name: 'ResetPassword',
                component: ResetPassword
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;