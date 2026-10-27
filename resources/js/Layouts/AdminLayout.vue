<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/stores/auth'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Logo from '@/Components/Logo.vue'

const route = useRoute()
const router = useRouter()
const { user, logout: performLogout } = useAuth()

const sidebarOpen = ref(false)
const confirmLogoutOpen = ref(false)
const loggingOut = ref(false)

const pageTitle = computed(() => (route.meta.title as string | undefined) ?? 'Dashboard Admin')

const navItems = [
    { label: 'Dashboard', to: '/admin/dashboard', match: '/admin/dashboard', icon: 'grid' },
    { label: 'Kursus', to: '/admin/kursus', match: '/admin/kursus', icon: 'book' },
    { label: 'Paket', to: '/admin/paket', match: '/admin/paket', icon: 'tag' },
    { label: 'Kategori', to: '/admin/kategori', match: '/admin/kategori', icon: 'tag' },
    { label: 'Pengguna', to: '/admin/pengguna', match: '/admin/pengguna', icon: 'users' },
    { label: 'Langganan', to: '/admin/langganan', match: '/admin/langganan', icon: 'card' },
    { label: 'Pembayaran', to: '/admin/pembayaran', match: '/admin/pembayaran', icon: 'wallet' },
    { label: 'Ulasan', to: '/admin/ulasan', match: '/admin/ulasan', icon: 'tag' },
]

function isActive(match: string): boolean {
    return route.path === match || route.path.startsWith(`${match}/`)
}

function requestLogout(): void {
    sidebarOpen.value = false
    confirmLogoutOpen.value = true
}

async function confirmLogout(): Promise<void> {
    loggingOut.value = true
    try {
        await performLogout()
        confirmLogoutOpen.value = false
        router.push('/')
    } finally {
        loggingOut.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-surface text-on-surface font-sans">
        <!-- Desktop sidebar -->
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-outline-variant/60 bg-surface-container-lowest lg:flex">
            <div class="flex h-16 items-center gap-2 border-b border-outline-variant/60 px-5">
                <RouterLink to="/admin/dashboard" class="flex items-center gap-2 text-headline-sm font-bold tracking-tight text-on-surface transition-colors hover:text-primary" aria-label="Ilmora Admin">
                    <Logo size="md" alt="Ilmora Admin" />
                    <span>Ilmora<span class="ml-1 text-label-sm font-bold uppercase text-primary">Admin</span></span>
                </RouterLink>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Navigasi admin">
                <RouterLink
                    v-for="item in navItems"
                    :key="item.match"
                    :to="item.to"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-label-lg font-medium transition-colors"
                    :class="isActive(item.match) ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary'"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <template v-if="item.icon === 'grid'"><rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" /><rect x="3" y="14" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" /></template>
                        <template v-else-if="item.icon === 'book'"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" /></template>
                        <template v-else-if="item.icon === 'users'"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></template>
                        <template v-else-if="item.icon === 'card'"><rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" /></template>
                        <template v-else-if="item.icon === 'tag'"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" /><line x1="7" y1="7" x2="7.01" y2="7" /></template>
                        <template v-else><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" /><path d="M3 5v14a2 2 0 0 0 2 2h16v-5" /><path d="M18 12a2 2 0 0 0 0 4h4v-4z" /></template>
                    </svg>
                    {{ item.label }}
                </RouterLink>
            </nav>

            <div class="space-y-1 border-t border-outline-variant/60 p-3">
                <RouterLink to="/" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-label-lg font-medium text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" /><path d="M9 22V12h6v10" /></svg>
                    Lihat Situs
                </RouterLink>
                <button type="button" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-label-lg font-medium text-error transition-colors hover:bg-error/10" @click="requestLogout">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="M16 17l5-5-5-5M21 12H9" /></svg>
                    Keluar
                </button>
            </div>
        </aside>

        <div class="lg:pl-64">
            <header class="surface-elevation-2 sticky top-0 z-30">
                <div class="flex h-16 items-center justify-between gap-4 px-4 md:px-6">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low lg:hidden"
                            aria-label="Buka menu admin"
                            @click="sidebarOpen = true"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6" /><line x1="3" y1="12" x2="21" y2="12" /><line x1="3" y1="18" x2="21" y2="18" /></svg>
                        </button>
                        <div>
                            <p class="text-label-sm font-bold uppercase tracking-wide text-primary">Panel Admin</p>
                            <h1 class="text-title-lg font-bold tracking-tight">{{ pageTitle }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p class="text-label-md font-semibold text-on-surface">{{ user?.name }}</p>
                            <p class="text-body-sm text-on-surface-variant">{{ user?.email }}</p>
                        </div>
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-on-primary">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg>
                        </span>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 md:px-6 md:py-8">
                <slot />
            </main>
        </div>

        <!-- Mobile drawer -->
        <Transition name="fade">
            <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
                <div class="modal-scrim" aria-hidden="true" @click="sidebarOpen = false" />
                <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-surface-container-lowest shadow-modal">
                    <div class="flex items-center justify-between border-b border-outline-variant/60 p-4">
                        <span class="text-headline-sm font-bold">Menu Admin</span>
                        <button type="button" class="rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low" aria-label="Tutup menu" @click="sidebarOpen = false">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
                        </button>
                    </div>
                    <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Navigasi admin mobile">
                        <RouterLink
                            v-for="item in navItems"
                            :key="item.match"
                            :to="item.to"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-label-lg font-medium transition-colors"
                            :class="isActive(item.match) ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary'"
                            @click="sidebarOpen = false"
                        >
                            {{ item.label }}
                        </RouterLink>
                    </nav>
                    <div class="space-y-1 border-t border-outline-variant/60 p-3">
                        <RouterLink to="/" class="block rounded-xl px-3 py-2.5 text-label-lg font-medium text-on-surface-variant" @click="sidebarOpen = false">Lihat Situs</RouterLink>
                        <button type="button" class="block w-full rounded-xl px-3 py-2.5 text-left text-label-lg font-medium text-error" @click="requestLogout">Keluar</button>
                    </div>
                </aside>
            </div>
        </Transition>

        <ConfirmDialog
            v-model="confirmLogoutOpen"
            title="Keluar dari Panel Admin?"
            message="Anda akan keluar dari akun Ilmora. Untuk kembali mengelola konten, Anda perlu masuk kembali."
            confirm-label="Keluar"
            cancel-label="Tetap di Sini"
            variant="danger"
            icon="logout"
            :loading="loggingOut"
            @confirm="confirmLogout"
        />
    </div>
</template>
