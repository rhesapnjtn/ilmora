<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import api from '@/lib/api'
import { normalizeApiError } from '@/lib/errors'
import { resolveVideo } from '@/lib/video'

interface LessonItem { id: string; title: string; duration: string; module?: string }
interface Lesson extends LessonItem { index: number; total: number; description: string; code: string; videoUrl: string | null; materials: { label: string; size: string; type: string }[] }
interface DiscussionUser { id: number; name: string; avatarUrl: string | null; role: 'user' | 'admin' }
interface Reply { id: number; body: string; isAnswer: boolean; createdAt: string; updatedAt: string; user: DiscussionUser; canUpdate: boolean; canDelete: boolean }
interface Discussion extends Reply { hasAcceptedAnswer: boolean; replies: Reply[] }
interface DiscussionMeta { currentPage: number; lastPage: number; perPage: number; total: number; hasMore: boolean }
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

const discussions = ref<Discussion[]>([])
const discussionsLoading = ref(false)
const discussionsLoadingMore = ref(false)
const discussionsError = ref<string | null>(null)
const discussionsLoaded = ref(false)
const discussionsMeta = ref<DiscussionMeta>({ currentPage: 1, lastPage: 1, perPage: 10, total: 0, hasMore: false })
const deleteTarget = ref<{ kind: 'discussion'; id: number } | { kind: 'reply'; parentId: number; id: number } | null>(null)
const deleteError = ref<string | null>(null)
const deleting = ref(false)
const answerSavingId = ref<number | null>(null)
const discussionDraft = ref('')
const discussionSubmitting = ref(false)
const discussionFormError = ref<string | null>(null)
const editingDiscussionId = ref<number | null>(null)
const editDraft = ref('')
const editSaving = ref(false)
const replyingToId = ref<number | null>(null)
const replyDraft = ref('')
const replySubmitting = ref(false)
const replyEditingId = ref<number | null>(null)
const replyEditDraft = ref('')
const replyEditSaving = ref(false)

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

onMounted(async () => {
    await load()

    if (!notFound.value && route.query.tab === 'diskusi') {
        activeTab.value = 'discussion'
    }
})
watch(() => route.params.lesson, async () => {
    await load()

    discussions.value = []
    discussionsLoaded.value = false
    discussionsMeta.value = { currentPage: 1, lastPage: 1, perPage: 10, total: 0, hasMore: false }
    deleteTarget.value = null
    replyingToId.value = null
    replyEditingId.value = null
    editingDiscussionId.value = null

    if (activeTab.value === 'discussion') {
        loadDiscussions()
    }
})
watch(activeTab, (tab) => {
    if (tab === 'discussion' && !discussionsLoaded.value) {
        loadDiscussions()
    }
})

function discussionUrl(): string {
    return `/courses/${route.params.slug}/lessons/${route.params.lesson}/discussions`
}

function formatDateTime(value: string | null | undefined): string {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(value))
}

function formatRelative(value: string | null | undefined): string {
    if (!value) {
        return '-'
    }

    const diffMs = Date.now() - new Date(value).getTime()
    const minutes = Math.floor(diffMs / 60000)

    if (minutes < 1) return 'Baru saja'
    if (minutes < 60) return `${minutes} menit lalu`

    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours} jam lalu`

    const days = Math.floor(hours / 24)
    if (days < 30) return `${days} hari lalu`

    return formatDateTime(value)
}

function isEdited(item: { createdAt: string; updatedAt: string }): boolean {
    if (!item.createdAt || !item.updatedAt) return false

    return new Date(item.updatedAt).getTime() - new Date(item.createdAt).getTime() > 1000
}

async function loadDiscussions(page = 1, append = false): Promise<void> {
    if (append) {
        discussionsLoadingMore.value = true
    } else {
        discussionsLoading.value = true
    }

    discussionsError.value = null

    try {
        const { data } = await api.get<{ discussions: Discussion[]; meta: DiscussionMeta }>(discussionUrl(), {
            params: { page, per_page: 10 },
        })

        discussions.value = append ? [...discussions.value, ...data.discussions] : data.discussions
        discussionsMeta.value = data.meta
        discussionsLoaded.value = true
    } catch (err) {
        discussionsError.value = normalizeApiError(err).message ?? 'Gagal memuat diskusi.'
    } finally {
        discussionsLoading.value = false
        discussionsLoadingMore.value = false
    }
}

function loadMoreDiscussions(): void {
    if (discussionsLoadingMore.value || !discussionsMeta.value.hasMore) return

    loadDiscussions(discussionsMeta.value.currentPage + 1, true)
}

async function submitDiscussion(): Promise<void> {
    const body = discussionDraft.value.trim()

    if (!body) return

    discussionSubmitting.value = true
    discussionFormError.value = null

    try {
        const { data } = await api.post<{ discussion: Discussion }>(discussionUrl(), { body })
        discussions.value = [data.discussion, ...discussions.value]
        discussionsMeta.value = { ...discussionsMeta.value, total: discussionsMeta.value.total + 1 }
        discussionDraft.value = ''
    } catch (err) {
        discussionFormError.value = normalizeApiError(err).message ?? 'Gagal mengirim diskusi.'
    } finally {
        discussionSubmitting.value = false
    }
}

function startEdit(discussion: Discussion): void {
    editingDiscussionId.value = discussion.id
    editDraft.value = discussion.body
}

function cancelEdit(): void {
    editingDiscussionId.value = null
    editDraft.value = ''
}

async function saveEdit(id: number): Promise<void> {
    const body = editDraft.value.trim()

    if (!body) return

    editSaving.value = true
    discussionFormError.value = null

    try {
        const { data } = await api.put<{ discussion: Reply }>(`${discussionUrl()}/${id}`, { body })
        discussions.value = discussions.value.map((item) => (item.id === id ? { ...item, ...data.discussion, replies: item.replies } : item))
        editingDiscussionId.value = null
    } catch (err) {
        discussionFormError.value = normalizeApiError(err).message ?? 'Gagal memperbarui diskusi.'
    } finally {
        editSaving.value = false
    }
}

async function removeDiscussion(id: number): Promise<string | null> {
    try {
        await api.delete(`${discussionUrl()}/${id}`)
        discussions.value = discussions.value.filter((item) => item.id !== id)
        discussionsMeta.value = { ...discussionsMeta.value, total: Math.max(0, discussionsMeta.value.total - 1) }

        return null
    } catch (err) {
        const message = normalizeApiError(err).message ?? 'Gagal menghapus diskusi.'
        discussionFormError.value = message

        return message
    }
}

async function startReply(id: number): Promise<void> {
    editingDiscussionId.value = null
    replyingToId.value = id
    replyDraft.value = ''

    await nextTick()
    document.getElementById(`reply-${id}`)?.focus()
}

function cancelReply(): void {
    replyingToId.value = null
    replyDraft.value = ''
}

async function submitReply(parentId: number): Promise<void> {
    const body = replyDraft.value.trim()

    if (!body) return

    replySubmitting.value = true
    discussionFormError.value = null

    try {
        const { data } = await api.post<{ discussion: Reply }>(discussionUrl(), { body, parent_id: parentId })
        const parent = discussions.value.find((item) => item.id === parentId)

        if (parent) {
            parent.replies = [...parent.replies, data.discussion]
        }

        replyDraft.value = ''
        replyingToId.value = null
    } catch (err) {
        discussionFormError.value = normalizeApiError(err).message ?? 'Gagal mengirim balasan.'
    } finally {
        replySubmitting.value = false
    }
}

async function startReplyEdit(reply: Reply): Promise<void> {
    replyEditingId.value = reply.id
    replyEditDraft.value = reply.body

    await nextTick()
    document.getElementById(`edit-reply-${reply.id}`)?.focus()
}

function cancelReplyEdit(): void {
    replyEditingId.value = null
    replyEditDraft.value = ''
}

async function saveReplyEdit(parentId: number, replyId: number): Promise<void> {
    const body = replyEditDraft.value.trim()

    if (!body) return

    replyEditSaving.value = true
    discussionFormError.value = null

    try {
        const { data } = await api.put<{ discussion: Reply }>(`${discussionUrl()}/${replyId}`, { body })
        const parent = discussions.value.find((item) => item.id === parentId)

        if (parent) {
            parent.replies = parent.replies.map((item) => (item.id === replyId ? data.discussion : item))
        }

        replyEditingId.value = null
    } catch (err) {
        discussionFormError.value = normalizeApiError(err).message ?? 'Gagal memperbarui balasan.'
    } finally {
        replyEditSaving.value = false
    }
}

async function removeReply(parentId: number, replyId: number): Promise<string | null> {
    try {
        await api.delete(`${discussionUrl()}/${replyId}`)
        const parent = discussions.value.find((item) => item.id === parentId)

        if (parent) {
            parent.replies = parent.replies.filter((item) => item.id !== replyId)
            parent.hasAcceptedAnswer = parent.replies.some((item) => item.isAnswer)
        }

        return null
    } catch (err) {
        const message = normalizeApiError(err).message ?? 'Gagal menghapus balasan.'
        discussionFormError.value = message

        return message
    }
}

function requestDeleteDiscussion(id: number): void {
    deleteError.value = null
    deleteTarget.value = { kind: 'discussion', id }
}

function requestDeleteReply(parentId: number, replyId: number): void {
    deleteError.value = null
    deleteTarget.value = { kind: 'reply', parentId, id: replyId }
}

async function confirmDelete(): Promise<void> {
    const target = deleteTarget.value

    if (!target) return

    deleting.value = true
    deleteError.value = null
    discussionFormError.value = null

    try {
        const error = target.kind === 'discussion'
            ? await removeDiscussion(target.id)
            : await removeReply(target.parentId, target.id)

        if (error === null) {
            deleteTarget.value = null
        } else {
            deleteError.value = error
        }
    } finally {
        deleting.value = false
    }
}

async function toggleAnswer(discussion: Discussion, reply: Reply): Promise<void> {
    answerSavingId.value = reply.id
    discussionFormError.value = null

    try {
        const url = `${discussionUrl()}/${reply.id}/accept`

        if (reply.isAnswer) {
            await api.delete(url)
        } else {
            await api.post(url)
        }

        const parent = discussions.value.find((item) => item.id === discussion.id)

        if (!parent) return

        parent.replies = parent.replies.map((item) => ({
            ...item,
            isAnswer: reply.isAnswer ? false : item.id === reply.id,
        }))
        parent.hasAcceptedAnswer = parent.replies.some((item) => item.isAnswer)
    } catch (err) {
        discussionFormError.value = normalizeApiError(err).message ?? 'Gagal mengubah jawaban terbaik.'
    } finally {
        answerSavingId.value = null
    }
}
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

                    <div v-else class="mt-6 space-y-6">
                        <section class="card">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary" aria-hidden="true">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-title-md font-semibold">Tulis Diskusi</h2>
                                    <p class="text-body-sm text-on-surface-variant">Ajukan pertanyaan atau bagikan pemahamanmu tentang pelajaran ini.</p>
                                </div>
                            </div>

                            <label for="discussion-body" class="label mt-5">Pesan</label>
                            <textarea
                                id="discussion-body"
                                v-model="discussionDraft"
                                rows="3"
                                maxlength="5000"
                                class="mt-2 w-full rounded-2xl border border-outline bg-surface p-4 text-body-md text-on-surface placeholder:text-on-surface-variant focus:border-primary focus:ring-2 focus:ring-primary/20"
                                placeholder="Tulis diskusi singkat dan jelas..."
                            />

                            <div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-body-sm text-on-surface-variant">{{ discussionDraft.length }}/5000</span>
                                <button type="button" class="btn-primary" :disabled="discussionSubmitting || !discussionDraft.trim()" @click="submitDiscussion">
                                    {{ discussionSubmitting ? 'Mengirim...' : 'Kirim Diskusi' }}
                                </button>
                            </div>

                            <p v-if="discussionFormError" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ discussionFormError }}</p>
                        </section>

                        <section>
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-title-md font-semibold">Semua Diskusi</h2>
                                <span class="badge-outline">{{ discussionsMeta.total }} diskusi</span>
                            </div>

                            <div v-if="discussionsLoading" class="mt-4 space-y-4">
                                <div v-for="n in 3" :key="n" class="h-28 animate-pulse rounded-2xl bg-surface-container-high" />
                            </div>

                            <p v-else-if="discussionsError" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ discussionsError }}</p>

                            <div v-else-if="discussions.length === 0" class="mt-4 rounded-2xl border border-dashed border-outline-variant bg-surface-container-lowest px-6 py-12 text-center">
                                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary" aria-hidden="true">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                                    </svg>
                                </span>
                                <p class="mt-4 text-title-md font-semibold text-on-surface">Belum ada diskusi</p>
                                <p class="mt-1 text-body-sm text-on-surface-variant">Jadilah yang pertama bertanya atau berbagi pemahamanmu.</p>
                            </div>

                            <ol v-else class="mt-4 space-y-6">
                                <li v-for="discussion in discussions" :key="discussion.id" class="space-y-3">
                                    <article class="card">
                                        <div class="flex items-start gap-3.5">
                                            <img
                                                v-if="discussion.user.avatarUrl"
                                                :src="discussion.user.avatarUrl"
                                                :alt="`Foto ${discussion.user.name}`"
                                                class="h-11 w-11 shrink-0 rounded-full object-cover ring-2 ring-outline-variant"
                                            />
                                            <span v-else class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-label-lg font-bold text-primary ring-2 ring-outline-variant">
                                                {{ discussion.user.name.charAt(0) }}
                                            </span>

                                            <div class="min-w-0 flex-1">
                                                <header class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                    <span class="text-body-md font-semibold text-on-surface">{{ discussion.user.name }}</span>
                                                    <span v-if="discussion.user.role === 'admin'" class="badge-secondary">Pengajar</span>
                                                    <span v-if="discussion.hasAcceptedAnswer" class="badge-success">Terjawab</span>
                                                    <span class="text-body-sm text-on-surface-variant" :title="formatDateTime(discussion.createdAt)">{{ formatRelative(discussion.createdAt) }}</span>
                                                    <span v-if="isEdited(discussion)" class="text-body-sm text-on-surface-variant">(diedit)</span>
                                                </header>

                                                <template v-if="editingDiscussionId === discussion.id">
                                                    <label :for="`edit-discussion-${discussion.id}`" class="label mt-3">Edit pesan</label>
                                                    <textarea
                                                        :id="`edit-discussion-${discussion.id}`"
                                                        v-model="editDraft"
                                                        rows="3"
                                                        maxlength="5000"
                                                        class="mt-2 w-full rounded-2xl border border-outline bg-surface p-3 text-body-md text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/20"
                                                    />
                                                    <div class="mt-3 flex justify-end gap-2">
                                                        <button type="button" class="btn-secondary" @click="cancelEdit">Batal</button>
                                                        <button type="button" class="btn-primary" :disabled="editSaving || !editDraft.trim()" @click="saveEdit(discussion.id)">
                                                            {{ editSaving ? 'Menyimpan...' : 'Simpan' }}
                                                        </button>
                                                    </div>
                                                </template>
                                                <p v-else class="mt-1.5 whitespace-pre-line text-body-md leading-relaxed text-on-surface-variant">{{ discussion.body }}</p>

                                                <footer v-if="editingDiscussionId !== discussion.id" class="mt-4 flex flex-wrap items-center gap-4 border-t border-outline-variant/60 pt-3">
                                                    <button v-if="discussion.canUpdate" type="button" class="inline-flex items-center gap-1.5 text-body-sm font-semibold text-primary transition-colors hover:text-primary/80" @click="startEdit(discussion)">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                        </svg>
                                                        Edit
                                                    </button>
                                                    <button v-if="discussion.canDelete" type="button" class="inline-flex items-center gap-1.5 text-body-sm font-semibold text-error transition-colors hover:text-error/80" @click="requestDeleteDiscussion(discussion.id)">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path d="M3 6h18" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14z" />
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                    <button type="button" class="inline-flex items-center gap-1.5 text-body-sm font-semibold text-primary transition-colors hover:text-primary/80" @click="startReply(discussion.id)">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <polyline points="9 17 4 12 9 7" /><path d="M20 18v-2a4 4 0 0 0-4-4H4" />
                                                        </svg>
                                                        Balas
                                                    </button>
                                                </footer>
                                            </div>
                                        </div>
                                    </article>

                                    <div v-if="discussion.replies.length > 0 || replyingToId === discussion.id" class="ml-5 border-l-2 border-outline-variant/50 pl-4">
                                        <p v-if="discussion.replies.length > 0" class="mb-3 text-body-sm font-semibold text-on-surface-variant">
                                            {{ discussion.replies.length }} balasan
                                        </p>

                                        <ul v-if="discussion.replies.length > 0" class="space-y-3">
                                            <li
                                                v-for="reply in discussion.replies"
                                                :key="reply.id"
                                                class="card p-4"
                                                :class="reply.isAnswer ? 'ring-1 ring-tertiary/50' : ''"
                                            >
                                                <div class="flex items-start gap-3">
                                                    <img
                                                        v-if="reply.user.avatarUrl"
                                                        :src="reply.user.avatarUrl"
                                                        :alt="`Foto ${reply.user.name}`"
                                                        class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-outline-variant"
                                                    />
                                                    <span v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-label-md font-bold text-primary ring-2 ring-outline-variant">
                                                        {{ reply.user.name.charAt(0) }}
                                                    </span>

                                                    <div class="min-w-0 flex-1">
                                                        <header class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                            <span class="text-body-md font-semibold text-on-surface">{{ reply.user.name }}</span>
                                                            <span v-if="reply.user.role === 'admin'" class="badge-secondary">Pengajar</span>
                                                            <span v-if="reply.isAnswer" class="badge-success">Jawaban terbaik</span>
                                                            <span class="text-body-sm text-on-surface-variant" :title="formatDateTime(reply.createdAt)">{{ formatRelative(reply.createdAt) }}</span>
                                                            <span v-if="isEdited(reply)" class="text-body-sm text-on-surface-variant">(diedit)</span>
                                                        </header>

                                                        <template v-if="replyEditingId === reply.id">
                                                            <label :for="`edit-reply-${reply.id}`" class="label mt-2">Edit balasan</label>
                                                            <textarea
                                                                :id="`edit-reply-${reply.id}`"
                                                                v-model="replyEditDraft"
                                                                rows="2"
                                                                maxlength="5000"
                                                                class="mt-1 w-full rounded-2xl border border-outline bg-surface p-3 text-body-md text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/20"
                                                            />
                                                            <div class="mt-2 flex justify-end gap-2">
                                                                <button type="button" class="btn-secondary" @click="cancelReplyEdit">Batal</button>
                                                                <button type="button" class="btn-primary" :disabled="replyEditSaving || !replyEditDraft.trim()" @click="saveReplyEdit(discussion.id, reply.id)">
                                                                    {{ replyEditSaving ? 'Menyimpan...' : 'Simpan' }}
                                                                </button>
                                                            </div>
                                                        </template>
                                                        <p v-else class="mt-1 whitespace-pre-line text-body-md leading-relaxed text-on-surface-variant">{{ reply.body }}</p>

                                                        <footer v-if="replyEditingId !== reply.id && (reply.canUpdate || reply.canDelete || discussion.canUpdate)" class="mt-3 flex flex-wrap items-center gap-4">
                                                            <button v-if="reply.canUpdate" type="button" class="inline-flex items-center gap-1.5 text-body-sm font-semibold text-primary transition-colors hover:text-primary/80" @click="startReplyEdit(reply)">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                                </svg>
                                                                Edit
                                                            </button>
                                                            <button v-if="reply.canDelete" type="button" class="inline-flex items-center gap-1.5 text-body-sm font-semibold text-error transition-colors hover:text-error/80" @click="requestDeleteReply(discussion.id, reply.id)">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                    <path d="M3 6h18" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14z" />
                                                                </svg>
                                                                Hapus
                                                            </button>
                                                            <button
                                                                v-if="discussion.canUpdate"
                                                                type="button"
                                                                class="inline-flex items-center gap-1.5 text-body-sm font-semibold text-tertiary transition-colors hover:text-tertiary/80 disabled:opacity-50"
                                                                :disabled="answerSavingId === reply.id"
                                                                @click="toggleAnswer(discussion, reply)"
                                                            >
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                    <path d="M20 6 9 17l-5-5" />
                                                                </svg>
                                                                {{ reply.isAnswer ? 'Batalkan jawaban' : 'Tandai jawaban' }}
                                                            </button>
                                                        </footer>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>

                                        <form v-if="replyingToId === discussion.id" class="card p-4" @submit.prevent="submitReply(discussion.id)">
                                            <label :for="`reply-${discussion.id}`" class="label">Tulis balasan</label>
                                            <textarea
                                                :id="`reply-${discussion.id}`"
                                                v-model="replyDraft"
                                                rows="2"
                                                maxlength="5000"
                                                class="mt-2 w-full rounded-2xl border border-outline bg-surface p-3 text-body-md text-on-surface placeholder:text-on-surface-variant focus:border-primary focus:ring-2 focus:ring-primary/20"
                                                placeholder="Tulis balasan singkat dan jelas..."
                                            />
                                            <div class="mt-3 flex justify-end gap-2">
                                                <button type="button" class="btn-secondary" @click="cancelReply">Batal</button>
                                                <button type="submit" class="btn-primary" :disabled="replySubmitting || !replyDraft.trim()">
                                                    {{ replySubmitting ? 'Mengirim...' : 'Kirim Balasan' }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </li>
                            </ol>

                            <div v-if="!discussionsLoading && !discussionsError && discussionsMeta.hasMore" class="mt-6 flex justify-center">
                                <button type="button" class="btn-secondary" :disabled="discussionsLoadingMore" @click="loadMoreDiscussions">
                                    {{ discussionsLoadingMore ? 'Memuat...' : 'Muat Diskusi Lainnya' }}
                                </button>
                            </div>
                        </section>
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

        <ConfirmDialog
            :model-value="deleteTarget !== null"
            :title="deleteTarget?.kind === 'reply' ? 'Hapus Balasan?' : 'Hapus Diskusi?'"
            confirm-label="Hapus"
            cancel-label="Batal"
            variant="danger"
            icon="trash"
            :loading="deleting"
            @update:model-value="deleteTarget = null"
            @confirm="confirmDelete"
        >
            <p>
                {{
                    deleteTarget?.kind === 'reply'
                        ? 'Balasan ini akan dihapus permanen dan tidak dapat dikembalikan.'
                        : 'Diskusi ini beserta seluruh balasannya akan dihapus permanen dan tidak dapat dikembalikan.'
                }}
            </p>
            <p v-if="deleteError" class="mt-2 text-body-sm text-error" role="alert">{{ deleteError }}</p>
        </ConfirmDialog>
    </AppLayout>
</template>
