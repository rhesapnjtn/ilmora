<script setup lang="ts">
interface CourseCoverProps {
    categoryName: string
    accent?: string
    durationHours?: number
    level?: string
    badge?: string
}

const props = withDefaults(defineProps<CourseCoverProps>(), {
    accent: 'primary',
    durationHours: 0,
    level: '',
    badge: '',
})

const accentClasses: Record<string, string> = {
    primary: 'from-primary to-primary-500',
    secondary: 'from-secondary to-[#A855F7]',
    tertiary: 'from-tertiary to-[#059669]',
}

const badgeClasses: Record<string, string> = {
    primary: 'bg-secondary-50 text-secondary-700',
    secondary: 'bg-primary-50 text-primary',
    tertiary: 'bg-tertiary-50 text-tertiary-700',
}
</script>

<template>
    <div
        class="relative aspect-[16/10] w-full overflow-hidden bg-gradient-to-br"
        :class="accentClasses[props.accent] || accentClasses.primary"
    >
        <div class="pattern-dots absolute inset-0 opacity-30" aria-hidden="true" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-white/10" aria-hidden="true" />

        <svg
            class="absolute -bottom-8 -right-6 h-40 w-40 text-white/15"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.2"
            aria-hidden="true"
        >
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
        </svg>

        <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 text-primary shadow-lg">
                <svg class="ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M8 5v14l11-7z" />
                </svg>
            </span>
        </div>

        <div v-if="props.badge" class="absolute left-4 top-4">
            <span
                class="rounded-full px-3 py-1 text-label-sm font-bold uppercase shadow-sm"
                :class="badgeClasses[props.accent] || badgeClasses.primary"
            >
                {{ props.badge }}
            </span>
        </div>

        <div
            v-if="props.durationHours"
            class="absolute bottom-4 right-4 flex items-center gap-1.5 rounded-lg bg-black/40 px-2.5 py-1 text-label-sm text-white backdrop-blur-sm"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path d="M12 7v5l3 2" />
            </svg>
            <span>{{ props.durationHours }} Jam<template v-if="props.level"> &bull; {{ props.level }}</template></span>
        </div>
    </div>
</template>
