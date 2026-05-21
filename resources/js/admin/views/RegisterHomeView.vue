<template>
  <RegisterPageShell
    hero-title="Internship Jun 2026 Registration"
    hero-description="Select your college below to open the student registration form."
  >
    <div class="register-card">
      <div class="register-card-head">
        <div class="register-card-head-icon"><i class="bi bi-building" /></div>
        <div>
          <h2>Select Your College</h2>
          <p>Choose your institution to continue registration</p>
        </div>
      </div>
      <ul class="register-college-list">
        <li v-for="college in REGISTRATION_COLLEGES" :key="college.slug">
          <router-link
            :to="{ name: 'register-college', params: { slug: college.slug } }"
            class="register-college-link"
          >
            <i class="bi bi-mortarboard" />
            <span>{{ college.shortName }}</span>
            <i class="bi bi-chevron-right" />
          </router-link>
        </li>
      </ul>
    </div>
  </RegisterPageShell>
</template>

<script setup>
import { REGISTRATION_COLLEGES } from '@/config/registrationColleges'
import RegisterPageShell from '@/components/RegisterPageShell.vue'
</script>
