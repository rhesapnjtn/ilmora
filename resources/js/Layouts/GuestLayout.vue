<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/stores/auth'
import BottomNav from '@/Components/BottomNav.vue'

const { user } = useAuth()
const route = useRoute()
const router = useRouter()

const searchQuery = ref('')

const navItems = [
    { label: 'Beranda', to: '/', match: '/' },
    { label: 'Katalog Kursus', to: '/kursus', match: '/kursus' },
    { label: 'Langganan', to: '/langganan', match: '/langganan' },
]

const footerCategories = [
    { label: 'Pemrograman', slug: 'pemrograman' },
    { label: 'Desain UI/UX', slug: 'desain' },
    { label: 'Marketing', slug: 'marketing' },
    { label: 'Data Science', slug: 'data' },
]

const currentPath = computed(() => route.path)

function isActive(match: string): boolean {
    return match === '/' ? currentPath.value === '/' : currentPath.value.startsWith(match)
}

function submitSearch(): void {
    const query = searchQuery.value.trim()
    router.push({ name: 'courses', query: query ? { search: query } : {} })
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface text-on-surface font-sans">
        <header class="surface-elevation-2 sticky top-0 z-40">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 lg:px-8">
                <div class="flex items-center gap-6">
                    <RouterLink
                        to="/"
                        class="flex items-center gap-2 text-headline-md font-bold tracking-tight text-on-surface transition-colors hover:text-primary"
                        aria-label="Ilmora - Beranda"
                    >
                        <svg class="h-8 w-8 text-primary" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <rect width="32" height="32" rx="8" fill="currentColor" />
                            <path d="M8 12h16M8 16h12M8 20h8" stroke="white" stroke-width="2.5" stroke-linecap="round" />
                        </svg>
                        <span>Ilmora</span>
                    </RouterLink>

                    <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
                        <RouterLink
                            v-for="item in navItems"
                            :key="item.match"
                            :to="item.to"
                            class="rounded-lg px-3 py-2 text-label-lg font-medium transition-colors"
                            :class="
                                isActive(item.match)
                                    ? 'bg-surface-container font-bold text-primary'
                                    : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'
                            "
                        >
                            {{ item.label }}
                        </RouterLink>
                    </nav>
                </div>

                <form class="relative hidden max-w-md flex-1 items-center lg:flex" @submit.prevent="submitSearch">
                    <span class="pointer-events-none absolute left-4 flex items-center text-on-surface-variant" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M21 21l-4.3-4.3" />
                        </svg>
                    </span>
                    <input
                        v-model="searchQuery"
                        type="search"
                        class="h-11 w-full rounded-xl bg-surface-container-low pl-11 pr-4 text-body-sm text-on-surface placeholder:text-outline transition-colors focus:bg-surface-container focus:outline-none"
                        placeholder="Cari kursus, topik, atau mentor..."
                        aria-label="Cari kursus"
                    />
                </form>

                <div class="flex items-center gap-2">
                    <template v-if="user">
                        <RouterLink to="/dashboard" class="btn-primary hidden h-10 px-4 text-label-md sm:inline-flex">
                            Dashboard
                        </RouterLink>
                        <RouterLink
                            to="/profile"
                            class="rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
                            aria-label="Profil"
                        >
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-on-primary">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </span>
                        </RouterLink>
                    </template>
                    <template v-else>
                        <RouterLink to="/masuk" class="btn-secondary hidden h-10 px-4 text-label-md sm:inline-flex">
                            Masuk
                        </RouterLink>
                        <RouterLink to="/daftar" class="btn-primary h-10 px-4 text-label-md">
                            Daftar
                        </RouterLink>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-1 pb-24 md:pb-0">
            <slot />
        </main>

        <footer class="mt-20 w-full border-t border-outline-variant/50 bg-surface-container-low">
            <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
                <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-5">
                    <div class="flex flex-col gap-4 lg:col-span-2">
                        <RouterLink to="/" class="flex select-none items-center gap-3" aria-label="Ilmora">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary font-headline-sm font-bold text-on-primary">I</span>
                            <span class="text-headline-sm font-bold tracking-tight text-on-surface">Ilmora</span>
                        </RouterLink>
                        <p class="max-w-sm text-body-md text-on-surface-variant">
                            Platform edukasi teknologi modern terdepan di Indonesia untuk mengasah kompetensi masa depan dengan kurikulum berbasis industri.
                        </p>
                        <div class="flex items-center gap-3 pt-2" aria-hidden="true">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container text-on-surface-variant">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 17l6-6-6-6M12 19h8" />
                                </svg>
                            </span>
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container text-on-surface-variant">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 18l6-6-6-6M8 6l-6 6 6 6" />
                                </svg>
                            </span>
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container text-on-surface-variant">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div>
                        <p class="mb-4 text-label-lg font-bold text-on-surface">Navigasi</p>
                        <div class="flex flex-col gap-3 text-body-md">
                            <RouterLink to="/" class="text-on-surface-variant transition-colors hover:text-on-surface">Beranda</RouterLink>
                            <RouterLink to="/kursus" class="text-on-surface-variant transition-colors hover:text-on-surface">Katalog Kursus</RouterLink>
                            <RouterLink to="/langganan" class="text-on-surface-variant transition-colors hover:text-on-surface">Paket Langganan</RouterLink>
                        </div>
                    </div>

                    <div>
                        <p class="mb-4 text-label-lg font-bold text-on-surface">Kategori Populer</p>
                        <div class="flex flex-col gap-3 text-body-md">
                            <RouterLink
                                v-for="category in footerCategories"
                                :key="category.slug"
                                :to="{ name: 'courses', query: { category: category.slug } }"
                                class="text-on-surface-variant transition-colors hover:text-on-surface"
                            >
                                {{ category.label }}
                            </RouterLink>
                        </div>
                    </div>

                    <div>
                        <p class="mb-4 text-label-lg font-bold text-on-surface">Bantuan &amp; Panduan</p>
                        <div class="flex flex-col gap-3 text-body-md">
                            <RouterLink to="/kebijakan-privasi" class="text-on-surface-variant transition-colors hover:text-on-surface">Kebijakan Privasi</RouterLink>
                            <RouterLink to="/syarat-layanan" class="text-on-surface-variant transition-colors hover:text-on-surface">Syarat &amp; Ketentuan</RouterLink>
                        </div>
                    </div>
                </div>

                <div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-surface-container pt-8 text-body-sm text-on-surface-variant sm:flex-row">
                    <p>&copy; {{ new Date().getFullYear() }} Ilmora Edutech. Seluruh hak cipta dilindungi.</p>
                    <div class="flex items-center gap-6">
                        <span class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-tertiary" />
                            Sistem Beroperasi Optimal
                        </span>
                        <span>Bahasa Indonesia (ID)</span>
                    </div>
                </div>
            </div>
        </footer>

        <BottomNav />
    </div>
</template>
