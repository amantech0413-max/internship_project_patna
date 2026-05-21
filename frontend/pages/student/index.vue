<script setup lang="ts">
const { isStaff } = useAuth()

await navigateTo(isStaff.value ? '/dashboard' : '/student/dashboard', { replace: true })
</script>
