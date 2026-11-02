<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import api from '@/lib/api'
import { normalizeApiError } from '@/lib/errors'

interface ChatAttachment { id: number; name: string; url: string; mimeType: string | null; size: number | null }
interface ChatMessage {
    id: number
    senderId: number
    senderName: string | null
    isAdmin: boolean
    body: string
    read: boolean
    createdAt: string | null
    attachments?: ChatAttachment[]
}

interface Conversation {
    id: number
    topic: string
    subject: string | null
    status: 'open' | 'closed'
    csName: string | null
    lastMessage: string | null
    lastMessageAt: string | null
    unreadCount: number
    createdAt: string | null
    messages?: ChatMessage[]
}

interface Topic {
    code: string
    label: string
}

const route = useRoute()
const routeConversationId = computed(() => Number(route.params.id) || null)

const topics = ref<Topic[]>([])
const conversations = ref<Conversation[]>([])
const active = ref<Conversation | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const newTopic = ref('')
const newSubject = ref('')
const newMessage = ref('')
const creating = ref(false)

const draft = ref('')
const sending = ref(false)
const polling = ref<number | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const uploading = ref(false)
const typing = ref({ user: false, cs: false })
let typingTimeout: number | null = null

async function loadTopics(): Promise<void> {
    try {
        const { data } = await api.get<{ topics: Topic[] }>('/chat/topics')
        topics.value = data.topics
        if (!newTopic.value && topics.value.length) newTopic.value = topics.value[0].code
    } catch {
        // ignore
    }
}

async function loadConversations(): Promise<void> {
    loading.value = true
    error.value = null
    try {
        const { data } = await api.get<{ conversations: Conversation[] }>('/chat/conversations')
        conversations.value = data.conversations
        if (active.value) {
            const updated = conversations.value.find((c) => c.id === active.value!.id)
            if (updated) active.value = { ...active.value, ...updated }
        }
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal memuat percakapan.'
    } finally {
        loading.value = false
    }
}

async function openConversation(conv: Conversation): Promise<void> {
    active.value = conv
    try {
        const { data } = await api.get<{ conversation: Conversation }>(`/chat/conversations/${conv.id}`)
        active.value = data.conversation
        startPolling()
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal memuat percakapan.'
    }
}

function startPolling(): void {
    stopPolling()
    polling.value = window.setInterval(async () => {
        if (!active.value) return
        const lastId = active.value.messages?.length ? active.value.messages[active.value.messages.length - 1].id : 0
        try {
            const { data } = await api.get<{ messages: ChatMessage[]; status: string; typing?: { cs: boolean; user: boolean } }>(`/chat/conversations/${active.value.id}/poll`, { params: { after: lastId } })
            if (data.messages.length) {
                active.value.messages = [...(active.value.messages ?? []), ...data.messages]
            }
            active.value.status = data.status as 'open' | 'closed'
            if (data.typing) typing.value = { user: !!data.typing.user, cs: !!data.typing.cs }
        } catch {
            // ignore transient errors
        }
    }, 4000)
}

function stopPolling(): void {
    if (polling.value !== null) {
        clearInterval(polling.value)
        polling.value = null
    }
}

async function createConversation(): Promise<void> {
    if (!newTopic.value || !newMessage.value.trim()) return
    creating.value = true
    error.value = null
    try {
        const { data } = await api.post<{ conversation: Conversation }>('/chat/conversations', {
            topic: newTopic.value,
            subject: newSubject.value || null,
            message: newMessage.value.trim(),
        })
        conversations.value.unshift(data.conversation)
        active.value = data.conversation
        newMessage.value = ''
        newSubject.value = ''
        startPolling()
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal membuat percakapan.'
    } finally {
        creating.value = false
    }
}

async function sendDraft(): Promise<void> {
    if (!active.value || !draft.value.trim() || sending.value) return
    sending.value = true
    const body = draft.value.trim()
    draft.value = ''
    try {
        const { data } = await api.post<{ chatMessage: ChatMessage }>(`/chat/conversations/${active.value.id}/messages`, { body })
        active.value.messages = [...(active.value.messages ?? []), data.chatMessage]
    } catch (err) {
        draft.value = body
        error.value = normalizeApiError(err).message ?? 'Gagal mengirim pesan.'
    } finally {
        sending.value = false
    }
}

function onTyping(): void {
    if (!active.value) return
    if (typingTimeout) clearTimeout(typingTimeout)
    api.post(`/chat/conversations/${active.value.id}/typing`).catch(() => {})
    typingTimeout = window.setTimeout(() => {
        if (active.value) api.post(`/chat/conversations/${active.value.id}/stop-typing`).catch(() => {})
    }, 2000)
}

function triggerUpload(): void {
    fileInput.value?.click()
}

async function handleFile(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    if (!file || !active.value) return
    uploading.value = true
    error.value = null
    try {
        const formData = new FormData()
        formData.append('file', file)
        const { data } = await api.post<{ chatMessage: ChatMessage }>(`/chat/conversations/${active.value.id}/attachments`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        active.value.messages = [...(active.value.messages ?? []), data.chatMessage]
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal mengunggah file.'
    } finally {
        uploading.value = false
        input.value = ''
    }
}

async function closeConversation(): Promise<void> {
    if (!active.value) return
    try {
        await api.post(`/chat/conversations/${active.value.id}/close`)
        active.value.status = 'closed'
        await loadConversations()
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal menutup percakapan.'
    }
}

onMounted(async () => {
    await loadTopics()
    await loadConversations()
    if (routeConversationId.value) {
        const found = conversations.value.find((c) => c.id === routeConversationId.value)
        if (found) {
            await openConversation(found)
        } else {
            try {
                const { data } = await api.get<{ conversation: Conversation }>(`/chat/conversations/${routeConversationId.value}`)
                conversations.value.unshift(data.conversation)
                await openConversation(data.conversation)
            } catch (err) {
                error.value = normalizeApiError(err).message ?? 'Gagal memuat percakapan.'
            }
        }
    }
})

onUnmounted(stopPolling)
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-6xl">
            <div class="mb-6 flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>
                </div>
                <div>
                    <h1 class="text-display-sm font-bold tracking-tight">Bantuan & Chat CS</h1>
                    <p class="mt-1 text-body-md text-on-surface-variant">Hubungi customer service kami untuk pertanyaan atau kendala.</p>
                </div>
            </div>

            <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

            <div class="grid gap-6 lg:grid-cols-[20rem_1fr]">
                <aside class="space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
                        <div class="bg-gradient-to-r from-primary to-secondary px-5 py-4 text-white">
                            <h2 class="text-title-md font-semibold">Mulai Percakapan Baru</h2>
                            <p class="mt-1 text-body-sm opacity-90">Ceritakan kendalamu, CS kami siap membantu.</p>
                        </div>
                        <div class="space-y-3 p-5">
                            <div>
                                <label class="label">Topik</label>
                                <select v-model="newTopic" class="input h-11">
                                    <option v-for="t in topics" :key="t.code" :value="t.code">{{ t.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="label">Subjek (opsional)</label>
                                <input v-model="newSubject" type="text" class="input" placeholder="Ringkasan masalah" />
                            </div>
                            <div>
                                <label class="label">Pesan</label>
                                <textarea v-model="newMessage" rows="3" class="input min-h-20 py-2" placeholder="Jelaskan masalahmu..." />
                            </div>
                            <Button type="button" :loading="creating" :disabled="creating || !newMessage.trim()" class="w-full" @click="createConversation">
                                Mulai Chat
                            </Button>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
                        <div class="flex items-center justify-between border-b border-outline-variant/40 px-5 py-4">
                            <h2 class="text-title-md font-semibold">Percakapan Saya</h2>
                            <span class="rounded-full bg-primary/10 px-2.5 py-1 text-label-sm font-semibold text-primary">{{ conversations.length }}</span>
                        </div>
                        <div v-if="loading" class="space-y-2 p-4">
                            <div v-for="n in 3" :key="n" class="h-14 animate-pulse rounded-xl bg-surface-container-high" />
                        </div>
                        <ul v-else class="divide-y divide-outline-variant/30">
                            <li v-for="c in conversations" :key="c.id">
                                <button
                                    type="button"
                                    class="flex w-full items-start gap-3 px-4 py-3 text-left transition-colors"
                                    :class="active?.id === c.id ? 'bg-primary/5' : 'hover:bg-surface-container-low'"
                                    @click="openConversation(c)"
                                >
                                    <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary text-sm font-bold text-white">
                                        {{ (c.subject || c.topic).charAt(0).toUpperCase() }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="truncate text-body-sm font-semibold text-on-surface">{{ c.subject || c.topic }}</span>
                                            <span v-if="c.unreadCount" class="rounded-full bg-primary px-2 py-0.5 text-label-sm text-white">{{ c.unreadCount }}</span>
                                        </div>
                                        <p class="mt-0.5 truncate text-label-sm text-on-surface-variant">{{ c.lastMessage || 'Belum ada pesan' }}</p>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full" :class="c.status === 'open' ? 'bg-tertiary' : 'bg-outline-variant'" />
                                            <span class="text-label-sm" :class="c.status === 'open' ? 'text-tertiary' : 'text-on-surface-variant'">
                                                {{ c.status === 'open' ? 'Terbuka' : 'Ditutup' }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </li>
                            <li v-if="conversations.length === 0" class="px-4 py-8 text-center text-body-sm text-on-surface-variant">Belum ada percakapan.</li>
                        </ul>
                    </div>
                </aside>

                <section class="flex min-h-[34rem] flex-col overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
                    <div v-if="!active" class="flex flex-1 flex-col items-center justify-center gap-3 p-8 text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>
                        </div>
                        <h3 class="text-title-md font-semibold">Belum ada percakapan dipilih</h3>
                        <p class="max-w-sm text-body-sm text-on-surface-variant">Pilih percakapan di samping atau buat percakapan baru untuk mulai chat dengan customer service.</p>
                    </div>
                    <template v-else>
                        <header class="flex items-center justify-between gap-3 border-b border-outline-variant/40 bg-surface-container-low/40 px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary text-sm font-bold text-white">
                                    {{ (active.subject || active.topic).charAt(0).toUpperCase() }}
                                </span>
                                <div>
                                    <h2 class="text-title-md font-semibold">{{ active.subject || active.topic }}</h2>
                                    <p class="text-body-sm text-on-surface-variant">
                                        Topik: {{ active.topic }} · CS: {{ active.csName ?? 'Menunggu CS' }}
                                        <span class="ml-2 font-medium" :class="active.status === 'open' ? 'text-tertiary' : 'text-on-surface-variant'">
                                            {{ active.status === 'open' ? 'Terbuka' : 'Ditutup' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <button
                                v-if="active.status === 'open'"
                                type="button"
                                class="btn-secondary h-9 px-4 text-label-sm"
                                @click="closeConversation"
                            >
                                Tutup
                            </button>
                        </header>

                        <div class="flex-1 space-y-4 overflow-y-auto bg-surface-container-low/20 px-5 py-5">
                            <div
                                v-for="m in active.messages"
                                :key="m.id"
                                class="flex items-end gap-2"
                                :class="m.isAdmin ? 'justify-start' : 'justify-end'"
                            >
                                <span v-if="m.isAdmin" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-secondary text-xs font-bold text-white">
                                    {{ (m.senderName || 'CS').charAt(0).toUpperCase() }}
                                </span>
                                <div
                                    class="max-w-[75%] rounded-2xl px-4 py-2.5 text-body-sm shadow-sm"
                                    :class="m.isAdmin ? 'rounded-bl-sm bg-white text-on-surface' : 'rounded-br-sm bg-gradient-to-br from-primary to-secondary text-white'"
                                >
                                    <p class="text-label-sm font-semibold opacity-80">{{ m.senderName }}{{ m.isAdmin ? ' (CS)' : '' }}</p>
                                    <p class="mt-1 whitespace-pre-wrap">{{ m.body }}</p>
                                    <div v-if="m.attachments?.length" class="mt-2 space-y-1">
                                        <a v-for="a in m.attachments" :key="a.id" :href="a.url" target="_blank" class="flex items-center gap-2 rounded-lg bg-black/5 px-3 py-2 text-label-sm hover:bg-black/10">
                                            📎 {{ a.name }} <span v-if="a.size" class="opacity-70">({{ Math.round(a.size / 1024) }} KB)</span>
                                        </a>
                                    </div>
                                    <p class="mt-1 text-right text-label-sm opacity-60">{{ new Date(m.createdAt ?? '').toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</p>
                                </div>
                                <span v-if="!m.isAdmin" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                    {{ (m.senderName || 'U').charAt(0).toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <div v-if="typing.cs" class="px-5 pb-2">
                            <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-label-sm text-on-surface-variant shadow-sm">
                                <span class="flex gap-1">
                                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-on-surface-variant" style="animation-delay: 0ms" />
                                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-on-surface-variant" style="animation-delay: 150ms" />
                                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-on-surface-variant" style="animation-delay: 300ms" />
                                </span>
                                CS sedang mengetik...
                            </div>
                        </div>

                        <form v-if="active.status === 'open'" class="flex items-end gap-2 border-t border-outline-variant/40 bg-white px-5 py-4" @submit.prevent>
                            <textarea
                                v-model="draft"
                                rows="2"
                                class="input min-h-12 flex-1 resize-none rounded-2xl py-2.5"
                                placeholder="Tulis pesan..."
                                @keydown.enter.exact.prevent="sendDraft"
                                @input="onTyping"
                            />
                            <button type="button" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-outline-variant/50 text-on-surface-variant transition-colors hover:bg-surface-container-low" :disabled="uploading || !active" @click="triggerUpload" title="Lampirkan file">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" /></svg>
                            </button>
                            <Button type="button" :loading="sending" :disabled="sending || !draft.trim()" class="h-11 rounded-2xl px-5" @click="sendDraft">Kirim</Button>
                            <input ref="fileInput" type="file" class="hidden" accept="image/*,application/pdf,text/plain,.doc,.docx" @change="handleFile" />
                        </form>
                        <p v-else class="border-t border-outline-variant/40 bg-white px-5 py-4 text-center text-body-sm text-on-surface-variant">Percakapan ini sudah ditutup.</p>
                    </template>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
