<template>
  <div class="register-page">
    <header class="register-header">
      <div class="register-header-inner">
        <div class="register-brand">
          <div class="register-logo">BL</div>
          <div>
            <div class="register-brand-name">M/s Bhagya Laxmi</div>
            <div class="register-brand-addr">A-1, Patliputra Industrial Area, Patna-800013, Bihar, India</div>
          </div>
        </div>
        <div class="register-badge">
          <i class="bi bi-mortarboard-fill" />
          <div>
            <strong>Internship Jun 2026</strong>
            <span>Registration Program</span>
          </div>
        </div>
      </div>
    </header>

    <section class="register-hero">
      <div class="register-hero-inner">
        <span class="register-hero-tag"><i class="bi bi-mortarboard-fill" /> Welcome to</span>
        <h1>{{ heroTitle }}</h1>
        <p v-if="collegeShortName" class="register-hero-college">{{ collegeShortName }}</p>
        <p class="register-hero-desc">{{ heroDescription }}</p>
      </div>
    </section>

    <div class="register-body-wrap">
      <slot />
    </div>

    <section class="register-features">
      <div class="register-feature">
        <div class="register-feature-icon blue"><i class="bi bi-briefcase-fill" /></div>
        <h3>Professional Experience</h3>
        <p>Gain hands-on industry exposure with structured internship guidance.</p>
      </div>
      <div class="register-feature">
        <div class="register-feature-icon purple"><i class="bi bi-lightbulb-fill" /></div>
        <h3>Skill Development</h3>
        <p>Enhance your practical knowledge and workplace readiness.</p>
      </div>
      <div class="register-feature">
        <div class="register-feature-icon green"><i class="bi bi-people-fill" /></div>
        <h3>Expert Mentorship</h3>
        <p>Learn from experienced professionals throughout your journey.</p>
      </div>
    </section>

    <footer class="register-footer">
      <div class="register-footer-inner">
        <span class="register-footer-trust">
          <i class="bi bi-shield-check" /> Secure | Trusted | Professional
        </span>
        <span>© {{ year }} M/s Bhagya Laxmi. All Rights Reserved.</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import '../../../css/register.css'

defineProps({
  heroTitle: { type: String, default: 'Internship Jun 2026' },
  heroDescription: {
    type: String,
    default: 'Begin your professional journey with us. Fill out the form to apply for the internship program.',
  },
  collegeShortName: { type: String, default: '' },
})

const year = new Date().getFullYear()
</script>
