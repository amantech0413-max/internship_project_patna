<template>
  <div class="card shadow-sm h-100">
    <div class="card-body">
      <p class="text-muted small mb-1">{{ title }}</p>
      <p class="h3 fw-bold mb-0">{{ value }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({ title: String, value: [String, Number] })
</script>
