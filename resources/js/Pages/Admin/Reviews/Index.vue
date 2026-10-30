<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import api from '@/lib/api'
interface Review { id: number; rating: number; body: string | null; status: string; user: { name: string } | null; course: { title: string } | null; created_at: string }
const reviews = ref<Review[]>([])
const loading = ref(true)
const filter = ref('')
async function load() { loading.value = true; const { data } = await api.get('/admin/reviews', { params: { status: filter.value } }); reviews.value = data.reviews.data; loading.value = false }
async function setStatus(id: number, status: string) { await api.patch(`/admin/reviews/${id}/status`, { status }); await load() }
async function remove(id: number) { if (!confirm('Hapus ulasan?')) return; await api.delete(`/admin/reviews/${id}`); await load() }
onMounted(load)
</script>
<template>
  <AdminLayout>
    <div class="mb-6 flex items-center justify-between">
      <div><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Moderasi</p><h1 class="mt-1 text-2xl font-bold">Ulasan</h1></div>
      <select v-model="filter" class="input" @change="load"><option value="">Semua</option><option value="pending">Pending</option><option value="approved">Disetujui</option><option value="rejected">Ditolak</option></select>
    </div>
    <div v-if="loading" class="card">Memuat...</div>
    <div v-else class="card-flush overflow-x-auto">
      <table class="w-full min-w-[720px] text-left"><thead class="bg-surface-container-low text-label-sm uppercase text-on-surface-variant"><tr><th class="px-6 py-4">Pengguna</th><th class="px-6 py-4">Kursus</th><th class="px-6 py-4">Rating</th><th class="px-6 py-4">Ulasan</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Aksi</th></tr></thead>
      <tbody class="divide-y divide-outline-variant/50"><tr v-for="r in reviews" :key="r.id"><td class="px-6 py-4">{{ r.user?.name }}</td><td class="px-6 py-4">{{ r.course?.title }}</td><td class="px-6 py-4">{{ r.rating }}/5</td><td class="px-6 py-4 max-w-xs truncate">{{ r.body }}</td><td class="px-6 py-4">{{ r.status }}</td><td class="px-6 py-4 flex gap-2"><button class="btn-secondary" @click="setStatus(r.id,'approved')">Setujui</button><button class="btn-secondary" @click="setStatus(r.id,'rejected')">Tolak</button><button class="btn-secondary text-error" @click="remove(r.id)">Hapus</button></td></tr></tbody></table>
    </div>
  </AdminLayout>
</template>