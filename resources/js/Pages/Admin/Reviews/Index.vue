<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import api from '@/lib/api'

interface Review {
    id: number
    rating: number
    body: string | null
    user: { id: number; name: string; email: string } | null
    course: { id: number; title: string; slug: string } | null
}

const reviews = ref<Review[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const notice = ref<string | null>(null)
const deleteOpen = ref(false)
const deleting = ref(false)
const deleteTarget = ref<Review | null>(null)
const deleteError = ref<string | null>(null)

async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get<{ reviews: Review[] }>('/admin/reviews')
        reviews.value = data.reviews
    } catch {
        error.value = 'Gagal memuat ulasan.'
    } finally {
        loading.value = false
    }
}

function askDelete(r: Review): void {
    deleteTarget.value = r
    deleteError.value = null
    deleteOpen.value = true
}

async function confirmDelete(): Promise<void> {
    if (!deleteTarget.value) return
    deleting.value = true
    try {
        await api.delete(`/admin/reviews/${deleteTarget.value.id}`)
        deleteOpen.value = false
        notice.value = 'Ulasan dihapus.'
        await load()
    } catch {
        deleteError.value = 'Gagal menghapus ulasan.'
    } finally {
        deleting.value = false
    }
}

onMounted(load)
</script>

<template>
    <AdminLayout>
        <div>
            <h2 class="text-headline-sm font-bold">Moderasi Ulasan</h2>
            <p class="text-body-sm text-on-surface-variant">Hapus ulasan yang tidak pantas.</p>
        </div>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
        <p v-if="notice" class="mt-4 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">{{ notice }}</p>

        <div class="card-flush mt-6 overflow-x-auto">
            <table class="w-full min-w-[700px] text-left">
                <thead>
                    <tr class="border-b border-outline-variant/60 text-label-md uppercase tracking-wide text-on-surface-variant">
                        <th class="px-5 py-4 font-semibold">Pengguna</th>
                        <th class="px-5 py-4 font-semibold">Kursus</th>
                        <th class="px-5 py-4 font-semibold">Rating</th>
                        <th class="px-5 py-4 font-semibold">Ulasan</th>
                        <th class="px-5 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    <tr v-if="loading">
                        <td colspan="5" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Memuat...</td>
                    </tr>
                    <tr v-else-if="reviews.length === 0">
                        <td colspan="5" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Belum ada ulasan.</td>
                    </tr>
                    <tr v-for="r in reviews" :key="r.id" class="hover:bg-surface-container-low/60">
                        <td class="px-5 py-4 text-body-sm text-on-surface">{{ r.user?.name ?? '-' }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ r.course?.title ?? '-' }}</td>
                        <td class="px-5 py-4 text-body-sm text-[#F59E0B]">{{ '★'.repeat(r.rating) }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ r.body ?? '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-error transition-colors hover:bg-error/10" @click="askDelete(r)" title="Hapus" aria-label="Hapus">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6" /><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" /><path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /></svg>
                                </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmDialog
            v-model="deleteOpen"
            title="Hapus Ulasan"
            variant="danger"
            icon="trash"
            confirm-label="Hapus"
            :loading="deleting"
            @confirm="confirmDelete"
        >
            <p>Yakin hapus ulasan ini?</p>
            <p v-if="deleteError" class="mt-3 text-body-sm text-error" role="alert">{{ deleteError }}</p>
        </ConfirmDialog>
    </AdminLayout>
</template>