<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/stores/auth'
import api from '@/lib/api'
import BottomNav from '@/Components/BottomNav.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Logo from '@/Components/Logo.vue'
import NotificationBell from '@/Components/NotificationBell.vue'
import UserAvatar from '@/Components/UserAvatar.vue'

const route = useRoute()
const router = useRouter()
const { user, isAdmin, logout: performLogout } = useAuth()

const mobileMenuOpen = ref(false)
const confirmLogoutOpen = ref(false)
const loggingOut = ref(false)

const searchOpen = ref(false)
const searchQuery = ref('')
const searchResults = ref<{ slug: string; title: string; category: string }[]>([])
const searchLoading = ref(false)
let searchTimer: ReturnType<typeof setTimeout> | null = null

function openSearch(): void {
    searchOpen.value = true
    searchQuery.value = ''
    searchResults.value = []
}

function closeSearch(): void {
    searchOpen.value = false
}

function onSearchInput(): void {
    if (searchTimer) clearTimeout(searchTimer)
    searchTimer = setTimeout(async () => {
        if (!searchQuery.value.trim()) {
            searchResults.value = []
            return
        }
        searchLoading.value = true
        try {
            const { data } = await api.get<{ courses: { slug: string; title: string; category: string }[] }>('/courses', {
                params: { search: searchQuery.value },
            })
            searchResults.value = data.courses ?? []
        } catch {
            searchResults.value = []
        } finally {
            searchLoading.value = false
        }
    }, 250)
}

function goSearch(): void {
    if (searchQuery.value.trim()) {
        router.push({ path: '/kursus', query: { search: searchQuery.value } })
        closeSearch()
    }
}

function handleKeydown(e: KeyboardEvent): void {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault()
        openSearch()
    }
    if (e.key === 'Escape' && searchOpen.value) {
        closeSearch()
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown))
onUnmounted(() => window.removeEventListener('keydown', handleKeydown))

const currentPath = computed(() => route.path)

const navItems = [
    { label: 'Beranda', to: '/', match: '/' },
    { label: 'Kursus', to: '/kursus', match: '/kursus' },
    { label: 'Catatan', to: '/catatan', match: '/catatan' },
    { label: 'Langganan', to: '/langganan', match: '/langganan' },
]

function isActive(match: string): boolean {
    return match === '/' ? currentPath.value === '/' : currentPath.value.startsWith(match)
}

function requestLogout(): void {
    mobileMenuOpen.value = false
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
                    <button
                        type="button"
                        class="hidden items-center gap-2 rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2 text-body-sm text-on-surface-variant transition-colors hover:border-primary hover:text-primary sm:inline-flex"
                        aria-label="Cari kursus (Ctrl+K)"
                        @click="openSearch"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.35-4.35" />
                        </svg>
                        <span>Cari...</span>
                        <kbd class="rounded bg-surface-container-high px-1.5 text-label-sm">Ctrl K</kbd>
                    </button>
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
                    <NotificationBell v-if="user" />

                    <RouterLink
                        v-if="user"
                        to="/profile"
                        class="hidden rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary sm:inline-flex"
                        aria-label="Profil"
                    >
                        <UserAvatar :src="user.avatar_url" :name="user.name" size="md" />
                    </RouterLink>

                    <button
                        v-if="user"
                        type="button"
                        class="btn-secondary h-10 px-4 text-label-md"
                        @click="requestLogout"
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

        <Transition name="fade">
            <div v-if="searchOpen" class="fixed inset-0 z-50 flex items-start justify-center bg-black/40 px-4 pt-20" @click.self="closeSearch">
                <div class="w-full max-w-xl rounded-2xl bg-surface p-4 shadow-modal">
                    <div class="flex items-center gap-2 border-b border-outline-variant pb-3">
                        <svg class="h-5 w-5 text-on-surface-variant" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.35-4.35" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="flex-1 bg-transparent text-body-md text-on-surface placeholder:text-on-surface-variant focus:outline-none"
                            placeholder="Cari kursus..."
                            autofocus
                            @input="onSearchInput"
                            @keydown.enter="goSearch"
                        />
                        <button type="button" class="text-body-sm text-on-surface-variant" @click="closeSearch">Esc</button>
                    </div>
                    <div class="mt-3 max-h-80 overflow-y-auto">
                        <p v-if="searchLoading" class="px-2 py-3 text-body-sm text-on-surface-variant">Mencari...</p>
                        <ul v-else-if="searchResults.length" class="space-y-1">
                            <li v-for="item in searchResults" :key="item.slug">
                                <RouterLink
                                    :to="`/kursus/${item.slug}`"
                                    class="flex items-center justify-between rounded-xl px-3 py-2 transition-colors hover:bg-surface-container-low"
                                    @click="closeSearch"
                                >
                                    <span class="text-body-md font-medium text-on-surface">{{ item.title }}</span>
                                    <span class="text-body-sm text-on-surface-variant">{{ item.category }}</span>
                                </RouterLink>
                            </li>
                        </ul>
                        <p v-else-if="searchQuery.trim()" class="px-2 py-3 text-body-sm text-on-surface-variant">Tidak ditemukan.</p>
                    </div>
                </div>
            </div>
        </Transition>

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
                            @click="requestLogout"
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

        <ConfirmDialog
            v-model="confirmLogoutOpen"
            title="Keluar dari Akun?"
            message="Anda akan keluar dari akun Ilmora. Untuk mengakses materi dan sertifikat lagi, Anda perlu masuk kembali."
            confirm-label="Keluar"
            cancel-label="Tetap di Sini"
            variant="danger"
            icon="logout"
            :loading="loggingOut"
            @confirm="confirmLogout"
        />
    </div>
</template>
