<template>
  <div class="register-page">
    <header class="register-header">
      <div class="register-header-inner">
        <div class="register-brand">
          <img
            v-if="branding.organization_logo_url"
            :src="branding.organization_logo_url"
            alt="Logo"
            class="register-logo-img"
          />
          <div v-else class="register-logo">BL</div>
          <div>
            <div class="register-brand-name">{{ branding.organization_name || 'M/s Bhagya Laxmi' }}</div>
            <div class="register-brand-addr">{{ branding.organization_address || '' }}</div>
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

    <section class="register-hero" :class="{ 'landing-hero': isLanding }">
      <div class="register-hero-inner" :class="{ 'landing-hero-inner': isLanding }">
        <slot name="hero">
          <span class="register-hero-tag"><i class="bi bi-mortarboard-fill" /> Welcome to</span>
          <h1>{{ heroTitle }}</h1>
          <p v-if="collegeShortName" class="register-hero-college">{{ collegeShortName }}</p>
          <p class="register-hero-desc">{{ heroDescription }}</p>
        </slot>
      </div>
    </section>

    <div class="register-body-wrap" :class="{ 'landing-body-wrap': isLanding }">
      <slot />
    </div>

    <section v-if="showFeatures" class="register-features">
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

    <footer class="register-footer landing-footer">
      <div class="register-footer-inner">
        <span class="register-footer-trust">
          <i class="bi bi-shield-check" /> Secure • Reliable • Future Ready
        </span>
        <span>© {{ year }} M/s Bhagya Laxmi. All rights reserved.</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import '../../../css/register.css'

defineProps({
  branding: {
    type: Object,
    default: () => ({}),
  },
  heroTitle: { type: String, default: 'Internship Jun 2026 Registration' },
  heroDescription: {
    type: String,
    default: 'Begin your professional journey with us. Fill out the form carefully to apply for the internship program.',
  },
  collegeShortName: { type: String, default: '' },
  isLanding: { type: Boolean, default: false },
  showFeatures: { type: Boolean, default: true },
})

const year = new Date().getFullYear()
</script>
