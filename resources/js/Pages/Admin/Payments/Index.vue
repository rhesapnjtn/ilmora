<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import api from '@/lib/api'

interface AdminPayment {
    id: number
    user: { id: number | null; name: string | null; email: string | null }
    course: { id: number; title: string; slug: string } | null
    amount: number
    currency: string
    status: string
    paymentMethod: string | null
    paymentId: string | null
    createdAt: string | null
}
interface Meta { page: number; perPage: number; total: number; lastPage: number }
interface Summary { total: number; success: number; pending: number; failed: number; refunded: number; revenue: number }

const payments = ref<AdminPayment[]>([])
const meta = ref<Meta>({ page: 1, perPage: 10, total: 0, lastPage: 1 })
const summary = ref<Summary>({ total: 0, success: 0, pending: 0, failed: 0, refunded: 0, revenue: 0 })
const filters = reactive({ search: '', status: '' })
const loading = ref(true)
const error = ref<string | null>(null)

const statusVariant: Record<string, 'success' | 'secondary' | 'error' | 'outline'> = {
    success: 'success',
    pending: 'secondary',
    failed: 'error',
    refunded: 'outline',
}
const statusLabel: Record<string, string> = {
    success: 'Berhasil',
    pending: 'Tertunda',
    failed: 'Gagal',
    refunded: 'Dikembalikan',
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
        const { data } = await api.get('/admin/payments', {
            params: { search: filters.search || undefined, status: filters.status || undefined, page },
        })
        payments.value = data.payments
        meta.value = data.meta
        summary.value = data.summary
    } catch {
        error.value = 'Gagal memuat data pembayaran.'
    } finally {
        loading.value = false
    }
}

watch(() => filters.status, () => load(1))
onMounted(() => load(1))
</script>

<template>
    <AdminLayout>
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-on-surface-variant">Total Transaksi</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ summary.total }}</p>
            </div>
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-tertiary">Berhasil</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ summary.success }}</p>
            </div>
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-secondary">Tertunda</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ summary.pending }}</p>
            </div>
            <div class="card">
                <p class="text-label-md font-semibold uppercase tracking-wide text-primary">Total Pendapatan</p>
                <p class="mt-2 text-title-lg font-bold tracking-tight">{{ formatCurrency(summary.revenue, 'IDR') }}</p>
            </div>
        </section>

        <form class="mt-6 flex flex-wrap items-center gap-2" @submit.prevent="load(1)">
            <input v-model="filters.search" type="search" class="input h-11 flex-1 min-w-52" placeholder="Cari nama atau email pengguna..." aria-label="Cari pembayaran" />
            <select v-model="filters.status" class="input h-11 w-full sm:w-44" aria-label="Filter status">
                <option value="">Semua Status</option>
                <option value="success">Berhasil</option>
                <option value="pending">Tertunda</option>
                <option value="failed">Gagal</option>
                <option value="refunded">Dikembalikan</option>
            </select>
            <button type="submit" class="btn-secondary h-11 px-5 text-label-md">Cari</button>
        </form>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

        <div class="card-flush mt-6 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead>
                    <tr class="border-b border-outline-variant/60 text-label-md uppercase tracking-wide text-on-surface-variant">
                        <th class="px-5 py-4 font-semibold">Pengguna</th>
                        <th class="px-5 py-4 font-semibold">Kursus</th>
                        <th class="px-5 py-4 font-semibold">Nominal</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold">Metode</th>
                        <th class="px-5 py-4 font-semibold">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    <tr v-if="loading">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Memuat data...</td>
                    </tr>
                    <tr v-else-if="payments.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Belum ada pembayaran.</td>
                    </tr>
                    <tr v-for="payment in payments" :key="payment.id" class="transition-colors hover:bg-surface-container-low/60">
                        <td class="px-5 py-4">
                            <p class="text-body-md font-semibold text-on-surface">{{ payment.user.name ?? 'Pengguna' }}</p>
                            <p class="text-body-sm text-on-surface-variant">{{ payment.user.email }}</p>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ payment.course?.title ?? 'Langganan' }}</td>
                        <td class="px-5 py-4 text-body-sm font-semibold text-on-surface">{{ formatCurrency(payment.amount, payment.currency) }}</td>
                        <td class="px-5 py-4">
                            <Badge :variant="statusVariant[payment.status] ?? 'outline'" size="sm">{{ statusLabel[payment.status] ?? payment.status }}</Badge>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ payment.paymentMethod ?? '-' }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ formatDate(payment.createdAt) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />
    </AdminLayout>
</template>
