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
        <div class="mx-auto max-w-5xl">
            <h1 class="text-display-sm font-bold tracking-tight">Bantuan & Chat CS</h1>
            <p class="mt-2 text-body-md text-on-surface-variant">Hubungi customer service kami untuk pertanyaan atau kendala.</p>

            <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

            <div class="mt-6 grid gap-6 lg:grid-cols-[18rem_1fr]">
                <aside class="space-y-4">
                    <div class="card">
                        <h2 class="text-title-md font-semibold">Mulai Percakapan Baru</h2>
                        <div class="mt-3 space-y-3">
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

                    <div class="card">
                        <h2 class="text-title-md font-semibold">Percakapan Saya</h2>
                        <div v-if="loading" class="mt-3 space-y-2">
                            <div v-for="n in 3" :key="n" class="h-12 animate-pulse rounded-xl bg-surface-container-high" />
                        </div>
                        <ul v-else class="mt-3 space-y-2">
                            <li v-for="c in conversations" :key="c.id">
                                <button
                                    type="button"
                                    class="w-full rounded-xl px-3 py-2 text-left transition-colors"
                                    :class="active?.id === c.id ? 'bg-primary/10 ring-1 ring-primary/30' : 'hover:bg-surface-container-low'"
                                    @click="openConversation(c)"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="truncate text-body-sm font-semibold">{{ c.subject || c.topic }}</span>
                                        <span v-if="c.unreadCount" class="rounded-full bg-primary px-2 py-0.5 text-label-sm text-white">{{ c.unreadCount }}</span>
                                    </div>
                                    <p class="mt-0.5 truncate text-label-sm text-on-surface-variant">{{ c.lastMessage }}</p>
                                    <span class="text-label-sm" :class="c.status === 'open' ? 'text-tertiary' : 'text-on-surface-variant'">
                                        {{ c.status === 'open' ? 'Terbuka' : 'Ditutup' }}
                                    </span>
                                </button>
                            </li>
                            <li v-if="conversations.length === 0" class="text-body-sm text-on-surface-variant">Belum ada percakapan.</li>
                        </ul>
                    </div>
                </aside>

                <section class="card flex min-h-[32rem] flex-col">
                    <div v-if="!active" class="flex flex-1 items-center justify-center text-body-md text-on-surface-variant">
                        Pilih atau buat percakapan untuk mulai chat.
                    </div>
                    <template v-else>
                        <header class="flex items-center justify-between border-b border-outline-variant/40 pb-3">
                            <div>
                                <h2 class="text-title-md font-semibold">{{ active.subject || active.topic }}</h2>
                                <p class="text-body-sm text-on-surface-variant">
                                    Topik: {{ active.topic }} · CS: {{ active.csName ?? 'Menunggu CS' }}
                                    <span class="ml-2" :class="active.status === 'open' ? 'text-tertiary' : 'text-on-surface-variant'">
                                        ({{ active.status === 'open' ? 'Terbuka' : 'Ditutup' }})
                                    </span>
                                </p>
                            </div>
                            <button
                                v-if="active.status === 'open'"
                                type="button"
                                class="btn-secondary h-9 px-3 text-label-sm"
                                @click="closeConversation"
                            >
                                Tutup
                            </button>
                        </header>

                        <div class="mt-4 flex-1 space-y-3 overflow-y-auto pr-2">
                            <div
                                v-for="m in active.messages"
                                :key="m.id"
                                class="flex"
                                :class="m.isAdmin ? 'justify-start' : 'justify-end'"
                            >
                                <div
                                    class="max-w-[75%] rounded-2xl px-4 py-2 text-body-sm"
                                    :class="m.isAdmin ? 'bg-surface-container-high text-on-surface' : 'bg-primary text-white'"
                                >
                                    <p class="text-label-sm font-semibold opacity-80">{{ m.senderName }}{{ m.isAdmin ? ' (CS)' : '' }}</p>
                                    <p class="mt-1 whitespace-pre-wrap">{{ m.body }}</p>
                                    <div v-if="m.attachments?.length" class="mt-2 space-y-1">
                                        <a v-for="a in m.attachments" :key="a.id" :href="a.url" target="_blank" class="block rounded-lg bg-white/10 px-3 py-2 text-label-sm underline-offset-2 hover:underline">
                                            📎 {{ a.name }} <span v-if="a.size" class="opacity-70">({{ Math.round(a.size / 1024) }} KB)</span>
                                        </a>
                                    </div>
                                    <p class="mt-1 text-label-sm opacity-60">{{ new Date(m.createdAt ?? '').toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</p>
                                </div>
                            </div>
                        </div>

                        <p v-if="typing.cs" class="mt-2 text-label-sm italic text-on-surface-variant">CS sedang mengetik...</p>

                        <form v-if="active.status === 'open'" class="mt-4 flex items-end gap-2 border-t border-outline-variant/40 pt-4" @submit.prevent="sendDraft">
                            <textarea
                                v-model="draft"
                                rows="2"
                                class="input min-h-16 flex-1 py-2"
                                placeholder="Tulis pesan..."
                                @keydown.enter.exact.prevent="sendDraft"
                                @input="onTyping"
                            />
                            <button type="button" class="btn-secondary h-10 w-10 shrink-0 px-0" :disabled="uploading || !active" @click="triggerUpload" title="Lampirkan file">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" /></svg>
                            </button>
                            <Button type="submit" :loading="sending" :disabled="sending || !draft.trim()">Kirim</Button>
                            <input ref="fileInput" type="file" class="hidden" accept="image/*,application/pdf,text/plain,.doc,.docx" @change="handleFile" />
                        </form>
                        <p v-else class="mt-4 text-center text-body-sm text-on-surface-variant">Percakapan ini sudah ditutup.</p>
                    </template>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
