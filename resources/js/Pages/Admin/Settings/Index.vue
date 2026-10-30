<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '@/lib/api'
import AdminLayout from '@/Layouts/AdminLayout.vue'

interface SettingItem { key: string; value: unknown; type: string }
const groups = ref<Record<string, SettingItem[]>>({})
const loading = ref(false)
const saving = ref(false)
const message = ref('')
const error = ref('')
const search = ref('')
const activeGroup = ref('')

const labels: Record<string, string> = {
  app_name: 'Nama aplikasi', app_description: 'Deskripsi aplikasi', support_email: 'Email support',
  support_whatsapp: 'WhatsApp support', maintenance_mode: 'Mode maintenance',
  registration_enabled: 'Pendaftaran pengguna', email_verification_required: 'Wajib verifikasi email',
  max_upload_size_mb: 'Batas upload video', default_currency: 'Mata uang default',
  service_fee: 'Biaya layanan', payment_instructions: 'Instruksi pembayaran',
  notify_payment_email: 'Notifikasi pembayaran', notify_chat_email: 'Notifikasi chat',
  notify_subscription_email: 'Notifikasi langganan', courses_per_page: 'Kursus per halaman',
  auto_approve_reviews: 'Setujui ulasan otomatis', subscription_duration_days: 'Durasi langganan',
}

const descriptions: Record<string, string> = {
  maintenance_mode: 'Sembunyikan layanan sementara dari pengguna.',
  registration_enabled: 'Izinkan pengguna baru membuat akun.',
  email_verification_required: 'Minta pengguna memverifikasi email saat daftar.',
  auto_approve_reviews: 'Publikasikan ulasan tanpa moderasi admin.',
  service_fee: 'Persentase biaya layanan yang ditambahkan ke transaksi.',
}

const suffixes: Record<string, string> = {
  max_upload_size_mb: 'MB',
  courses_per_page: 'item',
  subscription_duration_days: 'hari',
  service_fee: '%',
}

const dangerKeys = new Set(['maintenance_mode'])

function label(key: string): string { return labels[key] ?? key.replaceAll('_', ' ') }
function suffix(key: string): string { return suffixes[key] ?? '' }

const groupNames = computed(() => Object.keys(groups.value))
const filteredGroups = computed<Record<string, SettingItem[]>>(() => {
  const q = search.value.trim().toLowerCase()
  const result: Record<string, SettingItem[]> = {}
  for (const [group, items] of Object.entries(groups.value)) {
    const filtered = items.filter((s) =>
      !q || label(s.key).toLowerCase().includes(q) || s.key.toLowerCase().includes(q),
    )
    if (filtered.length) result[group] = filtered
  }
  return result
})

function scrollGroup(group: string): void {
  activeGroup.value = group
  const el = document.getElementById(`group-${group}`)
  el?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/admin/settings')
    groups.value = data.settings
    activeGroup.value = Object.keys(data.settings)[0] ?? ''
  } catch {
    error.value = 'Gagal memuat pengaturan.'
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  message.value = ''
  error.value = ''
  const settings: any[] = []
  for (const g of Object.keys(groups.value)) {
    for (const s of groups.value[g]) {
      settings.push({ key: s.key, value: s.value, type: s.type, group: g })
    }
  }
  try {
    await api.put('/admin/settings', { settings })
    message.value = 'Pengaturan berhasil disimpan.'
    setTimeout(() => { message.value = '' }, 3000)
  } catch {
    error.value = 'Gagal menyimpan pengaturan.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Konfigurasi</p>
          <h1 class="mt-1 text-2xl font-bold tracking-tight">Pengaturan Sistem</h1>
          <p class="mt-1 text-body-sm text-on-surface-variant">Kelola konfigurasi utama platform Ilmora.</p>
        </div>
        <div class="flex items-center gap-2">
          <input v-model="search" type="search" placeholder="Cari pengaturan..." class="input w-64" />
        </div>
      </div>

      <p v-if="message" class="rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary">{{ message }}</p>
      <p v-if="error" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error">{{ error }}</p>

      <div v-if="loading" class="card text-on-surface-variant">Memuat pengaturan...</div>

      <div v-else class="grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside class="hidden lg:block">
          <div class="sticky top-24 space-y-1 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-3 shadow-sm">
            <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-[0.18em] text-on-surface-variant">Kategori</p>
            <button
              v-for="group in groupNames"
              :key="group"
              type="button"
              class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-body-sm font-medium transition-colors"
              :class="activeGroup === group ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-primary/5 hover:text-primary'"
              @click="scrollGroup(group)"
            >
              <span>{{ group }}</span>
              <span class="text-[10px] opacity-70">{{ groups[group].length }}</span>
            </button>
          </div>
        </aside>

        <div class="space-y-6">
          <section
            v-for="(items, group) in filteredGroups"
            :id="`group-${group}`"
            :key="group"
            class="scroll-mt-24 overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm"
          >
            <div class="flex items-center gap-3 bg-surface-container-low/60 px-5 py-4">
              <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-on-primary">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.41 1.41-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-2v-.09a1.7 1.7 0 0 0-1.03-1.56 1.7 1.7 0 0 0-1.88.34l-.06.06-1.41-1.41.06-.06A1.7 1.7 0 0 0 9.4 15a1.7 1.7 0 0 0-1.56-1.03H7v-2h.84A1.7 1.7 0 0 0 9.4 11a1.7 1.7 0 0 0-.34-1.88L9 9.06l1.41-1.41.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 13.38 6.5V6h2v.5A1.7 1.7 0 0 0 16.4 8.05a1.7 1.7 0 0 0 1.88-.34l.06-.06 1.41 1.41-.06.06A1.7 1.7 0 0 0 19.4 11c.23.6.81.97 1.44.97H21v2h-.16A1.7 1.7 0 0 0 19.4 15Z"/></svg>
              </span>
              <div>
                <h2 class="font-bold text-on-surface">{{ group }}</h2>
                <p class="text-xs text-on-surface-variant">Konfigurasi {{ group.toLowerCase() }} aplikasi</p>
              </div>
            </div>

            <div class="divide-y divide-outline-variant/30">
              <div
                v-for="s in items"
                :key="s.key"
                class="flex min-h-[74px] items-center justify-between gap-5 px-5 py-3 transition-colors hover:bg-surface-container-low/40"
                :class="dangerKeys.has(s.key) ? 'bg-error/5' : ''"
              >
                <div class="max-w-md">
                  <label class="text-body-sm font-semibold capitalize text-on-surface">{{ label(s.key) }}</label>
                  <p v-if="descriptions[s.key]" class="mt-0.5 text-xs text-on-surface-variant">{{ descriptions[s.key] }}</p>
                </div>

                <div class="flex items-center gap-2">
                  <label v-if="s.type === 'boolean'" class="relative inline-flex cursor-pointer items-center">
                    <input v-model="s.value" type="checkbox" class="peer sr-only" />
                    <span class="h-7 w-12 rounded-full bg-outline-variant transition-colors after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:bg-primary peer-checked:after:translate-x-5" />
                  </label>

                  <div v-else class="flex items-center gap-2">
                    <input
                      v-model="s.value"
                      :type="s.type === 'integer' ? 'number' : 'text'"
                      class="input w-40 text-right"
                      :class="dangerKeys.has(s.key) ? 'border-error focus:border-error focus:ring-error/20' : ''"
                    />
                    <span v-if="suffix(s.key)" class="text-body-sm text-on-surface-variant">{{ suffix(s.key) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <p v-if="!Object.keys(filteredGroups).length" class="card text-center text-on-surface-variant">
            Tidak ada pengaturan yang cocok.
          </p>
        </div>
      </div>
    </div>

    <div class="sticky bottom-0 z-20 -mx-4 border-t border-outline-variant/50 bg-white/90 px-4 py-3 backdrop-blur lg:-mx-8 lg:px-8">
      <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-3">
        <p class="text-body-sm text-on-surface-variant">Perubahan belum disimpan sampai Anda menekan tombol simpan.</p>
        <button @click="save" :disabled="saving || loading" class="btn-primary disabled:opacity-50">
          {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </div>
    </div>
  </AdminLayout>
</template>