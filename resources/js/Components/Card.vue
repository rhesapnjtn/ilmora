<script setup lang="ts">
import { computed } from 'vue'

interface CardProps {
    variant?: 'default' | 'elevated' | 'hover'
    padding?: 'none' | 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<CardProps>(), {
    variant: 'default',
    padding: 'md',
})

const paddingClasses: Record<NonNullable<CardProps['padding']>, string> = {
    none: '',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
}

const classes = computed(() => {
    const base =
        'bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-card transition-shadow duration-normal'
    const hover =
        props.variant === 'hover'
            ? ' hover:shadow-[0_8px_30px_-4px_rgba(15,23,42,0.08)] hover:-translate-y-1 transition-transform duration-fast'
            : ''

    return `${base}${hover} ${paddingClasses[props.padding]}`
})
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
