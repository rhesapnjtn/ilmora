<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AuthLayout from '@/Layouts/AuthLayout.vue'
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
const showPassword = ref(false)

const passwordChecks = computed(() => [
    {
        label: 'Minimal 8 karakter',
        valid: form.password.length >= 8,
    },
    {
        label: 'Huruf besar dan kecil',
        valid:
            /[a-z]/.test(form.password) &&
            /[A-Z]/.test(form.password),
    },
    {
        label: 'Mengandung angka',
        valid: /\d/.test(form.password),
    },
])

const passwordScore = computed(
    () => passwordChecks.value.filter((check) => check.valid).length,
)

const strength = computed(() => {
    if (!form.password) {
        return {
            label: '',
            bar: 'bg-outline-variant',
            text: 'text-on-surface-variant',
        }
    }

    if (passwordScore.value <= 1) {
        return {
            label: 'Lemah',
            bar: 'bg-error',
            text: 'text-error',
        }
    }

    if (passwordScore.value === 2) {
        return {
            label: 'Cukup',
            bar: 'bg-secondary-300',
            text: 'text-secondary',
        }
    }

    return {
        label: 'Kuat',
        bar: 'bg-success',
        text: 'text-success',
    }
})

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

    try {
        await register({ ...form })
        await router.push('/dashboard')
    } catch (error: unknown) {
        const {
            messages,
            message,
            status: errorStatus,
        } = normalizeApiError(error)

        Object.assign(errors, messages)

        if (errorStatus === 429 || Object.keys(messages).length === 0) {
            status.value =
                message ?? 'Gagal mendaftar. Silakan coba lagi.'
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
        <div
            class="mx-auto my-auto grid min-h-[640px] w-full max-w-6xl grid-cols-1 overflow-hidden rounded-xl bg-surface-container-lowest shadow-xl lg:grid-cols-12"
        >
            <!-- Left Showcase Panel -->
            <div
                class="relative flex flex-col justify-between overflow-hidden bg-gradient-to-br from-primary via-primary-container to-inverse-surface p-space-lg text-on-primary lg:col-span-5 lg:p-space-xl"
            >
                <!-- Ambient Decorative Geometry -->
                <div
                    class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-primary-fixed/20 blur-3xl"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-24 -right-16 h-80 w-80 rounded-full bg-secondary-container/20 blur-3xl"
                ></div>

                <!-- Dot Grid -->
                <svg
                    class="pointer-events-none absolute inset-0 h-full w-full opacity-10"
                    height="100%"
                    width="100%"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <defs>
                        <pattern
                            id="dot-grid"
                            width="24"
                            height="24"
                            patternUnits="userSpaceOnUse"
                        >
                            <circle
                                cx="2"
                                cy="2"
                                r="1.5"
                                fill="currentColor"
                            />
                        </pattern>
                    </defs>
                    <rect
                        width="100%"
                        height="100%"
                        fill="url(#dot-grid)"
                    />
                </svg>

                <!-- Brand and Headline -->
                <div class="relative z-10 flex flex-col gap-space-lg">
                    <div class="flex items-center gap-space-sm">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container-lowest/15 text-tertiary-fixed shadow-sm backdrop-blur-md"
                        >
                            <span
                                class="material-symbols-outlined text-[24px]"
                                aria-hidden="true"
                            >
                                school
                            </span>
                        </div>

                        <div>
                            <span
                                class="flex items-center gap-1.5 font-title-md text-title-md font-bold tracking-tight text-white"
                            >
                                Ilmora Academy
                                <span
                                    class="inline-block h-2 w-2 animate-pulse rounded-full bg-tertiary-fixed-dim"
                                ></span>
                            </span>

                            <p
                                class="font-body-sm text-body-sm text-on-primary-container"
                            >
                                Platform Kursus Vokasi &amp; Teknologi
                            </p>
                        </div>
                    </div>

                    <div class="mt-space-md space-y-space-xs">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-surface-container-lowest/15 px-space-sm py-0.5 font-label-sm text-label-sm uppercase tracking-wider text-secondary-fixed backdrop-blur-sm"
                        >
                            <span
                                class="material-symbols-outlined text-[14px]"
                                aria-hidden="true"
                            >
                                auto_awesome
                            </span>
                            Akselerasi Karir Digital
                        </span>

                        <h1
                            class="font-headline-lg text-headline-lg leading-tight text-white"
                        >
                            Mulai Perjalanan Belajarmu Hari Ini
                        </h1>

                        <p
                            class="font-body-md text-body-md leading-relaxed text-on-primary-container"
                        >
                            Bergabunglah dengan ribuan siswa yang sudah
                            mengubah karir mereka bersama Ilmora.
                        </p>
                    </div>
                </div>

                <!-- Testimonial and Metrics -->
                <div
                    class="relative z-10 mt-space-xl flex flex-col gap-space-md"
                >
                    <div
                        class="rounded-xl bg-surface-container-lowest/10 p-space-md text-white shadow-sm backdrop-blur-md"
                    >
                        <div
                            class="mb-space-xs flex items-center gap-1 text-secondary-container"
                            aria-label="5 dari 5 bintang"
                        >
                            <span
                                v-for="star in 5"
                                :key="star"
                                class="material-symbols-outlined text-[18px]"
                                style="font-variation-settings: 'FILL' 1"
                                aria-hidden="true"
                            >
                                star
                            </span>
                        </div>

                        <p
                            class="font-body-md text-body-md leading-snug text-white/95 italic"
                        >
                            "Belajar di Ilmora membantu saya switch career ke
                            Full Stack Developer dalam waktu 6 bulan!
                            Kurikulumnya to the point dan terarah."
                        </p>

                        <div
                            class="mt-space-sm flex items-center gap-space-sm"
                        >
                            <img
                                class="h-10 w-10 rounded-full bg-surface-variant object-cover shadow-sm"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCl7pfZcyl-xdrgyBaRf976YibjvTnaO0A8lZBF0mRn8IXc-5uqg4j2JkmjaKmtkmA_nfupYmf38Xbj7OFSDNbt0rqjZsfqfJIK9TnEho_99oRjnVCYTzQf8rjb57kRbVJD_khVdTMJbIroVqni3otbr2dY5VM4IBSEC8MnxK8qJVpb5kK86V7Dw4yhfqJJQiPKlsUDUJJD5MGpuTAQV89QAXbl-7tvzR9lWbzhigw-LFRuyqnHDe7pCQ"
                                alt="Foto ilustrasi siswa Ilmora"
                            />

                            <div class="min-w-0">
                                <p
                                    class="truncate font-label-md text-label-md font-semibold text-white"
                                >
                                    Budi Pratama
                                </p>
                                <p
                                    class="truncate font-body-sm text-body-sm text-on-primary-container"
                                >
                                    Software Engineer di Tech Unicorn
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-3 gap-space-xs pt-space-xs"
                    >
                        <div
                            class="rounded-lg bg-surface-container-lowest/5 p-space-xs text-center backdrop-blur-sm"
                        >
                            <p
                                class="font-headline-sm text-headline-sm font-bold text-white"
                            >
                                120K+
                            </p>
                            <p
                                class="font-label-sm text-label-sm text-on-primary-container"
                            >
                                Siswa Aktif
                            </p>
                        </div>

                        <div
                            class="rounded-lg bg-surface-container-lowest/5 p-space-xs text-center backdrop-blur-sm"
                        >
                            <p
                                class="font-headline-sm text-headline-sm font-bold text-white"
                            >
                                350+
                            </p>
                            <p
                                class="font-label-sm text-label-sm text-on-primary-container"
                            >
                                Kursus Praktis
                            </p>
                        </div>

                        <div
                            class="rounded-lg bg-surface-container-lowest/5 p-space-xs text-center backdrop-blur-sm"
                        >
                            <p
                                class="flex items-center justify-center gap-0.5 font-headline-sm text-headline-sm font-bold text-secondary-container"
                            >
                                4.9
                                <span
                                    class="material-symbols-outlined text-[16px]"
                                    style="font-variation-settings: 'FILL' 1"
                                    aria-hidden="true"
                                >
                                    star
                                </span>
                            </p>
                            <p
                                class="font-label-sm text-label-sm text-on-primary-container"
                            >
                                Rating Platform
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Authentication Panel -->
            <div
                class="flex flex-col justify-between bg-surface-container-lowest p-space-lg lg:col-span-7 lg:p-space-xl"
            >
                <div class="mx-auto w-full max-w-lg">
                    <!-- Header -->
                    <div
                        class="mb-space-md flex flex-wrap items-center justify-between gap-3"
                    >
                        <div
                            class="inline-flex items-center gap-1.5 rounded-full bg-surface-container-high px-space-sm py-1 font-label-md text-label-md text-primary"
                        >
                            <span
                                class="material-symbols-outlined text-[16px]"
                                aria-hidden="true"
                            >
                                verified_user
                            </span>
                            Portal Siswa Resmi
                        </div>

                        <RouterLink
                            to="/"
                            class="inline-flex items-center gap-1 font-label-md text-label-md text-on-surface-variant transition-colors hover:text-primary"
                        >
                            <span
                                class="material-symbols-outlined text-[16px]"
                                aria-hidden="true"
                            >
                                arrow_back
                            </span>
                            Kembali ke Beranda
                        </RouterLink>
                    </div>

                    <div class="mb-space-lg">
                        <h2
                            class="flex items-center gap-2 font-headline-lg text-headline-lg tracking-tight text-on-surface"
                        >
                            Buat Akun Baru
                            <span
                                class="inline-block cursor-default transition-transform hover:rotate-12"
                            >
                                👋
                            </span>
                        </h2>

                        <p
                            class="mt-1 font-body-md text-body-md text-on-surface-variant"
                        >
                            Masukkan data Anda untuk membuat akun Ilmora baru.
                        </p>
                    </div>

                    <!-- Social Authentication -->
                    <div
                        class="mb-space-md grid grid-cols-1 gap-space-sm sm:grid-cols-2"
                    >
                        <button
                            type="button"
                            class="flex items-center justify-center gap-2.5 rounded-lg bg-surface-container-low px-space-md py-3 font-label-lg text-label-lg text-on-surface shadow-sm transition-all hover:bg-surface-container active:scale-[0.98]"
                            @click="status = 'Pendaftaran Google belum dikonfigurasi.'"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                    fill="#4285F4"
                                />
                                <path
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                    fill="#34A853"
                                />
                                <path
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"
                                    fill="#FBBC05"
                                />
                                <path
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"
                                    fill="#EA4335"
                                />
                            </svg>
                            <span>Daftar dengan Google</span>
                        </button>

                        <button
                            type="button"
                            class="flex items-center justify-center gap-2.5 rounded-lg bg-surface-container-low px-space-md py-3 font-label-lg text-label-lg text-on-surface shadow-sm transition-all hover:bg-surface-container active:scale-[0.98]"
                            @click="status = 'Pendaftaran GitHub belum dikonfigurasi.'"
                        >
                            <svg
                                class="h-5 w-5 fill-current text-on-surface"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                />
                            </svg>
                            <span>Daftar dengan GitHub</span>
                        </button>
                    </div>

                    <!-- Divider -->
                    <div
                        class="relative my-space-md flex items-center justify-center"
                    >
                        <div
                            class="h-px w-full bg-surface-container-high"
                        ></div>
                        <span
                            class="absolute bg-surface-container-lowest px-space-sm font-label-sm text-label-sm uppercase tracking-wider text-outline"
                        >
                            atau daftar dengan email
                        </span>
                    </div>

                    <!-- Registration Form -->
                    <form
                        class="space-y-space-md"
                        @submit.prevent="submit"
                    >
                        <!-- Name -->
                        <div class="space-y-1.5">
                            <label
                                for="nameInput"
                                class="block font-label-md text-label-md text-on-surface"
                            >
                                Nama Lengkap
                            </label>

                            <div class="relative flex items-center">
                                <span
                                    class="pointer-events-none absolute left-3.5 text-[20px] text-outline"
                                    aria-hidden="true"
                                >
                                    <span class="material-symbols-outlined">
                                        person
                                    </span>
                                </span>

                                <Input
                                    id="nameInput"
                                    v-model="form.name"
                                    label="Nama Lengkap"
                                    autocomplete="name"
                                    placeholder="Nama Anda"
                                    :error="errors.name"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label
                                for="emailInput"
                                class="block font-label-md text-label-md text-on-surface"
                            >
                                Alamat Email
                            </label>

                            <div class="relative flex items-center">
                                <span
                                    class="pointer-events-none absolute left-3.5 text-[20px] text-outline"
                                    aria-hidden="true"
                                >
                                    <span class="material-symbols-outlined">
                                        mail
                                    </span>
                                </span>

                                <Input
                                    id="emailInput"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="nama@email.com"
                                    :error="errors.email"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label
                                for="passwordInput"
                                class="block font-label-md text-label-md text-on-surface"
                            >
                                Kata Sandi
                            </label>

                            <div class="relative flex items-center">
                                <span
                                    class="pointer-events-none absolute left-3.5 text-[20px] text-outline"
                                    aria-hidden="true"
                                >
                                    <span class="material-symbols-outlined">
                                        lock
                                    </span>
                                </span>

                                <Input
                                    id="passwordInput"
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
                                    <span
                                        class="material-symbols-outlined text-[20px]"
                                        aria-hidden="true"
                                    >
                                        {{
                                            showPassword
                                                ? 'visibility_off'
                                                : 'visibility'
                                        }}
                                    </span>
                                </button>
                            </div>

                            <!-- Password Strength -->
                            <div
                                v-if="form.password"
                                class="mt-2 space-y-2"
                            >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-container-high"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all duration-300"
                                            :class="strength.bar"
                                            :style="{
                                                width: `${(passwordScore / passwordChecks.length) * 100}%`,
                                            }"
                                        ></div>
                                    </div>

                                    <span
                                        class="text-label-md"
                                        :class="strength.text"
                                    >
                                        {{ strength.label }}
                                    </span>
                                </div>

                                <ul
                                    class="grid grid-cols-1 gap-1 sm:grid-cols-2"
                                >
                                    <li
                                        v-for="check in passwordChecks"
                                        :key="check.label"
                                        class="flex items-center gap-1.5 text-body-sm"
                                        :class="
                                            check.valid
                                                ? 'text-success'
                                                : 'text-on-surface-variant'
                                        "
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="2"
                                            stroke="currentColor"
                                            class="h-3.5 w-3.5 shrink-0"
                                            aria-hidden="true"
                                        >
                                            <path
                                                v-if="check.valid"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m4.5 12.75 6 6 9-13.5"
                                            />
                                            <path
                                                v-else
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M6 18 18 6M6 6l12 12"
                                            />
                                        </svg>

                                        {{ check.label }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-1.5">
                            <label
                                for="passwordConfirmInput"
                                class="block font-label-md text-label-md text-on-surface"
                            >
                                Konfirmasi Kata Sandi
                            </label>

                            <div class="relative flex items-center">
                                <span
                                    class="pointer-events-none absolute left-3.5 text-[20px] text-outline"
                                    aria-hidden="true"
                                >
                                    <span class="material-symbols-outlined">
                                        lock
                                    </span>
                                </span>

                                <Input
                                    id="passwordConfirmInput"
                                    v-model="form.password_confirmation"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    :error="errors.password_confirmation"
                                    required
                                />

                                <button
                                    type="button"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                    class="absolute right-3 flex items-center justify-center rounded p-1 text-outline transition-colors hover:text-on-surface"
                                    @click="togglePasswordVisibility"
                                >
                                    <span
                                        class="material-symbols-outlined text-[20px]"
                                        aria-hidden="true"
                                    >
                                        {{
                                            showPassword
                                                ? 'visibility_off'
                                                : 'visibility'
                                        }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Server Status -->
                        <div
                            v-if="status"
                            role="alert"
                            class="rounded-lg border border-error/30 bg-error/10 p-3 text-body-sm text-error"
                        >
                            {{ status }}
                        </div>

                        <!-- Submit -->
                        <div class="mt-6 space-y-3">
                            <Button
                                type="submit"
                                class="h-12 w-full"
                                :loading="processing"
                                :disabled="processing"
                            >
                                <span>Daftar Sekarang</span>
                                <span
                                    class="material-symbols-outlined text-[20px]"
                                    aria-hidden="true"
                                >
                                    arrow_forward
                                </span>
                            </Button>

                            <p
                                class="text-center text-body-sm text-on-surface-variant"
                            >
                                Sudah punya akun?
                                <RouterLink to="/masuk" class="link">
                                    Masuk
                                </RouterLink>
                            </p>
                        </div>
                    </form>

                    <!-- Terms -->
                    <p
                        class="mt-4 text-center text-body-sm text-on-surface-variant"
                    >
                        Dengan mendaftar, Anda menyetujui
                        <RouterLink
                            to="/syarat-layanan"
                            class="link"
                        >
                            Syarat Layanan
                        </RouterLink>
                        dan
                        <RouterLink
                            to="/kebijakan-privasi"
                            class="link"
                        >
                            Kebijakan Privasi
                        </RouterLink>.
                    </p>

                    <!-- Security Information -->
                    <div
                        class="mt-6 rounded-2xl border border-outline-variant bg-surface-container-low p-4"
                    >
                        <p
                            class="flex items-center gap-2 text-title-md text-on-surface"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                                class="h-5 w-5 shrink-0 text-tertiary"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"
                                />
                            </svg>

                            Data Anda aman bersama kami
                        </p>

                        <ul
                            class="mt-3 space-y-2 text-body-sm text-on-surface-variant"
                        >
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary"
                                    aria-hidden="true"
                                ></span>
                                <span>
                                    Kata sandi disimpan menggunakan hashing
                                    yang aman dan tidak ditampilkan kembali.
                                </span>
                            </li>

                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary"
                                    aria-hidden="true"
                                ></span>
                                <span>
                                    Data pribadi diproses sesuai kebijakan
                                    privasi Ilmora.
                                </span>
                            </li>

                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-tertiary"
                                    aria-hidden="true"
                                ></span>
                                <span>
                                    Perlindungan akun bergantung pada
                                    konfigurasi autentikasi dan sesi aplikasi.
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>