<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Badge from '@/Components/Badge.vue'
import api from '@/lib/api'

interface Lesson { id: string; title: string; duration: string; free: boolean }
interface Module { title: string; lessons: Lesson[] }
interface Course {
    slug: string
    title: string
    summary: string
    description: string
    categoryName: string
    level: string
    price: number
    rating: number
    reviews: number
    students: number
    durationHours: number
    lessonsCount: number
    instructor: { name: string; role: string }
    learn: string[]
    includes: { label: string; icon: string }[]
    curriculum: Module[]
    free: boolean
}

const route = useRoute()
const course = ref<Course | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const notFound = ref(false)
const activeTab = ref<'curriculum' | 'about'>('curriculum')
const openModules = ref<number[]>([0])

const firstLesson = computed(() => course.value?.curriculum[0]?.lessons[0]?.id ?? null)

const ctaTarget = computed(() =>
    firstLesson.value ? `/belajar/${course.value?.slug}/${firstLesson.value}` : '/langganan',
)

function formatPrice(price: number, free: boolean): string {
    if (free || price <= 0) return 'Gratis'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function toggleModule(index: number): void {
    openModules.value = isModuleOpen(index)
        ? openModules.value.filter((i) => i !== index)
        : [...openModules.value, index]
}

function isModuleOpen(index: number): boolean {
    return openModules.value.includes(index)
}

async function loadCourse(): Promise<void> {
    loading.value = true
    error.value = null
    notFound.value = false
    try {
        const { data } = await api.get<{ course: Course }>(`/courses/${route.params.slug}`)
        course.value = data.course
    } catch (err) {
        const status = (err as { response?: { status?: number } }).response?.status
        if (status === 404) notFound.value = true
        else error.value = 'Gagal memuat detail kursus.'
    } finally {
        loading.value = false
    }
}

onMounted(loadCourse)
watch(() => route.params.slug, loadCourse)
</script>

<template>
    <GuestLayout>
        <div v-if="loading" class="mx-auto max-w-7xl px-4 py-12">
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    <div class="aspect-video w-full animate-pulse rounded-2xl bg-surface-container-high" />
                    <div class="h-9 w-3/4 animate-pulse rounded bg-surface-container-high" />
                </div>
                <div class="h-80 animate-pulse rounded-2xl bg-surface-container-high" />
            </div>
        </div>

        <div v-else-if="notFound" class="mx-auto flex max-w-md flex-col items-center px-4 py-20 text-center">
            <span class="text-display-sm font-bold text-primary">404</span>
            <h1 class="mt-4 text-headline-md font-bold">Kursus tidak ditemukan</h1>
            <p class="mt-2 text-body-md text-on-surface-variant">Kursus yang Anda cari tidak tersedia atau telah dipindahkan.</p>
            <RouterLink to="/kursus" class="btn-primary mt-8">Kembali ke Katalog</RouterLink>
        </div>

        <div v-else-if="error" class="mx-auto max-w-7xl px-4 py-12">
            <p class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>
        </div>

        <template v-else-if="course">
            <section class="border-b border-outline-variant/50 bg-surface-container-low">
                <div class="mx-auto max-w-7xl px-4 py-10 md:py-14">
                    <nav class="flex items-center gap-2 text-body-sm text-on-surface-variant" aria-label="Breadcrumb">
                        <RouterLink to="/kursus" class="transition-colors hover:text-primary">Katalog</RouterLink>
                        <span aria-hidden="true">/</span>
                        <span>{{ course.categoryName }}</span>
                    </nav>

                    <div class="mt-4 grid gap-10 lg:grid-cols-2">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <Badge variant="primary">{{ course.categoryName }}</Badge>
                                <Badge variant="outline">{{ course.level }}</Badge>
                            </div>
                            <h1 class="mt-4 text-display-sm font-bold leading-tight tracking-tight">{{ course.title }}</h1>
                            <p class="mt-4 max-w-xl text-body-lg text-on-surface-variant">{{ course.summary }}</p>

                            <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-body-md text-on-surface-variant">
                                <span class="flex items-center gap-1.5 font-semibold text-on-surface">
                                    <svg class="h-5 w-5 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12 2l2.4 7.4H22l-6 4.4 2.3 7.2L12 16.6 5.7 21l2.3-7.2-6-4.4h7.6z" />
                                    </svg>
                                    {{ course.rating }}
                                    <span class="font-normal">({{ course.reviews }} ulasan)</span>
                                </span>
                                <span>{{ course.students.toLocaleString('id-ID') }} peserta</span>
                                <span>{{ course.durationHours }} jam total</span>
                                <span>{{ course.lessonsCount }} pelajaran</span>
                            </div>

                            <div class="mt-6 flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 text-label-lg font-bold text-primary">
                                    {{ course.instructor.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-body-md font-semibold">{{ course.instructor.name }}</p>
                                    <p class="text-body-sm text-on-surface-variant">{{ course.instructor.role }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary-container shadow-modal">
                            <div class="flex aspect-video items-center justify-center">
                                <button type="button" class="flex h-16 w-16 items-center justify-center rounded-full bg-white/90 text-primary shadow-lg transition-transform hover:scale-105" aria-label="Putar video pengenalan">
                                    <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z" /></svg>
                                </button>
                            </div>
                            <p class="absolute bottom-4 left-4 text-label-md font-medium text-white/90">Tonton video pengenalan (2 menit)</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 py-10">
                <div class="grid gap-10 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="card md:p-8">
                            <h2 class="text-title-lg font-semibold">Apa yang akan kamu pelajari?</h2>
                            <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                                <li v-for="item in course.learn" :key="item" class="flex items-start gap-3 text-body-md text-on-surface-variant">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-tertiary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6L9 17l-5-5" /></svg>
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <div class="flex gap-2 border-b border-outline-variant" role="tablist">
                                <button
                                    type="button" role="tab" :aria-selected="activeTab === 'curriculum'"
                                    class="-mb-px border-b-2 px-4 py-3 text-label-lg font-semibold transition-colors"
                                    :class="activeTab === 'curriculum' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                                    @click="activeTab = 'curriculum'"
                                >
                                    Silabus &amp; Kurikulum
                                </button>
                                <button
                                    type="button" role="tab" :aria-selected="activeTab === 'about'"
                                    class="-mb-px border-b-2 px-4 py-3 text-label-lg font-semibold transition-colors"
                                    :class="activeTab === 'about' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                                    @click="activeTab = 'about'"
                                >
                                    Tentang Kursus
                                </button>
                            </div>

                            <div v-if="activeTab === 'curriculum'" class="mt-6 space-y-3">
                                <div v-for="(module, index) in course.curriculum" :key="module.title" class="card-flush">
                                    <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" :aria-expanded="isModuleOpen(index)" @click="toggleModule(index)">
                                        <span class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-label-md font-bold text-primary">{{ index + 1 }}</span>
                                            <span class="text-title-md font-semibold">{{ module.title }}</span>
                                        </span>
                                        <svg class="h-5 w-5 shrink-0 text-on-surface-variant transition-transform" :class="isModuleOpen(index) ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
                                    </button>

                                    <ul v-show="isModuleOpen(index)" class="divide-y divide-outline-variant border-t border-outline-variant">
                                        <li v-for="lesson in module.lessons" :key="lesson.id" class="flex items-center gap-3 px-5 py-3">
                                            <svg class="h-5 w-5 shrink-0 text-on-surface-variant" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9" /><path d="M10 9l5 3-5 3z" />
                                            </svg>
                                            <span class="flex-1 text-body-md text-on-surface-variant">{{ lesson.title }}</span>
                                            <span v-if="lesson.free" class="badge-primary">Pratinjau</span>
                                            <span class="text-body-sm text-on-surface-variant">{{ lesson.duration }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div v-else class="card mt-6 leading-relaxed text-on-surface-variant">
                                {{ course.description }}
                            </div>
                        </div>
                    </div>

                    <aside class="lg:col-span-1">
                        <div class="card sticky top-24">
                            <div class="flex items-end gap-2">
                                <span class="text-display-sm font-bold tracking-tight">{{ formatPrice(course.price, course.free) }}</span>
                                <span v-if="!course.free" class="pb-1 text-body-sm text-on-surface-variant">sekali bayar</span>
                            </div>
                            <p class="mt-1 text-body-sm text-on-surface-variant">Akses selamanya setelah pembelian.</p>

                            <RouterLink :to="ctaTarget" class="btn-primary mt-6 w-full">Daftar Sekarang</RouterLink>
                            <RouterLink to="/langganan" class="btn-secondary mt-3 w-full">Lihat Paket Langganan</RouterLink>

                            <div class="mt-6 border-t border-outline-variant pt-6">
                                <p class="text-title-md font-semibold">Termasuk dalam kursus ini</p>
                                <ul class="mt-4 space-y-3">
                                    <li v-for="item in course.includes" :key="item.label" class="flex items-center gap-3 text-body-md text-on-surface-variant">
                                        <svg class="h-5 w-5 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M20 6L9 17l-5-5" />
                                        </svg>
                                        <span>{{ item.label }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </template>
    </GuestLayout>
</template>
