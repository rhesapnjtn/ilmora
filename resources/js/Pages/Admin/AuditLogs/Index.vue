<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import api from '@/lib/api'

interface Log { id: number; action: string; admin: { name: string; email: string } | null; subjectType: string | null; subjectId: number | null; ipAddress: string | null; createdAt: string | null }
const logs = ref<Log[]>([])
const meta = ref({ page: 1, lastPage: 1, total: 0 })
const loading = ref(true)
const error = ref('')
function label(action: string): string { return ({ 'settings.updated': 'Memperbarui pengaturan', 'payment.approved': 'Menyetujui pembayaran', 'payment.rejected': 'Menolak pembayaran', 'payment.refunded': 'Melakukan refund' } as Record<string, string>)[action] ?? action }
function format(value: string | null): string { return value ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '-' }
async function load(page = 1): Promise<void> { loading.value = true; error.value = ''; try { const { data } = await api.get('/admin/audit-logs', { params: { page } }); logs.value = data.logs; meta.value = data.meta } catch { error.value = 'Gagal memuat aktivitas admin.' } finally { loading.value = false } }
onMounted(() => load())
</script>

<template>
  <AdminLayout>
    <div class="mb-6"><p class="text-label-sm font-bold uppercase tracking-wider text-primary">Keamanan</p><h2 class="mt-1 text-2xl font-bold">Aktivitas Admin</h2><p class="mt-1 text-body-sm text-on-surface-variant">Riwayat tindakan penting yang dilakukan administrator.</p></div>
    <p v-if="error" class="mb-5 rounded-xl bg-error/10 p-4 text-error">{{ error }}</p>
    <div class="card-flush overflow-x-auto"><table class="w-full min-w-[720px] text-left"><thead class="bg-surface-container-low text-label-sm uppercase tracking-wider text-on-surface-variant"><tr><th class="px-6 py-4">Waktu</th><th class="px-6 py-4">Admin</th><th class="px-6 py-4">Aktivitas</th><th class="px-6 py-4">Data</th><th class="px-6 py-4">IP</th></tr></thead><tbody class="divide-y divide-outline-variant/50"><tr v-if="loading"><td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">Memuat aktivitas...</td></tr><tr v-else-if="!logs.length"><td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">Belum ada aktivitas tercatat.</td></tr><tr v-for="log in logs" :key="log.id" class="hover:bg-primary/5"><td class="px-6 py-4 text-body-sm">{{ format(log.createdAt) }}</td><td class="px-6 py-4"><p class="font-semibold">{{ log.admin?.name ?? 'Sistem' }}</p><p class="text-body-sm text-on-surface-variant">{{ log.admin?.email }}</p></td><td class="px-6 py-4 font-medium">{{ label(log.action) }}</td><td class="px-6 py-4 text-body-sm text-on-surface-variant">{{ log.subjectType?.split('\\').pop() ?? '-' }} #{{ log.subjectId ?? '-' }}</td><td class="px-6 py-4 font-mono text-body-sm text-on-surface-variant">{{ log.ipAddress ?? '-' }}</td></tr></tbody></table></div>
    <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />
  </AdminLayout>
</template>
