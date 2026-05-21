<template>
  <div class="card">
    <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
      <strong>{{ title }}</strong>
      <div class="d-flex flex-wrap gap-2">
        <input v-model="search" type="text" class="form-control form-control-sm" placeholder="Search..." @input="emitSearch" />
        <select v-model="modeFilter" class="form-select form-select-sm" @change="emitSearch">
          <option value="">All Modes</option>
          <option value="online">Online</option>
          <option value="offline">Offline</option>
        </select>
        <button type="button" class="btn btn-sm btn-outline-primary" @click="toggleAll">
          {{ allSelected ? 'Deselect All' : 'Select All' }}
        </button>
      </div>
    </div>
    <div class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto">
      <label v-for="student in students" :key="student.id" class="list-group-item d-flex gap-2 align-items-center mb-0">
        <input v-model="selected" type="checkbox" class="form-check-input" :value="student.id" />
        <div class="flex-grow-1 min-w-0">
          <div class="fw-medium small text-truncate">{{ student.name }}</div>
          <div class="text-muted small">
            {{ student.student_code }} · {{ student.mobile }}
            <span v-if="student.internship_mode" class="text-uppercase ms-1">{{ student.internship_mode }}</span>
          </div>
        </div>
      </label>
      <p v-if="!students.length" class="text-center text-muted small py-4 mb-0">No students found</p>
    </div>
    <div class="card-footer small text-muted">{{ selected.length }} student(s) selected</div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  title: { type: String, default: 'Select Students' },
  students: { type: Array, default: () => [] },
  modelValue: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue', 'search'])

const search = ref('')
const modeFilter = ref('')
const selected = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const allSelected = computed(
  () => props.students.length > 0 && selected.value.length === props.students.length
)

const toggleAll = () => {
  selected.value = allSelected.value ? [] : props.students.map((s) => s.id)
}

const emitSearch = () => {
  emit('search', { search: search.value, internship_mode: modeFilter.value })
}
</script>
