<template>
  <div class="group bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 border outline-variant" style="border-color: rgba(226, 232, 240, 0.8)">
    <div class="relative rounded-t-xl">
      <img
        :src="thumbnail"
        alt=""
        class="h-48 w-full object-cover rounded-t-xl"
      />
      <span
        class="absolute top-2 left-2 bg-primary text-white text-xs font-semibold rounded px-2 py-1"
      >
        {{ category }}
      </span>
    </div>
    <div class="p-4">
      <h3 class="line-clamp-2 headline-sm font-medium text-on-surface group-hover:text-primary transition-colors">
        {{ title }}
      </h3>
      <div class="flex items-center justify-between mt-2">
        <div class="flex items-center gap-1 text-on-surface-sm">
          <span class="text-primary">★</span>
          <span v-for="i in 5" :key="i"
            :class="{'text-primary': i <= rating, 'text-on-surface-variant': i > rating}"
          >★</span>
        </div>
        <span class="text-on-surface-xs">{{ reviewCount }} ulasan</span>
      </div>
      <div class="mt-3 flex items-baseline">
        <span class="label-lg text-on-secondary">{{ price }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  level: { type: String, default: 'Semua Level' },
  progress: { type: Number, default: 0 },
  price: { type: String, required: true },
  thumbnail: { type: String, default: '/placeholder.svg?height=300&width=500' },
  category: { type: String, default: 'Populer' },
  reviewCount: { type: Number, default: 0 }
})
</script>