<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Input from '@/Components/Input.vue'
import Button from '@/Components/Button.vue'
import { useAuth } from '@/stores/auth'
import { normalizeApiError } from '@/lib/errors'

const route = useRoute()
const router = useRouter()
const { resetPassword } = useAuth()

const token = computed(() => String(route.query.token ?? ''))
const email = computed(() => String(route.query.email ?? ''))
const invalidLink = ref(token.value === '' || email.value === '')

const form = reactive({
    password: '',
    password_confirmation: '',
})

const errors = reactive<Record<string, string>>({})
const processing = ref(false)
const status = ref<string | null>(null)
const showPassword = ref(false)

const passwordChecks = computed(() => [
    { label: 'Minimal 8 karakter', valid: form.password.length >= 8 },
    { label: 'Huruf besar dan kecil', valid: /[a-z]/.test(form.password) && /[A-Z]/.test(form.password) },
    { label: 'Mengandung angka', valid: /\d/.test(form.password) },
])

const passwordScore = computed(() => passwordChecks.value.filter((check) => check.valid).length)

const strength = computed(() => {
    if (!form.password) return { label: '', bar: 'bg-outline-variant', text: 'text-on-surface-variant' }
    if (passwordScore.value <= 1) return { label: 'Lemah', bar: 'bg-error', text: 'text-error' }
    if (passwordScore.value === 2) return { label: 'Cukup', bar: 'bg-secondary-300', text: 'text-secondary' }
    return { label: 'Kuat', bar: 'bg-success', text: 'text-success' }
})

function clearErrors(): void {
    for (const key of Object.keys(errors)) {
        delete errors[key]
    }
}

async function submit(): Promise<void> {
    if (processing.value || invalidLink.value) return

    clearErrors()
    processing.value = true
    status.value = null

    try {
        await resetPassword({
            email: email.value,
            token: token.value,
            password: form.password,
            password_confirmation: form.password_confirmation,
        })
        await router.push('/masuk')
    } catch (error) {
        const { messages, message, status: errorStatus } = normalizeApiError(error)
        Object.assign(errors, messages)

        if (errorStatus === 429 || Object.keys(messages).length === 0) {
            status.value = message ?? 'Gagal mengatur ulang kata sandi. Silakan coba lagi.'
        }

        form.password = ''
        form.password_confirmation = ''
    } finally {
        processing.value = false
    }
}

function togglePasswordVisibility(): void {
    showPassword.value = !showPassword.value
}
</script>

<template>
    <AuthLayout>
        <div class="mx-auto flex w-full max-w-lg flex-col justify-center py-10">
            <div class="card overflow-hidden">
                <!-- Header -->
                <div class="border-b border-outline-variant/50 bg-surface-container-low px-6 py-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <span class="material-symbols-outlined text-[28px]" aria-hidden="true">passkey</span>
                    </div>
                    <h1 class="mt-4 font-headline-lg text-headline-lg tracking-tight text-on-surface">
                        Atur Ulang Kata Sandi
                    </h1>
                    <p class="mt-2 font-body-md text-body-md text-on-surface-variant">
                        Buat kata sandi baru yang kuat untuk akun
                        <span class="font-semibold text-on-surface">{{ email || 'Anda' }}</span>.
                    </p>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <div v-if="invalidLink" class="space-y-4">
                        <p role="alert" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error">
                            Tautan pengaturan ulang tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.
                        </p>
                        <RouterLink to="/lupa-sandi" class="btn-primary inline-flex h-12 w-full items-center justify-center">
                            Minta Tautan Baru
                        </RouterLink>
                    </div>

                    <template v-else>
                        <form class="space-y-5" @submit.prevent="submit">
                            <div class="space-y-1.5">
                                <label class="block font-label-md text-label-md text-on-surface" for="resetPassword">
                                    Kata Sandi Baru
                                </label>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined pointer-events-none absolute left-3.5 text-[20px] text-outline" aria-hidden="true">lock</span>
                                    <Input
                                        id="resetPassword"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        placeholder="••••••••"
                                        :error="errors.password"
                                        required
                                    />
                                    <button
                                        type="button"
                                        aria-label="Tampilkan atau sembunyikan kata sandi"
                                        class="absolute right-3 flex items-center justify-center rounded p-1 text-outline transition-colors hover:text-on-surface"
                                        @click="togglePasswordVisibility"
                                    >
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">
                                            {{ showPassword ? 'visibility_off' : 'visibility' }}
                                        </span>
                                    </button>
                                </div>

                                <div v-if="form.password" class="mt-2 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-container-high">
                                            <div
                                                class="h-full rounded-full transition-all duration-300"
                                                :class="strength.bar"
                                                :style="{ width: `${(passwordScore / passwordChecks.length) * 100}%` }"
                                            ></div>
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
                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">
                                                {{ check.valid ? 'check_circle' : 'cancel' }}
                                            </span>
                                            {{ check.label }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block font-label-md text-label-md text-on-surface" for="resetPasswordConfirm">
                                    Konfirmasi Kata Sandi
                                </label>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined pointer-events-none absolute left-3.5 text-[20px] text-outline" aria-hidden="true">lock</span>
                                    <Input
                                        id="resetPasswordConfirm"
                                        v-model="form.password_confirmation"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        placeholder="••••••••"
                                        :error="errors.password_confirmation"
                                        required
                                    />
                                </div>
                            </div>

                            <p v-if="status" role="alert" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error">
                                {{ status }}
                            </p>
                            <p v-if="errors.email" role="alert" class="rounded-2xl bg-error/10 px-4 py-3 text-body-sm text-error">
                                {{ errors.email }}
                            </p>

                            <Button type="submit" class="h-12 w-full" :loading="processing" :disabled="processing">
                                <span>Simpan Kata Sandi Baru</span>
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">check_circle</span>
                            </Button>
                        </form>

                        <p class="mt-6 text-center font-body-md text-body-md text-on-surface-variant">
                            Tidak jadi mengatur ulang?
                            <RouterLink to="/masuk" class="font-label-md font-bold text-primary hover:underline">
                                Masuk
                            </RouterLink>
                        </p>
                    </template>
                </div>
            </div>

            <!-- Security note -->
            <div class="mt-6 rounded-2xl border border-outline-variant bg-surface-container-low p-4">
                <p class="flex items-center gap-2 text-title-md text-on-surface">
                    <span class="material-symbols-outlined text-tertiary" aria-hidden="true">shield</span>
                    Tips keamanan
                </p>
                <ul class="mt-3 space-y-2 text-body-sm text-on-surface-variant">
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary" aria-hidden="true"></span>
                        <span>Gunakan kombinasi huruf besar, huruf kecil, dan angka.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary" aria-hidden="true"></span>
                        <span>Semua perangkat lain akan otomatis dikeluarkan setelah kata sandi diperbarui.</span>
                    </li>
                </ul>
            </div>
        </div>
    </AuthLayout>
</template>
