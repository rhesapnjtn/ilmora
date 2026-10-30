<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import api from '@/lib/api'
interface Media { id: number; name: string; path: string; mime_type: string | null; size: number | null; collection: string; created_at: string }
const media = ref<Media[]>([])
const loading = ref(true)
async function load() { loading.value = true; const { data } = await api.get('/admin/media'); media.value = data.media.data; loading.value = false }
async function upload(e: Event) { const f = (e.target as HTMLInputElement).files?.[0]; if (!f) return; const fd = new FormData(); fd.append('file', f); await api.post('/admin/media', fd, { headers: { 'Content-Type': 'multipart/form-data' } }); await load() }
async function remove(id: number) { if (!confirm('Hapus media?')) return; await api.delete(`/admin/media/${id}`); await load() }
onMounted(load)
</script>
<template>
  <AdminLayout>
    <div class="mb-6 flex items-center justify-between">
      <div><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Konten</p><h1 class="mt-1 text-2xl font-bold">Media Library</h1></div>
      <label class="btn-primary cursor-pointer">Upload<input type="file" class="hidden" @change="upload" /></label>
    </div>
    <div v-if="loading" class="card">Memuat...</div>
    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="m in media" :key="m.id" class="card space-y-2">
        <p class="truncate font-semibold">{{ m.name }}</p>
        <p class="text-xs text-on-surface-variant">{{ m.mime_type }} · {{ m.size }} bytes</p>
        <p class="text-xs text-on-surface-variant">{{ m.collection }}</p>
        <button class="btn-secondary text-error" @click="remove(m.id)">Hapus</button>
      </div>
      <p v-if="!media.length" class="card text-center text-on-surface-variant col-span-full">Belum ada media.</p>
    </div>
  </AdminLayout>
</template>