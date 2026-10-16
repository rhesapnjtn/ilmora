<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import Button from '@/Components/Button.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Pagination from '@/Components/Pagination.vue'
import api from '@/lib/api'
import { normalizeApiError } from '@/lib/errors'

interface AdminSubscriptionRef { id: number; plan: string | null; billingCycle: string | null; status: string }
interface AdminPayment {
    id: number
    user: { id: number | null; name: string | null; email: string | null }
    course: { id: number; title: string; slug: string } | null
    subscription: AdminSubscriptionRef | null
    reference: string | null
    amount: number
    currency: string
    status: string
    paymentMethod: string | null
    paymentId: string | null
    notes: string | null
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
const notice = ref<string | null>(null)

const reviewOpen = ref(false)
const reviewTarget = ref<AdminPayment | null>(null)
const reviewAction = ref<'approve' | 'reject'>('approve')
const reviewing = ref(false)

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

function itemLabel(payment: AdminPayment): string {
    if (payment.subscription?.plan) {
        return `Paket ${payment.subscription.plan}`
    }
    if (payment.course) {
        return payment.course.title
    }
    return '-'
}

async function load(page = 1): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get('/admin/payments', {
            params: { search: filters.search || undefined, status: filters.status || undefined, page },
        })
        payments.value = data.payments ?? []
        meta.value = data.meta ?? meta.value
        summary.value = data.summary ?? summary.value
    } catch {
        error.value = 'Gagal memuat data pembayaran.'
    } finally {
        loading.value = false
    }
}

function openReview(payment: AdminPayment, action: 'approve' | 'reject'): void {
    reviewTarget.value = payment
    reviewAction.value = action
    notice.value = null
    reviewOpen.value = true
}

async function confirmReview(): Promise<void> {
    if (!reviewTarget.value) {
        return
    }

    reviewing.value = true
    error.value = null
    notice.value = null

    try {
        const { data } = await api.post<{ message: string }>(
            `/admin/payments/${reviewTarget.value.id}/${reviewAction.value}`,
        )
        notice.value = data.message
        reviewOpen.value = false
        await load(meta.value.page)
    } catch (requestError) {
        const { message } = normalizeApiError(requestError)
        error.value = message ?? 'Gagal memproses pembayaran.'
    } finally {
        reviewing.value = false
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
        <p v-if="notice" class="mt-4 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">{{ notice }}</p>

        <div class="card-flush mt-6 overflow-x-auto">
            <table class="w-full min-w-[920px] text-left">
                <thead>
                    <tr class="border-b border-outline-variant/60 text-label-md uppercase tracking-wide text-on-surface-variant">
                        <th class="px-5 py-4 font-semibold">Pengguna</th>
                        <th class="px-5 py-4 font-semibold">Item</th>
                        <th class="px-5 py-4 font-semibold">Nominal</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold">Metode</th>
                        <th class="px-5 py-4 font-semibold">Tanggal</th>
                        <th class="px-5 py-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    <tr v-if="loading">
                        <td colspan="7" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Memuat data...</td>
                    </tr>
                    <tr v-else-if="payments.length === 0">
                        <td colspan="7" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Belum ada pembayaran.</td>
                    </tr>
                    <tr v-for="payment in payments" :key="payment.id" class="transition-colors hover:bg-surface-container-low/60">
                        <td class="px-5 py-4">
                            <p class="text-body-md font-semibold text-on-surface">{{ payment.user.name ?? 'Pengguna' }}</p>
                            <p class="text-body-sm text-on-surface-variant">{{ payment.user.email }}</p>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">
                            <p>{{ itemLabel(payment) }}</p>
                            <p v-if="payment.reference" class="mt-0.5 font-mono text-label-sm">{{ payment.reference }}</p>
                        </td>
                        <td class="px-5 py-4 text-body-sm font-semibold text-on-surface">{{ formatCurrency(payment.amount, payment.currency) }}</td>
                        <td class="px-5 py-4">
                            <Badge :variant="statusVariant[payment.status] ?? 'outline'" size="sm">{{ statusLabel[payment.status] ?? payment.status }}</Badge>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ payment.paymentMethod ?? '-' }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ formatDate(payment.createdAt) }}</td>
                        <td class="px-5 py-4">
                            <div v-if="payment.status === 'pending'" class="flex flex-wrap gap-2">
                                <Button size="sm" @click="openReview(payment, 'approve')">Verifikasi</Button>
                                <Button size="sm" variant="danger" @click="openReview(payment, 'reject')">Tolak</Button>
                            </div>
                            <span v-else class="text-body-sm text-on-surface-variant">&mdash;</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />

        <ConfirmDialog
            v-model="reviewOpen"
            :title="reviewAction === 'approve' ? 'Verifikasi Pembayaran?' : 'Tolak Pembayaran?'"
            :message="reviewAction === 'approve'
                ? 'Langganan pengguna akan langsung diaktifkan setelah pembayaran diverifikasi.'
                : 'Pembayaran akan ditandai gagal dan pesanan pengguna dibatalkan.'"
            :confirm-label="reviewAction === 'approve' ? 'Verifikasi' : 'Tolak'"
            cancel-label="Batal"
            :variant="reviewAction === 'approve' ? 'primary' : 'danger'"
            :icon="reviewAction === 'approve' ? 'question' : 'warning'"
            :loading="reviewing"
            @confirm="confirmReview"
        />
    </AdminLayout>
</template>
