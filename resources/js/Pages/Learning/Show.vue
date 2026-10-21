<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import api from '@/lib/api'
import { resolveVideo } from '@/lib/video'

interface LessonItem { id: string; title: string; duration: string; module?: string }
interface Lesson extends LessonItem { index: number; total: number; description: string; code: string; videoUrl: string | null; materials: { label: string; size: string; type: string }[] }
interface Payload {
    course: { slug: string; title: string; level: string; lessonsCount: number; instructor: { name: string; role: string } }
    lesson: Lesson
    lessons: LessonItem[]
    prev: string | null
    next: string | null
    progress: number
}

const route = useRoute()
const data = ref<Payload | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const notFound = ref(false)
const activeTab = ref<'description' | 'notes' | 'discussion'>('description')
const note = ref('')

const completedCount = computed(() => data.value?.lesson.index ?? 0)

const video = computed(() => resolveVideo(data.value?.lesson.videoUrl))

async function load(): Promise<void> {
    loading.value = true
    error.value = null
    notFound.value = false
    try {
        const { data: payload } = await api.get<Payload>(`/courses/${route.params.slug}/lessons/${route.params.lesson}`)
        data.value = payload
    } catch (err) {
        const status = (err as { response?: { status?: number } }).response?.status
        if (status === 404) notFound.value = true
        else error.value = 'Gagal memuat materi pelajaran.'
    } finally {
        loading.value = false
    }
}

onMounted(load)
watch(() => route.params.lesson, load)
</script>

<template>
    <AppLayout>
        <div v-if="loading" class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="aspect-video w-full animate-pulse rounded-2xl bg-surface-container-high" />
                <div class="h-8 w-2/3 animate-pulse rounded bg-surface-container-high" />
            </div>
            <div class="h-96 animate-pulse rounded-2xl bg-surface-container-high" />
        </div>

        <div v-else-if="notFound" class="mx-auto flex max-w-md flex-col items-center px-4 py-20 text-center">
            <h1 class="text-headline-md font-bold">Pelajaran tidak ditemukan</h1>
            <RouterLink to="/dashboard" class="btn-primary mt-6">Kembali ke Dashboard</RouterLink>
        </div>

        <p v-else-if="error" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

        <div v-else-if="data" class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="relative overflow-hidden rounded-2xl bg-on-surface">
                    <iframe
                        v-if="video?.kind === 'embed'"
                        :src="video.src"
                        class="aspect-video w-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        title="Video pelajaran"
                    />
                    <video
                        v-else-if="video?.kind === 'file'"
                        :src="video.src"
                        class="aspect-video w-full"
                        controls
                        preload="metadata"
                    />
                    <div v-else class="flex aspect-video flex-col items-center justify-center gap-3 bg-gradient-to-br from-[#1E1B4B] to-[#4C1D95]">
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/10 text-white/80" aria-hidden="true">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M15.6 4.6 12 2 8.4 4.6 4.8 5.4 2.6 9l3 3.4-1 3.8 3.2-2 3 2-1-3.8 3-3.4-2.2-3.4 2.2-2.4z" />
                            </svg>
                        </span>
                        <p class="text-body-sm font-medium text-white/80">Video untuk pelajaran ini belum tersedia.</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <span class="badge-primary">Modul {{ data.lesson.module }}</span>
                    <span class="badge-primary">{{ data.course.level }}</span>
                </div>
                <h1 class="mt-3 text-headline-md font-bold tracking-tight">{{ data.lesson.title }}</h1>

                <div class="mt-6 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 text-label-lg font-bold text-primary">
                        {{ data.course.instructor.name.charAt(0) }}
                    </div>
                    <div>
                        <p class="text-body-md font-semibold">{{ data.course.instructor.name }}</p>
                        <p class="text-body-sm text-on-surface-variant">{{ data.course.instructor.role }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <div class="flex gap-2 border-b border-outline-variant" role="tablist">
                        <button
                            v-for="tab in ([['description', 'Deskripsi'], ['notes', 'Catatan'], ['discussion', 'Diskusi']] as const)"
                            :key="tab[0]"
                            type="button" role="tab" :aria-selected="activeTab === tab[0]"
                            class="-mb-px border-b-2 px-4 py-3 text-label-lg font-semibold transition-colors"
                            :class="activeTab === tab[0] ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                            @click="activeTab = tab[0]"
                        >
                            {{ tab[1] }}
                        </button>
                    </div>

                    <div v-if="activeTab === 'description'" class="mt-6">
                        <p class="text-body-md leading-relaxed text-on-surface-variant">{{ data.lesson.description }}</p>
                        <pre class="mt-5 overflow-x-auto rounded-2xl bg-[#1E1B4B] p-5 text-body-sm leading-relaxed text-[#E9D5FF]"><code>{{ data.lesson.code }}</code></pre>

                        <div class="mt-6">
                            <h2 class="text-title-md font-semibold">File Materi</h2>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <button
                                    v-for="material in data.lesson.materials"
                                    :key="material.label"
                                    type="button"
                                    class="flex items-center gap-3 rounded-2xl border border-outline-variant bg-surface-container-lowest px-4 py-3 text-left transition-colors hover:bg-surface-container-low"
                                >
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M7 10l5 5 5-5M12 15V3" />
                                        </svg>
                                    </span>
                                    <span>
                                        <span class="block text-body-md font-semibold">Download {{ material.label }}</span>
                                        <span class="block text-body-sm text-on-surface-variant">{{ material.size }}</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="activeTab === 'notes'" class="mt-6">
                        <label for="note" class="label">Catatan pribadi</label>
                        <textarea
                            id="note"
                            v-model="note"
                            rows="6"
                            class="mt-2 w-full rounded-2xl border border-outline bg-surface p-4 text-body-md text-on-surface placeholder:text-on-surface-variant focus:border-primary focus:ring-2 focus:ring-primary/20"
                            placeholder="Tulis catatanmu untuk pelajaran ini..."
                        />
                    </div>

                    <div v-else class="mt-6 rounded-2xl border border-dashed border-outline px-6 py-12 text-center">
                        <p class="text-body-md text-on-surface-variant">Belum ada diskusi pada pelajaran ini.</p>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between gap-3">
                    <RouterLink
                        v-if="data.prev"
                        :to="`/belajar/${data.course.slug}/${data.prev}`"
                        class="btn-secondary"
                    >
                        Sebelumnya
                    </RouterLink>
                    <span v-else />
                    <RouterLink
                        v-if="data.next"
                        :to="`/belajar/${data.course.slug}/${data.next}`"
                        class="btn-primary"
                    >
                        Selanjutnya
                    </RouterLink>
                </div>
            </div>

            <aside>
                <div class="card sticky top-24">
                    <div class="flex items-center justify-between">
                        <h2 class="text-title-lg font-semibold">Daftar Materi</h2>
                        <span class="text-body-sm text-on-surface-variant">{{ completedCount }}/{{ data.lesson.total }}</span>
                    </div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-surface-container-high">
                        <div class="h-full rounded-full bg-primary" :style="{ width: `${(completedCount / data.lesson.total) * 100}%` }" />
                    </div>

                    <ul class="mt-5 space-y-1">
                        <li v-for="(item, i) in data.lessons" :key="item.id">
                            <RouterLink
                                :to="`/belajar/${data.course.slug}/${item.id}`"
                                class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors"
                                :class="i + 1 === data.lesson.index ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low'"
                            >
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-label-sm font-bold"
                                    :class="i + 1 < data.lesson.index ? 'bg-tertiary/15 text-tertiary' : i + 1 === data.lesson.index ? 'bg-primary text-white' : 'bg-surface-container-high text-on-surface-variant'"
                                >
                                    <svg v-if="i + 1 < data.lesson.index" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M20 6L9 17l-5-5" /></svg>
                                    <template v-else>{{ i + 1 }}</template>
                                </span>
                                <span class="flex-1 text-body-md">{{ item.title }}</span>
                                <span class="text-body-sm text-on-surface-variant">{{ item.duration }}</span>
                            </RouterLink>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>
