<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import Button from '@/Components/Button.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Input from '@/Components/Input.vue'
import Modal from '@/Components/Modal.vue'
import api from '@/lib/api'

interface Category { slug: string; name: string }
interface Lesson {
    id: number
    slug: string
    title: string
    module: string | null
    duration: string | null
    order: number
    isFree: boolean
}

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => route.name === 'admin-courses-edit')
const courseId = computed(() => route.params.id as string | undefined)

const categories = ref<Category[]>([])
const levels = ref<string[]>([])
const lessons = ref<Lesson[]>([])
const loading = ref(true)
const saving = ref(false)
const error = ref<string | null>(null)
const notice = ref<string | null>(null)
const errors = ref<Record<string, string>>({})

const form = reactive({
    title: '',
    slug: '',
    summary: '',
    description: '',
    category: 'pemrograman',
    category_name: 'Pemrograman',
    level: 'Pemula',
    price: 0,
    rating: 0,
    review_count: 0,
    students: 0,
    duration_hours: 0,
    instructor_name: '',
    instructor_role: '',
    accent: 'primary',
    thumbnail: '',
    popular: false,
    is_free: false,
    published: false,
    learnText: '',
    includesText: '',
})

const lessonModalOpen = ref(false)
const editingLesson = ref<Lesson | null>(null)
const lessonSaving = ref(false)
const lessonForm = reactive({ title: '', module: '', duration: '', order: 0, is_free: false })

const lessonDeleteOpen = ref(false)
const lessonDeleting = ref(false)
const lessonDeleteTarget = ref<Lesson | null>(null)

function firstError(key: string): string | undefined {
    return errors.value[key]
}

function applyCategory(): void {
    const found = categories.value.find((item) => item.slug === form.category)
    if (found) form.category_name = found.name
}

function loadMeta(): void {
    api.get('/courses').then(({ data }) => {
        categories.value = data.categories
        levels.value = data.levels
    })
}

async function loadCourse(): Promise<void> {
    if (!isEdit.value || !courseId.value) {
        loading.value = false
        return
    }
    try {
        const { data } = await api.get(`/admin/courses/${courseId.value}`)
        const c = data.course
        Object.assign(form, {
            title: c.title ?? '',
            slug: c.slug ?? '',
            summary: c.summary ?? '',
            description: c.description ?? '',
            category: c.category ?? 'pemrograman',
            category_name: c.categoryName ?? '',
            level: c.level ?? 'Pemula',
            price: c.price ?? 0,
            rating: c.rating ?? 0,
            review_count: c.reviewCount ?? 0,
            students: c.students ?? 0,
            duration_hours: c.durationHours ?? 0,
            instructor_name: c.instructorName ?? '',
            instructor_role: c.instructorRole ?? '',
            accent: c.accent ?? 'primary',
            thumbnail: c.thumbnail ?? '',
            popular: c.popular ?? false,
            is_free: c.isFree ?? false,
            published: c.published ?? false,
            learnText: (c.learn ?? []).join('\n'),
            includesText: (c.includes ?? []).join('\n'),
        })
        lessons.value = c.lessons ?? []
    } catch {
        error.value = 'Gagal memuat data kursus.'
    } finally {
        loading.value = false
    }
}

function toLines(value: string): string[] {
    return value
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => line !== '')
}

async function submit(): Promise<void> {
    saving.value = true
    error.value = null
    notice.value = null
    errors.value = {}

    const payload = {
        title: form.title,
        slug: form.slug || undefined,
        summary: form.summary || null,
        description: form.description || null,
        category: form.category,
        category_name: form.category_name,
        level: form.level,
        price: form.price,
        rating: form.rating,
        review_count: form.review_count,
        students: form.students,
        duration_hours: form.duration_hours,
        instructor_name: form.instructor_name || null,
        instructor_role: form.instructor_role || null,
        accent: form.accent,
        thumbnail: form.thumbnail || null,
        popular: form.popular,
        is_free: form.is_free,
        published: form.published,
        learn: toLines(form.learnText),
        includes: toLines(form.includesText),
    }

    try {
        if (isEdit.value && courseId.value) {
            const { data } = await api.put(`/admin/courses/${courseId.value}`, payload)
            notice.value = data.message
        } else {
            const { data } = await api.post('/admin/courses', payload)
            notice.value = data.message
            await router.replace({ name: 'admin-courses-edit', params: { id: data.course.id } })
        }
    } catch (err) {
        const response = (err as { response?: { status?: number; data?: { errors?: Record<string, string[]>; message?: string } } }).response
        if (response?.status === 422 && response.data?.errors) {
            errors.value = Object.fromEntries(Object.entries(response.data.errors).map(([key, value]) => [key, value[0]]))
            error.value = 'Periksa kembali data yang dimasukkan.'
        } else {
            error.value = response?.data?.message ?? 'Gagal menyimpan kursus.'
        }
    } finally {
        saving.value = false
    }
}

function openLessonModal(lesson?: Lesson): void {
    editingLesson.value = lesson ?? null
    lessonForm.title = lesson?.title ?? ''
    lessonForm.module = lesson?.module ?? ''
    lessonForm.duration = lesson?.duration ?? ''
    lessonForm.order = lesson?.order ?? lessons.value.length + 1
    lessonForm.is_free = lesson?.isFree ?? false
    lessonModalOpen.value = true
}

async function saveLesson(): Promise<void> {
    if (!courseId.value) return
    lessonSaving.value = true
    try {
        if (editingLesson.value) {
            const { data } = await api.put(`/admin/courses/${courseId.value}/lessons/${editingLesson.value.id}`, lessonForm)
            const index = lessons.value.findIndex((item) => item.id === editingLesson.value?.id)
            if (index !== -1) lessons.value[index] = data.lesson
        } else {
            const { data } = await api.post(`/admin/courses/${courseId.value}/lessons`, lessonForm)
            lessons.value.push(data.lesson)
        }
        lessons.value = [...lessons.value].sort((a, b) => a.order - b.order)
        lessonModalOpen.value = false
    } catch {
        error.value = 'Gagal menyimpan pelajaran.'
    } finally {
        lessonSaving.value = false
    }
}

function askDeleteLesson(lesson: Lesson): void {
    lessonDeleteTarget.value = lesson
    lessonDeleteOpen.value = true
}

async function confirmDeleteLesson(): Promise<void> {
    if (!courseId.value || !lessonDeleteTarget.value) return
    lessonDeleting.value = true
    try {
        await api.delete(`/admin/courses/${courseId.value}/lessons/${lessonDeleteTarget.value.id}`)
        lessons.value = lessons.value.filter((item) => item.id !== lessonDeleteTarget.value?.id)
        lessonDeleteOpen.value = false
    } catch {
        error.value = 'Gagal menghapus pelajaran.'
        lessonDeleteOpen.value = false
    } finally {
        lessonDeleting.value = false
    }
}

onMounted(() => {
    loadMeta()
    loadCourse()
})
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-headline-sm font-bold">{{ isEdit ? 'Edit Kursus' : 'Tambah Kursus Baru' }}</h2>
                <p class="text-body-sm text-on-surface-variant">Lengkapi informasi kursus dan kelola materinya.</p>
            </div>
            <RouterLink to="/admin/kursus" class="btn-secondary h-11 px-5 text-label-md">Kembali</RouterLink>
        </div>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
        <p v-if="notice" class="mt-4 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">{{ notice }}</p>

        <div v-if="loading" class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="h-96 animate-pulse rounded-2xl bg-surface-container-high lg:col-span-2" />
            <div class="h-96 animate-pulse rounded-2xl bg-surface-container-high" />
        </div>

        <form v-else class="mt-6 grid gap-6 lg:grid-cols-3" @submit.prevent="submit">
            <div class="space-y-6 lg:col-span-2">
                <div class="card space-y-4">
                    <h3 class="text-title-lg font-semibold">Informasi Utama</h3>
                    <Input v-model="form.title" label="Judul Kursus" required :error="firstError('title')" placeholder="Contoh: Laravel 11 untuk Pemula" />
                    <Input v-model="form.slug" label="Slug (opsional)" :error="firstError('slug')" placeholder="laravel-11-untuk-pemula" />
                    <div>
                        <label class="label" for="course-summary">Ringkasan</label>
                        <textarea id="course-summary" v-model="form.summary" rows="2" class="input h-auto py-3" placeholder="Ringkasan singkat kursus" />
                    </div>
                    <div>
                        <label class="label" for="course-description">Deskripsi</label>
                        <textarea id="course-description" v-model="form.description" rows="5" class="input h-auto py-3" placeholder="Deskripsi lengkap kursus" />
                    </div>
                </div>

                <div class="card grid gap-4 sm:grid-cols-2">
                    <h3 class="text-title-lg font-semibold sm:col-span-2">Detail Kursus</h3>
                    <div>
                        <label class="label" for="course-category">Kategori</label>
                        <select id="course-category" v-model="form.category" class="input" @change="applyCategory">
                            <option v-for="category in categories" :key="category.slug" :value="category.slug">{{ category.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="course-level">Tingkat</label>
                        <select id="course-level" v-model="form.level" class="input">
                            <option v-for="level in levels" :key="level" :value="level">{{ level }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="course-price">Harga (Rp)</label>
                        <input id="course-price" v-model.number="form.price" type="number" min="0" class="input" :class="{ 'input-error': firstError('price') }" />
                        <p v-if="firstError('price')" class="mt-1.5 text-body-sm text-error" role="alert">{{ firstError('price') }}</p>
                    </div>
                    <Input v-model="form.instructor_name" label="Nama Instruktur" :error="firstError('instructor_name')" />
                    <Input v-model="form.instructor_role" label="Peran Instruktur" />
                    <div>
                        <label class="label" for="course-accent">Aksen Warna</label>
                        <select id="course-accent" v-model="form.accent" class="input">
                            <option value="primary">Indigo</option>
                            <option value="secondary">Amber</option>
                            <option value="tertiary">Emerald</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="course-duration">Durasi (jam)</label>
                        <input id="course-duration" v-model.number="form.duration_hours" type="number" min="0" class="input" />
                    </div>
                    <div>
                        <label class="label" for="course-rating">Rating (0-5)</label>
                        <input id="course-rating" v-model.number="form.rating" type="number" min="0" max="5" step="0.1" class="input" />
                    </div>
                    <div>
                        <label class="label" for="course-reviews">Jumlah Ulasan</label>
                        <input id="course-reviews" v-model.number="form.review_count" type="number" min="0" class="input" />
                    </div>
                    <div>
                        <label class="label" for="course-students">Jumlah Peserta</label>
                        <input id="course-students" v-model.number="form.students" type="number" min="0" class="input" />
                    </div>
                </div>

                <div class="card space-y-4">
                    <h3 class="text-title-lg font-semibold">Konten Pembelajaran</h3>
                    <div>
                        <label class="label" for="course-learn">Yang akan dipelajari (satu per baris)</label>
                        <textarea id="course-learn" v-model="form.learnText" rows="5" class="input h-auto py-3" placeholder="Menguasai dasar Laravel" />
                    </div>
                    <div>
                        <label class="label" for="course-includes">Fasilitas kursus (satu per baris)</label>
                        <textarea id="course-includes" v-model="form.includesText" rows="4" class="input h-auto py-3" placeholder="Sertifikat penyelesaian" />
                    </div>
                </div>

                <div class="card">
                    <div class="flex items-center justify-between">
                        <h3 class="text-title-lg font-semibold">Materi &amp; Pelajaran</h3>
                        <Button v-if="isEdit" size="sm" @click="openLessonModal()">Tambah Pelajaran</Button>
                    </div>
                    <p v-if="!isEdit" class="mt-2 text-body-sm text-on-surface-variant">Simpan kursus terlebih dahulu untuk menambahkan pelajaran.</p>

                    <ul v-if="lessons.length" class="mt-4 divide-y divide-outline-variant/60">
                        <li v-for="lesson in lessons" :key="lesson.id" class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-body-md font-semibold text-on-surface">{{ lesson.order }}. {{ lesson.title }}</p>
                                <p class="truncate text-body-sm text-on-surface-variant">{{ lesson.module }} &bull; {{ lesson.duration }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge v-if="lesson.isFree" variant="success" size="sm">Pratinjau</Badge>
                                <button type="button" class="btn-secondary h-9 rounded-lg px-3 text-label-md" @click="openLessonModal(lesson)">Edit</button>
                                <button type="button" class="h-9 rounded-lg px-3 text-label-md font-semibold text-error transition-colors hover:bg-error/10" @click="askDeleteLesson(lesson)">Hapus</button>
                            </div>
                        </li>
                    </ul>
                    <p v-else-if="isEdit" class="mt-4 text-body-sm text-on-surface-variant">Belum ada pelajaran.</p>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="card space-y-4">
                    <h3 class="text-title-lg font-semibold">Publikasi</h3>
                    <label class="flex items-center justify-between gap-3 text-body-md">
                        <span>Terbitkan kursus</span>
                        <input v-model="form.published" type="checkbox" class="h-5 w-5 rounded text-primary" />
                    </label>
                    <label class="flex items-center justify-between gap-3 text-body-md">
                        <span>Tandai populer</span>
                        <input v-model="form.popular" type="checkbox" class="h-5 w-5 rounded text-primary" />
                    </label>
                    <label class="flex items-center justify-between gap-3 text-body-md">
                        <span>Kursus gratis</span>
                        <input v-model="form.is_free" type="checkbox" class="h-5 w-5 rounded text-primary" />
                    </label>
                    <Input v-model="form.thumbnail" label="URL Thumbnail" placeholder="/placeholder.svg" />
                </div>

                <div class="card">
                    <Button type="submit" :loading="saving" class="w-full">
                        {{ isEdit ? 'Simpan Perubahan' : 'Simpan Kursus' }}
                    </Button>
                </div>
            </aside>
        </form>

        <Modal v-model="lessonModalOpen" :title="editingLesson ? 'Edit Pelajaran' : 'Tambah Pelajaran'" size="md">
            <div class="space-y-4">
                <Input v-model="lessonForm.title" label="Judul Pelajaran" required />
                <Input v-model="lessonForm.module" label="Modul" placeholder="Contoh: Dasar Laravel" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <Input v-model="lessonForm.duration" label="Durasi" placeholder="12:40" />
                    <div>
                        <label class="label" for="lesson-order">Urutan</label>
                        <input id="lesson-order" v-model.number="lessonForm.order" type="number" min="1" class="input" />
                    </div>
                </div>
                <label class="flex items-center gap-3 text-body-md">
                    <input v-model="lessonForm.is_free" type="checkbox" class="h-5 w-5 rounded text-primary" />
                    Jadikan pelajaran pratinjau gratis
                </label>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button variant="secondary" size="sm" @click="lessonModalOpen = false">Batal</Button>
                    <Button size="sm" :loading="lessonSaving" @click="saveLesson">Simpan</Button>
                </div>
            </template>
        </Modal>

        <ConfirmDialog
            v-model="lessonDeleteOpen"
            title="Hapus Pelajaran"
            variant="danger"
            icon="trash"
            confirm-label="Hapus"
            :loading="lessonDeleting"
            @confirm="confirmDeleteLesson"
        >
            Yakin ingin menghapus pelajaran <strong class="text-on-surface">{{ lessonDeleteTarget?.title }}</strong>? Tindakan ini tidak dapat dibatalkan.
        </ConfirmDialog>
    </AdminLayout>
</template>
