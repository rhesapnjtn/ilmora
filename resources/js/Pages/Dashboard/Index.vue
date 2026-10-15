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
}

interface Dashboard {
    user: { name: string | null }
    progress: { percent: number; completed: number; total: number }
    streakDays: number
    subscription: { plan: string; status: string; expiresAt: string }
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
                                    <span class="badge-primary">Member {{ data.subscription.plan }}</span>
                                    <span class="badge-success">{{ data.subscription.status }}</span>
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

                        <div class="mt-4 space-y-4">
                            <div v-for="course in data.activeCourses" :key="course.slug" class="card">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <span class="badge-primary">{{ course.category }}</span>
                                        <h3 class="mt-3 text-title-md font-semibold">{{ course.title }}</h3>
                                        <p class="mt-1 text-body-sm text-on-surface-variant">{{ course.module }}</p>
                                    </div>
                                    <RouterLink to="/kursus" class="btn-primary h-10 px-4 text-label-md">Lanjutkan</RouterLink>
                                </div>
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-body-sm text-on-surface-variant">
                                        <span>Progres</span>
                                        <span class="font-semibold text-on-surface">{{ course.progress }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-surface-container-high">
                                        <div class="h-full rounded-full bg-primary" :style="{ width: `${course.progress}%` }" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card mt-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h2 class="text-title-lg font-semibold">Paket {{ data.subscription.plan }} Aktif</h2>
                                <p class="mt-1 text-body-md text-on-surface-variant">Aktif hingga {{ data.subscription.expiresAt }}</p>
                            </div>
                            <RouterLink to="/langganan" class="btn-secondary">Kelola Langganan</RouterLink>
                        </div>
                    </section>
                </template>
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
