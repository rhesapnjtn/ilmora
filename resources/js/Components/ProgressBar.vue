<script setup lang="ts">
import { computed } from 'vue'

interface ProgressBarProps {
    value: number
    max?: number
    size?: 'sm' | 'md' | 'lg'
    showLabel?: boolean
    variant?: 'default' | 'complete'
}

const props = withDefaults(defineProps<ProgressBarProps>(), {
    max: 100,
    size: 'md',
    showLabel: false,
    variant: 'default',
})

const percentage = computed(() => {
    if (props.max <= 0) {
        return 0
    }

    return Math.min(100, Math.max(0, (props.value / props.max) * 100))
})

const heightClasses: Record<NonNullable<ProgressBarProps['size']>, string> = {
    sm: 'h-1',
    md: 'h-1.5',
    lg: 'h-2',
}
</script>

<template>
    <div class="w-full">
        <div v-if="showLabel" class="mb-1.5 flex items-center justify-between">
            <span class="text-body-sm text-on-surface-variant">{{ Math.round(percentage) }}%</span>
            <span class="text-label-sm font-semibold text-on-surface">{{ value }} / {{ max }}</span>
        </div>
        <div
            class="w-full overflow-hidden rounded-full bg-surface-container-high"
            :class="heightClasses[size]"
            role="progressbar"
            :aria-valuenow="Math.round(percentage)"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="h-full rounded-full transition-all duration-500 ease-out"
                :class="variant === 'complete' ? 'bg-tertiary' : 'bg-primary'"
                :style="{ width: `${percentage}%` }"
            />
        </div>
    </div>
</template>
