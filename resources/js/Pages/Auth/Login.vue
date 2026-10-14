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
        const { messages, message } = normalizeApiError(error)
        Object.assign(errors, messages)

        if (!messages.email) {
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

            <form class="card mx-auto my-8 max-w-md p-6" @submit.prevent="submit">
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
        </div>
    </GuestLayout>
</template>