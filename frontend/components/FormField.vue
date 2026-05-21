<template>
  <div :class="class">
    <label v-if="label" class="block text-sm font-medium text-slate-700 mb-1">{{ label }}</label>
    <input
      :value="modelValue"
      :type="type"
      :required="required"
      :placeholder="placeholder"
      :maxlength="maxlength"
      :inputmode="inputmode"
      :class="[
        'w-full border rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition',
        invalid ? 'border-red-500' : 'border-slate-300',
      ]"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label?: string
  modelValue: string
  type?: string
  required?: boolean
  placeholder?: string
  maxlength?: string | number
  inputmode?: string
  invalid?: boolean
  class?: string
}>()

defineEmits<{ 'update:modelValue': [value: string] }>()
</script>
