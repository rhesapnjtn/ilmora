<script setup lang="ts">
import { computed } from 'vue'
import Modal from '@/Components/Modal.vue'
import Button from '@/Components/Button.vue'

type ConfirmVariant = 'danger' | 'primary'
type ConfirmIcon = 'warning' | 'question' | 'logout' | 'trash'

interface ConfirmDialogProps {
    modelValue: boolean
    title: string
    message?: string
    confirmLabel?: string
    cancelLabel?: string
    variant?: ConfirmVariant
    icon?: ConfirmIcon
    loading?: boolean
}

const props = withDefaults(defineProps<ConfirmDialogProps>(), {
    message: undefined,
    confirmLabel: 'Konfirmasi',
    cancelLabel: 'Batal',
    variant: 'primary',
    icon: undefined,
    loading: false,
})

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
    confirm: []
    cancel: []
}>()

const resolvedIcon = computed<ConfirmIcon>(
    () => props.icon ?? (props.variant === 'danger' ? 'warning' : 'question'),
)

const toneClasses = computed(() =>
    props.variant === 'danger' ? 'bg-error/10 text-error' : 'bg-primary/10 text-primary',
)

function onVisibilityChange(value: boolean): void {
    emit('update:modelValue', value)
}

function onCancel(): void {
    if (props.loading) return
    emit('cancel')
}

function onConfirm(): void {
    emit('confirm')
}
</script>

<template>
    <Modal
        :model-value="modelValue"
        :title="title"
        size="sm"
        :show-close="!loading"
        :close-on-overlay-click="!loading"
        :close-on-escape="!loading"
        @update:model-value="onVisibilityChange"
        @close="onCancel"
    >
        <div class="flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" :class="toneClasses" aria-hidden="true">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <template v-if="resolvedIcon === 'warning'">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                    </template>
                    <template v-else-if="resolvedIcon === 'trash'">
                        <path d="M3 6h18" />
                        <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                        <path d="M10 11v6M14 11v6" />
                    </template>
                    <template v-else-if="resolvedIcon === 'logout'">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <path d="M16 17l5-5-5-5" />
                        <path d="M21 12H9" />
                    </template>
                    <template v-else>
                        <circle cx="12" cy="12" r="10" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <path d="M12 17h.01" />
                    </template>
                </svg>
            </span>
            <div class="pt-0.5 text-body-md text-on-surface-variant">
                <slot>
                    <p>{{ message }}</p>
                </slot>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button variant="secondary" size="sm" :disabled="loading" @click="onCancel">
                    {{ cancelLabel }}
                </Button>
                <Button :variant="variant" size="sm" :loading="loading" @click="onConfirm">
                    {{ confirmLabel }}
                </Button>
            </div>
        </template>
    </Modal>
</template>
