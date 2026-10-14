<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
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
    recipient: string
}

const route = useRoute()
const certificate = ref<Certificate | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const notFound = ref(false)

const formattedDate = computed(() =>
    certificate.value
        ? new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(certificate.value.issuedAt))
        : '',
)

async function load(): Promise<void> {
    loading.value = true
    error.value = null
    notFound.value = false
    try {
        const { data } = await api.get<{ certificate: Certificate }>(`/certificates/${route.params.id}`)
        certificate.value = data.certificate
    } catch (err) {
        const status = (err as { response?: { status?: number } }).response?.status
        if (status === 404) notFound.value = true
        else error.value = 'Gagal memuat sertifikat.'
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>

<template>
    <AppLayout>
        <div v-if="loading" class="mx-auto max-w-4xl">
            <div class="aspect-[1.4] w-full animate-pulse rounded-2xl bg-surface-container-high" />
        </div>

        <div v-else-if="notFound" class="mx-auto flex max-w-md flex-col items-center px-4 py-20 text-center">
            <h1 class="text-headline-md font-bold">Sertifikat tidak ditemukan</h1>
            <RouterLink to="/sertifikat" class="btn-primary mt-6">Kembali ke Sertifikat</RouterLink>
        </div>

        <p v-else-if="error" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

        <div v-else-if="certificate" class="mx-auto max-w-4xl">
            <div class="overflow-hidden rounded-2xl border-[3px] border-primary/20 bg-white p-6 shadow-modal md:p-10">
                <div class="rounded-2xl border border-[#D97706]/40 p-6 text-center md:p-10">
                    <div class="flex items-center justify-center gap-2 text-primary">
                        <svg class="h-8 w-8" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <rect width="32" height="32" rx="8" fill="currentColor" />
                            <path d="M8 12h16M8 16h12M8 20h8" stroke="white" stroke-width="2.5" stroke-linecap="round" />
                        </svg>
                        <span class="text-headline-md font-bold tracking-tight">Ilmora</span>
                    </div>

                    <p class="mt-8 text-label-lg font-semibold uppercase tracking-[0.3em] text-[#D97706]">Sertifikat Penyelesaian</p>
                    <p class="mt-8 text-body-md text-on-surface-variant">Diberikan kepada</p>
                    <p class="mt-2 text-display-sm font-bold tracking-tight text-on-surface">{{ certificate.recipient }}</p>
                    <p class="mx-auto mt-4 max-w-md text-body-lg text-on-surface-variant">telah menyelesaikan kursus</p>
                    <p class="mt-2 text-headline-sm font-bold text-primary">{{ certificate.courseTitle }}</p>

                    <div class="mt-10 flex flex-col items-center justify-between gap-6 text-left sm:flex-row">
                        <div>
                            <p class="text-title-lg font-semibold text-on-surface">{{ formattedDate }}</p>
                            <p class="text-body-sm text-on-surface-variant">Tanggal terbit</p>
                        </div>
                        <div class="text-center sm:text-right">
                            <p class="text-title-lg font-semibold italic text-on-surface">{{ certificate.instructor }}</p>
                            <p class="text-body-sm text-on-surface-variant">Instruktur</p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-6 text-center text-title-md font-semibold text-tertiary">
                Selamat! Kamu telah menyelesaikan kursus dengan sangat baik!
            </p>

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <RouterLink :to="`/kursus/${certificate.courseSlug}`" class="btn-secondary">Kembali ke Kursus</RouterLink>
                <button type="button" class="btn-primary">Download</button>
                <button type="button" class="btn-secondary">Bagikan</button>
            </div>
        </div>
    </AppLayout>
</template>
