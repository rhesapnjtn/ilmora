<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import api from '@/lib/api'

interface NoteItem {
    id: number
    content: string
    updatedAt: string | null
    lesson: { slug: string; title: string }
    course: { slug: string; title: string }
}

const notes = ref<NoteItem[]>([])
const loading = ref(true)

onMounted(async () => {
    try {
        const { data } = await api.get<{ notes: NoteItem[] }>('/notes')
        notes.value = data.notes
    } catch {
        notes.value = []
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-4xl">
            <h1 class="text-headline-md font-bold">Catatan Saya</h1>
            <p class="mt-2 text-body-md text-on-surface-variant">Semua catatan pribadi yang tersimpan per pelajaran.</p>

            <div v-if="loading" class="mt-6 space-y-3">
                <div v-for="n in 3" :key="n" class="h-24 animate-pulse rounded-2xl bg-surface-container-high" />
            </div>

            <div v-else-if="notes.length === 0" class="mt-6 rounded-2xl border border-dashed border-outline-variant bg-surface-container-lowest px-6 py-12 text-center">
                <p class="text-title-md font-semibold text-on-surface">Belum ada catatan</p>
                <p class="mt-1 text-body-sm text-on-surface-variant">Tulis catatan di ruang belajar untuk menyimpannya di sini.</p>
            </div>

            <ul v-else class="mt-6 space-y-4">
                <li v-for="note in notes" :key="note.id" class="card">
                    <RouterLink :to="`/belajar/${note.course.slug}/${note.lesson.slug}`" class="text-title-md font-semibold text-primary hover:underline">
                        {{ note.lesson.title }}
                    </RouterLink>
                    <p class="text-body-sm text-on-surface-variant">{{ note.course.title }}</p>
                    <p class="mt-3 whitespace-pre-line text-body-md text-on-surface">{{ note.content }}</p>
                    <p v-if="note.updatedAt" class="mt-2 text-body-sm text-on-surface-variant">Diperbarui: {{ note.updatedAt }}</p>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>