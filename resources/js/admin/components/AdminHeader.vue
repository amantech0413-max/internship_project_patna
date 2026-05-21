<template>
  <header class="d-lg-none sticky-top bg-primary text-white shadow-sm">
    <div class="d-flex align-items-center justify-content-between px-3 py-2">
      <button type="button" class="btn btn-sm btn-outline-light" aria-label="Open menu" @click="$emit('toggle-menu')">
        <i class="bi bi-list fs-5" />
      </button>
      <span class="fw-semibold small text-truncate">{{ title || 'Admin' }}</span>
      <span style="width: 2.5rem" />
    </div>
  </header>
</template>

<script setup>
defineProps({ title: String })
defineEmits(['toggle-menu'])
</script>
