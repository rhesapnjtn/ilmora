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
    md: 'p-5 md:p-6',
    lg: 'p-6 md:p-8',
}

const classes = computed(() => {
    const base = props.padding === 'none' ? 'card-flush' : 'card'
    const hover = props.variant === 'hover' ? ' card-hover' : ''
    const padding = paddingClasses[props.padding]

    return `${base}${hover} ${padding}`.trim()
})
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
