<script setup lang="ts">
import { RouterLink } from 'vue-router'
import GuestLayout from '@/Layouts/GuestLayout.vue'
</script>

<template>
    <GuestLayout>
        <div class="mx-auto flex max-w-md flex-col items-center px-4 py-20 text-center md:py-28">
            <span class="text-display-lg font-bold text-primary">404</span>
            <h1 class="mt-4 text-headline-lg font-bold tracking-tight">Halaman tidak ditemukan</h1>
            <p class="mt-2 text-body-md text-on-surface-variant">
                Halaman yang Anda cari tidak tersedia atau telah dipindahkan.
            </p>
            <RouterLink to="/" class="btn-primary mt-8">
                Kembali ke Beranda
            </RouterLink>
        </div>
    </GuestLayout>
</template>
