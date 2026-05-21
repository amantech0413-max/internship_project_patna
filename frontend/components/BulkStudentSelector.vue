<template>
  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="p-4 border-b flex flex-wrap gap-3 items-center justify-between">
      <h3 class="font-semibold text-slate-800">{{ title }}</h3>
      <div class="flex gap-2">
        <input
          v-model="search"
          type="text"
          placeholder="Search students..."
          class="border rounded-lg px-3 py-1.5 text-sm"
          @input="emitSearch"
        />
        <select v-model="modeFilter" class="border rounded-lg px-3 py-1.5 text-sm" @change="emitSearch">
          <option value="">All Modes</option>
          <option value="online">Online</option>
          <option value="offline">Offline</option>
        </select>
        <button type="button" class="text-sm text-blue-700" @click="toggleAll">
          {{ allSelected ? 'Deselect All' : 'Select All' }}
        </button>
      </div>
    </div>
    <div class="max-h-80 overflow-y-auto divide-y">
      <label
        v-for="student in students"
        :key="student.id as number"
        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer"
      >
        <input v-model="selected" type="checkbox" :value="student.id" class="rounded" />
        <div class="flex-1 min-w-0">
          <p class="font-medium text-sm truncate">{{ student.name }}</p>
          <p class="text-xs text-slate-500">
            {{ student.student_code }} · {{ student.mobile }}
            <span v-if="student.internship_mode" class="ml-1 uppercase">{{ student.internship_mode }}</span>
          </p>
        </div>
      </label>
      <p v-if="!students.length" class="p-6 text-center text-slate-500 text-sm">No students found</p>
    </div>
    <div class="p-3 bg-slate-50 text-sm text-slate-600 border-t">
      {{ selected.length }} student(s) selected
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  title?: string
  students: Record<string, unknown>[]
  modelValue: number[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: number[]]
  search: [query: { search: string; internship_mode: string }]
}>()

const title = computed(() => props.title ?? 'Select Students')
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
  if (allSelected.value) {
    selected.value = []
  } else {
    selected.value = props.students.map((s) => s.id as number)
  }
}

const emitSearch = () => {
  emit('search', { search: search.value, internship_mode: modeFilter.value })
}
</script>
