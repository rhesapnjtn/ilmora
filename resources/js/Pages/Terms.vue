<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue'

const sections = [
    {
        title: 'Penerimaan Ketentuan',
        body: 'Dengan mengakses dan menggunakan Ilmora, Anda menyetujui syarat dan ketentuan yang berlaku. Jika Anda tidak menyetujui ketentuan ini, mohon untuk tidak menggunakan layanan.',
    },
    {
        title: 'Akun Pengguna',
        body: 'Anda bertanggung jawab menjaga kerahasiaan kredensial akun dan seluruh aktivitas yang terjadi pada akun Anda. Sampaikan kepada kami apabila Anda mengetahui adanya penggunaan yang tidak sah.',
    },
    {
        title: 'Penggunaan Layanan',
        body: 'Layanan disediakan untuk keperluan pembelajaran pribadi. Anda setuju untuk tidak menyalahgunakan, menyalin, atau mendistribusikan materi tanpa izin yang sah.',
    },
    {
        title: 'Langganan',
        body: 'Beberapa fitur memerlukan langganan aktif. Ketentuan paket, masa berlaku, dan hak akses mengikuti informasi yang ditampilkan pada halaman langganan.',
    },
    {
        title: 'Perubahan Ketentuan',
        body: 'Kami dapat memperbarui ketentuan ini dari waktu ke waktu. Perubahan berlaku sejak dipublikasikan pada halaman ini.',
    },
]
</script>

<template>
    <GuestLayout>
        <article class="mx-auto max-w-3xl px-4 py-12 md:py-16">
            <h1 class="text-display-sm font-bold tracking-tight">Syarat Layanan</h1>
            <p class="mt-3 text-body-lg text-on-surface-variant">
                Ketentuan berikut mengatur penggunaan layanan pembelajaran Ilmora.
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
