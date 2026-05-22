<template>
  <div v-if="meta" :class="rootClass">
    <div
      v-if="showPerPage"
      class="d-flex flex-wrap align-items-center gap-2 small"
      :class="{ 'mb-0': layout === 'per-page-only' }"
    >
      <span v-if="showRange" class="text-muted">
        Showing {{ rangeFrom }}–{{ rangeTo }} of {{ meta.total }}
      </span>
      <span v-if="showRange && layout === 'combined'" class="text-muted">|</span>
      <label class="text-muted mb-0">Show</label>
      <select
        class="form-select form-select-sm"
        style="width: auto; min-width: 4.5rem"
        :value="mode"
        @change="onModeChange"
      >
        <option value="10">10</option>
        <option value="50">50</option>
        <option value="100">100</option>
        <option value="custom">Custom</option>
      </select>
      <input
        v-if="mode === 'custom'"
        v-model.number="customDraft"
        type="number"
        class="form-control form-control-sm"
        style="width: 5rem"
        min="1"
        max="500"
        placeholder="No."
        @keyup.enter="applyCustom"
        @blur="applyCustom"
      />
      <span class="text-muted">entries</span>
    </div>

    <nav v-if="showPagination" aria-label="Table pagination">
      <ul class="pagination pagination-sm mb-0 flex-wrap">
        <li class="page-item" :class="{ disabled: meta.current_page <= 1 }">
          <button type="button" class="page-link" :disabled="meta.current_page <= 1" @click="$emit('page', meta.current_page - 1)">
            Prev
          </button>
        </li>
        <li
          v-for="p in visiblePages"
          :key="p"
          class="page-item"
          :class="{ active: p === meta.current_page }"
        >
          <button type="button" class="page-link" @click="$emit('page', p)">{{ p }}</button>
        </li>
        <li class="page-item" :class="{ disabled: meta.current_page >= meta.last_page }">
          <button
            type="button"
            class="page-link"
            :disabled="meta.current_page >= meta.last_page"
            @click="$emit('page', meta.current_page + 1)"
          >
            Next
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  meta: { type: Object, default: null },
  perPage: { type: Number, default: 10 },
  /** combined | per-page-only | pagination-only */
  layout: { type: String, default: 'combined' },
})

const emit = defineEmits(['page', 'update:perPage'])

const PRESET = new Set([10, 50, 100])

const mode = ref('10')
const customDraft = ref(25)

const showPerPage = computed(() => props.layout === 'combined' || props.layout === 'per-page-only')
const showPagination = computed(
  () => (props.layout === 'combined' || props.layout === 'pagination-only') && (props.meta?.last_page ?? 1) > 1
)
const showRange = computed(() => props.layout === 'combined' || props.layout === 'per-page-only')

const rootClass = computed(() => {
  if (props.layout === 'per-page-only') return ''
  if (props.layout === 'pagination-only') return 'd-flex justify-content-center mt-3'
  return 'd-flex flex-wrap justify-content-between align-items-center gap-2 mt-3'
})

const syncModeFromPerPage = (n) => {
  if (PRESET.has(n)) {
    mode.value = String(n)
  } else {
    mode.value = 'custom'
    customDraft.value = n > 0 ? n : 25
  }
}

watch(() => props.perPage, syncModeFromPerPage, { immediate: true })

const rangeFrom = computed(() => {
  if (!props.meta?.total) return 0
  return (props.meta.current_page - 1) * props.meta.per_page + 1
})

const rangeTo = computed(() => {
  if (!props.meta?.total) return 0
  return Math.min(props.meta.current_page * props.meta.per_page, props.meta.total)
})

const visiblePages = computed(() => {
  const last = props.meta?.last_page ?? 1
  const cur = props.meta?.current_page ?? 1
  const pages = []
  const start = Math.max(1, cur - 2)
  const end = Math.min(last, cur + 2)
  for (let p = start; p <= end; p++) pages.push(p)
  return pages
})

const onModeChange = (e) => {
  const v = e.target.value
  mode.value = v
  if (v === 'custom') {
    applyCustom()
    return
  }
  emit('update:perPage', Number(v))
}

const applyCustom = () => {
  const n = Math.min(500, Math.max(1, Number(customDraft.value) || 10))
  customDraft.value = n
  mode.value = 'custom'
  emit('update:perPage', n)
}
</script>
