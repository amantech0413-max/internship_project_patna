<script setup lang="ts">
const { token, isStaff } = useAuth()

if (token.value && isStaff.value) {
  await navigateTo('/dashboard')
} else {
  await navigateTo('/login')
}
</script>
