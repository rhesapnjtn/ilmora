<script setup lang="ts">
import { computed } from 'vue'

interface BadgeProps {
    variant?: 'primary' | 'secondary' | 'success' | 'error' | 'outline' | 'promo'
    size?: 'sm' | 'md' | 'lg'
    removable?: boolean
}

const props = withDefaults(defineProps<BadgeProps>(), {
    variant: 'primary',
    size: 'md',
    removable: false,
})

const emit = defineEmits<{ remove: [] }>()

const variants: Record<NonNullable<BadgeProps['variant']>, string> = {
    primary: 'bg-primary/10 text-primary',
    secondary: 'bg-secondary/10 text-secondary',
    success: 'bg-tertiary/10 text-tertiary',
    error: 'bg-error/10 text-error',
    outline: 'bg-transparent border border-outline text-on-surface-variant',
    promo: 'bg-[#FEF3C7] text-[#D97706]',
}

const sizes: Record<NonNullable<BadgeProps['size']>, string> = {
    sm: 'px-2 py-0.5 text-label-sm',
    md: 'px-3 py-1 text-label-md',
    lg: 'px-4 py-1.5 text-label-lg',
}

const classes = computed(() =>
    ['inline-flex items-center gap-1.5 font-semibold rounded-full', variants[props.variant], sizes[props.size]].join(' '),
)
</script>

<template>
    <span :class="classes">
        <slot />
        <button
            v-if="removable"
            type="button"
            class="ml-1 rounded-full p-0.5 transition-colors hover:bg-black/10"
            aria-label="Hapus"
            @click="emit('remove')"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
    </span>
</template>
