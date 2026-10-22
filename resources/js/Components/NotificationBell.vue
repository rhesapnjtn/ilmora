<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/lib/api'
import { normalizeApiError } from '@/lib/errors'

interface NotificationItem {
    id: string
    type: string
    data: {
        type?: string
        author_name?: string
        excerpt?: string
        lesson_title?: string
        url?: string | null
    }
    readAt: string | null
    createdAt: string
}

const router = useRouter()

const open = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)
const items = ref<NotificationItem[]>([])
const unreadCount = ref(0)

onMounted(load)

async function load(): Promise<void> {
    loading.value = true
    error.value = null

    try {
        const { data } = await api.get<{ notifications: NotificationItem[]; unreadCount: number }>('/notifications')
        items.value = data.notifications
        unreadCount.value = data.unreadCount
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal memuat notifikasi.'
    } finally {
        loading.value = false
    }
}

async function toggle(): Promise<void> {
    open.value = !open.value

    if (open.value && items.value.length === 0) {
        await load()
    }
}

async function markAllRead(): Promise<void> {
    error.value = null

    try {
        await api.post('/notifications/read-all')
        const readAt = new Date().toISOString()
        items.value = items.value.map((item) => ({ ...item, readAt: item.readAt ?? readAt }))
        unreadCount.value = 0
    } catch (err) {
        error.value = normalizeApiError(err).message ?? 'Gagal menandai notifikasi.'
    }
}

async function openItem(item: NotificationItem): Promise<void> {
    open.value = false

    if (!item.readAt) {
        try {
            await api.post(`/notifications/${item.id}/read`)
            item.readAt = new Date().toISOString()
            unreadCount.value = Math.max(0, unreadCount.value - 1)
        } catch {
            // Navigasi tetap berjalan walau penandaan "dibaca" gagal.
        }
    }

    if (item.data.url) {
        router.push(item.data.url)
    }
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

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value))
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="relative rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
            aria-label="Notifikasi"
            :aria-expanded="open"
            @click="toggle"
        >
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-error px-1 text-[0.625rem] font-bold text-white"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <div v-if="open" class="fixed inset-0 z-40" aria-hidden="true" @click="open = false" />

        <Transition name="fade">
            <div
                v-if="open"
                class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-outline-variant bg-surface shadow-modal"
            >
                <div class="flex items-center justify-between gap-2 border-b border-outline-variant px-4 py-3">
                    <h3 class="text-title-sm font-semibold">Notifikasi</h3>
                    <button
                        v-if="unreadCount > 0"
                        type="button"
                        class="text-body-sm font-semibold text-primary transition-colors hover:text-primary/80"
                        @click="markAllRead"
                    >
                        Tandai semua dibaca
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <p v-if="loading" class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Memuat notifikasi...</p>

                    <p v-else-if="error" class="px-4 py-6 text-center text-body-sm text-error">{{ error }}</p>

                    <p v-else-if="items.length === 0" class="px-4 py-8 text-center text-body-sm text-on-surface-variant">
                        Belum ada notifikasi.
                    </p>

                    <ul v-else class="divide-y divide-outline-variant">
                        <li v-for="item in items" :key="item.id">
                            <button
                                type="button"
                                class="flex w-full items-start gap-3 px-4 py-3 text-left transition-colors hover:bg-surface-container-low"
                                :class="item.readAt ? 'opacity-70' : ''"
                                @click="openItem(item)"
                            >
                                <span
                                    class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                    :class="item.readAt ? 'bg-transparent' : 'bg-primary'"
                                />
                                <span class="min-w-0 flex-1">
                                    <span class="block text-body-md font-semibold text-on-surface">
                                        {{ item.data.author_name || 'Seseorang' }} membalas diskusimu
                                    </span>
                                    <span class="mt-0.5 block truncate text-body-sm text-on-surface-variant">
                                        {{ item.data.excerpt }}
                                    </span>
                                    <span class="mt-1 block text-body-sm text-on-surface-variant">
                                        {{ item.data.lesson_title }} · {{ formatRelative(item.createdAt) }}
                                    </span>
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </Transition>
    </div>
</template>
