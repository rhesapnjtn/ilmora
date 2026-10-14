<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import api from '@/lib/api'

interface Certificate {
    id: string
    courseSlug: string
    courseTitle: string
    level: string
    hours: number
    issuedAt: string
    instructor: string
}

const certificates = ref<Certificate[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(value))
}

async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get<{ certificates: Certificate[] }>('/certificates')
        certificates.value = data.certificates
    } catch {
        error.value = 'Gagal memuat daftar sertifikat.'
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>

<template>
    <AppLayout>
        <section class="animate-fade-in">
            <h1 class="text-display-sm font-bold tracking-tight">Sertifikat Saya</h1>
            <p class="mt-2 text-body-lg text-on-surface-variant">Sertifikat yang telah kamu raih dari kursus yang diselesaikan.</p>
        </section>

        <p v-if="error" class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

        <div v-if="loading" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="n in 3" :key="n" class="h-40 animate-pulse rounded-2xl bg-surface-container-high" />
        </div>

        <div v-else-if="certificates.length === 0" class="mt-8">
            <div class="card flex flex-col items-center justify-center py-16 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-surface-container-high text-on-surface-variant">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="8" r="5" /><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5" />
                    </svg>
                </div>
                <h3 class="mt-4 text-title-md font-semibold">Belum ada sertifikat</h3>
                <p class="mt-2 max-w-sm text-body-md text-on-surface-variant">Selesaikan kursus pertamamu untuk mendapatkan sertifikat penyelesaian.</p>
                <RouterLink to="/kursus" class="btn-primary mt-6">Jelajahi Kursus</RouterLink>
            </div>
        </div>

        <div v-else class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <RouterLink
                v-for="certificate in certificates"
                :key="certificate.id"
                :to="`/sertifikat/${certificate.id}`"
                class="card-hover card flex flex-col"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#FEF3C7] text-[#D97706]">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="8" r="5" /><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5" />
                    </svg>
                </div>
                <h3 class="mt-4 line-clamp-2 text-title-md font-semibold">{{ certificate.courseTitle }}</h3>
                <p class="mt-2 text-body-sm text-on-surface-variant">{{ formatDate(certificate.issuedAt) }}</p>
                <span class="link mt-4 text-body-sm">Lihat sertifikat</span>
            </RouterLink>
        </div>
    </AppLayout>
</template>
