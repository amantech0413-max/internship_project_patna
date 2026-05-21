<template>
  <div class="p-6 lg:p-8">
    <div v-if="group" class="max-w-5xl mx-auto space-y-6">
      <div class="flex flex-wrap justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold">{{ group.name }}</h2>
          <p class="text-sm text-slate-500">
            {{ group.internship_mode }} · {{ group.semester }} · {{ group.college_name || 'All colleges' }}
          </p>
        </div>
        <div class="flex gap-2">
          <button class="px-3 py-1.5 text-sm border rounded-lg" @click="saveGroup">Save Changes</button>
          <button class="px-3 py-1.5 text-sm text-red-700 border border-red-200 rounded-lg" @click="deleteGroup">
            Delete
          </button>
        </div>
      </div>

      <div class="bg-white rounded-xl border p-4 space-y-3">
        <h3 class="font-semibold">Group Details</h3>
        <input v-model="editForm.whatsapp_group_link" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="WhatsApp invite link" />
        <p class="text-xs text-amber-700 bg-amber-50 p-2 rounded">
          Admin creates the WhatsApp group manually, pastes the invite link here, assigns students, then sends invitation messages.
        </p>
      </div>

      <div class="grid lg:grid-cols-2 gap-6">
        <BulkStudentSelector
          v-model="selectedIds"
          title="Assign Students to Group"
          :students="availableStudents"
          @search="loadAvailable"
        />
        <div class="space-y-3">
          <button
            class="w-full py-2.5 bg-slate-800 text-white rounded-lg text-sm"
            :disabled="!selectedIds.length"
            @click="assignStudents"
          >
            Assign Selected ({{ selectedIds.length }})
          </button>
          <button
            class="w-full py-2.5 bg-green-700 text-white rounded-lg text-sm"
            :disabled="!selectedIds.length || !editForm.whatsapp_group_link"
            @click="sendWhatsapp"
          >
            Send WhatsApp Invitation
          </button>
          <button
            class="w-full py-2.5 bg-green-600 text-white rounded-lg text-sm border border-green-800"
            :disabled="!selectedIds.length || !editForm.whatsapp_group_link"
            @click="sendWhatsapp(true)"
          >
            Re-send Invitation Links
          </button>
          <router-link
            :to="`/whatsapp?group=${group.id}`"
            class="block text-center py-2 text-sm text-blue-700 border rounded-lg"
          >
            View Message History
          </router-link>
        </div>
      </div>

      <div class="bg-white rounded-xl border p-4">
        <h3 class="font-semibold mb-3">Assigned Students ({{ assignedStudents.length }})</h3>
        <ul class="divide-y text-sm">
          <li v-for="s in assignedStudents" :key="s.id" class="py-2 flex justify-between">
            <span>{{ s.name }} ({{ s.student_code }}) — {{ s.mobile }}</span>
            <button class="text-red-600 text-xs" @click="unassign(s.id)">Remove</button>
          </li>
        </ul>
      </div>
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
import { useWhatsapp } from '@/composables/useWhatsapp'
const auth = useAuthStore()
const router = useRouter()

const route = useRoute()
const { sendInvitations } = useWhatsapp()

const group = ref(null)
const availableStudents = ref([])
const selectedIds = ref([])
const editForm = reactive({ whatsapp_group_link: '' })

const assignedStudents = computed(() => group.value?.students || [])

const loadGroup = async () => {
  const res = await apiFetch(`/admin/groups/${route.params.id}`)
  group.value = res.data
  editForm.whatsapp_group_link = (res.data.whatsapp_group_link) || ''
}

const loadAvailable = async (query = { search: '', internship_mode: '' }) => {
  const params = new URLSearchParams(query )
  const res = await apiFetch(
    `/admin/groups/${route.params.id}/available-students?${params}`
  )
  availableStudents.value = res.data || []
}

const assignStudents = async () => {
  await apiFetch(`/admin/groups/${route.params.id}/assign`, {
    method: 'POST',
    body: { student_ids: selectedIds.value },
  })
  selectedIds.value = []
  await loadGroup()
  await loadAvailable()
  alert('Students assigned. Click Send WhatsApp Invitation to notify them.')
}

const unassign = async (id) => {
  await apiFetch(`/admin/groups/${route.params.id}/unassign`, {
    method: 'POST',
    body: { student_ids: [id] },
  })
  await loadGroup()
}

const sendWhatsapp = async (resend = false) => {
  const ids = selectedIds.value.length ? selectedIds.value : assignedStudents.value.map((s) => s.id)
  await sendInvitations(Number(route.params.id), ids, resend)
  alert(resend ? 'Re-send queued' : 'WhatsApp invitations queued')
  router.push(`/whatsapp?group=${route.params.id}`)
}

const saveGroup = async () => {
  const g = group.value
  await apiFetch(`/admin/groups/${route.params.id}`, {
    method: 'PUT',
    body: {
      name: g.name,
      semester: g.semester,
      subject: g.subject,
      college_name: g.college_name,
      internship_mode: g.internship_mode,
      whatsapp_group_link: editForm.whatsapp_group_link,
      start_date: g.start_date,
      end_date: g.end_date,
      status: g.status,
    },
  })
  await loadGroup()
}

const deleteGroup = async () => {
  if (!confirm('Delete this group?')) return
  await apiFetch(`/admin/groups/${route.params.id}`, { method: 'DELETE' })
  router.push('/groups')
}

onMounted(async () => {
  await loadGroup()
  await loadAvailable()
})
</script>
