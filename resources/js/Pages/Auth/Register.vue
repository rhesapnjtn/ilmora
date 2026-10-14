<script setup lang="ts">
import { reactive, ref } from 'vue'
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
        const { messages, message } = normalizeApiError(error)
        Object.assign(errors, messages)

        if (Object.keys(messages).length === 0) {
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
        </div>
    </GuestLayout>
</template>