<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Input from '@/Components/Input.vue'
import Button from '@/Components/Button.vue'
import { useAuth } from '@/stores/auth'
import { normalizeApiError } from '@/lib/errors'

const { forgotPassword } = useAuth()

const form = reactive({
    email: '',
})

const errors = reactive<Record<string, string>>({})
const processing = ref(false)
const status = ref<string | null>(null)
const notice = ref<string | null>(null)

function clearErrors(): void {
    for (const key of Object.keys(errors)) {
        delete errors[key]
    }
}

async function submit(): Promise<void> {
    if (processing.value) return

    clearErrors()
    processing.value = true
    status.value = null
    notice.value = null

    try {
        await forgotPassword(form.email)

        notice.value =
            'Jika email Anda terdaftar, tautan pengaturan ulang kata sandi telah kami kirimkan. Periksa kotak masuk atau folder spam.'
        form.email = ''
    } catch (error) {
        const { messages, message, status: errorStatus } = normalizeApiError(error)
        Object.assign(errors, messages)

        if (errorStatus === 429 || Object.keys(messages).length === 0) {
            status.value = message ?? 'Terlalu banyak permintaan. Silakan coba lagi nanti.'
        }
    } finally {
        processing.value = false
    }
}
</script>

<template>
    <AuthLayout>
        <div class="mx-auto flex w-full max-w-lg flex-col justify-center py-10">
            <div class="card overflow-hidden">
                <!-- Header -->
                <div class="border-b border-outline-variant/50 bg-surface-container-low px-6 py-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <span class="material-symbols-outlined text-[28px]" aria-hidden="true">lock_reset</span>
                    </div>
                    <h1 class="mt-4 font-headline-lg text-headline-lg tracking-tight text-on-surface">
                        Lupa Kata Sandi?
                    </h1>
                    <p class="mt-2 font-body-md text-body-md text-on-surface-variant">
                        Masukkan email yang terdaftar. Kami akan mengirim tautan untuk mengatur ulang kata sandi Anda.
                    </p>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <p v-if="notice" role="status" class="mb-5 rounded-2xl bg-tertiary/10 px-4 py-3 text-body-sm text-tertiary">
                        {{ notice }}
                    </p>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="space-y-1.5">
                            <label class="block font-label-md text-label-md text-on-surface" for="forgotEmail">
                                Alamat Email
                            </label>
                            <div class="relative flex items-center">
                                <span class="material-symbols-outlined pointer-events-none absolute left-3.5 text-[20px] text-outline" aria-hidden="true">mail</span>
                                <Input
                                    id="forgotEmail"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="nama@email.com"
                                    :error="errors.email"
                                    required
                                />
                            </div>
                        </div>

                        <p v-if="status" role="alert" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error">
                            {{ status }}
                        </p>

                        <Button type="submit" class="h-12 w-full" :loading="processing" :disabled="processing">
                            <span>Kirim Tautan Reset</span>
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">send</span>
                        </Button>
                    </form>

                    <p class="mt-6 text-center font-body-md text-body-md text-on-surface-variant">
                        Ingat kata sandi Anda?
                        <RouterLink to="/masuk" class="font-label-md font-bold text-primary hover:underline">
                            Masuk
                        </RouterLink>
                    </p>
                </div>
            </div>

            <!-- Security note -->
            <div class="mt-6 rounded-2xl border border-outline-variant bg-surface-container-low p-4">
                <p class="flex items-center gap-2 text-title-md text-on-surface">
                    <span class="material-symbols-outlined text-tertiary" aria-hidden="true">shield</span>
                    Tautan aman &amp; terbatas
                </p>
                <ul class="mt-3 space-y-2 text-body-sm text-on-surface-variant">
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary" aria-hidden="true"></span>
                        <span>Tautan hanya berlaku 60 menit dan sekali pakai.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary" aria-hidden="true"></span>
                        <span>Kami tidak pernah meminta kata sandi Anda melalui email.</span>
                    </li>
                </ul>
            </div>
        </div>
    </AuthLayout>
</template>
