<template>
  <div :class="wrapperClass">
    <label v-if="label" class="form-label">{{ label }}</label>
    <input
      :value="modelValue"
      :type="type || 'text'"
      :required="required"
      :placeholder="placeholder"
      :maxlength="maxlength"
      :inputmode="inputmode"
      class="form-control"
      :class="{ 'is-invalid': invalid }"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>

<script setup>
defineProps({
  label: String,
  modelValue: String,
  type: String,
  required: Boolean,
  placeholder: String,
  maxlength: [String, Number],
  inputmode: String,
  invalid: Boolean,
  wrapperClass: String,
})
defineEmits(['update:modelValue'])
</script>
