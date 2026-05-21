<template>
  <div class="card table-card shadow-sm" style="max-width: 32rem">
    <div class="card-body">
      <h2 class="h5 fw-bold">Settings</h2>
      <div class="mt-3 small">
        <p class="mb-1"><strong>Organization:</strong> M/s Bhagya Laxmi</p>
        <p class="mb-1"><strong>Location:</strong> Mohali, Chandigarh</p>
        <p class="mb-1"><strong>API Base:</strong> {{ apiBase }}</p>
        <p class="text-muted mb-0">Configure mail &amp; queue in Laravel <code>.env</code> for production.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { getApiBase } from '@/api/client'

const apiBase = getApiBase()
</script>
