<template>
  <div class="list-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span v-if="pending" class="text-muted small">Loading...</span>
      <template v-else>
        <span class="badge text-bg-primary rounded-pill px-3 py-2">{{ total }} {{ itemLabel }}</span>
        <span v-if="rangeText" class="text-muted small">{{ rangeText }}</span>
      </template>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <label class="small text-muted mb-0 text-nowrap">Per page</label>
      <select
        :value="preset"
        class="form-select form-select-sm"
        style="width: auto; min-width: 5.5rem"
        @change="onPresetChange"
      >
        <option value="15">15</option>
        <option value="50">50</option>
        <option value="100">100</option>
        <option value="custom">Custom</option>
      </select>
      <input
        v-if="preset === 'custom'"
        :value="customValue"
        type="number"
        min="1"
        max="500"
        class="form-control form-control-sm"
        style="width: 5.5rem"
        placeholder="1–500"
        @input="onCustomInput"
        @keyup.enter="$emit('per-page-change')"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  total: { type: Number, default: 0 },
  pending: { type: Boolean, default: false },
  rangeText: { type: String, default: '' },
  itemLabel: { type: String, default: 'records' },
  preset: { type: String, default: '15' },
  customValue: { type: [String, Number], default: '' },
})

const emit = defineEmits(['update:preset', 'update:customValue', 'per-page-change'])

const onPresetChange = (e) => {
  emit('update:preset', e.target.value)
  emit('per-page-change')
}

const onCustomInput = (e) => {
  emit('update:customValue', e.target.value)
}
</script>
