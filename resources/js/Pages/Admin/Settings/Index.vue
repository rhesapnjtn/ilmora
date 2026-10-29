<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '@/lib/api'

interface SettingItem { key: string; value: any; type: string }
const groups = ref<Record<string, SettingItem[]>>({})
const loading = ref(false)
const saving = ref(false)
const message = ref('')
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/admin/settings')
    groups.value = data.settings
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
  } catch {
    error.value = 'Gagal menyimpan pengaturan.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">Pengaturan Sistem</h1>
    <p v-if="message" class="text-green-600">{{ message }}</p>
    <p v-if="error" class="text-red-600">{{ error }}</p>
    <div v-if="loading" class="text-gray-500">Memuat...</div>
    <div v-else class="space-y-6">
      <div v-for="(items, group) in groups" :key="group" class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3 capitalize">{{ group }}</h2>
        <div class="space-y-3">
          <div v-for="s in items" :key="s.key" class="flex flex-col">
            <label class="text-sm text-gray-600 mb-1">{{ s.key }}</label>
            <input v-model="s.value" class="border rounded px-3 py-2" />
          </div>
        </div>
      </div>
      <button
        @click="save"
        :disabled="saving"
        class="bg-purple-600 text-white px-4 py-2 rounded disabled:opacity-50"
      >
        {{ saving ? 'Menyimpan...' : 'Simpan' }}
      </button>
    </div>
  </div>
</template>
