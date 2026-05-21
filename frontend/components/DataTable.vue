<template>
  <div>
    <!-- Mobile cards -->
    <div class="md:hidden space-y-3">
      <div
        v-for="(row, idx) in rows"
        :key="idx"
        class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm"
      >
        <div class="space-y-2 text-sm">
          <div v-for="col in columns" :key="col.key" class="flex justify-between gap-2">
            <span class="text-slate-500 shrink-0">{{ col.label }}</span>
            <span class="font-medium text-right break-all">{{ formatCell(row[col.key]) }}</span>
          </div>
        </div>
        <div v-if="$slots.actions" class="mt-3 pt-3 border-t flex flex-wrap gap-2">
          <slot name="actions" :row="row" />
        </div>
      </div>
      <p v-if="!rows?.length" class="text-center text-slate-500 py-8 bg-white rounded-xl border">No records found</p>
    </div>

    <!-- Desktop table -->
    <div class="hidden md:block overflow-x-auto bg-white rounded-xl border border-slate-200">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-100 text-left">
          <tr>
            <th v-for="col in columns" :key="col.key" class="px-4 py-3 font-semibold whitespace-nowrap">{{ col.label }}</th>
            <th v-if="$slots.actions" class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, idx) in rows" :key="idx" class="border-t border-slate-100 hover:bg-slate-50">
            <td v-for="col in columns" :key="col.key" class="px-4 py-3">{{ formatCell(row[col.key]) }}</td>
            <td v-if="$slots.actions" class="px-4 py-3 whitespace-nowrap">
              <slot name="actions" :row="row" />
            </td>
          </tr>
          <tr v-if="!rows?.length">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-8 text-center text-slate-500">
              No records found
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  columns: { key: string; label: string }[]
  rows: Record<string, unknown>[]
}>()

const formatCell = (val: unknown) => {
  if (val == null || val === '') return '—'
  return String(val)
}
</script>
