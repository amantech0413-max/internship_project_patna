<template>
  <div v-if="message" class="toast-container-fixed">
    <div class="toast show align-items-center text-white border-0" :class="`bg-${type}`" role="alert">
      <div class="d-flex">
        <div class="toast-body">{{ message }}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="clear" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const { message, type, clear } = useToast()
</script>
