<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import api from '@/lib/api'

interface Course {
    slug: string
    title: string
    summary: string
    category: string
    categoryName: string
    level: string
    price: number
    rating: number
    reviews: number
    students: number
    durationHours: number
    lessonsCount: number
    instructor: { name: string; role: string }
    accent: string
    free: boolean
}

interface Category { slug: string; name: string }
interface Meta { page: number; perPage: number; total: number; lastPage: number }

const courses = ref<Course[]>([])
const categories = ref<Category[]>([])
const levels = ref<string[]>([])
const meta = ref<Meta>({ page: 1, perPage: 6, total: 0, lastPage: 1 })
const loading = ref(true)
const error = ref<string | null>(null)

const filters = reactive({ search: '', category: '', level: '', price: '', rating: 0 })

const hasFilters = computed(() =>
    filters.search !== '' || filters.category !== '' || filters.level !== '' || filters.price !== '' || filters.rating > 0,
)

const accentClasses: Record<string, string> = {
    primary: 'from-primary to-primary-container',
    secondary: 'from-secondary to-[#A855F7]',
    tertiary: 'from-tertiary to-[#059669]',
}

const ratingOptions = [
    { value: 0, label: 'Semua' },
    { value: 4.5, label: '4.5 ke atas' },
    { value: 4.8, label: '4.8 ke atas' },
]

function formatPrice(course: Course): string {
    if (course.free || course.price <= 0) return 'Gratis'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(course.price)
}

async function loadCourses(page = 1): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get('/courses', {
            params: {
                search: filters.search || undefined,
                category: filters.category || undefined,
                level: filters.level || undefined,
                price: filters.price || undefined,
                rating: filters.rating || undefined,
                page,
            },
        })
        courses.value = data.courses
        categories.value = data.categories
        levels.value = data.levels
        meta.value = data.meta
    } catch {
        error.value = 'Gagal memuat katalog kursus.'
    } finally {
        loading.value = false
    }
}

function applyFilters(): void {
    loadCourses(1)
}

function selectCategory(slug: string): void {
    filters.category = filters.category === slug ? '' : slug
    applyFilters()
}

function resetFilters(): void {
    filters.search = ''
    filters.category = ''
    filters.level = ''
    filters.price = ''
    filters.rating = 0
    applyFilters()
}

watch(() => [filters.level, filters.price, filters.rating], applyFilters)
onMounted(() => loadCourses())
</script>

<template>
    <GuestLayout>
        <section class="border-b border-outline-variant/50 bg-surface-container-low">
            <div class="mx-auto max-w-7xl px-4 py-10 md:py-14">
                <span class="badge-primary">Kurikulum Berbasis Industri</span>
                <h1 class="mt-3 text-display-sm font-bold tracking-tight">Jelajahi Semua Kursus</h1>
                <p class="mt-2 max-w-2xl text-body-lg text-on-surface-variant">
                    Temukan kursus terbaik sesuai minat dan tujuan karier, serta akselerasi keahlian masa depanmu.
                </p>

                <form class="mt-6 flex max-w-2xl gap-2" @submit.prevent="applyFilters">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-on-surface-variant" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg>
                        </span>
                        <input v-model="filters.search" type="search" class="input pl-11" placeholder="Cari kursus, pemrograman, desain, atau instruktur..." aria-label="Cari kursus" />
                    </div>
                    <button type="submit" class="btn-primary h-13 shrink-0 px-5 text-label-lg">Cari</button>
                </form>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-10">
            <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Filter kategori">
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-label-md font-medium transition-colors"
                    :class="filters.category === '' ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
                    @click="selectCategory('')"
                >
                    Semua
                </button>
                <button
                    v-for="category in categories"
                    :key="category.slug"
                    type="button"
                    class="rounded-full px-4 py-2 text-label-md font-medium transition-colors"
                    :class="filters.category === category.slug ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
                    @click="selectCategory(category.slug)"
                >
                    {{ category.name }}
                </button>
            </div>

            <div class="mt-6 grid gap-8 lg:grid-cols-[16rem_1fr]">
                <aside class="space-y-6">
                    <div class="card">
                        <h2 class="text-title-md font-semibold">Tingkat Kesulitan</h2>
                        <div class="mt-3 space-y-2">
                            <label class="flex items-center gap-3 text-body-md text-on-surface-variant">
                                <input v-model="filters.level" type="radio" value="" class="h-4 w-4 text-primary" /> Semua Tingkat
                            </label>
                            <label v-for="level in levels" :key="level" class="flex items-center gap-3 text-body-md text-on-surface-variant">
                                <input v-model="filters.level" type="radio" :value="level" class="h-4 w-4 text-primary" /> {{ level }}
                            </label>
                        </div>
                    </div>

                    <div class="card">
                        <h2 class="text-title-md font-semibold">Harga</h2>
                        <div class="mt-3 space-y-2">
                            <label class="flex items-center gap-3 text-body-md text-on-surface-variant">
                                <input v-model="filters.price" type="radio" value="" class="h-4 w-4 text-primary" /> Semua
                            </label>
                            <label class="flex items-center gap-3 text-body-md text-on-surface-variant">
                                <input v-model="filters.price" type="radio" value="free" class="h-4 w-4 text-primary" /> Gratis (Free Access)
                            </label>
                            <label class="flex items-center gap-3 text-body-md text-on-surface-variant">
                                <input v-model="filters.price" type="radio" value="premium" class="h-4 w-4 text-primary" /> Berbayar (Premium)
                            </label>
                        </div>
                    </div>

                    <div class="card">
                        <h2 class="text-title-md font-semibold">Rating</h2>
                        <div class="mt-3 space-y-2">
                            <label v-for="option in ratingOptions" :key="option.value" class="flex items-center gap-3 text-body-md text-on-surface-variant">
                                <input v-model.number="filters.rating" type="radio" :value="option.value" class="h-4 w-4 text-primary" /> {{ option.label }}
                            </label>
                        </div>
                    </div>

                    <button v-if="hasFilters" type="button" class="btn-secondary w-full" @click="resetFilters">Reset Filter</button>
                </aside>

                <div>
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-primary to-primary-container p-5 text-white">
                        <div>
                            <p class="text-title-md font-semibold">Hemat hingga 60% dengan Paket Langganan</p>
                            <p class="text-body-sm text-white/80">Nikmati ratusan kursus premium, bimbingan, dan portofolio siap kerja.</p>
                        </div>
                        <RouterLink to="/langganan" class="inline-flex h-10 items-center rounded-xl bg-white px-4 text-label-md font-semibold text-primary transition-colors hover:bg-white/90">
                            Lihat Paket
                        </RouterLink>
                    </div>

                    <p v-if="error" class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

                    <p v-if="!loading" class="mt-6 text-body-sm text-on-surface-variant">
                        Menampilkan {{ courses.length }} dari {{ meta.total }} kursus
                    </p>

                    <div v-if="loading" class="mt-4 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <div v-for="n in 3" :key="n" class="card-flush">
                            <div class="aspect-video w-full animate-pulse bg-surface-container-high" />
                            <div class="space-y-3 p-5">
                                <div class="h-5 w-24 animate-pulse rounded bg-surface-container-high" />
                                <div class="h-6 w-3/4 animate-pulse rounded bg-surface-container-high" />
                            </div>
                        </div>
                    </div>

                    <div v-else-if="courses.length === 0" class="mt-4">
                        <div class="card flex flex-col items-center justify-center py-16 text-center">
                            <h3 class="text-title-lg font-semibold">{{ hasFilters ? 'Kursus tidak ditemukan' : 'Belum ada kursus' }}</h3>
                            <p class="mt-2 max-w-md text-body-md text-on-surface-variant">
                                {{ hasFilters ? 'Coba ubah kata kunci atau filter untuk menemukan kursus lain.' : 'Katalog kursus akan segera tersedia.' }}
                            </p>
                            <button v-if="hasFilters" type="button" class="btn-secondary mt-6" @click="resetFilters">Reset Filter</button>
                        </div>
                    </div>

                    <div v-else class="mt-4 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <article v-for="course in courses" :key="course.slug" class="card-flush card-hover flex flex-col">
                            <div class="relative flex aspect-video items-center justify-center overflow-hidden bg-gradient-to-br" :class="accentClasses[course.accent] || accentClasses.primary">
                                <svg class="absolute -bottom-6 -right-4 h-32 w-32 text-white/10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                </svg>
                                <span class="relative text-label-lg font-semibold uppercase tracking-widest text-white/90">{{ course.categoryName }}</span>
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge-primary">{{ course.categoryName }}</span>
                                    <span class="badge-outline">{{ course.level }}</span>
                                </div>
                                <h3 class="mt-3 line-clamp-2 text-title-md font-semibold">{{ course.title }}</h3>
                                <p class="mt-1 text-body-sm text-on-surface-variant">{{ course.instructor.name }}</p>

                                <div class="mt-3 flex items-center gap-2 text-body-sm text-on-surface-variant">
                                    <svg class="h-4 w-4 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.4 7.4H22l-6 4.4 2.3 7.2L12 16.6 5.7 21l2.3-7.2-6-4.4h7.6z" /></svg>
                                    <span class="font-semibold text-on-surface">{{ course.rating }}</span>
                                    <span>({{ course.reviews }})</span>
                                </div>

                                <div class="mt-auto flex items-center justify-between pt-5">
                                    <span class="text-title-lg font-bold">{{ formatPrice(course) }}</span>
                                    <RouterLink :to="`/kursus/${course.slug}`" class="btn-primary h-10 px-4 text-label-md">Daftar Sekarang</RouterLink>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-if="!loading && meta.lastPage > 1" class="mt-8 flex items-center justify-center gap-2">
                        <button type="button" class="btn-secondary h-10 px-4 text-label-md" :disabled="meta.page <= 1" @click="loadCourses(meta.page - 1)">Sebelumnya</button>
                        <button
                            v-for="page in meta.lastPage"
                            :key="page"
                            type="button"
                            class="h-10 w-10 rounded-xl text-label-md font-semibold transition-colors"
                            :class="page === meta.page ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
                            @click="loadCourses(page)"
                        >
                            {{ page }}
                        </button>
                        <button type="button" class="btn-secondary h-10 px-4 text-label-md" :disabled="meta.page >= meta.lastPage" @click="loadCourses(meta.page + 1)">Berikutnya</button>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
