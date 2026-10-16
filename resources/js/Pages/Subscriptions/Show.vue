<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import api from '@/lib/api'
import { normalizeApiError } from '@/lib/errors'

interface OrderPayment {
    id: number
    reference: string | null
    amount: number
    currency: string
    status: string
    paymentMethod: string | null
    notes: string | null
    createdAt: string | null
}

interface Order {
    id: number
    plan: string
    planSlug: string | null
    billingCycle: string | null
    status: string
    amount: number
    currency: string
    startsAt: string | null
    expiresAt: string | null
    createdAt: string | null
    payment: OrderPayment | null
}

const route = useRoute()

const order = ref<Order | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const statusVariant: Record<string, 'success' | 'secondary' | 'error' | 'outline'> = {
    active: 'success',
    pending: 'secondary',
    expired: 'outline',
    canceled: 'error',
}
const statusLabel: Record<string, string> = {
    active: 'Langganan Aktif',
    pending: 'Menunggu Pembayaran',
    expired: 'Kedaluwarsa',
    canceled: 'Dibatalkan',
}
const paymentStatusLabel: Record<string, string> = {
    success: 'Berhasil',
    pending: 'Menunggu Verifikasi',
    failed: 'Gagal',
    refunded: 'Dikembalikan',
}
const paymentMethodLabel: Record<string, string> = {
    bank_transfer: 'Transfer Bank',
    e_wallet: 'E-Wallet',
    qris: 'QRIS',
}

const statusDescription = computed(() => {
    switch (order.value?.status) {
        case 'active':
            return 'Pembayaranmu telah diverifikasi. Seluruh materi premium kini terbuka untukmu.'
        case 'pending':
            return 'Pesananmu menunggu verifikasi pembayaran oleh admin. Simpan nomor referensi di bawah.'
        case 'canceled':
            return 'Pembayaran ditolak sehingga pesanan ini dibatalkan. Kamu dapat membuat pesanan baru.'
        default:
            return 'Pesanan ini sudah tidak aktif.'
    }
})

function formatPrice(value: number, currency = 'IDR'): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency, maximumFractionDigits: 0 }).format(value)
}

function formatDate(value: string | null): string {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(value))
}

async function loadOrder(): Promise<void> {
    loading.value = true
    error.value = null

    try {
        const { data } = await api.get<{ subscription: Order }>(`/subscriptions/${route.params.id}`)
        order.value = data.subscription
    } catch (requestError) {
        const { status, message } = normalizeApiError(requestError)
        error.value = status === 404 ? 'Pesanan tidak ditemukan.' : message ?? 'Gagal memuat detail pesanan.'
    } finally {
        loading.value = false
    }
}

onMounted(loadOrder)
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-display-sm font-bold tracking-tight">Status Pesanan</h1>
                <RouterLink to="/langganan/riwayat" class="link text-body-sm">Riwayat Transaksi</RouterLink>
            </div>

            <p v-if="error" class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

            <div v-if="loading" class="mt-6 space-y-4">
                <div class="h-40 animate-pulse rounded-2xl bg-surface-container-high" />
                <div class="h-40 animate-pulse rounded-2xl bg-surface-container-high" />
            </div>

            <template v-else-if="order">
                <section class="card mt-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <Badge :variant="statusVariant[order.status] ?? 'outline'" size="lg">{{ statusLabel[order.status] ?? order.status }}</Badge>
                        <span class="text-body-sm text-on-surface-variant">Dibuat {{ formatDate(order.createdAt) }}</span>
                    </div>
                    <p class="mt-4 text-body-md text-on-surface-variant">{{ statusDescription }}</p>
                </section>

                <section class="card mt-6">
                    <h2 class="text-title-lg font-semibold">Rincian Pesanan</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Paket</p>
                            <p class="mt-1 text-body-md font-semibold">{{ order.plan }}</p>
                        </div>
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Siklus Penagihan</p>
                            <p class="mt-1 text-body-md">{{ order.billingCycle === 'annual' ? 'Tahunan' : 'Bulanan' }}</p>
                        </div>
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Total</p>
                            <p class="mt-1 text-body-md font-semibold">{{ formatPrice(order.amount, order.currency) }}</p>
                        </div>
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Dibuat</p>
                            <p class="mt-1 text-body-md">{{ formatDate(order.createdAt) }}</p>
                        </div>
                        <div v-if="order.startsAt || order.expiresAt">
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Masa Aktif</p>
                            <p class="mt-1 text-body-md">{{ formatDate(order.startsAt) }} &ndash; {{ formatDate(order.expiresAt) }}</p>
                        </div>
                    </div>
                </section>

                <section v-if="order.payment" class="card mt-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <h2 class="text-title-lg font-semibold">Pembayaran</h2>
                        <Badge :variant="order.payment.status === 'success' ? 'success' : order.payment.status === 'failed' ? 'error' : 'secondary'" size="sm">
                            {{ paymentStatusLabel[order.payment.status] ?? order.payment.status }}
                        </Badge>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Nomor Referensi</p>
                            <p class="mt-1 font-mono text-body-md font-semibold">{{ order.payment.reference ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Metode</p>
                            <p class="mt-1 text-body-md">
                                {{ order.payment.paymentMethod ? (paymentMethodLabel[order.payment.paymentMethod] ?? order.payment.paymentMethod) : 'Belum dipilih' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Nominal</p>
                            <p class="mt-1 text-body-md font-semibold">{{ formatPrice(order.payment.amount, order.payment.currency) }}</p>
                        </div>
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Dibuat</p>
                            <p class="mt-1 text-body-md">{{ formatDate(order.payment.createdAt) }}</p>
                        </div>
                    </div>

                    <p v-if="order.payment.notes" class="mt-4 rounded-2xl bg-surface-container-low px-4 py-3 text-body-sm text-on-surface-variant">
                        Catatan: {{ order.payment.notes }}
                    </p>

                    <div v-if="order.status === 'pending'" class="mt-4 rounded-2xl bg-surface-container-low px-4 py-4 text-body-sm text-on-surface-variant">
                        <p class="font-semibold text-on-surface">Instruksi Pembayaran</p>
                        <ol class="mt-2 list-decimal space-y-1 pl-5">
                            <li>Selesaikan pembayaran sebesar <span class="font-semibold text-on-surface">{{ formatPrice(order.payment.amount, order.payment.currency) }}</span> sesuai metode yang kamu pilih.</li>
                            <li>Sertakan nomor referensi <span class="font-mono font-semibold text-on-surface">{{ order.payment.reference ?? '-' }}</span> pada keterangan pembayaran.</li>
                            <li>Admin akan memverifikasi pembayaran dan mengaktifkan langgananmu.</li>
                        </ol>
                    </div>
                </section>

                <div class="mt-6 flex flex-wrap gap-3">
                    <RouterLink to="/langganan/riwayat" class="btn-secondary">Lihat Riwayat Transaksi</RouterLink>
                    <RouterLink to="/langganan" class="btn-secondary">Kembali ke Langganan</RouterLink>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
