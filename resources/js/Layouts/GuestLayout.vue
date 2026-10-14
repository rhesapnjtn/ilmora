<script setup lang="ts">
import { useAuth } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import BottomNav from '@/Components/BottomNav.vue'

const { user } = useAuth()

const navItems = [
    { label: 'Kursus', to: '/kursus' },
    { label: 'Langganan', to: '/langganan' },
]
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface text-on-surface font-sans">
        <header class="surface-elevation-2 sticky top-0 z-40">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4">
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
                        :key="item.to"
                        :to="item.to"
                        class="rounded-xl px-4 py-2 text-label-lg font-medium text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
                    >
                        {{ item.label }}
                    </RouterLink>
                </nav>

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
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
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

        <footer class="border-t border-outline-variant/50 bg-surface-container-low">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 md:flex-row">
                <p class="text-body-sm text-on-surface-variant">
                    &copy; {{ new Date().getFullYear() }} Ilmora. Hak cipta dilindungi.
                </p>
                <nav class="flex items-center gap-6" aria-label="Navigasi footer">
                    <RouterLink to="/kebijakan-privasi" class="text-body-sm text-on-surface-variant transition-colors hover:text-primary">
                        Kebijakan Privasi
                    </RouterLink>
                    <RouterLink to="/syarat-layanan" class="text-body-sm text-on-surface-variant transition-colors hover:text-primary">
                        Syarat Layanan
                    </RouterLink>
                </nav>
            </div>
        </footer>

        <BottomNav />
    </div>
</template>
