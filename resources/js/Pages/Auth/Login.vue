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
    remember: false,
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
    } catch (error) {
        const { messages, message, status: errorStatus } = normalizeApiError(error)
        Object.assign(errors, messages)

        if (errorStatus === 429) {
            status.value = message
        } else if (!messages.email) {
            errors.email = message ?? 'Gagal masuk. Silakan periksa kembali data Anda.'
        }

        form.password = ''

        return
    } finally {
        processing.value = false
    }

    try {
        await router.push('/dashboard')
    } catch {
        // Authentication succeeded; a navigation failure (e.g. a stale chunk)
        // must not be reported as invalid credentials. router.onError reloads.
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
                        <pattern height="24" id="dot-grid" patternUnits="userSpaceOnUse" width="24">
                            <circle cx="2" cy="2" fill="currentColor" r="1.5"></circle>
                        </pattern>
                    </defs>
                    <rect fill="url(#dot-grid)" height="100%" width="100%"></rect>
                </svg>

                <!-- Brand Mark & Value Headline -->
                <div class="relative z-10 flex flex-col gap-space-lg">
                    <div class="flex items-center gap-space-sm">
                        
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
                    <div class="mb-space-lg">
                        <h2 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">
                            Selamat Datang Kembali
                        </h2>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                            Masukkan email dan kata sandi untuk mengakses akun Ilmora Anda.
                        </p>
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