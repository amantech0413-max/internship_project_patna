<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light p-3">
    <div class="w-100" style="max-width: 640px">
      <div class="text-center mb-4">
        <h1 class="h4 fw-bold text-primary">{{ PROGRAM_TITLE }}</h1>
        <p class="small text-muted mt-2">Apna college select karke registration form kholen</p>
      </div>

      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <strong>Select College</strong>
        </div>
        <div class="list-group list-group-flush">
          <router-link
            v-for="college in REGISTRATION_COLLEGES"
            :key="college.slug"
            :to="{ name: 'register-college', params: { slug: college.slug } }"
            class="list-group-item list-group-item-action py-3"
          >
            <i class="bi bi-building me-2 text-primary" />
            {{ college.name }}
          </router-link>
        </div>
      </div>

      <p class="text-center small text-muted mt-3 mb-0 d-none" aria-hidden="true">
        <router-link to="/login">Staff Login</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { PROGRAM_TITLE, REGISTRATION_COLLEGES } from '@/config/registrationColleges'
</script>
