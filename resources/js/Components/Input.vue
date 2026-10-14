<script setup lang="ts">
import { computed } from 'vue'

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
}>

const inputId = computed(() => props.id || `input-${Math.random().toString(36).slice(2)}`)
const hasError = computed(() => !!props.error)

const classes = computed(() => [
    'w-full h-13 px-4 bg-surface text-on-surface rounded-2xl',
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
        <input
            :id="inputId"
            :type="type || 'text'"
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
        <p v-if="error" :id="`${inputId}-error`" class="mt-1.5 text-body-sm text-error" role="alert">
            {{ error }}
        </p>
    </div>
</template>