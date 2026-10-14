<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Input from '@/Components/Input.vue'
import Button from '@/Components/Button.vue'
import { useAuth } from '@/stores/auth'
import { normalizeApiError } from '@/lib/errors'

const router = useRouter()
const { login } = useAuth()

const form = reactive({
    email: '',
    password: '',
})

const errors = reactive<Record<string, string>>({})
const processing = ref(false)
const status = ref<string | null>(null)

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
        await login({ email: form.email, password: form.password })
        await router.push('/dashboard')
    } catch (error) {
        const { messages, message, status: errorStatus } = normalizeApiError(error)
        Object.assign(errors, messages)

        if (errorStatus === 429) {
            status.value = message
        } else if (!messages.email) {
            errors.email = message ?? 'Gagal masuk. Silakan periksa kembali data Anda.'
        }

        form.password = ''
    } finally {
        processing.value = false
    }
}
</script>

<template>
    <GuestLayout>
        <div class="mx-auto flex max-w-md flex-col px-4 py-12 md:py-20">
            <div class="text-center">
                <h1 class="text-headline-lg font-bold tracking-tight">Selamat datang kembali</h1>
                <p class="mt-2 text-body-md text-on-surface-variant">Masuk untuk melanjutkan perjalanan belajar Anda.</p>
            </div>

            <p v-if="status" class="mt-6 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary" role="status">
                {{ status }}
            </p>

            <form class="card mt-6" @submit.prevent="submit">
                <div class="space-y-5">
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

                    <Input
                        id="password"
                        v-model="form.password"
                        label="Kata Sandi"
                        type="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        :error="errors.password"
                        required
                    />
                </div>

                <div class="mt-6 space-y-3">
                    <Button type="submit" class="w-full" :loading="processing" :disabled="processing">
                        Masuk
                    </Button>
                    <p class="text-center text-body-sm text-on-surface-variant">
                        Belum punya akun?
                        <RouterLink to="/daftar" class="link">Daftar gratis</RouterLink>
                    </p>
                </div>
            </form>

            <ul class="mt-6 space-y-2 text-body-sm text-on-surface-variant">
                <li class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0 text-tertiary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12.75v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    Kata sandi dienkripsi dan tidak pernah disimpan dalam bentuk teks biasa.
                </li>
                <li class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0 text-tertiary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Kami membatasi percobaan masuk untuk melindungi akun Anda.
                </li>
            </ul>
        </div>
    </GuestLayout>
</template>
