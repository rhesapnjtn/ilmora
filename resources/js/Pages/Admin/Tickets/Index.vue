<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import api from '@/lib/api'
interface Ticket { id: number; topic: string; status: string; priority: string; user: { name: string } | null; assigned_admin: { name: string } | null; last_message_at: string | null }
const tickets = ref<Ticket[]>([])
const loading = ref(true)
const statusFilter = ref('')
const priorityFilter = ref('')
async function load() { loading.value = true; const { data } = await api.get('/admin/tickets', { params: { status: statusFilter.value, priority: priorityFilter.value } }); tickets.value = data.tickets.data; loading.value = false }
async function update(id: number, payload: any) { await api.patch(`/admin/tickets/${id}`, payload); await load() }
onMounted(load)
</script>
<template>
  <AdminLayout>
    <div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
      <div><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Support</p><h1 class="mt-1 text-2xl font-bold">Tiket Chat</h1></div>
      <div class="flex gap-2">
        <select v-model="statusFilter" class="input" @change="load"><option value="">Semua status</option><option value="open">Open</option><option value="closed">Closed</option></select>
        <select v-model="priorityFilter" class="input" @change="load"><option value="">Semua prioritas</option><option value="low">Low</option><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select>
      </div>
    </div>
    <div v-if="loading" class="card">Memuat...</div>
    <div v-else class="card-flush overflow-x-auto">
      <table class="w-full min-w-[720px] text-left"><thead class="bg-surface-container-low text-label-sm uppercase text-on-surface-variant"><tr><th class="px-6 py-4">Pengguna</th><th class="px-6 py-4">Topik</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Prioritas</th><th class="px-6 py-4">Ditugaskan</th><th class="px-6 py-4">Aksi</th></tr></thead>
      <tbody class="divide-y divide-outline-variant/50"><tr v-for="t in tickets" :key="t.id"><td class="px-6 py-4">{{ t.user?.name }}</td><td class="px-6 py-4">{{ t.topic }}</td><td class="px-6 py-4">{{ t.status }}</td><td class="px-6 py-4">{{ t.priority }}</td><td class="px-6 py-4">{{ t.assigned_admin?.name ?? '-' }}</td><td class="px-6 py-4 flex gap-2"><button class="btn-secondary" @click="update(t.id,{status:'closed'})">Tutup</button><button class="btn-secondary" @click="update(t.id,{priority:'urgent'})">Urgent</button></td></tr></tbody></table>
    </div>
  </AdminLayout>
</template>