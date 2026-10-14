<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Input from '@/Components/Input.vue'
import Button from '@/Components/Button.vue'
import { useAuth } from '@/stores/auth'
import { normalizeApiError } from '@/lib/errors'

const router = useRouter()
const { register } = useAuth()

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const errors = reactive<Record<string, string>>({})
const processing = ref(false)
const status = ref<string | null>(null)

const passwordChecks = computed(() => [
    { label: 'Minimal 8 karakter', valid: form.password.length >= 8 },
    { label: 'Huruf besar dan kecil', valid: /[a-z]/.test(form.password) && /[A-Z]/.test(form.password) },
    { label: 'Mengandung angka', valid: /\d/.test(form.password) },
])

const passwordScore = computed(() => passwordChecks.value.filter((check) => check.valid).length)

const strength = computed(() => {
    if (!form.password) {
        return { label: '', bar: 'bg-outline-variant', text: 'text-on-surface-variant' }
    }

    if (passwordScore.value <= 1) {
        return { label: 'Lemah', bar: 'bg-error', text: 'text-error' }
    }

    if (passwordScore.value === 2) {
        return { label: 'Cukup', bar: 'bg-secondary-300', text: 'text-secondary' }
    }

    return { label: 'Kuat', bar: 'bg-success', text: 'text-success' }
})

function clearErrors(): void {
    for (const key of Object.keys(errors)) {
        delete errors[key]
    }
}

async function submit(): Promise<void> {
    clearErrors()
    processing.value = true
    status.value = null

    try {
        await register({ ...form })
        await router.push('/dashboard')
    } catch (error) {
        const { messages, message, status: errorStatus } = normalizeApiError(error)
        Object.assign(errors, messages)

        if (errorStatus === 429 || Object.keys(messages).length === 0) {
            status.value = message ?? 'Gagal mendaftar. Silakan coba lagi.'
        }

        form.password = ''
        form.password_confirmation = ''
    } finally {
        processing.value = false
    }
}
</script>

<template>
    <GuestLayout>
        <div class="mx-auto flex max-w-md flex-col px-4 py-12 md:py-20">
            <div class="text-center">
                <h1 class="text-headline-lg font-bold tracking-tight">Buat akun baru</h1>
                <p class="mt-2 text-body-md text-on-surface-variant">Mulai perjalanan belajar Anda di Ilmora secara gratis.</p>
            </div>

            <p v-if="status" class="mt-6 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="alert">
                {{ status }}
            </p>

            <form class="card mx-auto my-8 max-w-md p-6" @submit.prevent="submit">
                <div class="space-y-5">
                    <Input
                        id="name"
                        v-model="form.name"
                        label="Nama Lengkap"
                        autocomplete="name"
                        placeholder="Nama Anda"
                        :error="errors.name"
                        required
                    />

                    <Input
                        id="email"
                        v-model="form.email"
                        label="Email"
                        type="email"
                        autocomplete="username"
                        placeholder="nama@email.com"
                        :error="errors.email"
                        required
                    />

                    <div>
                        <Input
                            id="password"
                            v-model="form.password"
                            label="Kata Sandi"
                            type="password"
                            autocomplete="new-password"
                            placeholder="••••••••"
                            :error="errors.password"
                            required
                        />

                        <div v-if="form.password" class="mt-2 space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-container-high">
                                    <div
                                        class="h-full rounded-full transition-all duration-normal"
                                        :class="strength.bar"
                                        :style="{ width: `${(passwordScore / passwordChecks.length) * 100}%` }"
                                    />
                                </div>
                                <span class="text-label-md" :class="strength.text">{{ strength.label }}</span>
                            </div>
                            <ul class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                                <li
                                    v-for="check in passwordChecks"
                                    :key="check.label"
                                    class="flex items-center gap-1.5 text-body-sm"
                                    :class="check.valid ? 'text-success' : 'text-on-surface-variant'"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5 shrink-0">
                                        <path v-if="check.valid" stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                    {{ check.label }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        label="Konfirmasi Kata Sandi"
                        type="password"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        :error="errors.password_confirmation"
                        required
                    />
                </div>

                <div class="mt-6 space-y-3">
                    <Button type="submit" class="w-full" :loading="processing" :disabled="processing">
                        Daftar
                    </Button>
                    <p class="text-center text-body-sm text-on-surface-variant">
                        Sudah punya akun?
                        <RouterLink to="/masuk" class="link">Masuk</RouterLink>
                    </p>
                </div>
            </form>

            <p class="mt-4 text-center text-body-sm text-on-surface-variant">
                Dengan mendaftar, Anda menyetujui
                <RouterLink to="/syarat-layanan" class="link">Syarat Layanan</RouterLink>
                dan
                <RouterLink to="/kebijakan-privasi" class="link">Kebijakan Privasi</RouterLink>.
            </p>

            <div class="mt-6 rounded-2xl border border-outline-variant bg-surface-container-low p-4">
                <p class="flex items-center gap-2 text-title-md text-on-surface">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5 shrink-0 text-tertiary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    Data Anda aman bersama kami
                </p>
                <ul class="mt-3 space-y-2 text-body-sm text-on-surface-variant">
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary" aria-hidden="true" />
                        Kata sandi dienkripsi satu arah, tidak pernah disimpan atau ditampilkan kembali.
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary" aria-hidden="true" />
                        Kami tidak pernah membagikan data pribadi Anda kepada pihak ketiga.
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary" aria-hidden="true" />
                        Akun dilindungi pembatasan percobaan masuk dan sesi yang dapat dicabut kapan saja.
                    </li>
                </ul>
            </div>
        </div>
    </GuestLayout>
</template>