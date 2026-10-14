<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useAuth } from '@/stores/auth'

const { user } = useAuth()
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface font-sans text-on-surface">
        <!-- Header matching mockup -->
        <header class="w-full bg-surface/80 backdrop-blur-xl z-20">
            <div class="h-16 max-w-7xl mx-auto px-margin lg:px-margin-tablet flex items-center justify-between">
                <RouterLink
                    to="/"
                    class="flex items-center gap-space-sm group select-none"
                    data-path="beranda"
                    aria-label="Ilmora - Beranda"
                >
                    <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
                        <span class="material-symbols-outlined text-on-primary text-[20px]">auto_stories</span>
                    </div>
                    <span class="font-title-md text-title-md tracking-tight text-on-surface">Ilmora</span>
                </RouterLink>

                <RouterLink
                    to="/"
                    class="flex items-center gap-space-xs font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors"
                    data-path="beranda"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span>Kembali ke Beranda</span>
                </RouterLink>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer matching mockup -->
        <footer class="w-full bg-surface-container-low py-space-lg">
            <div class="max-w-7xl mx-auto px-margin lg:px-margin-tablet flex flex-col sm:flex-row items-center justify-between gap-space-sm text-center sm:text-left">
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                    &copy; {{ new Date().getFullYear() }} Ilmora Academy. Seluruh hak cipta dilindungi undang-undang.
                </p>
                <div class="flex items-center gap-space-md font-label-sm text-label-sm text-on-surface-variant">
                    <RouterLink to="/syarat-dan-ketentuan" class="hover:text-on-surface transition-colors" data-path="syarat-dan-ketentuan">Syarat & Ketentuan</RouterLink>
                    <RouterLink to="/kebijakan-privasi" class="hover:text-on-surface transition-colors" data-path="kebijakan-privasi">Kebijakan Privasi</RouterLink>
                    <RouterLink to="/bantuan" class="hover:text-on-surface transition-colors" data-path="bantuan">Pusat Bantuan</RouterLink>
                </div>
            </div>
        </footer>
    </div>
</template>