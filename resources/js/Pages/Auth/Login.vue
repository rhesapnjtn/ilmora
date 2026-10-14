<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AuthLayout from '@/Layouts/AuthLayout.vue'
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
const showPassword = ref(false)

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

function togglePasswordVisibility(): void {
    showPassword.value = !showPassword.value
}

function handleGoogleLogin(): void {
    // TODO: Implement Google OAuth
    console.log('Google login clicked')
}

function handleGitHubLogin(): void {
    // TODO: Implement GitHub OAuth
    console.log('GitHub login clicked')
}
</script>

<template>
    <AuthLayout>
        <div class="w-full max-w-6xl mx-auto my-auto min-h-[640px] rounded-xl overflow-hidden shadow-xl bg-surface-container-lowest grid grid-cols-1 lg:grid-cols-12">
            <!-- Left Showcase Panel (45%) -->
            <div class="lg:col-span-5 bg-gradient-to-br from-primary via-primary-container to-inverse-surface p-space-lg lg:p-space-xl flex flex-col justify-between relative overflow-hidden text-on-primary">
                <!-- Ambient Decorative Geometry -->
                <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-primary-fixed/20 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-16 w-80 h-80 rounded-full bg-secondary-container/20 blur-3xl pointer-events-none"></div>
                <svg class="absolute inset-0 w-full h-full opacity-10 pointer-events-none" height="100%" width="100%" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern height="24" id="dot-grid" patternunits="userSpaceOnUse" width="24">
                            <circle cx="2" cy="2" fill="currentColor" r="1.5"></circle>
                        </pattern>
                    </defs>
                    <rect fill="url(#dot-grid)" height="100%" width="100%"></rect>
                </svg>

                <!-- Brand Mark & Value Headline -->
                <div class="relative z-10 flex flex-col gap-space-lg">
                    <div class="flex items-center gap-space-sm">
                        <div class="w-10 h-10 rounded-lg bg-surface-container-lowest/15 backdrop-blur-md flex items-center justify-center text-tertiary-fixed shadow-sm">
                            <span class="material-symbols-outlined text-[24px]">school</span>
                        </div>
                        <div>
                            <span class="font-title-md text-title-md tracking-tight font-bold text-white flex items-center gap-1.5">
                                Ilmora Academy
                                <span class="inline-block w-2 h-2 rounded-full bg-tertiary-fixed-dim animate-pulse"></span>
                            </span>
                            <p class="font-body-sm text-body-sm text-on-primary-container">Platform Kursus Vokasi & Teknologi</p>
                        </div>
                    </div>
                    <div class="space-y-space-xs mt-space-md">
                        <span class="inline-flex items-center gap-1.5 px-space-sm py-0.5 rounded-full bg-surface-container-lowest/15 backdrop-blur-sm text-secondary-fixed text-label-sm font-label-sm uppercase tracking-wider">
                            <span class="material-symbols-outlined text-[14px]">auto_awesome</span>
                            Akselerasi Karir Digital
                        </span>
                        <h1 class="font-headline-lg text-headline-lg text-white leading-tight">
                            Lanjutkan Perjalanan Belajarmu Menuju Karier Impian
                        </h1>
                        <p class="font-body-md text-body-md text-on-primary-container leading-relaxed">
                            Kurikulum berbasis studi kasus industri terkini, didampingi mentor praktisi terdepan di Indonesia.
                        </p>
                    </div>
                </div>

                <!-- Testimonial & Metrics Stacking -->
                <div class="relative z-10 flex flex-col gap-space-md mt-space-xl">
                    <!-- Student Quote Card -->
                    <div class="bg-surface-container-lowest/10 backdrop-blur-md p-space-md rounded-xl text-white shadow-sm">
                        <div class="flex items-center gap-1 text-secondary-container mb-space-xs">
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                        </div>
                        <p class="font-body-md text-body-md italic text-white/95 leading-snug">
                            "Belajar di Ilmora membantu saya switch career ke Full Stack Developer dalam waktu 6 bulan! Kurikulumnya to the point dan terarah."
                        </p>
                        <div class="flex items-center gap-space-sm mt-space-sm">
                            <img class="w-10 h-10 rounded-full object-cover shadow-sm bg-surface-variant" data-alt="Portrait of Budi Pratama" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCl7pfZcyl-xdrgyBaRf976YibjvTnaO0A8lZBF0mRn8IXc-5uqg4j2JkmjaKmtkmA_nfupYmf38Xbj7OFSDNbt0rqjZsfqfJIK9TnEho_99oRjnVCYTzQf8rjb57kRbVJD_khVdTMJbIroVqni3otbr2dY5VM4IBSEC8MnxK8qJVpb5kK86V7Dw4yhfqJJQiPKlsUDUJJD5MGpuTAQV89QAXbl-7tvzR9lWbzhigw-LFRuyqnHDe7pCQ" />
                            <div class="min-w-0">
                                <p class="font-label-md text-label-md text-white font-semibold truncate">Budi Pratama</p>
                                <p class="font-body-sm text-body-sm text-on-primary-container truncate">Software Engineer di Tech Unicorn</p>
                            </div>
                        </div>
                    </div>

                    <!-- Metric Counters Strip -->
                    <div class="grid grid-cols-3 gap-space-xs pt-space-xs">
                        <div class="bg-surface-container-lowest/5 rounded-lg p-space-xs text-center backdrop-blur-sm">
                            <p class="font-headline-sm text-headline-sm text-white font-bold">120K+</p>
                            <p class="font-label-sm text-label-sm text-on-primary-container">Siswa Aktif</p>
                        </div>
                        <div class="bg-surface-container-lowest/5 rounded-lg p-space-xs text-center backdrop-blur-sm">
                            <p class="font-headline-sm text-headline-sm text-white font-bold">350+</p>
                            <p class="font-label-sm text-label-sm text-on-primary-container">Kursus Praktis</p>
                        </div>
                        <div class="bg-surface-container-lowest/5 rounded-lg p-space-xs text-center backdrop-blur-sm">
                            <p class="font-headline-sm text-headline-sm text-secondary-container font-bold flex items-center justify-center gap-0.5">
                                4.9
                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            </p>
                            <p class="font-label-sm text-label-sm text-on-primary-container">Rating Platform</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Authentication Form Panel (55%) -->
            <div class="lg:col-span-7 p-space-lg lg:p-space-xl flex flex-col justify-between bg-surface-container-lowest">
                <!-- Top Form Header -->
                <div class="w-full max-w-lg mx-auto">
                    <div class="flex items-center justify-between mb-space-md">
                        <div class="inline-flex items-center gap-1.5 px-space-sm py-1 rounded-full bg-surface-container-high text-primary font-label-md text-label-md">
                            <span class="material-symbols-outlined text-[16px]">verified_user</span>
                            Portal Siswa Resmi
                        </div>
                        <RouterLink
                            to="/"
                            class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary font-label-md text-label-md transition-colors"
                            data-path="beranda"
                        >
                            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                            Kembali ke Beranda
                        </RouterLink>
                    </div>

                    <div class="mb-space-lg">
                        <h2 class="font-headline-lg text-headline-lg text-on-surface tracking-tight flex items-center gap-2">
                            Selamat Datang Kembali <span class="inline-block transform hover:rotate-12 transition-transform cursor-default">👋</span>
                        </h2>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                            Masukkan email dan kata sandi untuk mengakses akun Ilmora Anda.
                        </p>
                    </div>

                    <!-- Social Auth Buttons -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-sm mb-space-md">
                        <button
                            type="button"
                            class="flex items-center justify-center gap-2.5 px-space-md py-3 rounded-lg bg-surface-container-low hover:bg-surface-container transition-all active:scale-[0.98] text-on-surface font-label-lg text-label-lg shadow-sm"
                            @click="handleGoogleLogin"
                        >
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"></path>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"></path>
                            </svg>
                            <span>Masuk dengan Google</span>
                        </button>

                        <button
                            type="button"
                            class="flex items-center justify-center gap-2.5 px-space-md py-3 rounded-lg bg-surface-container-low hover:bg-surface-container transition-all active:scale-[0.98] text-on-surface font-label-lg text-label-lg shadow-sm"
                            @click="handleGitHubLogin"
                        >
                            <svg class="w-5 h-5 fill-current text-on-surface" viewBox="0 0 24 24">
                                <path clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" fill-rule="evenodd"></path>
                            </svg>
                            <span>Masuk dengan GitHub</span>
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="relative flex items-center justify-center my-space-md">
                        <div class="w-full h-px bg-surface-container-high"></div>
                        <span class="absolute bg-surface-container-lowest px-space-sm font-label-sm text-label-sm text-outline uppercase tracking-wider">
                            atau masuk dengan email
                        </span>
                    </div>

                    <!-- Credentials Form -->
                    <form class="space-y-space-md" id="loginForm" @submit.prevent="submit">
                        <!-- Email Field -->
                        <div class="space-y-1.5">
                            <label class="block font-label-md text-label-md text-on-surface" for="emailInput">
                                Alamat Email
                            </label>
                            <div class="relative flex items-center">
                                <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none" aria-hidden="true">
                                    mail
                                </span>
                                <Input
                                    id="emailInput"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="username"
                                    placeholder="nama@email.com"
                                    :error="errors.email"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block font-label-md text-label-md text-on-surface" for="passwordInput">
                                    Kata Sandi
                                </label>
                                <RouterLink
                                    to="/lupa-sandi"
                                    class="font-label-md text-label-md text-primary hover:text-on-primary-fixed-variant transition-colors"
                                    data-path="lupa-sandi"
                                >
                                    Lupa Kata Sandi?
                                </RouterLink>
                            </div>
                            <div class="relative flex items-center">
                                <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none" aria-hidden="true">
                                    lock
                                </span>
                                <Input
                                    id="passwordInput"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    placeholder="Minimal 8 karakter"
                                    :error="errors.password"
                                    required
                                />
                                <button
                                    type="button"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                    class="absolute right-3 text-outline hover:text-on-surface transition-colors p-1 flex items-center justify-center rounded"
                                    @click="togglePasswordVisibility"
                                >
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">
                                        {{ showPassword ? 'visibility_off' : 'visibility' }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me Checkbox -->
                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-primary accent-primary cursor-pointer focus:ring-0"
                                    v-model="form.remember"
                                />
                                <span class="font-body-md text-body-md text-on-surface-variant">
                                    Ingat saya selama 30 hari
                                </span>
                            </label>
                        </div>

                        <!-- Submit CTA Button -->
                        <Button type="submit" class="w-full h-12" :loading="processing" :disabled="processing">
                            <span>Masuk Sekarang</span>
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                        </Button>
                    </form>

                    <!-- Registration Switch Link -->
                    <div class="text-center mt-space-md pt-space-xs">
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            Belum punya akun Ilmora?
                            <RouterLink
                                to="/daftar"
                                class="font-label-md text-label-md text-primary font-bold hover:underline ml-1"
                                data-path="daftar"
                            >
                                Daftar Gratis Sekarang
                            </RouterLink>
                        </p>
                    </div>
                </div>

                <!-- Footer Secure Badge -->
                <div class="w-full max-w-lg mx-auto pt-space-lg flex items-center justify-center gap-2 text-on-surface-variant">
                    <span class="material-symbols-outlined text-tertiary-container text-[18px]" aria-hidden="true">lock</span>
                    <span class="font-body-sm text-body-sm text-outline">
                        Data Anda dienkripsi dengan standar keamanan 256-bit SSL
                    </span>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>