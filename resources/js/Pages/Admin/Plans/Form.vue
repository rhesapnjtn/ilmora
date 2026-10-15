<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Button from '@/Components/Button.vue'
import Input from '@/Components/Input.vue'
import api from '@/lib/api'

interface Plan {
    id: number
    name: string
    slug: string
    monthly: number
    annual: number
    highlighted: boolean
    features: string[]
    isActive: boolean
    sortOrder: number
}

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => route.name === 'admin-plans-edit')
const planId = computed(() => route.params.id as string | undefined)

const loading = ref(true)
const saving = ref(false)
const error = ref<string | null>(null)
const notice = ref<string | null>(null)
const errors = ref<Record<string, string>>({})

const form = reactive({
    name: '',
    slug: '',
    monthly: 0,
    annual: 0,
    highlighted: false,
    is_active: true,
    sort_order: 0,
    featuresText: '',
})

function firstError(key: string): string | undefined {
    return errors.value[key]
}

function toLines(value: string): string[] {
    return value
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => line !== '')
}

async function loadPlan(): Promise<void> {
    if (!isEdit.value || !planId.value) {
        loading.value = false
        return
    }

    try {
        const { data } = await api.get<{ plan: Plan }>(`/admin/plans/${planId.value}`)
        const plan = data.plan
        Object.assign(form, {
            name: plan.name ?? '',
            slug: plan.slug ?? '',
            monthly: plan.monthly ?? 0,
            annual: plan.annual ?? 0,
            highlighted: plan.highlighted ?? false,
            is_active: plan.isActive ?? true,
            sort_order: plan.sortOrder ?? 0,
            featuresText: (plan.features ?? []).join('\n'),
        })
    } catch {
        error.value = 'Gagal memuat data paket.'
    } finally {
        loading.value = false
    }
}

async function submit(): Promise<void> {
    saving.value = true
    error.value = null
    notice.value = null
    errors.value = {}

    const payload = {
        name: form.name,
        slug: form.slug || undefined,
        monthly: form.monthly,
        annual: form.annual,
        highlighted: form.highlighted,
        is_active: form.is_active,
        sort_order: form.sort_order,
        features: toLines(form.featuresText),
    }

    try {
        if (isEdit.value && planId.value) {
            const { data } = await api.put<{ message: string }>(`/admin/plans/${planId.value}`, payload)
            notice.value = data.message
        } else {
            const { data } = await api.post<{ message: string; plan: { id: number } }>('/admin/plans', payload)
            notice.value = data.message
            await router.replace({ name: 'admin-plans-edit', params: { id: data.plan.id } })
        }
    } catch (err) {
        const response = (err as { response?: { status?: number; data?: { errors?: Record<string, string[]>; message?: string } } }).response
        if (response?.status === 422 && response.data?.errors) {
            errors.value = Object.fromEntries(Object.entries(response.data.errors).map(([key, value]) => [key, value[0]]))
            error.value = 'Periksa kembali data yang dimasukkan.'
        } else {
            error.value = response?.data?.message ?? 'Gagal menyimpan paket.'
        }
    } finally {
        saving.value = false
    }
}

onMounted(loadPlan)
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-headline-sm font-bold">{{ isEdit ? 'Edit Paket' : 'Tambah Paket Baru' }}</h2>
                <p class="text-body-sm text-on-surface-variant">Lengkapi informasi paket langganan yang akan ditawarkan.</p>
            </div>
            <RouterLink to="/admin/paket" class="btn-secondary h-11 px-5 text-label-md">Kembali</RouterLink>
        </div>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
        <p v-if="notice" class="mt-4 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">{{ notice }}</p>

        <div v-if="loading" class="mt-6 h-80 animate-pulse rounded-2xl bg-surface-container-high" />

        <form v-else class="mt-6 grid gap-6 lg:grid-cols-3" @submit.prevent="submit">
            <div class="space-y-6 lg:col-span-2">
                <div class="card space-y-4">
                    <h3 class="text-title-lg font-semibold">Informasi Paket</h3>
                    <Input v-model="form.name" label="Nama Paket" required :error="firstError('name')" placeholder="Contoh: Paket Premium" />
                    <Input v-model="form.slug" label="Slug (opsional)" :error="firstError('slug')" placeholder="paket-premium" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="plan-monthly">Harga Bulanan (Rp)</label>
                            <input id="plan-monthly" v-model.number="form.monthly" type="number" min="0" step="1000" class="input" :class="{ 'input-error': firstError('monthly') }" />
                            <p v-if="firstError('monthly')" class="mt-1.5 text-body-sm text-error" role="alert">{{ firstError('monthly') }}</p>
                        </div>
                        <div>
                            <label class="label" for="plan-annual">Harga Tahunan (Rp)</label>
                            <input id="plan-annual" v-model.number="form.annual" type="number" min="0" step="1000" class="input" :class="{ 'input-error': firstError('annual') }" />
                            <p v-if="firstError('annual')" class="mt-1.5 text-body-sm text-error" role="alert">{{ firstError('annual') }}</p>
                        </div>
                    </div>
                </div>

                <div class="card space-y-4">
                    <h3 class="text-title-lg font-semibold">Fitur Paket</h3>
                    <div>
                        <label class="label" for="plan-features">Daftar fitur (satu per baris)</label>
                        <textarea id="plan-features" v-model="form.featuresText" rows="6" class="input h-auto py-3" placeholder="Akses seluruh katalog kursus&#10;Sertifikat premium&#10;Akses 3 perangkat" />
                        <p v-if="firstError('features')" class="mt-1.5 text-body-sm text-error" role="alert">{{ firstError('features') }}</p>
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="card space-y-4">
                    <h3 class="text-title-lg font-semibold">Pengaturan</h3>
                    <label class="flex items-center justify-between gap-3 text-body-md">
                        <span>Tandai populer</span>
                        <input v-model="form.highlighted" type="checkbox" class="h-5 w-5 rounded text-primary" />
                    </label>
                    <label class="flex items-center justify-between gap-3 text-body-md">
                        <span>Paket aktif</span>
                        <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded text-primary" />
                    </label>
                    <div>
                        <label class="label" for="plan-sort">Urutan Tampil</label>
                        <input id="plan-sort" v-model.number="form.sort_order" type="number" min="0" class="input" />
                    </div>
                </div>

                <div class="card">
                    <Button type="submit" :loading="saving" class="w-full">
                        {{ isEdit ? 'Simpan Perubahan' : 'Simpan Paket' }}
                    </Button>
                </div>
            </aside>
        </form>
    </AdminLayout>
</template>
