<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import Badge from '@/Components/Badge.vue'
import api from '@/lib/api'

interface RecentUser { id: number; name: string; email: string; role: string; created_at: string }
interface RecentPayment {
    id: number
    amount: number
    currency: string
    status: string
    created_at: string
    user: { name: string; email: string } | null
    course: { title: string; slug: string } | null
}
interface Dashboard {
    stats: {
        users: number
        admins: number
        courses: number
        publishedCourses: number
        subscriptions: number
        activeSubscriptions: number
        payments: number
        pendingPayments: number
        revenue: number
    }
    recentUsers: RecentUser[]
    recentPayments: RecentPayment[]
    subscriptionBreakdown: { active: number; expired: number; canceled: number }
    alerts: { label: string; count: number; route: string; tone: string }[]
}

const data = ref<Dashboard | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value))
}

async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data: payload } = await api.get<Dashboard>('/admin/dashboard')
        data.value = payload
    } catch {
        error.value = 'Gagal memuat data dashboard admin.'
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>

<template>
    <AdminLayout>
        <p v-if="error" class="mb-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

        <div v-if="loading" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="n in 4" :key="n" class="h-32 animate-pulse rounded-2xl bg-surface-container-high" />
        </div>

        <template v-else-if="data">
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total Pengguna" :value="data.stats.users" :hint="`${data.stats.admins} admin`" icon="users" accent="primary" />
                <StatCard label="Total Kursus" :value="data.stats.courses" :hint="`${data.stats.publishedCourses} dipublikasikan`" icon="book" accent="secondary" />
                <StatCard label="Langganan Aktif" :value="data.stats.activeSubscriptions" :hint="`${data.stats.subscriptions} total langganan`" icon="card" accent="tertiary" />
                <StatCard label="Total Pendapatan" :value="formatCurrency(data.stats.revenue)" :hint="`${data.stats.pendingPayments} pembayaran tertunda`" icon="wallet" accent="primary" />
            </section>

            <section class="mt-8 grid gap-6 lg:grid-cols-2">
                <div class="card">
                    <div class="flex items-center justify-between">
                        <h2 class="text-title-lg font-semibold">Pendaftar Terbaru</h2>
                        <RouterLink to="/admin/pengguna" class="link text-body-sm">Lihat semua</RouterLink>
                    </div>
                    <ul class="mt-4 divide-y divide-outline-variant/60">
                        <li v-for="user in data.recentUsers" :key="user.id" class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-body-md font-semibold text-on-surface">{{ user.name }}</p>
                                <p class="truncate text-body-sm text-on-surface-variant">{{ user.email }}</p>
                            </div>
                            <Badge :variant="user.role === 'admin' ? 'primary' : 'outline'" size="sm">{{ user.role === 'admin' ? 'Admin' : 'Pengguna' }}</Badge>
                        </li>
                        <li v-if="data.recentUsers.length === 0" class="py-6 text-center text-body-sm text-on-surface-variant">Belum ada pengguna.</li>
                    </ul>
                </div>

                <div class="card">
                    <div class="flex items-center justify-between">
                        <h2 class="text-title-lg font-semibold">Pembayaran Terbaru</h2>
                        <RouterLink to="/admin/pembayaran" class="link text-body-sm">Lihat semua</RouterLink>
                    </div>
                    <ul class="mt-4 divide-y divide-outline-variant/60">
                        <li v-for="payment in data.recentPayments" :key="payment.id" class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-body-md font-semibold text-on-surface">{{ payment.user?.name ?? 'Pengguna' }}</p>
                                <p class="truncate text-body-sm text-on-surface-variant">{{ payment.course?.title ?? 'Kursus' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-body-md font-semibold text-on-surface">{{ formatCurrency(payment.amount) }}</p>
                                <span class="text-body-sm text-on-surface-variant">{{ formatDate(payment.created_at) }}</span>
                            </div>
                        </li>
                        <li v-if="data.recentPayments.length === 0" class="py-6 text-center text-body-sm text-on-surface-variant">Belum ada pembayaran.</li>
                    </ul>
                </div>
            </section>

            <section class="mt-8">
                <div class="card-flush">
                    <div class="flex items-center justify-between border-b border-outline-variant/50 px-6 py-5">
                        <div><p class="text-label-sm font-bold uppercase tracking-wider text-primary">Perlu perhatian</p><h2 class="mt-1 text-title-lg font-bold">Alert operasional</h2></div>
                        <span class="badge-primary">Live</span>
                    </div>
                    <div class="grid divide-y divide-outline-variant/50 md:grid-cols-3 md:divide-x md:divide-y-0">
                        <RouterLink v-for="alert in data.alerts" :key="alert.label" :to="alert.route" class="group flex items-center justify-between gap-4 p-6 transition-colors hover:bg-primary/5">
                            <div><p class="text-body-sm font-semibold text-on-surface">{{ alert.label }}</p><p class="mt-1 text-body-sm text-on-surface-variant">Tinjau dan tindak lanjuti</p></div>
                            <span class="text-3xl font-bold text-primary group-hover:scale-110 transition-transform">{{ alert.count }}</span>
                        </RouterLink>
                    </div>
                </div>
            </section>

            <section class="mt-8">
                <div class="card">
                    <h2 class="text-title-lg font-semibold">Ringkasan Status Langganan</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-tertiary/10 p-4">
                            <p class="text-label-md font-semibold uppercase tracking-wide text-tertiary">Aktif</p>
                            <p class="mt-1 text-headline-md font-bold text-on-surface">{{ data.subscriptionBreakdown.active }}</p>
                        </div>
                        <div class="rounded-2xl bg-surface-container p-4">
                            <p class="text-label-md font-semibold uppercase tracking-wide text-on-surface-variant">Kedaluwarsa</p>
                            <p class="mt-1 text-headline-md font-bold text-on-surface">{{ data.subscriptionBreakdown.expired }}</p>
                        </div>
                        <div class="rounded-2xl bg-error/10 p-4">
                            <p class="text-label-md font-semibold uppercase tracking-wide text-error">Dibatalkan</p>
                            <p class="mt-1 text-headline-md font-bold text-on-surface">{{ data.subscriptionBreakdown.canceled }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </AdminLayout>
</template>
