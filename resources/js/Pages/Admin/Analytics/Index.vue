<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '@/lib/api'

interface Overview {
  totalUsers: number
  totalAdmins: number
  totalCourses: number
  publishedCourses: number
  totalSubscriptions: number
  activeSubscriptions: number
  totalRevenue: number
  revenueThisMonth: number
  pendingPayments: number
}

const overview = ref<Overview | null>(null)
const topCourses = ref<{ id: number; title: string; enrollments: number }[]>([])
const loading = ref(false)
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/admin/analytics')
    overview.value = data.overview
    topCourses.value = data.topCourses
  } catch {
    error.value = 'Gagal memuat analitik.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">Analitik</h1>
    <p v-if="error" class="text-red-600">{{ error }}</p>
    <div v-if="loading" class="text-gray-500">Memuat...</div>
    <div v-else-if="overview" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white rounded shadow p-4">
        <div class="text-sm text-gray-500">Total Pengguna</div>
        <div class="text-2xl font-bold">{{ overview.totalUsers }}</div>
      </div>
      <div class="bg-white rounded shadow p-4">
        <div class="text-sm text-gray-500">Kursus (Publik)</div>
        <div class="text-2xl font-bold">{{ overview.publishedCourses }}/{{ overview.totalCourses }}</div>
      </div>
      <div class="bg-white rounded shadow p-4">
        <div class="text-sm text-gray-500">Langganan Aktif</div>
        <div class="text-2xl font-bold">{{ overview.activeSubscriptions }}/{{ overview.totalSubscriptions }}</div>
      </div>
      <div class="bg-white rounded shadow p-4">
        <div class="text-sm text-gray-500">Pendapatan Total</div>
        <div class="text-2xl font-bold">Rp {{ overview.totalRevenue.toLocaleString('id-ID') }}</div>
      </div>
      <div class="bg-white rounded shadow p-4">
        <div class="text-sm text-gray-500">Pendapatan Bulan Ini</div>
        <div class="text-2xl font-bold">Rp {{ overview.revenueThisMonth.toLocaleString('id-ID') }}</div>
      </div>
      <div class="bg-white rounded shadow p-4">
        <div class="text-sm text-gray-500">Pembayaran Pending</div>
        <div class="text-2xl font-bold">{{ overview.pendingPayments }}</div>
      </div>
    </div>
    <div v-if="topCourses.length" class="bg-white rounded shadow p-4">
      <h2 class="font-semibold mb-3">Kursus Terpopuler</h2>
      <ul class="space-y-2">
        <li v-for="c in topCourses" :key="c.id" class="flex justify-between">
          <span>{{ c.title }}</span>
          <span class="text-gray-500">{{ c.enrollments }} pendaftar</span>
        </li>
      </ul>
    </div>
  </div>
</template>
