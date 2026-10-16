<script setup lang="ts">
import { computed, reactive, ref, watchEffect } from 'vue'
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

// ---- Foto profil ----
const avatarInput = ref<HTMLInputElement | null>(null)
const avatarPreview = ref<string | null>(null)
const avatarProcessing = ref(false)
const avatarError = ref<string | null>(null)

const avatarSrc = computed(() => avatarPreview.value ?? user.value?.avatar_url ?? null)
const initial = computed(() => (user.value?.name ?? '?').charAt(0).toUpperCase())

async function onAvatarChange(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    input.value = '' // supaya file yang sama bisa dipilih lagi
    if (!file) return

    avatarError.value = null

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        avatarError.value = 'Format foto harus JPG, PNG, atau WebP.'
        return
    }
    if (file.size > 2 * 1024 * 1024) {
        avatarError.value = 'Ukuran foto maksimal 2 MB.'
        return
    }

    avatarPreview.value = URL.createObjectURL(file) // tampil langsung sebelum upload selesai
    avatarProcessing.value = true

    try {
        const body = new FormData()
        body.append('avatar', file)

        const { data } = await api.post<{ user: { avatar_url: string | null } }>('/profile/avatar', body, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        if (user.value) {
            user.value.avatar_url = data.user.avatar_url
        }
    } catch (error) {
        const { messages, message } = normalizeApiError(error)
        avatarError.value = messages.avatar ?? message ?? 'Gagal mengunggah foto.'
    } finally {
        if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value)
        avatarPreview.value = null
        avatarProcessing.value = false
    }
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <!-- Foto profil -->
            <div class="flex flex-col items-center text-center">
            <h1 class="mt-6 text-display-sm font-bold tracking-tight">Foto Profil</h1>
                <div class="group relative h-28 w-28 shrink-0">
                    <img
                        v-if="avatarSrc"
                        :src="avatarSrc"
                        alt="Foto profil"
                        class="h-full w-full rounded-full border-2 border-primary/20 object-cover"
                    />
                    <div
                        v-else
                        class="grid h-full w-full place-items-center rounded-full bg-primary text-4xl font-bold text-white"
                        aria-hidden="true"
                    >
                        {{ initial }}
                    </div>

                    <button
                        type="button"
                        class="absolute inset-0 flex flex-col items-center justify-center gap-1 rounded-full bg-black/55 text-white transition-opacity group-hover:opacity-100 focus-visible:opacity-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                        :class="avatarProcessing ? 'opacity-100' : 'opacity-0'"
                        :disabled="avatarProcessing"
                        aria-label="Ubah foto profil"
                        @click="avatarInput?.click()"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M13.5 3.5l3 3L7 16H4v-3z" />
                        </svg>
                        <span class="text-xs font-semibold">{{ avatarProcessing ? 'Mengunggah...' : 'Ubah' }}</span>
                    </button>

                    <input
                        ref="avatarInput"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        class="hidden"
                        @change="onAvatarChange"
                    />
                </div>

                <p class="mt-2 text-body-sm text-on-surface-variant">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                <p v-if="avatarError" class="mt-1 text-body-sm text-error" role="alert">{{ avatarError }}</p>
            </div>

            <h1 class="mt-6 text-display-sm font-bold tracking-tight">Profil Saya</h1>
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