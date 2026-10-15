<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/stores/auth'
import BottomNav from '@/Components/BottomNav.vue'
import Logo from '@/Components/Logo.vue'

const route = useRoute()
const router = useRouter()
const { user, isAdmin, logout: performLogout } = useAuth()

const mobileMenuOpen = ref(false)

const currentPath = computed(() => route.path)

const navItems = [
    { label: 'Beranda', to: '/', match: '/' },
    { label: 'Kursus', to: '/kursus', match: '/kursus' },
    { label: 'Langganan', to: '/langganan', match: '/langganan' },
]

function isActive(match: string): boolean {
    return match === '/' ? currentPath.value === '/' : currentPath.value.startsWith(match)
}

async function logout(): Promise<void> {
    await performLogout()
    mobileMenuOpen.value = false
    router.push('/')
}
</script>

<template>
    <div class="min-h-screen bg-surface text-on-surface font-sans">
        <header class="surface-elevation-2 sticky top-0 z-40">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low md:hidden"
                        aria-label="Buka menu"
                        @click="mobileMenuOpen = true"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="12" x2="21" y2="12" />
                            <line x1="3" y1="18" x2="21" y2="18" />
                        </svg>
                    </button>

                    <RouterLink
                        to="/"
                        class="flex items-center gap-2 text-headline-md font-bold tracking-tight text-on-surface transition-colors hover:text-primary"
                        aria-label="Ilmora"
                    >
                        <Logo size="md" alt="Ilmora" />
                        <span>Ilmora</span>
                    </RouterLink>
                </div>

                <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
                    <RouterLink
                        v-for="item in navItems"
                        :key="item.match"
                        :to="item.to"
                        class="rounded-xl px-4 py-2 text-label-lg font-medium transition-colors"
                        :class="
                            isActive(item.match)
                                ? 'bg-primary/10 text-primary'
                                : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary'
                        "
                    >
                        {{ item.label }}
                    </RouterLink>
                </nav>

                <div class="flex items-center gap-2">
                    <RouterLink
                        v-if="isAdmin"
                        to="/admin/dashboard"
                        class="hidden items-center gap-2 rounded-xl px-4 py-2 text-label-lg font-semibold text-primary transition-colors hover:bg-primary/10 sm:inline-flex"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z" />
                        </svg>
                        Panel Admin
                    </RouterLink>
                    <RouterLink
                        v-if="user"
                        to="/profile"
                        class="hidden rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary sm:inline-flex"
                        aria-label="Profil"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </RouterLink>

                    <button
                        v-if="user"
                        type="button"
                        class="btn-secondary h-10 px-4 text-label-md"
                        @click="logout"
                    >
                        Keluar
                    </button>
                    <RouterLink
                        v-else
                        to="/masuk"
                        class="btn-primary h-10 px-4 text-label-md"
                    >
                        Masuk
                    </RouterLink>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 pb-28 pt-6 md:pb-12">
            <slot />
        </main>

        <BottomNav />

        <Transition name="fade">
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 md:hidden">
                <div class="modal-scrim" aria-hidden="true" @click="mobileMenuOpen = false" />

                <aside class="fixed inset-y-0 right-0 z-50 w-72 bg-surface shadow-modal">
                    <div class="flex items-center justify-between border-b border-outline-variant p-4">
                        <h3 class="text-headline-sm font-semibold">Menu</h3>
                        <button
                            type="button"
                            class="rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low"
                            aria-label="Tutup menu"
                            @click="mobileMenuOpen = false"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <nav class="space-y-2 p-4" aria-label="Navigasi mobile">
                        <RouterLink
                            v-for="item in navItems"
                            :key="item.match"
                            :to="item.to"
                            class="block rounded-xl px-4 py-3 text-label-lg font-medium transition-colors"
                            :class="
                                isActive(item.match)
                                    ? 'bg-primary/10 text-primary'
                                    : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary'
                            "
                            @click="mobileMenuOpen = false"
                        >
                            {{ item.label }}
                        </RouterLink>

                        <hr class="my-4 border-outline-variant" />

                        <RouterLink
                            v-if="isAdmin"
                            to="/admin/dashboard"
                            class="block rounded-xl px-4 py-3 text-label-lg font-semibold text-primary transition-colors hover:bg-primary/10"
                            @click="mobileMenuOpen = false"
                        >
                            Panel Admin
                        </RouterLink>
                        <RouterLink
                            to="/profile"
                            class="block rounded-xl px-4 py-3 text-label-lg font-medium text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
                            @click="mobileMenuOpen = false"
                        >
                            Profil
                        </RouterLink>
                        <button
                            v-if="user"
                            type="button"
                            class="block w-full rounded-xl px-4 py-3 text-left text-label-lg font-medium text-error transition-colors hover:bg-error/10"
                            @click="logout"
                        >
                            Keluar
                        </button>
                        <RouterLink
                            v-else
                            to="/masuk"
                            class="block rounded-xl px-4 py-3 text-label-lg font-medium text-primary transition-colors hover:bg-primary/10"
                            @click="mobileMenuOpen = false"
                        >
                            Masuk
                        </RouterLink>
                    </nav>
                </aside>
            </div>
        </Transition>
    </div>
</template>
