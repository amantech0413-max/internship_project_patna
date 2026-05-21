<template>
  <div v-if="toast.visible" class="toast-container-fixed">
    <div class="toast show align-items-center text-white border-0" :class="`bg-${toast.type}`" role="alert">
      <div class="d-flex">
        <div class="toast-body">{{ toast.message }}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="toast.visible = false" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/toast'
const toast = useToastStore()
</script>
