<script setup lang="ts">
import { reactive, ref, watchEffect } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Input from '@/Components/Input.vue'
import Button from '@/Components/Button.vue'
import { useAuth } from '@/stores/auth'
import api from '@/lib/api'
import { normalizeApiError } from '@/lib/errors'

const { user } = useAuth()

const form = reactive({
    name: '',
    email: '',
})

watchEffect(() => {
    if (user.value) {
        form.name = user.value.name
        form.email = user.value.email
    }
})

const errors = reactive<Record<string, string>>({})
const processing = ref(false)
const saved = ref(false)
const status = ref<string | null>(null)

function clearErrors(): void {
    for (const key of Object.keys(errors)) {
        delete errors[key]
    }
}

async function submit(): Promise<void> {
    clearErrors()
    processing.value = true
    saved.value = false
    status.value = null

    try {
        const { data } = await api.put<{ user: { id: number; name: string; email: string } }>('/profile', {
            name: form.name,
            email: form.email,
        })

        if (user.value) {
            user.value.name = data.user.name
            user.value.email = data.user.email
        }

        saved.value = true
    } catch (error) {
        const { messages, message } = normalizeApiError(error)
        Object.assign(errors, messages)

        if (Object.keys(messages).length === 0) {
            status.value = message ?? 'Gagal menyimpan perubahan.'
        }
    } finally {
        processing.value = false
    }
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-display-sm font-bold tracking-tight">Profil Saya</h1>
            <p class="mt-2 text-body-md text-on-surface-variant">Perbarui informasi akun Anda.</p>

            <p v-if="status" class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error" role="alert">
                {{ status }}
            </p>

            <form class="card mt-6" @submit.prevent="submit">
                <div class="space-y-5">
                    <Input
                        id="name"
                        v-model="form.name"
                        label="Nama Lengkap"
                        autocomplete="name"
                        :error="errors.name"
                        required
                    />

                    <Input
                        id="email"
                        v-model="form.email"
                        label="Email"
                        type="email"
                        autocomplete="username"
                        :error="errors.email"
                        required
                    />
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <Button type="submit" :loading="processing" :disabled="processing">
                        Simpan Perubahan
                    </Button>
                    <span v-if="saved" class="text-body-sm text-tertiary">Tersimpan.</span>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
