<script setup lang="ts">
import { computed } from 'vue'

interface ButtonProps {
    variant?: 'primary' | 'secondary' | 'ghost'
    size?: 'sm' | 'md' | 'lg'
    disabled?: boolean
    loading?: boolean
    type?: 'button' | 'submit' | 'reset'
}

const props = withDefaults(defineProps<ButtonProps>(), {
    variant: 'primary',
    size: 'md',
    disabled: false,
    loading: false,
    type: 'button',
})

const base =
    'inline-flex items-center justify-center gap-2 font-semibold transition-all duration-normal ' +
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 ' +
    'disabled:opacity-50 disabled:cursor-not-allowed'

const variants: Record<NonNullable<ButtonProps['variant']>, string> = {
    primary: 'bg-primary text-white hover:bg-primary/90 active:scale-[0.98] shadow-sm',
    secondary: 'bg-transparent border-[1.5px] border-outline text-on-surface hover:bg-surface-container-low active:scale-[0.98]',
    ghost: 'bg-transparent text-on-surface hover:bg-surface-container-low',
}

const sizes: Record<NonNullable<ButtonProps['size']>, string> = {
    sm: 'h-10 px-4 text-label-md rounded-xl',
    md: 'h-13 px-6 text-label-lg rounded-2xl',
    lg: 'h-14 px-8 text-title-md rounded-2xl',
}

const classes = computed(() =>
    [base, variants[props.variant], sizes[props.size]].join(' '),
)
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="classes"
    >
        <span v-if="loading" class="animate-spin" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round" />
            </svg>
        </span>
        <slot />
    </button>
</template>
