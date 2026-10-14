<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuth } from '@/stores/auth'

const route = useRoute()
const { isAuthenticated } = useAuth()

const currentPath = computed(() => route.path)
const profileHref = computed(() => (isAuthenticated.value ? '/profile' : '/masuk'))

function itemClass(active: boolean): string {
    return [
        'flex flex-col items-center justify-center gap-1 rounded-t-2xl py-3 text-label-sm font-medium transition-colors',
        active ? 'text-primary' : 'text-on-surface-variant hover:text-primary',
    ].join(' ')
}
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-50 border-t border-outline-variant/50 bg-surface/85 shadow-sticky backdrop-blur-md md:hidden"
        aria-label="Navigasi bawah"
    >
        <div class="grid grid-cols-4">
            <RouterLink
                to="/"
                :class="itemClass(currentPath === '/')"
                :aria-current="currentPath === '/' ? 'page' : undefined"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                <span>Beranda</span>
            </RouterLink>

            <RouterLink
                to="/kursus"
                :class="itemClass(currentPath.startsWith('/kursus'))"
                :aria-current="currentPath.startsWith('/kursus') ? 'page' : undefined"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <path d="M8 21h8M12 17v4" />
                </svg>
                <span>Kursus</span>
            </RouterLink>

            <RouterLink
                to="/langganan"
                :class="itemClass(currentPath.startsWith('/langganan'))"
                :aria-current="currentPath.startsWith('/langganan') ? 'page' : undefined"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="2" y="5" width="20" height="14" rx="2" />
                    <path d="M2 10h20" />
                </svg>
                <span>Langganan</span>
            </RouterLink>

            <RouterLink
                :to="profileHref"
                :class="itemClass(currentPath.startsWith('/profile'))"
                :aria-current="currentPath.startsWith('/profile') ? 'page' : undefined"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <span>Profil</span>
            </RouterLink>
        </div>
    </nav>
</template>
