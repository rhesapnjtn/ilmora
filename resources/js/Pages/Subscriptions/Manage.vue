<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import Button from '@/Components/Button.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import api from '@/lib/api'
import { normalizeApiError } from '@/lib/errors'

interface SubscriptionPayment {
    id: number
    reference: string | null
    status: string
}

interface Subscription {
    id: number
    plan: string | null
    planSlug: string | null
    billingCycle: string | null
    status: string
    amount: number
    currency: string
    startsAt: string | null
    expiresAt: string | null
    cancelAtPeriodEnd: boolean
    canceledAt: string | null
    cancellationReason: string | null
    canCancel: boolean
    canResume: boolean
    createdAt: string | null
    payment: SubscriptionPayment | null
}

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

const subscriptions = ref<Subscription[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const notice = ref<string | null>(null)

const cancelOpen = ref(false)
const cancelReason = ref('')
const canceling = ref(false)

const resumeOpen = ref(false)
const resuming = ref(false)

const current = computed<Subscription | null>(() => {
    const active = subscriptions.value.find((item) => item.status === 'active')
    const pending = subscriptions.value.find((item) => item.status === 'pending')

    return active ?? pending ?? null
})

const history = computed(() =>
    subscriptions.value.filter((item) => item.id !== current.value?.id),
)

function formatPrice(value: number, currency: string): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: currency || 'IDR', maximumFractionDigits: 0 }).format(value)
}

function formatDate(value: string | null): string {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(value))
}

async function loadSubscriptions(): Promise<void> {
    loading.value = true
    error.value = null

    try {
        const { data } = await api.get<{ subscriptions: Subscription[] }>('/subscriptions')
        subscriptions.value = data.subscriptions ?? []
    } catch {
        error.value = 'Gagal memuat data langganan.'
    } finally {
        loading.value = false
    }
}

async function confirmCancel(): Promise<void> {
    if (!current.value) {
        return
    }

    canceling.value = true
    error.value = null
    notice.value = null

    try {
        const { data } = await api.post<{ message: string }>(`/subscriptions/${current.value.id}/cancel`, {
            reason: cancelReason.value.trim() === '' ? null : cancelReason.value.trim(),
        })
        notice.value = data.message
        cancelOpen.value = false
        cancelReason.value = ''
        await loadSubscriptions()
    } catch (requestError) {
        const { message } = normalizeApiError(requestError)
        error.value = message ?? 'Gagal membatalkan langganan.'
        cancelOpen.value = false
    } finally {
        canceling.value = false
    }
}

async function confirmResume(): Promise<void> {
    if (!current.value) {
        return
    }

    resuming.value = true
    error.value = null
    notice.value = null

    try {
        const { data } = await api.post<{ message: string }>(`/subscriptions/${current.value.id}/resume`)
        notice.value = data.message
        resumeOpen.value = false
        await loadSubscriptions()
    } catch (requestError) {
        const { message } = normalizeApiError(requestError)
        error.value = message ?? 'Gagal mengaktifkan kembali langganan.'
        resumeOpen.value = false
    } finally {
        resuming.value = false
    }
}

onMounted(loadSubscriptions)
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-display-sm font-bold tracking-tight">Kelola Langganan</h1>
                <RouterLink to="/langganan" class="link text-body-sm">Lihat Paket</RouterLink>
            </div>
            <p class="mt-2 text-body-md text-on-surface-variant">Pantau status langgananmu dan batalkan kapan saja tanpa perlu menghubungi admin.</p>

            <p v-if="error" class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
            <p v-if="notice" class="mt-6 rounded-2xl bg-primary/10 px-4 py-3 text-body-sm text-primary" role="status">{{ notice }}</p>

            <div v-if="loading" class="mt-6 space-y-4">
                <div class="h-40 animate-pulse rounded-2xl bg-surface-container-high" />
                <div class="h-40 animate-pulse rounded-2xl bg-surface-container-high" />
            </div>

            <template v-else>
                <section v-if="current" class="card mt-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <Badge :variant="statusVariant[current.status] ?? 'outline'" size="lg">{{ statusLabel[current.status] ?? current.status }}</Badge>
                        <span class="text-body-sm text-on-surface-variant">
                            {{ current.billingCycle === 'annual' ? 'Penagihan Tahunan' : 'Penagihan Bulanan' }}
                        </span>
                    </div>

                    <h2 class="mt-4 text-title-lg font-semibold">Paket {{ current.plan ?? '-' }}</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Masa Aktif</p>
                            <p class="mt-1 text-body-md">{{ formatDate(current.startsAt) }} &ndash; {{ formatDate(current.expiresAt) }}</p>
                        </div>
                        <div>
                            <p class="text-label-md uppercase tracking-wide text-on-surface-variant">Nilai Langganan</p>
                            <p class="mt-1 text-body-md font-semibold">{{ formatPrice(current.amount, current.currency) }}</p>
                        </div>
                    </div>

                    <p v-if="current.status === 'active' && current.cancelAtPeriodEnd" class="mt-4 rounded-2xl bg-surface-container-low px-4 py-3 text-body-sm text-on-surface-variant">
                        Langganan ini <span class="font-semibold text-on-surface">akan berakhir pada {{ formatDate(current.expiresAt) }}</span> dan tidak diperpanjang otomatis. Kamu masih bisa mengaktifkannya kembali sebelum tanggal tersebut.
                    </p>
                    <p v-else-if="current.status === 'active'" class="mt-4 text-body-sm text-on-surface-variant">
                        Langganan akan diperpanjang otomatis pada {{ formatDate(current.expiresAt) }}.
                    </p>
                    <p v-else-if="current.status === 'pending'" class="mt-4 rounded-2xl bg-surface-container-low px-4 py-4 text-body-sm text-on-surface-variant">
                        Pesananmu menunggu verifikasi pembayaran.
                        <RouterLink :to="`/langganan/pesanan/${current.id}`" class="link">Lihat instruksi pembayaran</RouterLink>.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <Button v-if="current.canCancel" variant="danger" @click="cancelOpen = true">
                            {{ current.status === 'pending' ? 'Batalkan Pesanan' : 'Batalkan Langganan' }}
                        </Button>
                        <Button v-if="current.canResume" variant="primary" @click="resumeOpen = true">Lanjutkan Langganan</Button>
                        <RouterLink v-if="current.status === 'pending'" :to="`/langganan/pesanan/${current.id}`" class="btn-secondary">Lihat Pesanan</RouterLink>
                    </div>

                    <p v-if="current.cancelAtPeriodEnd" class="mt-3 text-body-sm text-on-surface-variant">
                        Kamu membatalkan langganan pada {{ formatDate(current.canceledAt) }}. Akses tetap terbuka hingga {{ formatDate(current.expiresAt) }}.
                    </p>
                </section>

                <p v-else class="card mt-6 text-center text-body-md text-on-surface-variant">
                    Belum ada langganan aktif. <RouterLink to="/langganan" class="link">Pilih paket langganan</RouterLink> untuk memulai.
                </p>

                <section v-if="history.length > 0" class="mt-8">
                    <h2 class="text-title-md font-semibold">Riwayat Langganan</h2>
                    <ul class="mt-3 space-y-3">
                        <li v-for="item in history" :key="item.id" class="card">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-title-md font-semibold">Paket {{ item.plan ?? '-' }}</p>
                                    <p class="mt-1 text-body-sm text-on-surface-variant">
                                        {{ formatDate(item.startsAt) }} &ndash; {{ formatDate(item.expiresAt) }}
                                        <span aria-hidden="true"> &middot; </span>
                                        {{ item.billingCycle === 'annual' ? 'Tahunan' : 'Bulanan' }}
                                    </p>
                                </div>
                                <Badge :variant="statusVariant[item.status] ?? 'outline'" size="sm">{{ statusLabel[item.status] ?? item.status }}</Badge>
                            </div>
                            <RouterLink :to="`/langganan/pesanan/${item.id}`" class="link mt-3 inline-block text-body-sm">Lihat Pesanan</RouterLink>
                        </li>
                    </ul>
                </section>
            </template>
        </div>

        <ConfirmDialog
            v-model="cancelOpen"
            title="Batalkan langganan?"
            variant="danger"
            :confirm-label="current?.status === 'pending' ? 'Batalkan Pesanan' : 'Ya, Batalkan'"
            cancel-label="Batal"
            :loading="canceling"
            @confirm="confirmCancel"
        >
            <p v-if="current?.status === 'pending'" class="text-body-sm">
                Pesanan paket {{ current?.plan }} akan dibatalkan dan tidak akan diverifikasi admin. Kamu bisa membuat pesanan baru kapan saja.
            </p>
            <template v-else>
                <p class="text-body-sm">
                    Langgananmu tetap aktif hingga {{ formatDate(current?.expiresAt ?? null) }}, lalu berakhir tanpa perpanjangan otomatis. Kamu masih bisa membatalkan pembatalan ini sebelum tanggal tersebut.
                </p>
                <label class="mt-4 block text-label-md text-on-surface" for="cancel-reason">
                    Alasan (opsional)
                </label>
                <textarea
                    id="cancel-reason"
                    v-model="cancelReason"
                    rows="3"
                    maxlength="500"
                    class="input mt-2 w-full"
                    placeholder="Ceritakan alasanmu agar kami bisa berkembang lebih baik"
                />
            </template>
        </ConfirmDialog>

        <ConfirmDialog
            v-model="resumeOpen"
            title="Lanjutkan langganan?"
            variant="primary"
            confirm-label="Ya, Lanjutkan"
            cancel-label="Batal"
            :loading="resuming"
            message="Langganan akan kembali diperpanjang otomatis pada akhir periode."
            @confirm="confirmResume"
        />
    </AppLayout>
</template>
