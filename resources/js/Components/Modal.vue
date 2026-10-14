<script setup lang="ts">
import { nextTick, ref, watch } from 'vue'

interface ModalProps {
    modelValue: boolean
    title?: string
    description?: string
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
    showClose?: boolean
    closeOnOverlayClick?: boolean
    closeOnEscape?: boolean
}

const props = withDefaults(defineProps<ModalProps>(), {
    size: 'md',
    showClose: true,
    closeOnOverlayClick: true,
    closeOnEscape: true,
})

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
    close: []
}>()

const panelRef = ref<HTMLDivElement | null>(null)
let previouslyFocused: HTMLElement | null = null

const sizeClasses: Record<NonNullable<ModalProps['size']>, string> = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    full: 'max-w-4xl',
}

function close() {
    emit('update:modelValue', false)
    emit('close')
}

function onOverlayClick() {
    if (props.closeOnOverlayClick) {
        close()
    }
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape' && props.closeOnEscape) {
        close()
        return
    }

    if (event.key === 'Tab' && panelRef.value) {
        const focusable = panelRef.value.querySelectorAll<HTMLElement>(
            'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])',
        )

        if (focusable.length === 0) {
            return
        }

        const first = focusable[0]
        const last = focusable[focusable.length - 1]

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault()
            last.focus()
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault()
            first.focus()
        }
    }
}

watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen) {
            previouslyFocused = document.activeElement as HTMLElement | null
            document.body.style.overflow = 'hidden'
            document.addEventListener('keydown', onKeydown)
            await nextTick()
            panelRef.value?.focus()
        } else {
            document.body.style.overflow = ''
            document.removeEventListener('keydown', onKeydown)
            previouslyFocused?.focus()
        }
    },
    { immediate: true },
)
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="modelValue" class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
                <div class="modal-scrim animate-fade-in" aria-hidden="true" @click="onOverlayClick" />

                <div
                    ref="panelRef"
                    tabindex="-1"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="title ? 'modal-title' : undefined"
                    :aria-describedby="description ? 'modal-description' : undefined"
                    class="relative w-full rounded-t-3xl bg-surface-container-lowest shadow-modal focus:outline-none sm:rounded-3xl"
                    :class="sizeClasses[size]"
                >
                    <div
                        v-if="title || description || $slots.header"
                        class="flex items-start justify-between gap-4 border-b border-outline-variant/50 px-6 py-4"
                    >
                        <div class="flex-1">
                            <h2 v-if="title" id="modal-title" class="text-headline-sm font-semibold text-on-surface">
                                {{ title }}
                            </h2>
                            <p v-if="description" id="modal-description" class="mt-1 text-body-md text-on-surface-variant">
                                {{ description }}
                            </p>
                            <slot name="header" />
                        </div>
                        <button
                            v-if="showClose"
                            type="button"
                            class="rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface"
                            aria-label="Tutup"
                            @click="close"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto p-6">
                        <slot />
                    </div>

                    <div v-if="$slots.footer" class="border-t border-outline-variant/50 px-6 pb-6 pt-4">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
