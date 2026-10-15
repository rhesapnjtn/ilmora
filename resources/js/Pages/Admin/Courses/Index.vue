<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import Button from '@/Components/Button.vue'
import Modal from '@/Components/Modal.vue'
import Pagination from '@/Components/Pagination.vue'
import api from '@/lib/api'

interface AdminCourse {
    id: number
    slug: string
    title: string
    categoryName: string
    level: string
    price: number
    students: number
    lessonsCount: number
    published: boolean
    popular: boolean
    isFree: boolean
    updatedAt: string | null
}
interface Meta { page: number; perPage: number; total: number; lastPage: number }

const courses = ref<AdminCourse[]>([])
const meta = ref<Meta>({ page: 1, perPage: 10, total: 0, lastPage: 1 })
const filters = reactive({ search: '', status: '' })
const loading = ref(true)
const error = ref<string | null>(null)
const notice = ref<string | null>(null)
const togglingId = ref<number | null>(null)

const deleteModalOpen = ref(false)
const deleting = ref(false)
const target = ref<AdminCourse | null>(null)

function formatCurrency(value: number, free: boolean): string {
    if (free || value <= 0) return 'Gratis'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}

async function load(page = 1): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get('/admin/courses', {
            params: { search: filters.search || undefined, status: filters.status || undefined, page },
        })
        courses.value = data.courses
        meta.value = data.meta
    } catch {
        error.value = 'Gagal memuat daftar kursus.'
    } finally {
        loading.value = false
    }
}

async function togglePublish(course: AdminCourse): Promise<void> {
    togglingId.value = course.id
    error.value = null
    try {
        const { data } = await api.post(`/admin/courses/${course.id}/publish`)
        course.published = data.course.published
        notice.value = data.message
    } catch {
        error.value = 'Gagal mengubah status publikasi kursus.'
    } finally {
        togglingId.value = null
    }
}

function askDelete(course: AdminCourse): void {
    target.value = course
    deleteModalOpen.value = true
}

async function confirmDelete(): Promise<void> {
    if (!target.value) return
    deleting.value = true
    try {
        await api.delete(`/admin/courses/${target.value.id}`)
        notice.value = 'Kursus berhasil dihapus.'
        deleteModalOpen.value = false
        await load(meta.value.page)
    } catch {
        error.value = 'Gagal menghapus kursus.'
    } finally {
        deleting.value = false
    }
}

watch(() => filters.status, () => load(1))
onMounted(() => load(1))
</script>

<template>
    <AdminLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-1 flex-wrap items-center gap-2" @submit.prevent="load(1)">
                <input v-model="filters.search" type="search" class="input h-11 flex-1 min-w-52" placeholder="Cari kursus..." aria-label="Cari kursus" />
                <select v-model="filters.status" class="input h-11 w-full sm:w-44" aria-label="Filter status">
                    <option value="">Semua Status</option>
                    <option value="published">Dipublikasikan</option>
                    <option value="draft">Draf</option>
                </select>
                <button type="submit" class="btn-secondary h-11 px-5 text-label-md">Cari</button>
            </form>
            <RouterLink to="/admin/kursus/baru" class="btn-primary h-11 px-5 text-label-md">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                Tambah Kursus
            </RouterLink>
        </div>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
        <p v-if="notice" class="mt-4 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">{{ notice }}</p>

        <div class="card-flush mt-6 overflow-x-auto">
            <table class="w-full min-w-[720px] text-left">
                <thead>
                    <tr class="border-b border-outline-variant/60 text-label-md uppercase tracking-wide text-on-surface-variant">
                        <th class="px-5 py-4 font-semibold">Kursus</th>
                        <th class="px-5 py-4 font-semibold">Kategori</th>
                        <th class="px-5 py-4 font-semibold">Harga</th>
                        <th class="px-5 py-4 font-semibold">Materi</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    <tr v-if="loading">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Memuat data...</td>
                    </tr>
                    <tr v-else-if="courses.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-body-sm text-on-surface-variant">Belum ada kursus.</td>
                    </tr>
                    <tr v-for="course in courses" :key="course.id" class="transition-colors hover:bg-surface-container-low/60">
                        <td class="px-5 py-4">
                            <p class="text-body-md font-semibold text-on-surface">{{ course.title }}</p>
                            <p class="text-body-sm text-on-surface-variant">{{ course.level }}</p>
                        </td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ course.categoryName }}</td>
                        <td class="px-5 py-4 text-body-sm font-semibold text-on-surface">{{ formatCurrency(course.price, course.isFree) }}</td>
                        <td class="px-5 py-4 text-body-sm text-on-surface-variant">{{ course.lessonsCount }} pelajaran</td>
                        <td class="px-5 py-4">
                            <Badge :variant="course.published ? 'success' : 'outline'" size="sm">{{ course.published ? 'Terbit' : 'Draf' }}</Badge>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" class="btn-ghost h-9 rounded-lg px-3 text-label-md" :disabled="togglingId === course.id" @click="togglePublish(course)">
                                    {{ course.published ? 'Jadikan Draf' : 'Terbitkan' }}
                                </button>
                                <RouterLink :to="`/admin/kursus/${course.id}/edit`" class="btn-secondary h-9 rounded-lg px-3 text-label-md">Edit</RouterLink>
                                <button type="button" class="h-9 rounded-lg px-3 text-label-md font-semibold text-error transition-colors hover:bg-error/10" @click="askDelete(course)">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />

        <Modal v-model="deleteModalOpen" title="Hapus Kursus" size="sm">
            <p class="text-body-md text-on-surface-variant">
                Yakin ingin menghapus kursus <strong class="text-on-surface">{{ target?.title }}</strong>? Tindakan ini juga menghapus seluruh pelajarannya dan tidak dapat dibatalkan.
            </p>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button variant="secondary" size="sm" @click="deleteModalOpen = false">Batal</Button>
                    <Button variant="primary" size="sm" :loading="deleting" @click="confirmDelete">Hapus</Button>
                </div>
            </template>
        </Modal>
    </AdminLayout>
</template>
