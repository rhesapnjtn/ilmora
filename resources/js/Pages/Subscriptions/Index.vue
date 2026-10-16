<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { useAuth } from '@/stores/auth'
import api from '@/lib/api'

interface Plan {
    id: string
    name: string
    monthly: number
    annual: number
    highlighted: boolean
    features: string[]
}

interface Membership {
    id: number
    plan: string | null
    status: string
    billingCycle: string | null
    expiresAt: string | null
    cancelAtPeriodEnd: boolean
}

const { isAuthenticated, user } = useAuth()

const plans = ref<Plan[]>([])
const discount = ref(20)
const billing = ref<'monthly' | 'annual'>('monthly')
const loading = ref(true)
const error = ref<string | null>(null)

const subscriptions = ref<Membership[]>([])
const membershipLoading = ref(false)

const memberLabel = computed(() => user.value?.name ?? 'Siswa Ilmora')

const membership = computed<Membership | null>(() => {
    const active = subscriptions.value.find((item) => item.status === 'active')
    const pending = subscriptions.value.find((item) => item.status === 'pending')

    return active ?? pending ?? null
})

const membershipBadgeClass = computed(() => (membership.value?.status === 'active' ? 'badge-success' : 'badge-promo'))

const membershipStatusLabel = computed(() => (membership.value?.status === 'active' ? 'Aktif' : 'Menunggu Pembayaran'))

const membershipHeadline = computed(() => {
    if (membershipLoading.value) {
        return 'Memuat status langganan…'
    }

    if (!membership.value) {
        return 'Kamu belum berlangganan'
    }

    return membership.value.status === 'active'
        ? `Paket ${membership.value.plan ?? 'Langganan'} aktif`
        : `Pesanan paket ${membership.value.plan ?? ''} menunggu verifikasi`
})

const membershipSubtitle = computed(() => {
    const current = membership.value

    if (!current) {
        return 'Pilih paket di bawah untuk membuka seluruh materi dan sertifikat premium Ilmora.'
    }

    if (current.status === 'pending') {
        return 'Selesaikan pembayaran agar admin dapat memverifikasi dan mengaktifkan langgananmu.'
    }

    if (current.cancelAtPeriodEnd) {
        return `Dibatalkan — tetap aktif hingga ${formatDate(current.expiresAt)} dan tidak diperpanjang otomatis.`
    }

    return `Aktif hingga ${formatDate(current.expiresAt)}. Nikmati akses penuh ke seluruh katalog premium.`
})

const benefits = [
    { title: 'Katalog materi lengkap', description: 'Akses seluruh kursus premium Ilmora.', icon: 'grid' },
    { title: 'Sertifikat resmi', description: 'Divalidasi industri dan siap dilampirkan.', icon: 'award' },
    { title: 'Fleksibel belajar', description: 'Kapan saja dan di mana saja.', icon: 'clock' },
    { title: 'Dukungan mentor', description: 'Bimbingan interaktif dari praktisi.', icon: 'chat' },
]

function planLink(plan: Plan): string {
    if (!isAuthenticated.value) {
        return '/daftar'
    }

    return `/langganan/paket/${encodeURIComponent(plan.id)}?billing=${billing.value}`
}

function priceFor(plan: Plan): number {
    return billing.value === 'annual' ? plan.annual : plan.monthly
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function formatDate(value: string | null): string {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(value))
}

async function loadPlans(): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get<{ plans: Plan[]; discountPercent: number }>('/plans')
        plans.value = data.plans
        discount.value = data.discountPercent
    } catch {
        error.value = 'Gagal memuat paket langganan.'
    } finally {
        loading.value = false
    }
}

async function loadMembership(): Promise<void> {
    if (!isAuthenticated.value) {
        return
    }

    membershipLoading.value = true

    try {
        const { data } = await api.get<{ subscriptions: Membership[] }>('/subscriptions')
        subscriptions.value = data.subscriptions ?? []
    } catch {
        subscriptions.value = []
    } finally {
        membershipLoading.value = false
    }
}

onMounted(() => {
    loadPlans()
    loadMembership()
})
</script>

<template>
    <GuestLayout>
        <section class="border-b border-outline-variant/50 bg-surface-container-low">
            <div class="mx-auto max-w-7xl px-4 py-10 text-center md:py-14">
                <span class="badge-promo">Member {{ memberLabel }}</span>
                <h1 class="mt-3 text-display-sm font-bold tracking-tight">Pilih Paket Langganan</h1>
                <p class="mx-auto mt-2 max-w-2xl text-body-lg text-on-surface-variant">
                    Dapatkan akses penuh ke semua kursus premium dengan harga terbaik.
                </p>

                <div class="mt-6 inline-flex rounded-2xl bg-surface-container-high p-1" role="group" aria-label="Siklus penagihan">
                    <button
                        type="button"
                        class="rounded-xl px-5 py-2 text-label-md font-semibold transition-colors"
                        :class="billing === 'monthly' ? 'bg-primary text-white' : 'text-on-surface-variant'"
                        @click="billing = 'monthly'"
                    >
                        Bulanan
                    </button>
                    <button
                        type="button"
                        class="rounded-xl px-5 py-2 text-label-md font-semibold transition-colors"
                        :class="billing === 'annual' ? 'bg-primary text-white' : 'text-on-surface-variant'"
                        @click="billing = 'annual'"
                    >
                        Tahunan <span class="text-tertiary">Hemat {{ discount }}%</span>
                    </button>
                </div>
            </div>
        </section>

        <section v-if="isAuthenticated" class="border-b border-outline-variant/50 bg-gradient-to-r from-primary/5 via-surface to-tertiary/5">
            <div class="mx-auto max-w-7xl px-4 py-6 lg:px-8">
                <div class="card flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary/15 to-tertiary/25 text-primary" aria-hidden="true">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3l2.6 5.3 5.9.9-4.3 4.1 1 5.9L12 16.9 6.8 19.5l1-5.9-4.3-4.1 5.9-.9z" />
                            </svg>
                        </span>

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-title-md font-semibold">{{ membershipHeadline }}</h2>
                                <span v-if="membership" :class="membershipBadgeClass">{{ membershipStatusLabel }}</span>
                            </div>

                            <p v-if="membershipLoading" class="mt-2 h-4 w-56 animate-pulse rounded bg-surface-container-high" aria-hidden="true" />
                            <p v-else class="mt-1 max-w-xl text-body-sm text-on-surface-variant">{{ membershipSubtitle }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 lg:justify-end">
                        <template v-if="membership?.status === 'pending'">
                            <RouterLink :to="`/langganan/pesanan/${membership.id}`" class="btn-primary">Lihat Pesanan</RouterLink>
                            <RouterLink to="/langganan/kelola" class="btn-secondary">Kelola Langganan</RouterLink>
                        </template>
                        <template v-else-if="membership">
                            <RouterLink to="/langganan/kelola" class="btn-primary">Kelola Langganan</RouterLink>
                            <RouterLink to="/langganan/riwayat" class="btn-secondary">Riwayat Transaksi</RouterLink>
                        </template>
                        <template v-else>
                            <a href="#paket" class="btn-primary">Pilih Paket</a>
                            <RouterLink to="/langganan/riwayat" class="btn-secondary">Riwayat Transaksi</RouterLink>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <section id="paket" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-12">
            <p v-if="error" class="mb-6 rounded-2xl bg-error/10 px-4 py-3 text-center text-body-sm text-error" role="alert">{{ error }}</p>

            <div v-if="loading" class="grid gap-6 md:grid-cols-3">
                <div v-for="n in 3" :key="n" class="card">
                    <div class="h-6 w-1/2 animate-pulse rounded bg-surface-container-high" />
                    <div class="mt-4 h-9 w-2/3 animate-pulse rounded bg-surface-container-high" />
                </div>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-3">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="card relative flex flex-col"
                    :class="plan.highlighted ? 'border-primary shadow-[0_12px_40px_-12px_rgba(53,37,205,0.35)] md:-translate-y-2' : ''"
                >
                    <span v-if="plan.highlighted" class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary px-4 py-1 text-label-sm font-semibold text-white">Paling Populer</span>

                    <h2 class="text-title-lg font-semibold">Paket {{ plan.name }}</h2>
                    <div class="mt-4 flex items-end gap-1">
                        <span class="text-display-sm font-bold tracking-tight">{{ formatPrice(priceFor(plan)) }}</span>
                        <span class="pb-1 text-body-sm text-on-surface-variant">/ {{ billing === 'annual' ? 'tahun' : 'bulan' }}</span>
                    </div>
                    <p v-if="billing === 'annual'" class="mt-1 text-body-sm text-tertiary">Hemat {{ discount }}% dibanding bulanan</p>

                    <ul class="mt-6 flex-1 space-y-3">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-3 text-body-md text-on-surface-variant">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-tertiary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6L9 17l-5-5" /></svg>
                            <span>{{ feature }}</span>
                        </li>
                    </ul>

                    <RouterLink :to="planLink(plan)" class="mt-8 w-full text-center" :class="plan.highlighted ? 'btn-primary' : 'btn-secondary'">Pilih Paket</RouterLink>
                </div>
            </div>
        </section>

        <section class="bg-surface-container-low py-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-headline-lg font-bold tracking-tight">Investasi terbaik untuk masa depanmu</h2>
                    <p class="mt-2 text-body-lg text-on-surface-variant">Pilih paket yang paling sesuai dengan kebutuhanmu.</p>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
