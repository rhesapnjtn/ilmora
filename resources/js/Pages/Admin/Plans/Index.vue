<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
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
    createdAt: string | null
}

const plans = ref<Plan[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const notice = ref<string | null>(null)

const deleteOpen = ref(false)
const deleting = ref(false)
const deleteTarget = ref<Plan | null>(null)
const deleteError = ref<string | null>(null)

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}

async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get<{ plans: Plan[] }>('/admin/plans')
        plans.value = data.plans
    } catch {
        error.value = 'Gagal memuat daftar paket.'
    } finally {
        loading.value = false
    }
}

function askDelete(plan: Plan): void {
    deleteTarget.value = plan
    deleteError.value = null
    deleteOpen.value = true
}

async function confirmDelete(): Promise<void> {
    if (!deleteTarget.value) return
    deleting.value = true
    try {
        const { data } = await api.delete<{ message: string }>(`/admin/plans/${deleteTarget.value.id}`)
        notice.value = data.message
        deleteOpen.value = false
        await load()
    } catch (err) {
        const response = (err as { response?: { data?: { message?: string } } }).response
        deleteError.value = response?.data?.message ?? 'Gagal menghapus paket.'
    } finally {
        deleting.value = false
    }
}

onMounted(load)
</script>

<template>
    <AdminLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-headline-sm font-bold">Kelola Paket Langganan</h2>
                <p class="text-body-sm text-on-surface-variant">Tambah, ubah, atau nonaktifkan paket yang ditampilkan di halaman langganan.</p>
            </div>
            <RouterLink to="/admin/paket/baru" class="btn-primary h-11 px-5 text-label-md">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                Tambah Paket
            </RouterLink>
        </div>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
        <p v-if="notice" class="mt-4 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">{{ notice }}</p>

        <div class="card-flush mt-6 overflow-x-auto">
            <table class="w-full min-w-[820px] text-left">
                <thead>
                    <tr class="border-b border-outline-variant/60 text-label-md uppercase tracking-wide text-on-surface-variant">
                        <th class="px-5 py-4 font-semibold">Paket</th>
                        <th class="px-5 py-4 font-semibold">Bulanan</th>
                        <th class="px-5 py-4 font-semibold">Tahunan</th>
                        <th class="px-5 py-4 font-semibold">Fitur</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    <tr v-if="loading">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Memuat data...</td>
                    </tr>
                    <tr v-else-if="plans.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Belum ada paket.</td>
                    </tr>
                    <tr v-for="plan in plans" :key="plan.id" class="transition-colors hover:bg-surface-container-low/60">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <p class="text-body-md font-semibold text-on-surface">{{ plan.name }}</p>
                                <Badge v-if="plan.highlighted" variant="primary" size="sm">Populer</Badge>
                            </div>
                            <p class="text-body-sm text-on-surface-variant">{{ plan.slug }}</p>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ formatCurrency(plan.monthly) }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ formatCurrency(plan.annual) }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ plan.features.length }} fitur</td>
                        <td class="px-5 py-4">
                            <Badge :variant="plan.isActive ? 'success' : 'outline'" size="sm">{{ plan.isActive ? 'Aktif' : 'Nonaktif' }}</Badge>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <RouterLink :to="`/admin/paket/${plan.id}/edit`" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary" title="Edit" aria-label="Edit">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                                </RouterLink>
                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-error transition-colors hover:bg-error/10" @click="askDelete(plan)" title="Hapus" aria-label="Hapus">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6" /><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" /><path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmDialog
            v-model="deleteOpen"
            title="Hapus Paket"
            variant="danger"
            icon="trash"
            confirm-label="Hapus"
            :loading="deleting"
            @confirm="confirmDelete"
        >
            <p>
                Yakin ingin menghapus paket <strong class="text-on-surface">{{ deleteTarget?.name }}</strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <p v-if="deleteError" class="mt-3 text-body-sm text-error" role="alert">{{ deleteError }}</p>
        </ConfirmDialog>
    </AdminLayout>
</template>
