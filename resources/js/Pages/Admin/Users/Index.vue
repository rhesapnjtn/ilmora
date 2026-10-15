<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import Button from '@/Components/Button.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Input from '@/Components/Input.vue'
import Modal from '@/Components/Modal.vue'
import Pagination from '@/Components/Pagination.vue'
import api from '@/lib/api'
import { useAuth } from '@/stores/auth'

interface AdminUser {
    id: number
    name: string
    email: string
    role: string
    subscriptionsCount: number
    paymentsCount: number
    createdAt: string | null
}
interface Meta { page: number; perPage: number; total: number; lastPage: number }

const { user: currentUser } = useAuth()

const users = ref<AdminUser[]>([])
const meta = ref<Meta>({ page: 1, perPage: 10, total: 0, lastPage: 1 })
const filters = reactive({ search: '', role: '' })
const loading = ref(true)
const error = ref<string | null>(null)
const notice = ref<string | null>(null)

const editOpen = ref(false)
const saving = ref(false)
const target = ref<AdminUser | null>(null)
const editForm = reactive({ name: '', role: 'user' })
const editError = ref<string | null>(null)

const deleteOpen = ref(false)
const deleting = ref(false)
const deleteTarget = ref<AdminUser | null>(null)

function formatDate(value: string | null): string {
    if (!value) return '-'
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value))
}

async function load(page = 1): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get('/admin/users', {
            params: { search: filters.search || undefined, role: filters.role || undefined, page },
        })
        users.value = data.users
        meta.value = data.meta
    } catch {
        error.value = 'Gagal memuat daftar pengguna.'
    } finally {
        loading.value = false
    }
}

function openEdit(user: AdminUser): void {
    target.value = user
    editForm.name = user.name
    editForm.role = user.role
    editError.value = null
    editOpen.value = true
}

async function saveEdit(): Promise<void> {
    if (!target.value) return
    saving.value = true
    editError.value = null
    try {
        const { data } = await api.put(`/admin/users/${target.value.id}`, {
            name: editForm.name,
            role: editForm.role,
        })
        const index = users.value.findIndex((item) => item.id === target.value?.id)
        if (index !== -1) users.value[index] = { ...users.value[index], ...data.user }
        notice.value = data.message
        editOpen.value = false
    } catch (err) {
        const response = (err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response
        editError.value = response?.data?.errors?.role?.[0] ?? response?.data?.message ?? 'Gagal memperbarui pengguna.'
    } finally {
        saving.value = false
    }
}

function askDelete(user: AdminUser): void {
    deleteTarget.value = user
    deleteOpen.value = true
}

async function confirmDelete(): Promise<void> {
    if (!deleteTarget.value) return
    deleting.value = true
    try {
        await api.delete(`/admin/users/${deleteTarget.value.id}`)
        notice.value = 'Pengguna berhasil dihapus.'
        deleteOpen.value = false
        await load(meta.value.page)
    } catch (err) {
        const response = (err as { response?: { data?: { message?: string } } }).response
        error.value = response?.data?.message ?? 'Gagal menghapus pengguna.'
        deleteOpen.value = false
    } finally {
        deleting.value = false
    }
}

watch(() => filters.role, () => load(1))
onMounted(() => load(1))
</script>

<template>
    <AdminLayout>
        <form class="flex flex-wrap items-center gap-2" @submit.prevent="load(1)">
            <input v-model="filters.search" type="search" class="input h-11 flex-1 min-w-52" placeholder="Cari nama atau email..." aria-label="Cari pengguna" />
            <select v-model="filters.role" class="input h-11 w-full sm:w-44" aria-label="Filter peran">
                <option value="">Semua Peran</option>
                <option value="user">Pengguna</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" class="btn-secondary h-11 px-5 text-label-md">Cari</button>
        </form>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
        <p v-if="notice" class="mt-4 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">{{ notice }}</p>

        <div class="card-flush mt-6 overflow-x-auto">
            <table class="w-full min-w-[720px] text-left">
                <thead>
                    <tr class="border-b border-outline-variant/60 text-label-md uppercase tracking-wide text-on-surface-variant">
                        <th class="px-5 py-4 font-semibold">Pengguna</th>
                        <th class="px-5 py-4 font-semibold">Peran</th>
                        <th class="px-5 py-4 font-semibold">Langganan</th>
                        <th class="px-5 py-4 font-semibold">Pembayaran</th>
                        <th class="px-5 py-4 font-semibold">Terdaftar</th>
                        <th class="px-5 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    <tr v-if="loading">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Memuat data...</td>
                    </tr>
                    <tr v-else-if="users.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Belum ada pengguna.</td>
                    </tr>
                    <tr v-for="user in users" :key="user.id" class="transition-colors hover:bg-surface-container-low/60">
                        <td class="px-5 py-4">
                            <p class="text-body-md font-semibold text-on-surface">{{ user.name }}</p>
                            <p class="text-body-sm text-on-surface-variant">{{ user.email }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <Badge :variant="user.role === 'admin' ? 'primary' : 'outline'" size="sm">{{ user.role === 'admin' ? 'Admin' : 'Pengguna' }}</Badge>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ user.subscriptionsCount }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ user.paymentsCount }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ formatDate(user.createdAt) }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" class="btn-secondary h-9 rounded-lg px-3 text-label-md" @click="openEdit(user)">Edit</button>
                                <button
                                    type="button"
                                    class="h-9 rounded-lg px-3 text-label-md font-semibold text-error transition-colors hover:bg-error/10 disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="user.id === currentUser?.id"
                                    @click="askDelete(user)"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />

        <Modal v-model="editOpen" title="Edit Pengguna" size="sm">
            <div class="space-y-4">
                <Input v-model="editForm.name" label="Nama" required />
                <div>
                    <label class="label" for="user-role">Peran</label>
                    <select id="user-role" v-model="editForm.role" class="input">
                        <option value="user">Pengguna</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <p v-if="editError" class="text-body-sm text-error" role="alert">{{ editError }}</p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button variant="secondary" size="sm" @click="editOpen = false">Batal</Button>
                    <Button size="sm" :loading="saving" @click="saveEdit">Simpan</Button>
                </div>
            </template>
        </Modal>

        <ConfirmDialog
            v-model="deleteOpen"
            title="Hapus Pengguna"
            variant="danger"
            icon="trash"
            confirm-label="Hapus"
            :loading="deleting"
            @confirm="confirmDelete"
        >
            Yakin ingin menghapus pengguna <strong class="text-on-surface">{{ deleteTarget?.name }}</strong>? Seluruh token aksesnya akan dicabut dan tindakan ini tidak dapat dibatalkan.
        </ConfirmDialog>
    </AdminLayout>
</template>
