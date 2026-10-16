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

const { isAuthenticated, user } = useAuth()

const plans = ref<Plan[]>([])
const discount = ref(20)
const billing = ref<'monthly' | 'annual'>('monthly')
const loading = ref(true)
const error = ref<string | null>(null)

const memberLabel = computed(() => user.value?.name ?? 'Siswa Ilmora')

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

onMounted(loadPlans)
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

        <section class="mx-auto max-w-7xl px-4 py-12">
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
                    <p class="mt-3 text-body-lg text-on-surface-variant">
                        Tingkatkan skill digital praktis dan bangun karier impian bersama ribuan alumni yang telah sukses bekerja di berbagai industri.
                    </p>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="benefit in benefits" :key="benefit.title" class="card">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <template v-if="benefit.icon === 'grid'"><rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" /><rect x="3" y="14" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" /></template>
                                <template v-else-if="benefit.icon === 'award'"><circle cx="12" cy="8" r="5" /><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5" /></template>
                                <template v-else-if="benefit.icon === 'clock'"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></template>
                                <template v-else><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></template>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-title-md font-semibold">{{ benefit.title }}</h3>
                        <p class="mt-2 text-body-md text-on-surface-variant">{{ benefit.description }}</p>
                    </div>
                </div>

                <p class="mt-8 text-center text-body-sm text-on-surface-variant">Harga dapat berubah. Pastikan detail paket sesuai sebelum melanjutkan.</p>
            </div>
        </section>
    </GuestLayout>
</template>
