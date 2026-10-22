<script setup lang="ts">
type AvatarSize = 'xs' | 'sm' | 'md'
type AvatarVariant = 'filled' | 'plain'

withDefaults(
    defineProps<{
        src?: string | null
        name?: string | null
        size?: AvatarSize
        variant?: AvatarVariant
    }>(),
    {
        src: null,
        name: null,
        size: 'md',
        variant: 'filled',
    },
)

const containerClasses: Record<AvatarSize, string> = {
    xs: 'h-7 w-7',
    sm: 'h-8 w-8',
    md: 'h-9 w-9',
}

const iconClasses: Record<AvatarSize, string> = {
    xs: 'h-4 w-4',
    sm: 'h-5 w-5',
    md: 'h-5 w-5',
}
</script>

<template>
    <span
        class="flex shrink-0 items-center justify-center overflow-hidden rounded-full"
        :class="[
            containerClasses[size],
            variant === 'filled' ? 'bg-primary text-on-primary' : 'text-current',
        ]"
    >
        <img
            v-if="src"
            :src="src"
            :alt="name ?? 'Foto profil'"
            class="h-full w-full object-cover"
        />
        <svg
            v-else
            :class="iconClasses[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </svg>
    </span>
</template>
