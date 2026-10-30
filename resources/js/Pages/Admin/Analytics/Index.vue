<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import api from '@/lib/api'

interface TrendPoint { label: string; key: string; revenue: number; users: number }
interface TopCourse { id: number; title: string; enrollments: number }
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
  totalEnrollments: number
  successRate: number
}
interface Analytics {
  overview: Overview
  trend: TrendPoint[]
  topCourses: TopCourse[]
  subscriptionsByStatus: Record<string, number>
  paymentsByStatus: Record<string, number>
}

const months = ref(6)
const data = ref<Analytics | null>(null)
const loading = ref(true)
const error = ref('')

function currency(value: number): string {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}
function compact(value: number): string {
  return new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value)
}

const maxRevenue = computed(() => Math.max(1, ...(data.value?.trend.map((t) => t.revenue) ?? [1])))
const maxUsers = computed(() => Math.max(1, ...(data.value?.trend.map((t) => t.users) ?? [1])))
const maxEnroll = computed(() => Math.max(1, ...(data.value?.topCourses.map((c) => c.enrollments) ?? [1])))

const subTotal = computed(() => Object.values(data.value?.subscriptionsByStatus ?? {}).reduce((a, b) => a + b, 0) || 1)
const donutStyle = computed(() => {
  const s = data.value?.subscriptionsByStatus ?? {}
  const active = ((s.active ?? 0) / subTotal.value) * 100
  const pending = ((s.pending ?? 0) / subTotal.value) * 100
  const expired = ((s.expired ?? 0) / subTotal.value) * 100
  const canceled = ((s.canceled ?? 0) / subTotal.value) * 100
  const a2 = active + pending
  const a3 = a2 + expired
  return {
    background: `conic-gradient(#7c3aed 0 ${active}%, #f6c945 ${active}% ${a2}%, #94a3b8 ${a2}% ${a3}%, #ef4444 ${a3}% ${active + pending + expired + canceled}%)`,
  }
})

const subLegend = [
  { key: 'active', label: 'Aktif', color: '#7c3aed' },
  { key: 'pending', label: 'Tertunda', color: '#f6c945' },
  { key: 'expired', label: 'Kedaluwarsa', color: '#94a3b8' },
  { key: 'canceled', label: 'Dibatalkan', color: '#ef4444' },
]

async function load(): Promise<void> {
  loading.value = true
  error.value = ''
  try {
    const { data: payload } = await api.get<Analytics>('/admin/analytics', { params: { months: months.value } })
    data.value = payload
  } catch {
    error.value = 'Gagal memuat analitik.'
  } finally {
    loading.value = false
  }
}

function setMonths(value: number): void {
  months.value = value
  load()
}

const revenueGrowth = computed(() => {
  const trend = data.value?.trend ?? []
  if (trend.length < 2) return 0
  const last = trend[trend.length - 1].revenue
  const prev = trend[trend.length - 2].revenue
  if (prev === 0) return last > 0 ? 100 : 0
  return Math.round(((last - prev) / prev) * 100)
})

onMounted(load)
</script>

<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Insight</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight">Analitik</h1>
        <p class="mt-1 text-body-sm text-on-surface-variant">Performa pengguna, kursus, dan pendapatan Ilmora.</p>
      </div>
      <div class="flex gap-2">
        <button v-for="opt in [6, 12]" :key="opt" class="btn-secondary" :class="months === opt ? 'ring-2 ring-primary' : ''" :disabled="loading" @click="setMonths(opt)">{{ opt }} bulan</button>
        <button class="btn-secondary" :disabled="loading" @click="load">{{ loading ? 'Memuat...' : 'Muat ulang' }}</button>
      </div>
    </div>

    <p v-if="error" class="mb-5 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error">{{ error }}</p>

    <div v-if="loading" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="n in 4" :key="n" class="h-32 animate-pulse rounded-2xl bg-surface-container-high" />
    </div>

    <template v-else-if="data">
      <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Total Pengguna" :value="compact(data.overview.totalUsers)" :hint="`${data.overview.totalAdmins} admin`" icon="users" accent="primary" />
        <StatCard label="Pendapatan" :value="currency(data.overview.totalRevenue)" :hint="`${currency(data.overview.revenueThisMonth)} bulan ini`" icon="wallet" accent="tertiary" />
        <StatCard label="Langganan Aktif" :value="data.overview.activeSubscriptions" :hint="`${data.overview.totalSubscriptions} total`" icon="card" accent="secondary" />
        <StatCard label="Total Pendaftaran" :value="compact(data.overview.totalEnrollments)" hint="Enrollment kursus" icon="book" accent="primary" />
        <StatCard label="Tingkat Sukses Bayar" :value="`${data.overview.successRate}%`" :hint="`${revenueGrowth >= 0 ? '+' : ''}${revenueGrowth}% pendapatan bulan ini`" icon="spark" accent="tertiary" />
      </section>

      <section class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card-flush lg:col-span-2">
          <div class="flex items-center justify-between border-b border-outline-variant/50 px-6 py-5">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">6 bulan terakhir</p>
              <h2 class="mt-1 text-title-lg font-bold">Tren Pendapatan</h2>
            </div>
            <span class="badge-primary">{{ currency(data.overview.revenueThisMonth) }}</span>
          </div>
          <div class="flex h-64 items-end gap-3 px-6 pb-6 pt-8">
            <div v-for="point in data.trend" :key="point.key" class="group flex flex-1 flex-col items-center gap-2">
              <span class="text-[10px] font-semibold text-on-surface-variant opacity-0 transition-opacity group-hover:opacity-100">{{ compact(point.revenue) }}</span>
              <div class="flex w-full flex-1 items-end">
                <div class="w-full rounded-t-xl bg-gradient-to-t from-primary to-primary/50 transition-all duration-500 group-hover:from-primary group-hover:to-primary/80" :style="{ height: `${Math.max(4, (point.revenue / maxRevenue) * 100)}%` }" />
              </div>
              <span class="text-[11px] font-medium text-on-surface-variant">{{ point.label }}</span>
            </div>
          </div>
        </div>

        <div class="card-flush">
          <div class="border-b border-outline-variant/50 px-6 py-5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Komposisi</p>
            <h2 class="mt-1 text-title-lg font-bold">Status Langganan</h2>
          </div>
          <div class="flex flex-col items-center gap-6 p-6">
            <div class="relative h-40 w-40 rounded-full" :style="donutStyle">
              <div class="absolute inset-6 flex flex-col items-center justify-center rounded-full bg-surface-container-lowest">
                <span class="text-3xl font-bold">{{ subTotal }}</span>
                <span class="text-[10px] uppercase tracking-wide text-on-surface-variant">total</span>
              </div>
            </div>
            <ul class="w-full space-y-2">
              <li v-for="item in subLegend" :key="item.key" class="flex items-center justify-between text-body-sm">
                <span class="flex items-center gap-2 text-on-surface-variant"><span class="h-2.5 w-2.5 rounded-full" :style="{ background: item.color }" />{{ item.label }}</span>
                <span class="font-semibold">{{ data.subscriptionsByStatus[item.key] ?? 0 }}</span>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card-flush">
          <div class="border-b border-outline-variant/50 px-6 py-5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Pertumbuhan</p>
            <h2 class="mt-1 text-title-lg font-bold">Pengguna Baru</h2>
          </div>
          <div class="space-y-4 p-6">
            <div v-for="point in data.trend" :key="point.key" class="flex items-center gap-3">
              <span class="w-10 text-body-sm font-medium text-on-surface-variant">{{ point.label }}</span>
              <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-surface-container-high">
                <div class="h-full rounded-full bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500" :style="{ width: `${Math.max(2, (point.users / maxUsers) * 100)}%` }" />
              </div>
              <span class="w-10 text-right text-body-sm font-semibold">{{ point.users }}</span>
            </div>
          </div>
        </div>

        <div class="card-flush">
          <div class="border-b border-outline-variant/50 px-6 py-5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Populer</p>
            <h2 class="mt-1 text-title-lg font-bold">Kursus Teratas</h2>
          </div>
          <div class="space-y-4 p-6">
            <div v-for="(course, index) in data.topCourses" :key="course.id">
              <div class="mb-1.5 flex items-center justify-between text-body-sm">
                <span class="flex items-center gap-2 font-medium"><span class="flex h-5 w-5 items-center justify-center rounded-md bg-primary/10 text-[10px] font-bold text-primary">{{ index + 1 }}</span>{{ course.title }}</span>
                <span class="text-on-surface-variant">{{ course.enrollments }}</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-surface-container-high">
                <div class="h-full rounded-full bg-gradient-to-r from-primary to-primary/50" :style="{ width: `${Math.max(3, (course.enrollments / maxEnroll) * 100)}%` }" />
              </div>
            </div>
            <p v-if="!data.topCourses.length" class="py-6 text-center text-body-sm text-on-surface-variant">Belum ada data pendaftaran.</p>
          </div>
        </div>
      </section>

      <section class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card">
          <p class="text-label-md font-semibold uppercase tracking-wide text-on-surface-variant">Kursus</p>
          <p class="mt-2 text-display-sm font-bold">{{ data.overview.publishedCourses }}<span class="text-lg text-on-surface-variant">/{{ data.overview.totalCourses }}</span></p>
          <p class="mt-1 text-body-sm text-on-surface-variant">Dipublikasikan</p>
        </div>
        <div class="card">
          <p class="text-label-md font-semibold uppercase tracking-wide text-secondary">Pembayaran tertunda</p>
          <p class="mt-2 text-display-sm font-bold">{{ data.paymentsByStatus.pending ?? 0 }}</p>
          <p class="mt-1 text-body-sm text-on-surface-variant">Perlu verifikasi</p>
        </div>
        <div class="card">
          <p class="text-label-md font-semibold uppercase tracking-wide text-tertiary">Pembayaran sukses</p>
          <p class="mt-2 text-display-sm font-bold">{{ data.paymentsByStatus.success ?? 0 }}</p>
          <p class="mt-1 text-body-sm text-on-surface-variant">Transaksi berhasil</p>
        </div>
        <div class="card">
          <p class="text-label-md font-semibold uppercase tracking-wide text-error">Refund</p>
          <p class="mt-2 text-display-sm font-bold">{{ data.paymentsByStatus.refunded ?? 0 }}</p>
          <p class="mt-1 text-body-sm text-on-surface-variant">Pembayaran dikembalikan</p>
        </div>
      </section>
    </template>
  </AdminLayout>
</template>
