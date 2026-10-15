<script setup lang="ts">
interface StatCardProps {
    label: string
    value: string | number
    hint?: string
    icon?: 'grid' | 'book' | 'users' | 'wallet' | 'card' | 'spark'
    accent?: 'primary' | 'secondary' | 'tertiary'
}

withDefaults(defineProps<StatCardProps>(), {
    icon: 'grid',
    accent: 'primary',
    hint: '',
})

const accentClasses: Record<NonNullable<StatCardProps['accent']>, string> = {
    primary: 'bg-primary/10 text-primary',
    secondary: 'bg-secondary/10 text-secondary',
    tertiary: 'bg-tertiary/10 text-tertiary',
}
</script>

<template>
    <div class="card">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-label-md font-semibold uppercase tracking-wide text-on-surface-variant">{{ label }}</p>
                <p class="mt-2 text-display-sm font-bold tracking-tight">{{ value }}</p>
                <p v-if="hint" class="mt-1 text-body-sm text-on-surface-variant">{{ hint }}</p>
            </div>
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" :class="accentClasses[accent]">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <template v-if="icon === 'book'"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" /></template>
                    <template v-else-if="icon === 'users'"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></template>
                    <template v-else-if="icon === 'wallet'"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" /><path d="M3 5v14a2 2 0 0 0 2 2h16v-5" /><path d="M18 12a2 2 0 0 0 0 4h4v-4z" /></template>
                    <template v-else-if="icon === 'card'"><rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" /></template>
                    <template v-else-if="icon === 'spark'"><path d="M12 2l2.4 7.4H22l-6 4.4 2.3 7.2L12 16.6 5.7 21l2.3-7.2-6-4.4h7.6z" /></template>
                    <template v-else><rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" /><rect x="3" y="14" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" /></template>
                </svg>
            </span>
        </div>
    </div>
</template>
