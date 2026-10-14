<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import CourseCover from '@/Components/CourseCover.vue'
import api from '@/lib/api'

interface PopularCourse {
    slug: string
    title: string
    summary: string
    categoryName: string
    level: string
    price: number
    rating: number
    reviews: number
    durationHours: number
    instructor: { name: string; role: string }
    accent: string
    free: boolean
}

const router = useRouter()

const popularCourses = ref<PopularCourse[]>([])
const heroQuery = ref('')

const popularKeywords = ['Laravel', 'Figma UI/UX', 'Python AI', 'Digital Ads']

const valueProps = [
    {
        title: 'Kursus Berkualitas',
        description: 'Materi dikurasi ketat dan disusun langsung oleh praktisi senior di perusahaan teknologi skala global.',
        icon: 'verified',
        iconClass: 'bg-primary-50 text-primary',
        linkClass: 'text-primary',
        link: 'Studi Kasus Riil',
    },
    {
        title: 'Akses Unlimited',
        description: 'Belajar mandiri dengan ritme sendiri tanpa batas kedaluwarsa materi, video, serta aset latihan.',
        icon: 'infinity',
        iconClass: 'bg-surface-container-high text-primary-500',
        linkClass: 'text-primary',
        link: 'Lifetime Update',
    },
    {
        title: 'Sertifikat Resmi',
        description: 'Bukti kompetensi terverifikasi dengan kode unik yang siap diintegrasikan langsung ke LinkedIn.',
        icon: 'award',
        iconClass: 'bg-secondary-50 text-secondary',
        linkClass: 'text-secondary',
        link: 'Verifikasi Online',
    },
    {
        title: 'Dukungan Mentor',
        description: 'Ruang diskusi interaktif dan review proyek personal agar kendala kode atau portofolio terselesaikan.',
        icon: 'mentor',
        iconClass: 'bg-tertiary-50 text-tertiary',
        linkClass: 'text-tertiary',
        link: 'Sesi 1-on-1 Q&A',
    },
]

function badgeLabel(course: PopularCourse, index: number): string {
    return index === 0 ? 'Terpopuler' : course.categoryName
}

const badgeClasses: Record<string, string> = {
    primary: 'bg-secondary-50 text-secondary-700',
    secondary: 'bg-primary-50 text-primary',
    tertiary: 'bg-tertiary-50 text-tertiary-700',
}

const valueProps = [
    {
        title: 'Kursus Berkualitas',
        description: 'Materi dikurasi ketat dan disusun langsung oleh praktisi senior di perusahaan teknologi skala global.',
        icon: 'verified',
        iconClass: 'bg-primary-50 text-primary',
        linkClass: 'text-primary',
        link: 'Studi Kasus Riil',
    },
    {
        title: 'Akses Unlimited',
        description: 'Belajar mandiri dengan ritme sendiri tanpa batas kedaluwarsa materi, video, serta aset latihan.',
        icon: 'infinity',
        iconClass: 'bg-surface-container-high text-primary-500',
        linkClass: 'text-primary',
        link: 'Lifetime Update',
    },
    {
        title: 'Sertifikat Resmi',
        description: 'Bukti kompetensi terverifikasi dengan kode unik yang siap diintegrasikan langsung ke LinkedIn.',
        icon: 'award',
        iconClass: 'bg-secondary-50 text-secondary',
        linkClass: 'text-secondary',
        link: 'Verifikasi Online',
    },
    {
        title: 'Dukungan Mentor',
        description: 'Ruang diskusi interaktif dan review proyek personal agar kendala kode atau portofolio terselesaikan.',
        icon: 'mentor',
        iconClass: 'bg-tertiary-50 text-tertiary',
        linkClass: 'text-tertiary',
        link: 'Sesi 1-on-1 Q&A',
    },
]

function badgeLabel(course: PopularCourse, index: number): string {
    return index === 0 ? 'Terpopuler' : course.categoryName
}

function formatPrice(course: PopularCourse): string {
    if (course.free || course.price <= 0) return 'Gratis'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(course.price)
}

function initials(name: string): string {
    return name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join('')
        .toUpperCase()
}

function submitHeroSearch(): void {
    const query = heroQuery.value.trim()
    router.push({ name: 'courses', query: query ? { search: query } : {} })
}

function searchFor(keyword: string): void {
    router.push({ name: 'courses', query: { search: keyword } })
}

async function loadPopular(): Promise<void> {
    try {
        const { data } = await api.get<{ courses: PopularCourse[] }>('/courses')
        popularCourses.value = data.courses.slice(0, 3)
    } catch {
        popularCourses.value = []
    }
}

onMounted(loadPopular)
</script>

<template>
    <GuestLayout>
        <div class="relative w-full overflow-hidden">
            <div class="pointer-events-none absolute -top-32 left-1/2 h-[360px] w-[900px] -translate-x-1/2 rounded-full bg-gradient-to-b from-primary-50/60 via-surface-container/60 to-transparent blur-3xl" />
            <div class="pointer-events-none absolute -right-24 top-20 h-96 w-96 rounded-full bg-secondary-50/50 blur-3xl" />

            <section class="mx-auto max-w-7xl px-4 pb-16 pt-10 lg:px-8 lg:pb-24 lg:pt-14">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-8">
                    <div class="z-10 flex flex-col items-start lg:col-span-7">
                        <div class="mb-6 inline-flex flex-wrap items-center gap-2 rounded-full bg-surface-container-high px-4 py-1.5 text-label-md text-primary shadow-sm">
                            <span class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-secondary-300" />
                            <span>Learn &bull; Grow &bull; Succeed</span>
                            <span class="text-outline">/</span>
                            <span class="font-medium text-on-surface-variant">Platform Edukasi No. 1 Indonesia</span>
                        </div>

                        <h1 class="max-w-2xl text-[40px] font-extrabold leading-[1.12] tracking-tight text-on-surface sm:text-[52px] lg:text-[56px]">
                            Tingkatkan Skillmu untuk Masa Depan yang
                            <span class="relative inline-block text-primary">
                                Lebih Baik
                                <svg class="absolute -bottom-2 left-0 h-3 w-full text-secondary-300" viewBox="0 0 240 12" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M2 9.5C65 2.5 175 2 238 9.5" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" />
                                </svg>
                            </span>
                        </h1>

                        <p class="mt-6 max-w-xl text-body-lg leading-relaxed text-on-surface-variant">
                            Ilmora menyediakan kursus online berkualitas dengan kurikulum industri terkini, akses fleksibel seumur hidup, dan biaya yang sangat terjangkau bagi talenta muda.
                        </p>

                        <form
                            class="mt-8 flex w-full max-w-xl items-center gap-3 rounded-2xl bg-surface-container-lowest p-2 shadow-xl shadow-surface-dim/40 transition-shadow focus-within:shadow-2xl"
                            @submit.prevent="submitHeroSearch"
                        >
                            <div class="ml-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-container-low text-primary">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7" />
                                    <path d="M21 21l-4.3-4.3" />
                                </svg>
                            </div>
                            <input
                                v-model="heroQuery"
                                type="search"
                                class="w-full min-w-0 bg-transparent text-body-md text-on-surface placeholder:text-outline focus:outline-none"
                                placeholder="Cari kursus, skill, atau topik impianmu..."
                                aria-label="Cari kursus"
                            />
                            <button
                                type="submit"
                                class="flex h-12 shrink-0 items-center gap-2 rounded-xl bg-primary px-6 text-label-lg font-bold text-white shadow-md shadow-primary/25 transition-transform hover:bg-primary-500 active:scale-95"
                            >
                                <span>Cari</span>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </button>
                        </form>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="text-label-sm font-bold uppercase tracking-wider text-outline">Populer:</span>
                            <button
                                v-for="keyword in popularKeywords"
                                :key="keyword"
                                type="button"
                                class="rounded-lg bg-surface-container px-2.5 py-1 text-label-sm text-on-surface transition-colors hover:bg-primary-50 hover:text-primary"
                                @click="searchFor(keyword)"
                            >
                                {{ keyword }}
                            </button>
                        </div>

                        <div class="mt-10 flex items-center gap-8 pt-8 sm:gap-10">
                            <div>
                                <p class="text-headline-lg font-bold text-on-surface">120K+</p>
                                <p class="text-body-sm text-on-surface-variant">Siswa Terdaftar</p>
                            </div>
                            <div class="h-10 w-px bg-surface-container-high" />
                            <div>
                                <p class="text-headline-lg font-bold text-on-surface">350+</p>
                                <p class="text-body-sm text-on-surface-variant">Kursus Praktik</p>
                            </div>
                            <div class="h-10 w-px bg-surface-container-high" />
                            <div class="flex items-center gap-2">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary-50 text-secondary">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12 2l2.9 6.3 6.9.6-5.2 4.5 1.6 6.7L12 16.9 5.8 20.1l1.6-6.7L2.2 8.9l6.9-.6z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-headline-md font-bold text-on-surface">4.9 / 5.0</p>
                                    <p class="text-body-sm text-on-surface-variant">Ulasan Mentor</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-center lg:col-span-5">
                        <div class="relative flex aspect-[4/5] w-full max-w-[440px] flex-col justify-between overflow-hidden rounded-3xl bg-surface-container p-4 shadow-2xl">
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary/10 via-transparent to-secondary-300/15" />

                            <div class="relative z-10 flex items-center justify-between rounded-2xl bg-surface-container-lowest/90 p-3 shadow-md backdrop-blur-md">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-tertiary-50 text-tertiary">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M13 2L4 14h7l-1 8 9-12h-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-label-md font-bold leading-tight text-on-surface">Sesi Live Interaktif</p>
                                        <p class="text-body-sm font-semibold text-tertiary">Sedang berlangsung &bull; 842 Peserta</p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-tertiary px-2.5 py-1 text-label-sm font-bold uppercase text-white">Online</span>
                            </div>

                            <div class="relative z-0 my-auto aspect-[4/3] overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary-500 shadow-inner">
                                <svg class="absolute -bottom-8 -right-6 h-44 w-44 text-white/10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white/90 text-primary shadow-lg">
                                        <svg class="ml-0.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="absolute inset-x-3 bottom-3 flex items-center justify-between text-white">
                                    <span class="rounded-lg bg-black/40 px-2.5 py-1 text-label-md backdrop-blur-sm">Modul 04: Stateful Architecture</span>
                                    <span class="flex items-center gap-1 rounded-md bg-primary/80 px-2 py-0.5 text-label-sm">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z" /></svg>
                                        18:42
                                    </span>
                                </div>
                            </div>

                            <div class="relative z-10 -mt-3 flex items-center gap-4 rounded-2xl bg-surface-container-lowest/95 p-3.5 shadow-lg backdrop-blur-md">
                                <div class="relative h-12 w-12 shrink-0">
                                    <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                                        <path class="text-surface-container-high" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3.5" />
                                        <path class="text-primary" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="82, 100" stroke-linecap="round" stroke-width="3.5" />
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-label-md font-bold text-primary">82%</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-label-md font-bold leading-tight text-on-surface">Target Kelulusan Pekan Ini</p>
                                    <p class="truncate text-body-sm text-on-surface-variant">Tinggal 2 modul lagi untuk sertifikat</p>
                                </div>
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-secondary-50 text-secondary">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="8" r="5" />
                                        <path d="M8.5 12.5 7 21l5-3 5 3-1.5-8.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -bottom-6 -left-6 z-20 hidden items-center gap-3 rounded-2xl bg-surface-container-lowest px-4 py-3 shadow-xl sm:flex">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary-50 text-secondary">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M9 18h6M10 21h4" />
                                    <path d="M12 3a6 6 0 0 0-4 10.5c.6.5 1 1.2 1 2h6c0-.8.4-1.5 1-2A6 6 0 0 0 12 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-label-md font-bold text-on-surface">Kurikulum 2025</p>
                                <p class="text-body-sm text-on-surface-variant">Standar Industri Digital</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section class="mx-auto w-full max-w-7xl px-4 py-10 lg:px-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="item in valueProps"
                    :key="item.title"
                    class="group flex flex-col rounded-2xl bg-surface-container-lowest p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl transition-transform group-hover:scale-110" :class="item.iconClass">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <template v-if="item.icon === 'verified'">
                                <path d="M12 3l7 3v5c0 4.4-3 8.4-7 10-4-1.6-7-5.6-7-10V6z" />
                                <path d="M9 12l2 2 4-4" />
                            </template>
                            <template v-else-if="item.icon === 'infinity'">
                                <path d="M6.5 8.5a3.5 3.5 0 1 0 0 7c3.5 0 4.5-7 7-7a3.5 3.5 0 1 1 0 7c-2.5 0-3.5-7-7-7z" />
                            </template>
                            <template v-else-if="item.icon === 'award'">
                                <circle cx="12" cy="8" r="5" />
                                <path d="M8.5 12.5 7 21l5-3 5 3-1.5-8.5" />
                            </template>
                            <template v-else>
                                <path d="M4 13a8 8 0 0 1 16 0" />
                                <rect x="2" y="13" width="4" height="6" rx="1.5" />
                                <rect x="18" y="13" width="4" height="6" rx="1.5" />
                                <path d="M20 19v1a2 2 0 0 1-2 2h-4" />
                            </template>
                        </svg>
                    </div>
                    <h2 class="mb-2 text-headline-sm font-bold text-on-surface">{{ item.title }}</h2>
                    <p class="text-body-md leading-relaxed text-on-surface-variant">{{ item.description }}</p>
                    <div class="mt-4 flex items-center gap-1 border-t border-surface-container pt-4 text-label-md" :class="item.linkClass">
                        <span>{{ item.link }}</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="popularCourses.length > 0" class="mx-auto w-full max-w-7xl px-4 py-12 lg:px-8 lg:py-16">
            <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-1 w-6 rounded-full bg-primary" />
                        <p class="text-label-md font-bold uppercase tracking-wider text-primary">Pilihan Terbaik Pekan Ini</p>
                    </div>
                    <h2 class="text-headline-lg font-bold text-on-surface">Kursus Populer</h2>
                </div>
                <RouterLink to="/kursus" class="group inline-flex items-center gap-2 text-label-lg font-bold text-primary transition-colors hover:text-primary-500">
                    <span>Lihat Semua Kursus</span>
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </RouterLink>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <article v-for="(course, index) in popularCourses" :key="course.slug" class="card-flush card-hover group flex flex-col">
                    <CourseCover
                        :category-name="course.categoryName"
                        :accent="course.accent"
                        :duration-hours="course.durationHours"
                        :level="course.level"
                        :badge="badgeLabel(course, index)"
                    />

                    <div class="flex flex-1 flex-col p-6">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="rounded-md bg-primary-50 px-2.5 py-1 text-label-md font-bold text-primary">{{ course.categoryName }}</span>
                            <div class="flex items-center gap-1">
                                <svg class="h-4 w-4 text-secondary-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2l2.9 6.3 6.9.6-5.2 4.5 1.6 6.7L12 16.9 5.8 20.1l1.6-6.7L2.2 8.9l6.9-.6z" />
                                </svg>
                                <span class="text-label-lg font-bold text-on-surface">{{ course.rating }}</span>
                                <span class="text-body-sm text-on-surface-variant">({{ course.reviews }})</span>
                            </div>
                        </div>

                        <h3 class="mb-2 line-clamp-2 text-title-md font-bold leading-snug text-on-surface transition-colors group-hover:text-primary">
                            {{ course.title }}
                        </h3>
                        <p class="mb-4 line-clamp-2 text-body-sm leading-relaxed text-on-surface-variant">{{ course.summary }}</p>

                        <div class="mb-4 mt-auto flex items-center gap-3 border-t border-surface-container pt-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-container-high text-label-md font-bold text-primary">
                                {{ initials(course.instructor.name) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-label-md font-semibold text-on-surface">{{ course.instructor.name }}</p>
                                <p class="truncate text-body-sm text-on-surface-variant">{{ course.instructor.role }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-headline-md font-extrabold text-primary">{{ formatPrice(course) }}</span>
                            <RouterLink
                                :to="`/kursus/${course.slug}`"
                                class="flex h-10 items-center gap-1.5 rounded-xl bg-primary px-4 text-label-md font-bold text-white shadow-sm transition-transform hover:bg-primary-500 active:scale-95"
                            >
                                <span>Detail</span>
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </RouterLink>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section class="mx-auto w-full max-w-7xl px-4 py-10 lg:px-8 lg:py-16">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary-500 via-primary to-[#283044] p-8 text-white shadow-2xl sm:p-12 lg:p-16">
                <div class="pointer-events-none absolute -bottom-20 -right-16 h-80 w-80 rounded-full bg-secondary-300/20 blur-2xl" />
                <div class="pointer-events-none absolute right-1/4 top-0 h-60 w-60 rounded-full bg-white/10 blur-xl" />
                <div class="relative z-10 flex flex-col items-center justify-between gap-8 lg:flex-row">
                    <div class="max-w-2xl text-center lg:text-left">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-label-sm text-primary-50 backdrop-blur-sm">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 13c-1.5 1.5-2 5-2 5s3.5-.5 5-2M14 4c3-2 6-2 6-2s0 3-2 6l-6 6-4-4z" />
                                <circle cx="15" cy="9" r="1.5" />
                            </svg>
                            <span>Akses Gratis Modul Pengenalan</span>
                        </div>
                        <h2 class="text-[28px] font-extrabold leading-tight text-white sm:text-[34px] lg:text-[40px]">
                            Mulai perjalanan belajar bersama Ilmora sekarang!
                        </h2>
                        <p class="mt-4 max-w-xl text-body-lg text-primary-50/90">
                            Bergabunglah dengan ribuan talenta digital lainnya dan rasakan lompatan karier dengan kurikulum aplikatif langsung ke portofolio.
                        </p>
                    </div>
                    <div class="flex w-full shrink-0 flex-col items-center gap-4 sm:w-auto sm:flex-row">
                        <RouterLink
                            to="/daftar"
                            class="flex h-13 w-full items-center justify-center gap-2 rounded-2xl bg-white px-8 text-label-lg font-bold text-primary shadow-lg transition-transform hover:scale-105 active:scale-95 sm:w-auto"
                        >
                            <span>Daftar Gratis</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="9" cy="7" r="4" />
                                <path d="M2 21v-2a4 4 0 0 1 4-4h6" />
                                <path d="M19 8v6M22 11h-6" />
                            </svg>
                        </RouterLink>
                        <RouterLink
                            to="/langganan"
                            class="flex h-13 w-full items-center justify-center gap-2 rounded-2xl bg-white/15 px-8 text-label-lg font-bold text-white backdrop-blur-sm transition-colors hover:bg-white/25 sm:w-auto"
                        >
                            <span>Lihat Paket Pro</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="8" r="5" />
                                <path d="M8.5 12.5 7 21l5-3 5 3-1.5-8.5" />
                            </svg>
                        </RouterLink>
                    </div>
                </article>
            </div>
        </section>

        <section class="mx-auto w-full max-w-7xl px-4 py-10 lg:px-8 lg:py-16">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary-500 via-primary to-[#283044] p-8 text-white shadow-2xl sm:p-12 lg:p-16">
                <div class="pointer-events-none absolute -bottom-20 -right-16 h-80 w-80 rounded-full bg-secondary-300/20 blur-2xl" />
                <div class="pointer-events-none absolute right-1/4 top-0 h-60 w-60 rounded-full bg-white/10 blur-xl" />
                <div class="relative z-10 flex flex-col items-center justify-between gap-8 lg:flex-row">
                    <div class="max-w-2xl text-center lg:text-left">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-label-sm text-primary-50 backdrop-blur-sm">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 13c-1.5 1.5-2 5-2 5s3.5-.5 5-2M14 4c3-2 6-2 6-2s0 3-2 6l-6 6-4-4z" />
                                <circle cx="15" cy="9" r="1.5" />
                            </svg>
                            <span>Akses Gratis Modul Pengenalan</span>
                        </div>
                        <h2 class="text-[28px] font-extrabold leading-tight text-white sm:text-[34px] lg:text-[40px]">
                            Mulai perjalanan belajar bersama Ilmora sekarang!
                        </h2>
                        <p class="mt-4 max-w-xl text-body-lg text-primary-50/90">
                            Bergabunglah dengan ribuan talenta digital lainnya dan rasakan lompatan karier dengan kurikulum aplikatif langsung ke portofolio.
                        </p>
                    </div>
                    <div class="flex w-full shrink-0 flex-col items-center gap-4 sm:w-auto sm:flex-row">
                        <RouterLink
                            to="/daftar"
                            class="flex h-13 w-full items-center justify-center gap-2 rounded-2xl bg-white px-8 text-label-lg font-bold text-primary shadow-lg transition-transform hover:scale-105 active:scale-95 sm:w-auto"
                        >
                            <span>Daftar Gratis</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="9" cy="7" r="4" />
                                <path d="M2 21v-2a4 4 0 0 1 4-4h6" />
                                <path d="M19 8v6M22 11h-6" />
                            </svg>
                        </RouterLink>
                        <RouterLink
                            to="/langganan"
                            class="flex h-13 w-full items-center justify-center gap-2 rounded-2xl bg-white/15 px-8 text-label-lg font-bold text-white backdrop-blur-sm transition-colors hover:bg-white/25 sm:w-auto"
                        >
                            <span>Lihat Paket Pro</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="8" r="5" />
                                <path d="M8.5 12.5 7 21l5-3 5 3-1.5-8.5" />
                            </svg>
                        </RouterLink>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
