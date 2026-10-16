<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import api from '@/lib/api'

interface Payment {
    id: number
    reference: string | null
    amount: number
    currency: string
    status: string
    paymentMethod: string | null
    notes: string | null
    subscriptionId: number | null
    plan: string | null
    billingCycle: string | null
    createdAt: string | null
}

const payments = ref<Payment[]>([])
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
    pending: 'Menunggu Verifikasi',
    failed: 'Gagal',
    refunded: 'Dikembalikan',
}
const paymentMethodLabel: Record<string, string> = {
    bank_transfer: 'Transfer Bank',
    e_wallet: 'E-Wallet',
    qris: 'QRIS',
}

function formatPrice(value: number, currency: string): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: currency || 'IDR', maximumFractionDigits: 0 }).format(value)
}

function formatDate(value: string | null): string {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value))
}

async function loadPayments(): Promise<void> {
    loading.value = true
    error.value = null

    try {
        const { data } = await api.get<{ payments: Payment[] }>('/payments')
        payments.value = data.payments
    } catch {
        error.value = 'Gagal memuat riwayat transaksi.'
    } finally {
        loading.value = false
    }
}

onMounted(loadPayments)
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-display-sm font-bold tracking-tight">Riwayat Transaksi</h1>
            <p class="mt-2 text-body-md text-on-surface-variant">Semua transaksi langganan dan pembayaranmu tampil di sini.</p>

            <p v-if="error" class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

            <div v-if="loading" class="mt-6 space-y-3">
                <div v-for="n in 3" :key="n" class="h-24 animate-pulse rounded-2xl bg-surface-container-high" />
            </div>

            <p v-else-if="payments.length === 0" class="card mt-6 text-center text-body-md text-on-surface-variant">
                Belum ada transaksi. <RouterLink to="/langganan" class="link">Pilih paket langganan</RouterLink> untuk memulai.
            </p>

            <ul v-else class="mt-6 space-y-3">
                <li v-for="payment in payments" :key="payment.id" class="card">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-mono text-body-sm text-on-surface-variant">{{ payment.reference ?? 'Tanpa referensi' }}</p>
                            <p class="mt-1 text-title-md font-semibold">{{ payment.plan ? `Paket ${payment.plan}` : 'Pembayaran' }}</p>
                            <p class="mt-1 text-body-sm text-on-surface-variant">
                                {{ payment.billingCycle === 'annual' ? 'Tahunan' : payment.billingCycle === 'monthly' ? 'Bulanan' : '-' }}
                                <span aria-hidden="true"> &middot; </span>
                                {{ payment.paymentMethod ? (paymentMethodLabel[payment.paymentMethod] ?? payment.paymentMethod) : 'Metode belum dipilih' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-title-md font-bold tracking-tight">{{ formatPrice(payment.amount, payment.currency) }}</p>
                            <Badge :variant="statusVariant[payment.status] ?? 'outline'" size="sm">{{ statusLabel[payment.status] ?? payment.status }}</Badge>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-outline-variant/50 pt-3">
                        <span class="text-body-sm text-on-surface-variant">{{ formatDate(payment.createdAt) }}</span>
                        <RouterLink v-if="payment.subscriptionId" :to="`/langganan/pesanan/${payment.subscriptionId}`" class="link text-body-sm">
                            Lihat Pesanan
                        </RouterLink>
                    </div>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>
