<script setup lang="ts">
import { computed } from 'vue'

interface PaginationProps {
    page: number
    lastPage: number
}

const props = defineProps<PaginationProps>()

const emit = defineEmits<{ change: [page: number] }>()

const pages = computed(() => {
    const total = props.lastPage
    const current = props.page
    const window = 2
    const start = Math.max(1, current - window)
    const end = Math.min(total, current + window)
    const list: number[] = []

    for (let i = start; i <= end; i++) list.push(i)

    return list
})

function go(page: number): void {
    if (page < 1 || page > props.lastPage || page === props.page) return
    emit('change', page)
}
</script>

<template>
    <div v-if="lastPage > 1" class="mt-6 flex flex-wrap items-center justify-center gap-2">
        <button type="button" class="btn-secondary h-10 px-4 text-label-md" :disabled="page <= 1" @click="go(page - 1)">Sebelumnya</button>
        <button
            v-for="p in pages"
            :key="p"
            type="button"
            class="h-10 w-10 rounded-xl text-label-md font-semibold transition-colors"
            :class="p === page ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
            :aria-current="p === page ? 'page' : undefined"
            @click="go(p)"
        >
            {{ p }}
        </button>
        <button type="button" class="btn-secondary h-10 px-4 text-label-md" :disabled="page >= lastPage" @click="go(page + 1)">Berikutnya</button>
    </div>
</template>
