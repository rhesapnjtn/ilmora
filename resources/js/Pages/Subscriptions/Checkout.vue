<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import api from '@/lib/api'
import { normalizeApiError } from '@/lib/errors'

interface Plan {
    id: string
    name: string
    monthly: number
    annual: number
    highlighted: boolean
    features: string[]
}

const route = useRoute()
const router = useRouter()

const plan = ref<Plan | null>(null)
const discountPercent = ref(20)
const loading = ref(true)
const error = ref<string | null>(null)

const billing = ref<'monthly' | 'annual'>(route.query.billing === 'annual' ? 'annual' : 'monthly')
const paymentMethod = ref('bank_transfer')
const notes = ref('')

const errors = reactive<Record<string, string>>({})
const processing = ref(false)
const status = ref<string | null>(null)

const paymentMethods = [
    { value: 'bank_transfer', label: 'Transfer Bank' },
    { value: 'e_wallet', label: 'E-Wallet (OVO, GoPay, Dana)' },
    { value: 'qris', label: 'QRIS' },
]

const price = computed(() => {
    if (!plan.value) {
        return 0
    }

    return billing.value === 'annual' ? plan.value.annual : plan.value.monthly
})

function formatPrice(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}

async function loadPlan(): Promise<void> {
    loading.value = true
    error.value = null

    try {
        const { data } = await api.get<{ plan: Plan; discountPercent: number }>(
            `/plans/${encodeURIComponent(String(route.params.slug))}`,
        )
        plan.value = data.plan
        discountPercent.value = data.discountPercent
    } catch (requestError) {
        const { status: errorStatus, message } = normalizeApiError(requestError)
        error.value = errorStatus === 404 ? 'Paket langganan tidak ditemukan.' : message ?? 'Gagal memuat paket langganan.'
    } finally {
        loading.value = false
    }
}

function clearErrors(): void {
    for (const key of Object.keys(errors)) {
        delete errors[key]
    }
}

async function submit(): Promise<void> {
    if (!plan.value) {
        return
    }

    clearErrors()
    status.value = null
    processing.value = true

    try {
        const { data } = await api.post<{ subscription: { id: number } }>('/subscriptions', {
            plan: plan.value.id,
            billing_cycle: billing.value,
            payment_method: paymentMethod.value,
            notes: notes.value || null,
        })

        router.push(`/langganan/pesanan/${data.subscription.id}`)
    } catch (requestError) {
        const { messages, message } = normalizeApiError(requestError)
        Object.assign(errors, messages)

        if (Object.keys(messages).length === 0) {
            status.value = message ?? 'Gagal membuat pesanan. Silakan coba lagi.'
        }
    } finally {
        processing.value = false
    }
}

onMounted(loadPlan)
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-display-sm font-bold tracking-tight">Checkout Langganan</h1>
            <p class="mt-2 text-body-md text-on-surface-variant">
                Lengkapi detail pesanan. Akses premium aktif setelah pembayaran diverifikasi admin.
            </p>

            <p v-if="error" class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

            <div v-if="loading" class="mt-6 h-72 animate-pulse rounded-2xl bg-surface-container-high" />

            <template v-else-if="plan">
                <form class="mt-6 space-y-6" @submit.prevent="submit">
                    <div class="card">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-title-lg font-semibold">Paket {{ plan.name }}</h2>
                                <p class="mt-1 text-body-sm text-on-surface-variant">Pilih siklus penagihan sesuai kebutuhanmu.</p>
                            </div>
                            <p class="text-display-sm font-bold tracking-tight">{{ formatPrice(price) }}</p>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <button
                                type="button"
                                class="rounded-2xl border p-4 text-left transition-colors"
                                :class="billing === 'monthly' ? 'border-primary bg-primary/5' : 'border-outline-variant hover:border-primary/40'"
                                @click="billing = 'monthly'"
                            >
                                <span class="text-label-md font-semibold">Bulanan</span>
                                <span class="mt-1 block text-body-sm text-on-surface-variant">{{ formatPrice(plan.monthly) }} / bulan</span>
                            </button>
                            <button
                                type="button"
                                class="rounded-2xl border p-4 text-left transition-colors"
                                :class="billing === 'annual' ? 'border-primary bg-primary/5' : 'border-outline-variant hover:border-primary/40'"
                                @click="billing = 'annual'"
                            >
                                <span class="text-label-md font-semibold">Tahunan</span>
                                <span class="mt-1 block text-body-sm text-on-surface-variant">{{ formatPrice(plan.annual) }} / tahun</span>
                                <span class="mt-1 block text-label-sm font-semibold text-tertiary">Hemat {{ discountPercent }}%</span>
                            </button>
                        </div>

                        <ul class="mt-5 space-y-2">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-3 text-body-md text-on-surface-variant">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-tertiary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6L9 17l-5-5" /></svg>
                                <span>{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="card">
                        <h2 class="text-title-lg font-semibold">Metode Pembayaran</h2>

                        <div class="mt-4">
                            <label for="payment_method" class="label">Metode</label>
                            <select id="payment_method" v-model="paymentMethod" class="input h-13">
                                <option v-for="method in paymentMethods" :key="method.value" :value="method.value">{{ method.label }}</option>
                            </select>
                            <p v-if="errors.payment_method" class="mt-1.5 text-body-sm text-error" role="alert">{{ errors.payment_method }}</p>
                        </div>

                        <div class="mt-4">
                            <label for="notes" class="label">Catatan <span class="text-on-surface-variant">(opsional)</span></label>
                            <textarea
                                id="notes"
                                v-model="notes"
                                rows="3"
                                maxlength="500"
                                class="input min-h-24 py-3"
                                placeholder="Contoh: nama pengirim atau nomor referensi transfer"
                            />
                            <p v-if="errors.notes" class="mt-1.5 text-body-sm text-error" role="alert">{{ errors.notes }}</p>
                        </div>
                    </div>

                    <p v-if="status" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ status }}</p>
                    <p v-if="errors.plan" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ errors.plan }}</p>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-body-sm text-on-surface-variant">
                            Total dibayar: <span class="font-semibold text-on-surface">{{ formatPrice(price) }}</span>
                        </p>
                        <Button type="submit" :loading="processing" :disabled="processing">Buat Pesanan</Button>
                    </div>
                </form>
            </template>
        </div>
    </AppLayout>
</template>
