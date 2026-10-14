import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuth } from '@/stores/auth';

const APP_NAME = 'Ilmora';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/Pages/Home.vue'),
        meta: { title: 'Beranda' },
    },
    {
        path: '/kursus',
        name: 'courses',
        component: () => import('@/Pages/Courses/Index.vue'),
        meta: { title: 'Katalog Kursus' },
    },
    {
        path: '/kursus/:slug',
        name: 'course-detail',
        component: () => import('@/Pages/Courses/Show.vue'),
        meta: { title: 'Detail Kursus' },
    },
    {
        path: '/belajar/:slug/:lesson',
        name: 'learning',
        component: () => import('@/Pages/Learning/Show.vue'),
        meta: { title: 'Ruang Belajar', requiresAuth: true },
    },
    {
        path: '/langganan',
        name: 'subscriptions',
        component: () => import('@/Pages/Subscriptions/Index.vue'),
        meta: { title: 'Langganan' },
    },
    {
        path: '/sertifikat',
        name: 'certificates',
        component: () => import('@/Pages/Certificates/Index.vue'),
        meta: { title: 'Sertifikat', requiresAuth: true },
    },
    {
        path: '/sertifikat/:id',
        name: 'certificate-detail',
        component: () => import('@/Pages/Certificates/Show.vue'),
        meta: { title: 'Sertifikat', requiresAuth: true },
    },
    {
        path: '/masuk',
        name: 'login',
        component: () => import('@/Pages/Auth/Login.vue'),
        meta: { title: 'Masuk', guestOnly: true },
    },
    {
        path: '/daftar',
        name: 'register',
        component: () => import('@/Pages/Auth/Register.vue'),
        meta: { title: 'Daftar', guestOnly: true },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/Pages/Dashboard/Index.vue'),
        meta: { title: 'Dashboard', requiresAuth: true },
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('@/Pages/Profile/Edit.vue'),
        meta: { title: 'Profil', requiresAuth: true },
    },
    {
        path: '/kebijakan-privasi',
        name: 'privacy',
        component: () => import('@/Pages/Privacy.vue'),
        meta: { title: 'Kebijakan Privasi' },
    },
    {
        path: '/syarat-layanan',
        name: 'terms',
        component: () => import('@/Pages/Terms.vue'),
        meta: { title: 'Syarat Layanan' },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/Pages/NotFound.vue'),
        meta: { title: 'Halaman Tidak Ditemukan' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach((to) => {
    const { isAuthenticated } = useAuth();

    if (to.meta.requiresAuth && !isAuthenticated.value) {
        return { name: 'login' };
    }

    if (to.meta.guestOnly && isAuthenticated.value) {
        return { name: 'dashboard' };
    }

    return true;
});

router.afterEach((to) => {
    const title = to.meta.title as string | undefined;
    document.title = title ? `${title} — ${APP_NAME}` : APP_NAME;
});

export default router;
