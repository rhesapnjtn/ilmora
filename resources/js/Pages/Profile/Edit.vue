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

const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const passwordErrors = reactive<Record<string, string>>({})
const passwordProcessing = ref(false)
const passwordSaved = ref(false)
const passwordStatus = ref<string | null>(null)

function clearPasswordErrors(): void {
    for (const key of Object.keys(passwordErrors)) {
        delete passwordErrors[key]
    }
}

async function submitPassword(): Promise<void> {
    clearPasswordErrors()
    passwordProcessing.value = true
    passwordSaved.value = false
    passwordStatus.value = null

    try {
        await api.put('/password', { ...passwordForm })

        passwordSaved.value = true
        passwordForm.current_password = ''
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
    } catch (error) {
        const { messages, message, status: errorStatus } = normalizeApiError(error)
        Object.assign(passwordErrors, messages)

        if (Object.keys(messages).length === 0 || errorStatus === 429) {
            passwordStatus.value = message ?? 'Gagal memperbarui kata sandi.'
        }
    } finally {
        passwordProcessing.value = false
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

            <div class="mt-10">
                <h2 class="text-headline-sm font-semibold tracking-tight">Keamanan</h2>
                <p class="mt-1 text-body-md text-on-surface-variant">
                    Ganti kata sandi secara berkala. Semua perangkat lain akan otomatis dikeluarkan setelah penggantian.
                </p>

                <p
                    v-if="passwordStatus"
                    class="mt-6 rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error"
                    role="alert"
                >
                    {{ passwordStatus }}
                </p>

                <form class="card mt-6" @submit.prevent="submitPassword">
                    <div class="space-y-5">
                        <Input
                            id="current_password"
                            v-model="passwordForm.current_password"
                            label="Kata Sandi Saat Ini"
                            type="password"
                            autocomplete="current-password"
                            :error="passwordErrors.current_password"
                            required
                        />

                        <Input
                            id="new_password"
                            v-model="passwordForm.password"
                            label="Kata Sandi Baru"
                            type="password"
                            autocomplete="new-password"
                            :error="passwordErrors.password"
                            required
                        />

                        <Input
                            id="new_password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            label="Konfirmasi Kata Sandi Baru"
                            type="password"
                            autocomplete="new-password"
                            :error="passwordErrors.password_confirmation"
                            required
                        />
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <Button type="submit" :loading="passwordProcessing" :disabled="passwordProcessing">
                            Perbarui Kata Sandi
                        </Button>
                        <span v-if="passwordSaved" class="text-body-sm text-tertiary">Kata sandi diperbarui.</span>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
