<template>
  <div>
    <div class="d-md-none">
      <div v-for="(row, idx) in rows" :key="idx" class="card mb-2">
        <div class="card-body small">
          <div v-for="col in columns" :key="col.key" class="d-flex justify-content-between gap-2 mb-1">
            <span class="text-muted">{{ col.label }}</span>
            <span class="fw-medium text-end text-break">{{ formatCell(row[col.key]) }}</span>
          </div>
          <div v-if="$slots.actions" class="mt-2 pt-2 border-top d-flex flex-wrap gap-1">
            <slot name="actions" :row="row" />
          </div>
        </div>
      </div>
      <p v-if="!rows?.length" class="text-center text-muted py-4">No records found</p>
    </div>

    <div class="d-none d-md-block table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th v-for="col in columns" :key="col.key">{{ col.label }}</th>
            <th v-if="$slots.actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, idx) in rows" :key="idx">
            <td v-for="col in columns" :key="col.key">{{ formatCell(row[col.key]) }}</td>
            <td v-if="$slots.actions" class="text-nowrap">
              <slot name="actions" :row="row" />
            </td>
          </tr>
          <tr v-if="!rows?.length">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="text-center text-muted py-4">
              No records found
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  columns: { type: Array, required: true },
  rows: { type: Array, default: () => [] },
})

const formatCell = (val) => {
  if (val == null || val === '') return '—'
  return String(val)
}
</script>
