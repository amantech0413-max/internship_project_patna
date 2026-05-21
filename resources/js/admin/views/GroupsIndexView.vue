<template>
  <div class="p-6 lg:p-8">
    <div class="flex flex-wrap justify-between items-center gap-4">
      <div>
        <h2 class="text-2xl font-bold">Internship Groups</h2>
        <p class="text-sm text-slate-500 mt-1">Admin pastes WhatsApp invite link — students join manually</p>
      </div>
      <router-link to="/groups/create" class="px-4 py-2 bg-blue-700 text-white rounded-lg text-sm">Add Group</router-link>
    </div>

    <div class="mt-4 flex flex-wrap gap-3">
      <input v-model="filters.search" placeholder="Search groups..." class="border rounded-lg px-3 py-2 text-sm" />
      <select v-model="filters.internship_mode" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">All Modes</option>
        <option value="online">Online</option>
        <option value="offline">Offline</option>
      </select>
      <select v-model="filters.status" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
        <option value="inactive">Inactive</option>
      </select>
      <button class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm" @click="load">Filter</button>
    </div>

    <div class="mt-4 grid gap-3">
      <div
        v-for="group in groups"
        :key="group.id"
        class="bg-white rounded-xl border p-4 flex flex-wrap justify-between items-center gap-3"
      >
        <div>
          <h3 class="font-semibold">{{ group.name }}</h3>
          <p class="text-xs text-slate-500 mt-1">
            {{ group.internship_mode }} · {{ group.semester || '—' }} · {{ group.students_count ?? 0 }} students
          </p>
        </div>
        <div class="flex gap-2">
          <router-link :to="`/groups/${group.id}`" class="px-3 py-1.5 text-sm border rounded-lg hover:bg-slate-50">
            Manage
          </router-link>
        </div>
      </div>
      <p v-if="!groups.length" class="text-slate-500 text-sm">No groups found</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch, apiForm, apiDownload, getPublicApi } from '@/api/client'
import { parseApiError, unwrapList, useFetchData } from '@/utils/apiHelpers'
import { useToastStore } from '@/stores/toast'
const auth = useAuthStore()
const groups = ref([])
const filters = reactive({ search: '', internship_mode: '', status: '' })

const load = async () => {
  const query = new URLSearchParams()
  Object.entries(filters).forEach(([k, v]) => v && query.set(k, v))
  const res = await apiFetch(`/admin/groups?${query}`)
  const { items } = unwrapList(res)
  groups.value = items
}

onMounted(load)
</script>
