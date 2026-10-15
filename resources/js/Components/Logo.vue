<script setup lang="ts">
interface LogoProps {
    size?: 'sm' | 'md' | 'lg' | 'xl'
    alt?: string
}

withDefaults(defineProps<LogoProps>(), {
    size: 'md',
    alt: 'Ilmora',
})

const sizes: Record<NonNullable<LogoProps['size']>, string> = {
    sm: 'h-8 w-8',
    md: 'h-9 w-9',
    lg: 'h-12 w-12',
    xl: 'h-16 w-16',
}
</script>

<template>
    <img src="/images/logo.png" :alt="alt" :class="sizes[size]" class="shrink-0 object-contain" />
</template>
