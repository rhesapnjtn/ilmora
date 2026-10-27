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

const pointsBalance = ref(0)
const pointsStreak = ref(0)
const checkingIn = ref(false)
const checkinMessage = ref<string | null>(null)
const justCheckedIn = ref(false)
const showBadgeGuide = ref(false)
const confettiPieces = ref<number[]>([])
const badges = ref<Array<{ code: string; name: string; description: string; icon: string; unlocked: boolean; unlockedAt: string | null }>>([])
const leaderboard = ref<Array<{ rank: number; id: number; name: string; avatar: string | null; points: number; streak: number }>>([])
const userRank = ref<number | null>(null)

const nextMilestone = computed(() => {
    const s = pointsStreak.value
    if (s < 7) return 7
    if (s < 30) return 30
    return 100
})

const milestoneProgress = computed(() => {
    const s = pointsStreak.value
    const prev = s >= 30 ? 30 : s >= 7 ? 7 : 0
    const next = nextMilestone.value
    if (next === prev) return 100
    return Math.min(100, Math.round(((s - prev) / (next - prev)) * 100))
})

const tomorrowReward = computed(() => {
    const next = pointsStreak.value + 1
    let r = 5 + Math.min(next, 30)
    if (next === 7) r += 20
    else if (next === 30) r += 100
    else if (next === 100) r += 500
    return r
})

const streakTier = computed(() => {
    const s = pointsStreak.value
    if (s >= 30) return { label: 'Emas', color: 'text-amber-500' }
    if (s >= 7) return { label: 'Perak', color: 'text-slate-400' }
    return { label: 'Perunggu', color: 'text-orange-600' }
})

async function loadPoints(): Promise<void> {
    try {
        const { data: payload } = await api.get<{ balance: number; currentStreak: number; badges?: typeof badges.value }>('/points')
        pointsBalance.value = payload.balance
        pointsStreak.value = payload.currentStreak
        if (payload.badges) badges.value = payload.badges
    } catch {
        // non-critical
    }
    try {
        const { data: payload } = await api.get<{ entries: typeof leaderboard.value; userRank: number | null }>('/leaderboard')
        leaderboard.value = payload.entries
        userRank.value = payload.userRank
    } catch {
        // non-critical
    }
}

function triggerConfetti(): void {
    confettiPieces.value = Array.from({ length: 40 }, (_, i) => i)
    setTimeout(() => { confettiPieces.value = [] }, 2200)
}

async function handleCheckin(): Promise<void> {
    checkingIn.value = true
    checkinMessage.value = null
    justCheckedIn.value = false
    try {
        const { data: payload } = await api.post<{ message: string; balance: number; streak: number; alreadyCheckedIn: boolean }>('/checkin')
        pointsBalance.value = payload.balance
        pointsStreak.value = payload.streak
        checkinMessage.value = payload.message
        if (!payload.alreadyCheckedIn) {
            justCheckedIn.value = true
            triggerConfetti()
            setTimeout(() => { justCheckedIn.value = false }, 2500)
            loadPoints()
        }
    } catch (err) {
        const response = (err as { response?: { data?: { message?: string } } }).response
        checkinMessage.value = response?.data?.message ?? 'Gagal check-in.'
    } finally {
        checkingIn.value = false
    }
}

const greeting = computed(() => data.value?.user.name ?? user.value?.name ?? 'Siswa')

const circumference = 2 * Math.PI * 52
const dashOffset = computed(() => {
    const percent = data.value?.progress.percent ?? 0
    return circumference - (percent / 100) * circumference
})

const sidebarNav = [
    { label: 'Kursus Saya', to: '/dashboard', icon: 'book' },
    { label: 'Bantuan & Chat CS', to: '/bantuan/chat', icon: 'chat' },
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

onMounted(() => {
    loadDashboard()
    loadPoints()
})
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
                            <template v-else-if="item.icon === 'chat'"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></template>
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
                    <section class="mt-6">
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500/15 via-orange-500/10 to-rose-500/15 p-6 ring-1 ring-amber-500/20">
                            <div v-if="confettiPieces.length" class="pointer-events-none absolute inset-0 overflow-hidden">
                                <span
                                    v-for="p in confettiPieces"
                                    :key="p"
                                    class="confetti-piece"
                                    :style="{ left: (Math.random() * 100) + '%', animationDelay: (Math.random() * 0.4) + 's', background: ['#f59e0b','#fb923c','#f43f5e','#a855f7','#22c55e','#3b82f6'][p % 6] }"
                                />
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-600 text-3xl shadow-lg shadow-orange-500/30">
                                        <span class="animate-pulse">🔥</span>
                                        <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-surface text-xs font-bold text-orange-600 ring-2 ring-orange-500">
                                            {{ pointsStreak }}
                                        </span>
                                    </div>
                                    <div>
                                        <h2 class="text-title-lg font-bold">Streak Pembelajaran</h2>
                                        <p class="mt-0.5 text-body-sm text-on-surface-variant">
                                            {{ pointsStreak }} hari berturut-turut · {{ pointsBalance }} poin
                                        </p>
                                        <p v-if="checkinMessage" class="mt-1 text-body-sm font-medium text-tertiary">{{ checkinMessage }}</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="relative overflow-hidden rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-3 text-label-lg font-semibold text-white shadow-lg shadow-orange-500/30 transition-all hover:scale-105 hover:shadow-xl hover:shadow-orange-500/40 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="checkingIn"
                                    @click="handleCheckin"
                                >
                                    <span v-if="justCheckedIn" class="absolute inset-0 animate-ping bg-white/30" />
                                    {{ checkingIn ? 'Memproses...' : 'Check-in Hari Ini' }}
                                </button>
                            </div>

                            <div class="mt-5">
                                <div class="flex items-center justify-between text-label-sm text-on-surface-variant">
                                    <span>Progress ke {{ nextMilestone }} hari</span>
                                    <span>{{ milestoneProgress }}%</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-surface-container-high">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 transition-all duration-700"
                                        :style="{ width: milestoneProgress + '%' }"
                                    />
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span
                                        v-for="m in [7, 30, 100]"
                                        :key="m"
                                        class="rounded-full px-3 py-1 text-label-sm font-semibold transition-all"
                                        :class="pointsStreak >= m
                                            ? 'bg-amber-500/20 text-amber-600 ring-1 ring-amber-500/40'
                                            : 'bg-surface-container-high text-on-surface-variant'"
                                    >
                                        {{ pointsStreak >= m ? 'OK' : 'o' }} {{ m }} hari
                                        <span class="ml-1 opacity-70">
                                            +{{ m === 7 ? 20 : m === 30 ? 100 : 500 }}
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-3 text-body-sm text-on-surface-variant">
                                <span>Besok kamu bisa dapat <span class="font-semibold text-amber-600">+{{ tomorrowReward }} poin</span> jika check-in.</span>
                                <span class="rounded-full bg-surface/60 px-3 py-1 text-label-sm">Tier: {{ streakTier.label }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="mt-6 grid gap-6 lg:grid-cols-2">
                        <div class="card">
                            <h2 class="text-title-md font-semibold">Badge Kamu</h2>
                            <p class="mt-1 text-body-sm text-on-surface-variant">Kumpulkan badge dengan belajar konsisten.</p>
                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div
                                    v-for="badge in badges"
                                    :key="badge.code"
                                    class="flex flex-col items-center gap-1 rounded-xl p-3 text-center transition-all"
                                    :class="badge.unlocked
                                        ? 'bg-amber-500/10 ring-1 ring-amber-500/30'
                                        : 'bg-surface-container-low opacity-50 grayscale'"
                                >
                                    <span class="text-2xl">{{ badge.icon }}</span>
                                    <span class="text-label-sm font-semibold">{{ badge.name }}</span>
                                </div>
                            </div>
                            <button type="button" class="mt-3 text-label-sm font-medium text-primary hover:underline" @click="showBadgeGuide = !showBadgeGuide">
                                {{ showBadgeGuide ? 'Sembunyikan cara mendapatkan' : 'Lihat cara mendapatkan badge' }}
                            </button>
                            <ul v-if="showBadgeGuide" class="mt-3 space-y-2 text-body-sm text-on-surface-variant">
                                <li v-for="badge in badges" :key="'guide-' + badge.code" class="flex items-start gap-2">
                                    <span>{{ badge.icon }}</span>
                                    <span><span class="font-semibold text-on-surface">{{ badge.name }}</span> — {{ badge.description }}</span>
                                </li>
                            </ul>
                            <p v-if="badges.length === 0" class="mt-4 text-body-sm text-on-surface-variant">Belum ada badge.</p>
                        </div>

                        <div class="card">
                            <h2 class="text-title-md font-semibold">Papan Peringkat</h2>
                            <p class="mt-1 text-body-sm text-on-surface-variant">
                                Top 10 pembelajar. Peringkatmu: <span class="font-semibold text-primary">{{ userRank ?? '-' }}</span>
                            </p>
                            <ol class="mt-4 space-y-2">
                                <li
                                    v-for="entry in leaderboard.slice(0, 10)"
                                    :key="entry.id"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2"
                                    :class="entry.rank <= 3 ? 'bg-amber-500/10' : 'bg-surface-container-low/60'"
                                >
                                    <span class="w-6 text-center text-label-md font-bold" :class="entry.rank === 1 ? 'text-amber-500' : entry.rank === 2 ? 'text-slate-400' : entry.rank === 3 ? 'text-orange-600' : 'text-on-surface-variant'">
                                        {{ entry.rank === 1 ? '1st' : entry.rank === 2 ? '2nd' : entry.rank === 3 ? '3rd' : entry.rank }}
                                    </span>
                                    <span class="flex-1 truncate text-body-sm font-medium">{{ entry.name }}</span>
                                    <span class="text-label-sm text-on-surface-variant">🔥 {{ entry.streak }}</span>
                                    <span class="text-label-sm font-semibold text-primary">{{ entry.points }} poin</span>
                                </li>
                            </ol>
                        </div>
                    </section>

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

<style scoped>
.confetti-piece {
    position: absolute;
    top: -10px;
    width: 8px;
    height: 14px;
    border-radius: 2px;
    opacity: 0.9;
    animation: confetti-fall 2s ease-out forwards;
}
@keyframes confetti-fall {
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(260px) rotate(720deg); opacity: 0; }
}
</style>
