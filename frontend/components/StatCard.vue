<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
    <p class="text-sm text-slate-500">{{ title }}</p>
    <p class="text-3xl font-bold text-slate-900 mt-2">{{ value }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{ title: string; value: string | number }>()
</script>
