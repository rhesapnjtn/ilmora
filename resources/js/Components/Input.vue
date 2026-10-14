<script setup lang="ts">
import { computed, ref } from 'vue'

interface InputProps {
    modelValue: string
    label?: string
    type?: string
    placeholder?: string
    error?: string
    disabled?: boolean
    required?: boolean
    id?: string
    autocomplete?: string
    class?: string
}

const props = defineProps<InputProps>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
    blur: []
}>()

const inputId = computed(() => props.id || `input-${Math.random().toString(36).slice(2)}`)
const hasError = computed(() => !!props.error)
const isPassword = computed(() => props.type === 'password')
const revealed = ref(false)
const effectiveType = computed(() => (isPassword.value && revealed.value ? 'text' : props.type || 'text'))

const classes = computed(() => [
    'w-full h-13 bg-surface text-on-surface rounded-2xl',
    isPassword.value ? 'pl-4 pr-12' : 'px-4',
    'placeholder:text-on-surface-variant',
    'transition-colors duration-fast',
    hasError.value
        ? 'border border-error focus:border-error focus:ring-2 focus:ring-error/20'
        : 'border border-outline focus:border-primary focus:ring-2 focus:ring-primary/20',
    props.disabled && 'bg-surface-container-low cursor-not-allowed',
].join(' '))
</script>

<template>
    <div class="w-full">
        <label v-if="label" :for="inputId" class="label">
            {{ label }}
            <span v-if="required" class="text-error ml-1" aria-hidden="true">*</span>
        </label>
        <div class="relative">
            <input
                :id="inputId"
                :type="effectiveType"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :autocomplete="autocomplete"
                :class="classes"
                :aria-invalid="hasError"
                :aria-describedby="hasError ? `${inputId}-error` : undefined"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                @blur="$emit('blur')"
            />
            <button
                v-if="isPassword"
                type="button"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-on-surface-variant transition-colors duration-fast hover:text-on-surface"
                :aria-label="revealed ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                :aria-pressed="revealed"
                @click="revealed = !revealed"
            >
                <svg v-if="!revealed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
            </button>
        </div>
        <p v-if="error" :id="`${inputId}-error`" class="mt-1.5 text-body-sm text-error" role="alert">
            {{ error }}
        </p>
    </div>
</template>