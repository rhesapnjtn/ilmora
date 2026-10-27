<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import api from '@/lib/api'

interface Category { id: number; slug: string; name: string }

const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const notice = ref<string | null>(null)
const form = ref({ name: '', slug: '' })
const saving = ref(false)
const deleteOpen = ref(false)
const deleting = ref(false)
const deleteTarget = ref<Category | null>(null)
const deleteError = ref<string | null>(null)

async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get<{ categories: Category[] }>('/admin/categories')
        categories.value = data.categories
    } catch {
        error.value = 'Gagal memuat kategori.'
    } finally {
        loading.value = false
    }
}

async function create(): Promise<void> {
    if (!form.value.name.trim()) return
    saving.value = true
    try {
        await api.post('/admin/categories', { name: form.value.name, slug: form.value.slug || undefined })
        notice.value = 'Kategori ditambahkan.'
        form.value = { name: '', slug: '' }
        await load()
    } catch {
        error.value = 'Gagal menambah kategori.'
    } finally {
        saving.value = false
    }
}

function askDelete(c: Category): void {
    deleteTarget.value = c
    deleteError.value = null
    deleteOpen.value = true
}

async function confirmDelete(): Promise<void> {
    if (!deleteTarget.value) return
    deleting.value = true
    try {
        await api.delete(`/admin/categories/${deleteTarget.value.id}`)
        deleteOpen.value = false
        await load()
    } catch (err) {
        const response = (err as { response?: { data?: { message?: string } } }).response
        deleteError.value = response?.data?.message ?? 'Gagal menghapus.'
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
                <h2 class="text-headline-sm font-bold">Kelola Kategori</h2>
                <p class="text-body-sm text-on-surface-variant">Tambah atau hapus kategori kursus.</p>
            </div>
        </div>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
        <p v-if="notice" class="mt-4 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">{{ notice }}</p>

        <form class="card mt-6 flex flex-wrap items-end gap-3" @submit.prevent="create">
            <div class="flex-1 min-w-[200px]">
                <label class="label">Nama</label>
                <input v-model="form.name" type="text" class="mt-1 w-full rounded-xl border border-outline bg-surface px-3 py-2" placeholder="Nama kategori" />
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="label">Slug (opsional)</label>
                <input v-model="form.slug" type="text" class="mt-1 w-full rounded-xl border border-outline bg-surface px-3 py-2" placeholder="slug-kategori" />
            </div>
            <button type="submit" class="btn-primary h-11 px-5" :disabled="saving">
                {{ saving ? 'Menyimpan...' : 'Tambah' }}
            </button>
        </form>

        <div class="card-flush mt-6 overflow-x-auto">
            <table class="w-full min-w-[500px] text-left">
                <thead>
                    <tr class="border-b border-outline-variant/60 text-label-md uppercase tracking-wide text-on-surface-variant">
                        <th class="px-5 py-4 font-semibold">Nama</th>
                        <th class="px-5 py-4 font-semibold">Slug</th>
                        <th class="px-5 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    <tr v-if="loading">
                        <td colspan="3" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Memuat...</td>
                    </tr>
                    <tr v-else-if="categories.length === 0">
                        <td colspan="3" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Belum ada kategori.</td>
                    </tr>
                    <tr v-for="c in categories" :key="c.id" class="hover:bg-surface-container-low/60">
                        <td class="px-5 py-4 text-body-md font-semibold text-on-surface">{{ c.name }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ c.slug }}</td>
                        <td class="px-5 py-4 text-right">
                            <button type="button" class="h-9 rounded-lg px-3 text-label-md font-semibold text-error hover:bg-error/10" @click="askDelete(c)">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmDialog
            v-model="deleteOpen"
            title="Hapus Kategori"
            variant="danger"
            icon="trash"
            confirm-label="Hapus"
            :loading="deleting"
            @confirm="confirmDelete"
        >
            <p>Yakin hapus kategori <strong>{{ deleteTarget?.name }}</strong>?</p>
            <p v-if="deleteError" class="mt-3 text-body-sm text-error" role="alert">{{ deleteError }}</p>
        </ConfirmDialog>
    </AdminLayout>
</template>