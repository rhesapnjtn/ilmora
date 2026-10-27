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
        path: '/langganan/paket/:slug',
        name: 'subscription-checkout',
        component: () => import('@/Pages/Subscriptions/Checkout.vue'),
        meta: { title: 'Checkout Langganan', requiresAuth: true },
    },
    {
        path: '/langganan/pesanan/:id',
        name: 'subscription-order',
        component: () => import('@/Pages/Subscriptions/Show.vue'),
        meta: { title: 'Status Pesanan', requiresAuth: true },
    },
    {
        path: '/langganan/riwayat',
        name: 'subscription-history',
        component: () => import('@/Pages/Subscriptions/History.vue'),
        meta: { title: 'Riwayat Transaksi', requiresAuth: true },
    },
    {
        path: '/langganan/kelola',
        name: 'subscription-manage',
        component: () => import('@/Pages/Subscriptions/Manage.vue'),
        meta: { title: 'Kelola Langganan', requiresAuth: true },
    },
    {
        path: '/sertifikat',
        name: 'certificates',
        component: () => import('@/Pages/Certificates/Index.vue'),
        meta: { title: 'Sertifikat', requiresAuth: true },
    },
    {
        path: '/catatan',
        name: 'notes',
        component: () => import('@/Pages/Notes/Index.vue'),
        meta: { title: 'Catatan Saya', requiresAuth: true },
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
        path: '/lupa-sandi',
        name: 'forgot-password',
        component: () => import('@/Pages/Auth/ForgotPassword.vue'),
        meta: { title: 'Lupa Kata Sandi', guestOnly: true },
    },
    {
        path: '/reset-sandi',
        name: 'reset-password',
        component: () => import('@/Pages/Auth/ResetPassword.vue'),
        meta: { title: 'Atur Ulang Kata Sandi', guestOnly: true },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/Pages/Dashboard/Index.vue'),
        meta: { title: 'Dashboard', requiresAuth: true },
    },
    {
        path: '/bantuan/chat',
        name: 'support-chat',
        component: () => import('@/Pages/Support/Chat.vue'),
        meta: { title: 'Bantuan & Chat CS', requiresAuth: true },
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('@/Pages/Profile/Edit.vue'),
        meta: { title: 'Profil', requiresAuth: true },
    },
    {
        path: '/admin',
        redirect: { name: 'admin-dashboard' },
    },
    {
        path: '/admin/dashboard',
        name: 'admin-dashboard',
        component: () => import('@/Pages/Admin/Dashboard/Index.vue'),
        meta: { title: 'Dashboard Admin', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/kursus',
        name: 'admin-courses',
        component: () => import('@/Pages/Admin/Courses/Index.vue'),
        meta: { title: 'Kelola Kursus', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/kursus/baru',
        name: 'admin-courses-create',
        component: () => import('@/Pages/Admin/Courses/Form.vue'),
        meta: { title: 'Tambah Kursus', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/kursus/:id/edit',
        name: 'admin-courses-edit',
        component: () => import('@/Pages/Admin/Courses/Form.vue'),
        meta: { title: 'Edit Kursus', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/paket',
        name: 'admin-plans',
        component: () => import('@/Pages/Admin/Plans/Index.vue'),
        meta: { title: 'Kelola Paket', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/kategori',
        name: 'admin-categories',
        component: () => import('@/Pages/Admin/Categories/Index.vue'),
        meta: { title: 'Kelola Kategori', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/paket/baru',
        name: 'admin-plans-create',
        component: () => import('@/Pages/Admin/Plans/Form.vue'),
        meta: { title: 'Tambah Paket', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/paket/:id/edit',
        name: 'admin-plans-edit',
        component: () => import('@/Pages/Admin/Plans/Form.vue'),
        meta: { title: 'Edit Paket', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/pengguna',
        name: 'admin-users',
        component: () => import('@/Pages/Admin/Users/Index.vue'),
        meta: { title: 'Kelola Pengguna', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/langganan',
        name: 'admin-subscriptions',
        component: () => import('@/Pages/Admin/Subscriptions/Index.vue'),
        meta: { title: 'Kelola Langganan', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/pembayaran',
        name: 'admin-payments',
        component: () => import('@/Pages/Admin/Payments/Index.vue'),
        meta: { title: 'Kelola Pembayaran', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/chat',
        name: 'admin-chat',
        component: () => import('@/Pages/Admin/Chat/Index.vue'),
        meta: { title: 'Chat Customer Service', requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/ulasan',
        name: 'admin-reviews',
        component: () => import('@/Pages/Admin/Reviews/Index.vue'),
        meta: { title: 'Moderasi Ulasan', requiresAuth: true, requiresAdmin: true },
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

router.onError((error, to) => {
    const message = error instanceof Error ? error.message : String(error);

    if (
        /Failed to fetch dynamically imported module|error loading dynamically imported module|Importing a module script failed/i.test(
            message,
        )
    ) {
        window.location.assign(to.fullPath || '/');
    }
});

router.beforeEach((to) => {
    const { isAuthenticated, isAdmin } = useAuth();

    if (to.meta.requiresAuth && !isAuthenticated.value) {
        return { name: 'login' };
    }

    if (to.meta.requiresAdmin && !isAdmin.value) {
        return { name: 'dashboard' };
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
