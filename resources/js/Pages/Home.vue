<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import api from '@/lib/api'

interface PopularCourse {
    slug: string
    title: string
    categoryName: string
    level: string
    price: number
    rating: number
    instructor: { name: string }
    accent: string
    free: boolean
}

const popularCourses = ref<PopularCourse[]>([])

const accentClasses: Record<string, string> = {
    primary: 'from-primary to-primary-container',
    secondary: 'from-secondary to-[#A855F7]',
    tertiary: 'from-tertiary to-[#059669]',
}

function formatPrice(course: PopularCourse): string {
    if (course.free || course.price <= 0) return 'Gratis'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(course.price)
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

const features = [
    {
        title: 'Katalog Terkurasi',
        description: 'Kursus disusun rapi berdasarkan kategori agar mudah menemukan materi yang tepat untuk tujuan belajar Anda.',
        icon: 'grid',
    },
    {
        title: 'Belajar Fleksibel',
        description: 'Akses materi kapan saja dari perangkat Anda, dengan progres yang tersimpan otomatis di setiap langkah.',
        icon: 'clock',
    },
    {
        title: 'Langganan Sederhana',
        description: 'Pilih paket yang sesuai kebutuhan. Satu langganan membuka akses ke seluruh katalog kursus.',
        icon: 'spark',
    },
]

const steps = [
    {
        title: 'Buat akun',
        description: 'Daftar gratis untuk mulai menjelajahi katalog dan menyimpan kursus pilihan Anda.',
    },
    {
        title: 'Pilih kursus',
        description: 'Telusuri kursus berdasarkan kategori atau kata kunci sesuai bidang yang ingin dikuasai.',
    },
    {
        title: 'Mulai belajar',
        description: 'Ikuti materi, kerjakan latihan, dan pantau kemajuan belajar Anda dari dashboard.',
    },
]
</script>

<template>
    <GuestLayout>
        <section class="relative overflow-hidden">
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 md:grid-cols-2 md:py-24">
                <div class="animate-fade-in">
                    <span class="badge-primary">Platform Pembelajaran Digital</span>
                    <h1 class="mt-4 text-display-sm font-bold leading-tight tracking-tight text-on-surface md:text-display-md">
                        Belajar tanpa batas,<br />
                        <span class="text-gradient-primary">tumbuh setiap hari.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-body-lg text-on-surface-variant">
                        Ilmora membantu Anda menguasai keterampilan baru melalui kursus terstruktur, materi praktis, dan pengalaman belajar yang menyenangkan.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <RouterLink to="/daftar" class="btn-primary">
                            Mulai Gratis
                        </RouterLink>
                        <RouterLink to="/kursus" class="btn-secondary">
                            Jelajahi Kursus
                        </RouterLink>
                    </div>
                </div>

                <div class="relative hidden md:block">
                    <div class="absolute -inset-6 rounded-[2.5rem] bg-gradient-to-br from-primary/10 to-secondary/10" aria-hidden="true" />
                    <div class="relative grid grid-cols-2 gap-4">
                        <div class="card translate-y-6 p-5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="2" y="3" width="20" height="14" rx="2" />
                                    <path d="M8 21h8M12 17v4" />
                                </svg>
                            </div>
                            <p class="mt-4 text-title-md font-semibold">Kursus Terstruktur</p>
                            <p class="mt-1 text-body-sm text-on-surface-variant">Materi tersusun dari dasar hingga mahir.</p>
                        </div>
                        <div class="card -translate-y-6 p-5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-secondary/10 text-secondary">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 2l2.4 7.4H22l-6 4.4 2.3 7.2L12 16.6 5.7 21l2.3-7.2-6-4.4h7.6z" />
                                </svg>
                            </div>
                            <p class="mt-4 text-title-md font-semibold">Materi Praktis</p>
                            <p class="mt-1 text-body-sm text-on-surface-variant">Latihan dan contoh nyata di setiap modul.</p>
                        </div>
                        <div class="card translate-y-6 p-5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-tertiary/10 text-tertiary">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M3 3v18h18" />
                                    <path d="M7 14l4-4 3 3 5-6" />
                                </svg>
                            </div>
                            <p class="mt-4 text-title-md font-semibold">Progres Terukur</p>
                            <p class="mt-1 text-body-sm text-on-surface-variant">Pantau kemajuan belajar Anda.</p>
                        </div>
                        <div class="card -translate-y-6 p-5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <p class="mt-4 text-title-md font-semibold">Komunitas Belajar</p>
                            <p class="mt-1 text-body-sm text-on-surface-variant">Bertukar pengalaman antar pembelajar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="popularCourses.length > 0" class="py-16 md:py-24">
            <div class="mx-auto max-w-7xl px-4">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-headline-lg font-bold tracking-tight">Kursus Populer</h2>
                        <p class="mt-2 text-body-lg text-on-surface-variant">Pilihan kursus terbaik yang paling diminati bulan ini.</p>
                    </div>
                    <RouterLink to="/kursus" class="btn-secondary h-10 px-4 text-label-md">Lihat Semua</RouterLink>
                </div>

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="course in popularCourses" :key="course.slug" class="card-hover card flex flex-col overflow-hidden" style="padding: 0">
                        <div class="flex aspect-video items-center justify-center bg-gradient-to-br" :class="accentClasses[course.accent] || accentClasses.primary">
                            <span class="text-label-lg font-semibold uppercase tracking-widest text-white/90">{{ course.categoryName }}</span>
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
                            </div>
                            <div class="mt-auto flex items-center justify-between pt-5">
                                <span class="text-title-lg font-bold">{{ formatPrice(course) }}</span>
                                <RouterLink :to="`/kursus/${course.slug}`" class="btn-primary h-10 px-4 text-label-md">Daftar</RouterLink>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="bg-surface-container-low py-16 md:py-24">
            <div class="mx-auto max-w-7xl px-4">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-headline-lg font-bold tracking-tight">Mengapa memilih Ilmora</h2>
                    <p class="mt-3 text-body-lg text-on-surface-variant">
                        Dirancang agar pengalaman belajar terasa jelas, ramah, dan menyenangkan.
                    </p>
                </div>

                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title" class="card-hover card">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <svg v-if="feature.icon === 'grid'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                            <svg v-else-if="feature.icon === 'clock'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 2" />
                            </svg>
                            <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 2l2.4 7.4H22l-6 4.4 2.3 7.2L12 16.6 5.7 21l2.3-7.2-6-4.4h7.6z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-title-lg font-semibold">{{ feature.title }}</h3>
                        <p class="mt-2 text-body-md text-on-surface-variant">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 md:py-24">
            <div class="mx-auto max-w-7xl px-4">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-headline-lg font-bold tracking-tight">Mulai dalam tiga langkah</h2>
                    <p class="mt-3 text-body-lg text-on-surface-variant">Proses sederhana untuk memulai perjalanan belajar Anda.</p>
                </div>

                <ol class="mt-12 grid gap-6 md:grid-cols-3">
                    <li v-for="(step, index) in steps" :key="step.title" class="relative card">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-label-lg font-bold text-white">
                            {{ index + 1 }}
                        </span>
                        <h3 class="mt-4 text-title-lg font-semibold">{{ step.title }}</h3>
                        <p class="mt-2 text-body-md text-on-surface-variant">{{ step.description }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="pb-20">
            <div class="mx-auto max-w-7xl px-4">
                <div class="relative overflow-hidden rounded-3xl bg-primary px-8 py-14 text-center text-white md:px-16">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary to-primary-container opacity-90" aria-hidden="true" />
                    <div class="relative mx-auto max-w-2xl">
                        <h2 class="text-display-sm font-bold tracking-tight">Siap memulai belajar hari ini?</h2>
                        <p class="mt-4 text-body-lg text-white/80">
                            Buat akun gratis dan temukan kursus yang sesuai dengan tujuan Anda.
                        </p>
                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                            <RouterLink
                                to="/daftar"
                                class="inline-flex h-13 items-center justify-center rounded-2xl bg-white px-6 text-label-lg font-semibold text-primary transition-colors hover:bg-white/90"
                            >
                                Daftar Sekarang
                            </RouterLink>
                            <RouterLink
                                to="/langganan"
                                class="inline-flex h-13 items-center justify-center rounded-2xl border-[1.5px] border-white/40 px-6 text-label-lg font-semibold text-white transition-colors hover:bg-white/10"
                            >
                                Lihat Paket
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
