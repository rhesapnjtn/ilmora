<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { useAuth } from '@/stores/auth'
import api from '@/lib/api'

interface ActiveCourse {
    slug: string
    title: string
    category: string
    module: string
    progress: number
    completedCount: number
    totalLessons: number
}

interface Subscription {
    id: number | null
    plan: string | null
    code: string | null
    status: string
    billingCycle: string | null
    expiresAt: string | null
    cancelAtPeriodEnd: boolean
    hasActive: boolean
}

interface Dashboard {
    user: { name: string }
    progress: { percent: number; completed: number; total: number }
    streakDays: number
    subscription: Subscription
    activeCourses: ActiveCourse[]
}

const router = useRouter()
const { user, logout } = useAuth()

const data = ref<Dashboard | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const confirmLogoutOpen = ref(false)
const loggingOut = ref(false)

const greeting = computed(() => data.value?.user.name ?? user.value?.name ?? 'Siswa')

const circumference = 2 * Math.PI * 52
const dashOffset = computed(() => {
    const percent = data.value?.progress.percent ?? 0
    return circumference - (percent / 100) * circumference
})

const sidebarNav = [
    { label: 'Kursus Saya', to: '/dashboard', icon: 'book' },
    { label: 'Langganan', to: '/langganan', icon: 'spark' },
    { label: 'Riwayat Transaksi', to: '/langganan/riwayat', icon: 'history' },
    { label: 'Sertifikat', to: '/sertifikat', icon: 'award' },
    { label: 'Pengaturan', to: '/profile', icon: 'cog' },
]

async function loadDashboard(): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data: payload } = await api.get<Dashboard>('/dashboard')
        data.value = payload
    } catch {
        error.value = 'Gagal memuat data dashboard.'
    } finally {
        loading.value = false
    }
}

async function handleLogout(): Promise<void> {
    loggingOut.value = true
    try {
        await logout()
        confirmLogoutOpen.value = false
        router.push('/')
    } finally {
        loggingOut.value = false
    }
}

onMounted(loadDashboard)
</script>

<template>
    <AppLayout>
        <div class="grid gap-8 lg:grid-cols-[16rem_1fr]">
            <aside class="hidden lg:block">
                <div class="card sticky top-24 p-4">
                    <RouterLink
                        v-for="item in sidebarNav"
                        :key="item.to"
                        :to="item.to"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-label-lg font-medium text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <template v-if="item.icon === 'book'"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" /></template>
                            <template v-else-if="item.icon === 'spark'"><path d="M12 2l2.4 7.4H22l-6 4.4 2.3 7.2L12 16.6 5.7 21l2.3-7.2-6-4.4h7.6z" /></template>
                            <template v-else-if="item.icon === 'award'"><circle cx="12" cy="8" r="5" /><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5" /></template>
                            <template v-else><path d="M4 6h16M4 12h16M4 18h16" /><circle cx="9" cy="6" r="2" /><circle cx="15" cy="12" r="2" /><circle cx="8" cy="18" r="2" /></template>
                        </svg>
                        {{ item.label }}
                    </RouterLink>
                    <button type="button" class="mt-2 flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-label-lg font-medium text-error transition-colors hover:bg-error/10" @click="confirmLogoutOpen = true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="M16 17l5-5-5-5M21 12H9" />
                        </svg>
                        Keluar
                    </button>
                </div>
            </aside>

            <div>
                <section class="animate-fade-in">
                    <h1 class="text-display-sm font-bold tracking-tight">Halo, {{ greeting }}</h1>
                    <p class="mt-2 text-body-lg text-on-surface-variant">Selamat datang kembali di platform belajar Ilmora!</p>
                </section>

                <p v-if="error" class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

                <div v-if="loading" class="mt-8 grid gap-6 lg:grid-cols-3">
                    <div class="h-56 animate-pulse rounded-2xl bg-surface-container-high lg:col-span-2" />
                    <div class="h-56 animate-pulse rounded-2xl bg-surface-container-high" />
                </div>

                <template v-else-if="data">
                    <section class="mt-8 grid gap-6 lg:grid-cols-3">
                        <div class="card flex items-center gap-6 lg:col-span-2">
                            <div class="relative h-32 w-32 shrink-0">
                                <svg class="h-32 w-32 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" class="text-surface-container-high" stroke-width="10" />
                                    <circle
                                        cx="60" cy="60" r="52" fill="none" stroke="currentColor" class="text-primary" stroke-width="10"
                                        stroke-linecap="round" :stroke-dasharray="circumference" :stroke-dashoffset="dashOffset"
                                    />
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-display-sm font-bold">{{ data.progress.percent }}%</span>
                            </div>
                            <div>
                                <h2 class="text-title-lg font-semibold">Progres Belajar</h2>
                                <p class="mt-1 text-body-md text-on-surface-variant">
                                    {{ data.progress.completed }} dari {{ data.progress.total }} kursus telah diselesaikan dengan baik.
                                </p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span :class="data.subscription.plan ? 'badge-primary' : 'badge-outline'">
                                        {{ data.subscription.plan ? `Member ${data.subscription.plan}` : 'Belum Berlangganan' }}
                                    </span>
                                    <span :class="data.subscription.hasActive ? 'badge-success' : data.subscription.code === 'pending' ? 'badge-promo' : 'badge-outline'">
                                        {{ data.subscription.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card flex flex-col justify-between bg-gradient-to-br from-primary to-primary-container text-white">
                            <div>
                                <p class="text-label-md font-medium text-white/70">Rekor belajar</p>
                                <p class="mt-2 text-display-sm font-bold">{{ data.streakDays }} Hari</p>
                                <p class="mt-1 text-body-sm text-white/80">Streak belajarmu saat ini.</p>
                            </div>
                            <p class="mt-4 text-body-sm text-white/80">Konsistensi hari ini, hasil luar biasa esok!</p>
                        </div>
                    </section>

                    <section class="mt-10">
                        <div class="flex items-center justify-between">
                            <h2 class="text-title-lg font-semibold">Kursus Aktif</h2>
                            <RouterLink to="/kursus" class="link text-body-sm">Lihat katalog</RouterLink>
                        </div>

                        <!-- Empty state for new users -->
                        <div v-if="data.activeCourses.length === 0" class="mt-6 flex flex-col items-center rounded-2xl border border-dashed border-outline-variant py-14 text-center">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary" aria-hidden="true">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                </svg>
                            </span>
                            <p class="mt-4 text-title-md font-semibold text-on-surface">Kamu belum mengikuti kursus apa pun</p>
                            <p class="mt-1 text-body-sm text-on-surface-variant">Mulai belajar sekarang dari ratusan kursus terbaik di Ilmora.</p>
                            <RouterLink to="/kursus" class="btn-primary mt-6 inline-flex items-center gap-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.35-4.35" /></svg>
                                Jelajahi Katalog Kursus
                            </RouterLink>
                        </div>

                        <div v-else class="mt-4 space-y-4">
                            <div v-for="course in data.activeCourses" :key="course.slug" class="card">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <span class="badge-primary">{{ course.category }}</span>
                                        <h3 class="mt-3 text-title-md font-semibold">{{ course.title }}</h3>
                                        <p class="mt-1 text-body-sm text-on-surface-variant">{{ course.module }}</p>
                                    </div>
                                    <RouterLink :to="`/kursus/${course.slug}`" class="btn-primary h-10 px-4 text-label-md">Lanjutkan</RouterLink>
                                </div>
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-body-sm text-on-surface-variant">
                                        <span>{{ course.completedCount }} / {{ course.totalLessons }} pelajaran selesai</span>
                                        <span class="font-semibold text-on-surface">{{ course.progress }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-surface-container-high">
                                        <div class="h-full rounded-full bg-primary transition-all duration-500" :style="{ width: `${course.progress}%` }" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card mt-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <template v-if="data.subscription.hasActive">
                                    <h2 class="text-title-lg font-semibold">Paket {{ data.subscription.plan }} Aktif</h2>
                                    <p v-if="data.subscription.cancelAtPeriodEnd" class="mt-1 text-body-md text-on-surface-variant">
                                        Dibatalkan &mdash; tetap aktif hingga {{ data.subscription.expiresAt ?? '-' }} dan tidak diperpanjang.
                                    </p>
                                    <p v-else class="mt-1 text-body-md text-on-surface-variant">Aktif hingga {{ data.subscription.expiresAt ?? '-' }}</p>
                                </template>
                                <template v-else-if="data.subscription.code === 'pending'">
                                    <h2 class="text-title-lg font-semibold">Pesanan Menunggu Verifikasi</h2>
                                    <p class="mt-1 text-body-md text-on-surface-variant">Selesaikan pembayaran paket {{ data.subscription.plan }} agar dapat diverifikasi oleh admin.</p>
                                </template>
                                <template v-else>
                                    <h2 class="text-title-lg font-semibold">Belum Berlangganan</h2>
                                    <p class="mt-1 text-body-md text-on-surface-variant">Aktifkan langganan untuk membuka seluruh materi premium Ilmora.</p>
                                </template>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <RouterLink v-if="data.subscription.plan" to="/langganan/riwayat" class="btn-secondary">Riwayat Transaksi</RouterLink>
                                <RouterLink :to="data.subscription.plan ? '/langganan/kelola' : '/langganan'" class="btn-secondary">
                                    {{ data.subscription.plan ? 'Kelola Langganan' : 'Lihat Paket' }}
                                </RouterLink>
                            </div>
                        </div>
                    </section>
                </template>

    <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-sm text-on-surface-variant">
      <span>&copy; 2026 Ilmora. Hak cipta dilindungi.</span>
      <span class="flex gap-6"><a href="#" class="hover:text-primary">Kebijakan Privasi</a><a href="#" class="hover:text-primary">Syarat Layanan</a></span>
    </div>
            </div>
        </div>

        <ConfirmDialog
            v-model="confirmLogoutOpen"
            title="Keluar dari Akun?"
            message="Anda akan keluar dari akun Ilmora. Untuk mengakses materi dan sertifikat lagi, Anda perlu masuk kembali."
            confirm-label="Keluar"
            cancel-label="Tetap di Sini"
            variant="danger"
            icon="logout"
            :loading="loggingOut"
            @confirm="handleLogout"
        />
    </AppLayout>
</template>
