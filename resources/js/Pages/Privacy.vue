<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue'

const sections = [
    {
        title: 'Informasi yang Kami Kumpulkan',
        body: 'Kami mengumpulkan informasi yang Anda berikan secara langsung, seperti nama dan alamat email saat mendaftar, serta data aktivitas belajar yang diperlukan untuk menjalankan layanan.',
    },
    {
        title: 'Penggunaan Informasi',
        body: 'Informasi digunakan untuk menyediakan, memelihara, dan meningkatkan layanan, memberikan dukungan, serta menyampaikan informasi penting terkait akun dan langganan Anda.',
    },
    {
        title: 'Keamanan Data',
        body: 'Kami menerapkan langkah teknis dan organisasi yang wajar untuk melindungi data Anda dari akses, perubahan, atau pengungkapan yang tidak sah.',
    },
    {
        title: 'Berbagi Informasi',
        body: 'Kami tidak menjual data pribadi Anda. Informasi hanya dibagikan kepada pihak yang diperlukan untuk mengoperasikan layanan dan sesuai ketentuan hukum yang berlaku.',
    },
    {
        title: 'Hak Anda',
        body: 'Anda dapat mengakses, memperbarui, atau meminta penghapusan data pribadi Anda melalui halaman profil atau dengan menghubungi kami.',
    },
]
</script>

<template>
    <GuestLayout>
        <article class="mx-auto max-w-3xl px-4 py-12 md:py-16">
            <h1 class="text-display-sm font-bold tracking-tight">Kebijakan Privasi</h1>
            <p class="mt-3 text-body-lg text-on-surface-variant">
                Kami menghargai privasi Anda dan berkomitmen melindungi data pribadi pengguna Ilmora.
            </p>

            <div class="mt-10 space-y-8">
                <section v-for="section in sections" :key="section.title">
                    <h2 class="text-title-lg font-semibold">{{ section.title }}</h2>
                    <p class="mt-2 text-body-md leading-relaxed text-on-surface-variant">{{ section.body }}</p>
                </section>
            </div>
        </article>
    </GuestLayout>
</template>
