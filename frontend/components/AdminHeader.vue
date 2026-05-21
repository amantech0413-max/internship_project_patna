<template>
  <header class="lg:hidden sticky top-0 z-30 bg-blue-900 text-white shadow-md">
    <div class="flex items-center justify-between px-4 py-3">
      <button
        type="button"
        class="p-2 rounded-lg hover:bg-blue-800"
        aria-label="Open menu"
        @click="$emit('toggle-menu')"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <span class="font-semibold text-sm truncate">{{ title }}</span>
      <div class="w-10" />
    </div>
  </header>
</template>

<script setup lang="ts">
defineProps<{ title?: string }>()
defineEmits<{ 'toggle-menu': [] }>()
</script>
