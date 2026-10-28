<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
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
interface Conversation { id: number; userId: number; userName: string | null; userEmail: string | null; topic: string; subject: string | null; status: 'open' | 'closed'; csName: string | null; lastMessage: string | null; lastMessageAt: string | null; unreadCount: number; createdAt: string | null; messages?: ChatMessage[] }

const conversations = ref<Conversation[]>([])
const active = ref<Conversation | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const statusFilter = ref('')
const draft = ref('')
const sending = ref(false)
const polling = ref<number | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const uploading = ref(false)
const typing = ref({ user: false, cs: false })
let typingTimeout: number | null = null

async function loadConversations(): Promise<void> {
    loading.value = true
    try {
        const { data } = await api.get<{ conversations: Conversation[] }>('/admin/chat/conversations', { params: statusFilter.value ? { status: statusFilter.value } : {} })
        conversations.value = data.conversations
        if (active.value) {
            const u = conversations.value.find((c) => c.id === active.value!.id)
            if (u) active.value = { ...active.value, ...u }
        }
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal memuat percakapan.'
    } finally {
        loading.value = false
    }
}

async function openConversation(c: Conversation): Promise<void> {
    active.value = c
    try {
        const { data } = await api.get<{ conversation: Conversation }>(`/admin/chat/conversations/${c.id}`)
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
            const { data } = await api.get<{ messages: ChatMessage[]; status: string; typing?: { cs: boolean; user: boolean } }>(`/admin/chat/conversations/${active.value.id}/poll`, { params: { after: lastId } })
            if (data.messages.length) active.value.messages = [...(active.value.messages ?? []), ...data.messages]
            active.value.status = data.status as 'open' | 'closed'
            if (data.typing) typing.value = { user: !!data.typing.user, cs: !!data.typing.cs }
        } catch { /* ignore */ }
    }, 4000)
}

function stopPolling(): void {
    if (polling.value !== null) { clearInterval(polling.value); polling.value = null }
}

async function sendDraft(): Promise<void> {
    if (!active.value || !draft.value.trim() || sending.value) return
    sending.value = true
    const body = draft.value.trim()
    draft.value = ''
    try {
        const { data } = await api.post<{ chatMessage: ChatMessage }>(`/admin/chat/conversations/${active.value.id}/messages`, { body })
        active.value.messages = [...(active.value.messages ?? []), data.chatMessage]
    } catch (err) {
        draft.value = body
        error.value = normalizeApiError(err).message ?? 'Gagal mengirim.'
    } finally {
        sending.value = false
    }
}

function onTyping(): void {
    if (!active.value) return
    if (typingTimeout) clearTimeout(typingTimeout)
    api.post(`/admin/chat/conversations/${active.value.id}/typing`).catch(() => {})
    typingTimeout = window.setTimeout(() => {
        if (active.value) api.post(`/admin/chat/conversations/${active.value.id}/stop-typing`).catch(() => {})
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
        const { data } = await api.post<{ chatMessage: ChatMessage }>(`/admin/chat/conversations/${active.value.id}/attachments`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
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
        await api.post(`/admin/chat/conversations/${active.value.id}/close`)
        active.value.status = 'closed'
        await loadConversations()
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal menutup.'
    }
}

onMounted(loadConversations)
onUnmounted(stopPolling)
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-headline-sm font-bold">Chat Customer Service</h2>
                <p class="text-body-sm text-on-surface-variant">Kelola percakapan dengan pengguna.</p>
            </div>
            <select v-model="statusFilter" class="input h-10 w-40" @change="loadConversations">
                <option value="">Semua</option>
                <option value="open">Terbuka</option>
                <option value="closed">Ditutup</option>
            </select>
        </div>

        <p v-if="error" class="mt-4 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">{{ error }}</p>

        <div class="mt-6 grid gap-6 lg:grid-cols-[20rem_1fr]">
            <aside class="card max-h-[36rem] overflow-y-auto p-3">
                <div v-if="loading" class="space-y-2">
                    <div v-for="n in 5" :key="n" class="h-14 animate-pulse rounded-xl bg-surface-container-high" />
                </div>
                <ul v-else class="space-y-2">
                    <li v-for="c in conversations" :key="c.id">
                        <button type="button" class="w-full rounded-xl px-3 py-2 text-left transition-colors" :class="active?.id === c.id ? 'bg-primary/10 ring-1 ring-primary/30' : 'hover:bg-surface-container-low'" @click="openConversation(c)">
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate text-body-sm font-semibold">{{ c.userName }}</span>
                                <span v-if="c.unreadCount" class="rounded-full bg-primary px-2 py-0.5 text-label-sm text-white">{{ c.unreadCount }}</span>
                            </div>
                            <p class="mt-0.5 truncate text-label-sm text-on-surface-variant">{{ c.topic }} · {{ c.lastMessage }}</p>
                        </button>
                    </li>
                    <li v-if="conversations.length === 0" class="text-body-sm text-on-surface-variant">Belum ada percakapan.</li>
                </ul>
            </aside>

            <section class="card flex min-h-[36rem] flex-col">
                <div v-if="!active" class="flex flex-1 items-center justify-center text-body-md text-on-surface-variant">
                    Pilih percakapan untuk mulai membalas.
                </div>
                <template v-else>
                    <header class="flex items-center justify-between border-b border-outline-variant/40 pb-3">
                        <div>
                            <h2 class="text-title-md font-semibold">{{ active.userName }}</h2>
                            <p class="text-body-sm text-on-surface-variant">{{ active.topic }} · {{ active.userEmail }}</p>
                        </div>
                        <button v-if="active.status === 'open'" type="button" class="btn-secondary h-9 px-3 text-label-sm" @click="closeConversation">Tutup</button>
                    </header>
                    <div class="mt-4 flex-1 space-y-3 overflow-y-auto pr-2">
                        <div v-for="m in active.messages" :key="m.id" class="flex" :class="m.isAdmin ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[75%] rounded-2xl px-4 py-2 text-body-sm" :class="m.isAdmin ? 'bg-primary text-white' : 'bg-surface-container-high text-on-surface'">
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
                    <p v-if="typing.user" class="mt-2 text-label-sm italic text-on-surface-variant">User sedang mengetik...</p>
                    <form v-if="active.status === 'open'" class="mt-4 flex items-end gap-2 border-t border-outline-variant/40 pt-4" @submit.prevent="sendDraft">
                        <textarea v-model="draft" rows="2" class="input min-h-16 flex-1 py-2" placeholder="Balas sebagai CS..." @keydown.enter.exact.prevent="sendDraft" @input="onTyping" />
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
    </AdminLayout>
</template>
