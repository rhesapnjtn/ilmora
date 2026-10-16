<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import api from '@/lib/api'

interface AdminSubscription {
    id: number
    user: { id: number | null; name: string | null; email: string | null }
    plan: string
    status: string
    amount: number
    currency: string
    startsAt: string | null
    expiresAt: string | null
    createdAt: string | null
}
interface Meta { page: number; perPage: number; total: number; lastPage: number }
interface Summary { total: number; pending: number; active: number; expired: number; canceled: number }

const subscriptions = ref<AdminSubscription[]>([])
const meta = ref<Meta>({ page: 1, perPage: 10, total: 0, lastPage: 1 })
const summary = ref<Summary>({ total: 0, pending: 0, active: 0, expired: 0, canceled: 0 })
const filters = reactive({ search: '', status: '' })
const loading = ref(true)
const error = ref<string | null>(null)

const statusVariant: Record<string, 'success' | 'secondary' | 'error' | 'outline'> = {
    active: 'success',
    pending: 'secondary',
    expired: 'outline',
    canceled: 'error',
}
const statusLabel: Record<string, string> = {
    active: 'Aktif',
    pending: 'Tertunda',
    expired: 'Kedaluwarsa',
    canceled: 'Dibatalkan',
}

function formatCurrency(value: number, currency: string): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: currency || 'IDR', maximumFractionDigits: 0 }).format(value)
}
function formatDate(value: string | null): string {
    if (!value) return '-'
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value))
}

async function load(page = 1): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get('/admin/subscriptions', {
            params: { search: filters.search || undefined, status: filters.status || undefined, page },
        })
        subscriptions.value = data.subscriptions
        meta.value = data.meta
        summary.value = data.summary
    } catch {
        error.value = 'Gagal memuat data langganan.'
    } finally {
        loading.value = false
    }
}

watch(() => filters.status, () => load(1))
onMounted(() => load(1))
</script>

<template>
    <AdminLayout>
        <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-on-surface-variant">Total Langganan</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ summary.total }}</p>
            </div>
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-secondary">Tertunda</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ summary.pending }}</p>
            </div>
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-tertiary">Aktif</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ summary.active }}</p>
            </div>
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-on-surface-variant">Kedaluwarsa</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ summary.expired }}</p>
            </div>
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-error">Dibatalkan</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ summary.canceled }}</p>
            </div>
        </section>

        <form class="mt-6 flex flex-wrap items-center gap-2" @submit.prevent="load(1)">
            <input v-model="filters.search" type="search" class="input h-11 flex-1 min-w-52" placeholder="Cari nama atau email pengguna..." aria-label="Cari langganan" />
            <select v-model="filters.status" class="input h-11 w-full sm:w-44" aria-label="Filter status">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="pending">Tertunda</option>
                <option value="expired">Kedaluwarsa</option>
                <option value="canceled">Dibatalkan</option>
            </select>
            <button type="submit" class="btn-secondary h-11 px-5 text-label-md">Cari</button>
        </form>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

        <div class="card-flush mt-6 overflow-x-auto">
            <table class="w-full min-w-[720px] text-left">
                <thead>
                    <tr class="border-b border-outline-variant/60 text-label-md uppercase tracking-wide text-on-surface-variant">
                        <th class="px-5 py-4 font-semibold">Pengguna</th>
                        <th class="px-5 py-4 font-semibold">Paket</th>
                        <th class="px-5 py-4 font-semibold">Nominal</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold">Mulai</th>
                        <th class="px-5 py-4 font-semibold">Berakhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    <tr v-if="loading">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Memuat data...</td>
                    </tr>
                    <tr v-else-if="subscriptions.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Belum ada langganan.</td>
                    </tr>
                    <tr v-for="subscription in subscriptions" :key="subscription.id" class="transition-colors hover:bg-surface-container-low/60">
                        <td class="px-5 py-4">
                            <p class="text-body-md font-semibold text-on-surface">{{ subscription.user.name ?? 'Pengguna' }}</p>
                            <p class="text-body-sm text-on-surface-variant">{{ subscription.user.email }}</p>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ subscription.plan }}</td>
                        <td class="px-5 py-4 text-body-sm font-semibold text-on-surface">{{ formatCurrency(subscription.amount, subscription.currency) }}</td>
                        <td class="px-5 py-4">
                            <Badge :variant="statusVariant[subscription.status] ?? 'outline'" size="sm">{{ statusLabel[subscription.status] ?? subscription.status }}</Badge>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ formatDate(subscription.startsAt) }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ formatDate(subscription.expiresAt) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />
    </AdminLayout>
</template>
