<template>
  <nav v-if="meta && meta.last_page > 1" class="mt-3">
    <ul class="pagination justify-content-center flex-wrap">
      <li v-for="p in meta.last_page" :key="p" class="page-item" :class="{ active: p === meta.current_page }">
        <button type="button" class="page-link" @click="$emit('page', p)">{{ p }}</button>
      </li>
    </ul>
  </nav>
</template>

<script setup>
defineProps({ meta: Object })
defineEmits(['page'])
</script>
