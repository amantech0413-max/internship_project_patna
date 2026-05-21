<template>
  <div class="p-6 lg:p-8 max-w-xl">
    <h2 class="text-2xl font-bold">Settings</h2>
    <div class="mt-4 bg-white p-6 rounded-xl border space-y-2 text-sm">
      <p><strong>Organization:</strong> M/s Bhagya Laxmi</p>
      <p><strong>Location:</strong> Mohali, Chandigarh</p>
      <p><strong>API Base:</strong> {{ config.public.apiBase }}</p>
      <p class="text-slate-500">Configure mail & queue in Laravel <code>.env</code> for production.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
</script>
