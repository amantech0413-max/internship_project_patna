<template>
  <div>
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <button type="button" class="nav-link" :class="{ active: tab === 'send' }" @click="tab = 'send'">Send Messages</button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" :class="{ active: tab === 'logs' }" @click="tab = 'logs'; load()">Message Logs</button>
      </li>
    </ul>

    <!-- Send Panel -->
    <div v-show="tab === 'send'" class="card table-card shadow-sm">
      <div class="card-header bg-success text-white fw-semibold">
        <i class="bi bi-whatsapp me-2" />WhatsApp Send Panel
      </div>
      <div class="card-body">
        <p class="text-muted small">Admin pastes group link in Internship Group — system sends invite via Cloud API only.</p>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Select College</label>
            <select v-model="sendForm.college_id" class="form-select" @change="onCollegeChange">
              <option value="">All / Any College</option>
              <option v-for="c in colleges" :key="c.id" :value="c.id">{{ c.college_name }}</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">WhatsApp Group *</label>
            <select v-model="sendForm.internship_group_id" class="form-select" required>
              <option value="">Choose group...</option>
              <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Sending Type</label>
            <div class="btn-group w-100" role="group">
              <input id="typeManual" v-model="sendForm.send_type" type="radio" class="btn-check" value="manual" />
              <label class="btn btn-outline-primary" for="typeManual">Manual Students</label>
              <input id="typeRange" v-model="sendForm.send_type" type="radio" class="btn-check" value="range" />
              <label class="btn btn-outline-primary" for="typeRange">Student ID Range</label>
            </div>
          </div>

          <template v-if="sendForm.send_type === 'manual'">
            <div class="col-12">
              <label class="form-label">Select Students (multi)</label>
              <select v-model="sendForm.student_ids" class="form-select" multiple size="8">
                <option v-for="s in collegeStudents" :key="s.id" :value="s.id">
                  #{{ s.id }} — {{ s.student_name }} ({{ s.mobile_number }})
                </option>
              </select>
              <div class="form-text">Hold Ctrl to select multiple.</div>
            </div>
          </template>

          <template v-else>
            <div class="col-md-6">
              <label class="form-label">Start Student ID</label>
              <input v-model.number="sendForm.start_student_id" type="number" class="form-control" min="1" />
            </div>
            <div class="col-md-6">
              <label class="form-label">End Student ID</label>
              <input v-model.number="sendForm.end_student_id" type="number" class="form-control" min="1" />
            </div>
          </template>

          <div class="col-12 d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-secondary" :disabled="previewing" @click="loadPreview">
              {{ previewing ? 'Loading...' : 'Preview Selected Students' }}
            </button>
            <button type="button" class="btn btn-success" :disabled="sending || !previewStudents.length" @click="send">
              <i class="bi bi-send me-1" />{{ sending ? 'Queuing...' : `Send (${previewStudents.length})` }}
            </button>
          </div>
        </div>

        <div v-if="previewStudents.length" class="mt-4">
          <h3 class="h6">Preview ({{ previewStudents.length }} students)</h3>
          <div class="table-responsive border rounded">
            <table class="table table-sm table-striped mb-0">
              <thead class="table-light">
                <tr><th>ID</th><th>Name</th><th>Code</th><th>Mobile</th></tr>
              </thead>
              <tbody>
                <tr v-for="s in previewStudents" :key="s.id">
                  <td>{{ s.id }}</td>
                  <td>{{ s.student_name }}</td>
                  <td>{{ s.student_code || '—' }}</td>
                  <td>{{ s.mobile_number }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Logs -->
    <div v-show="tab === 'logs'">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <select v-model="filters.status" class="form-select form-select-sm w-auto" @change="load">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="sent">Sent</option>
          <option value="failed">Failed</option>
        </select>
        <button class="btn btn-sm btn-warning" @click="retryAll">Retry Failed</button>
      </div>
      <div class="card table-card">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Student</th>
                <th>Mobile</th>
                <th>Group</th>
                <th>Status</th>
                <th>Retries</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in messages" :key="m.id">
                <td>{{ m.student_name }}<br><small class="text-muted">{{ m.student_code }}</small></td>
                <td>{{ m.mobile }}</td>
                <td>{{ m.group_name }}</td>
                <td><span class="badge" :class="statusBadge(m.status)">{{ m.status }}</span></td>
                <td>{{ m.retry_count }}/{{ m.max_retries }}</td>
                <td>
                  <button v-if="m.status === 'failed'" class="btn btn-sm btn-link" @click="resend(m.id)">Resend</button>
                </td>
              </tr>
              <tr v-if="!messages.length"><td colspan="6" class="text-center text-muted py-4">No messages</td></tr>
            </tbody>
          </table>
        </div>
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
const { fetchMessages, previewStudents: previewApi, sendMessages, retryFailed, resendMessage } = useWhatsapp()
const toast = useToastStore()

const tab = ref('send')
const colleges = ref([])
const groups = ref([])
const collegeStudents = ref([])
const previewStudents = ref([])
const messages = ref([])
const previewing = ref(false)
const sending = ref(false)

const sendForm = reactive({
  college_id: '',
  internship_group_id: '',
  send_type: 'manual',
  student_ids: [],
  start_student_id: null | null,
  end_student_id: null | null,
})

const filters = reactive({ status: '', internship_group_id: '' })

const statusBadge = (s) => {
  const m = { sent: 'text-bg-success', failed: 'text-bg-danger', pending: 'text-bg-warning' }
  return m[String(s)] || 'text-bg-secondary'
}

onMounted(async () => {
  const [c, g] = await Promise.all([
    apiFetch('/admin/colleges/dropdown'),
    apiFetch('/admin/groups?per_page=100'),
  ])
  colleges.value = c.data || []
  groups.value = unwrapList(g).items
})

const onCollegeChange = async () => {
  sendForm.student_ids = []
  previewStudents.value = []
  if (!sendForm.college_id) {
    collegeStudents.value = []
    return
  }
  const res = await apiFetch(
    `/admin/staff-students?college_id=${sendForm.college_id}&per_page=500`
  )
  collegeStudents.value = unwrapList(res).items
}

const buildPreviewBody = () => ({
  college_id: sendForm.college_id ? Number(sendForm.college_id) : null,
  send_type: sendForm.send_type,
  student_ids: sendForm.send_type === 'manual' ? sendForm.student_ids.map(Number) : [],
  start_student_id: sendForm.send_type === 'range' ? sendForm.start_student_id : null,
  end_student_id: sendForm.send_type === 'range' ? sendForm.end_student_id : null,
})

const loadPreview = async () => {
  previewing.value = true
  try {
    const res = await previewApi(buildPreviewBody())
    previewStudents.value = res.data.students
    useToastStore().show(`${res.data.count} student(s) ready to send.`, 'info')
  } catch (e) {
    useToastStore().show(parseApiError(e), 'danger')
  } finally {
    previewing.value = false
  }
}

const send = async () => {
  if (!sendForm.internship_group_id) {
    useToastStore().show('Select a WhatsApp group.', 'danger')
    return
  }
  sending.value = true
  try {
    const res = await sendMessages({
      ...buildPreviewBody(),
      internship_group_id: Number(sendForm.internship_group_id),
      resend: false,
    })
    useToastStore().show(res.message || 'Messages queued.', 'success')
    tab.value = 'logs'
    await load()
  } catch (e) {
    useToastStore().show(parseApiError(e), 'danger')
  } finally {
    sending.value = false
  }
}

const load = async () => {
  const res = await fetchMessages(Object.fromEntries(Object.entries(filters).filter(([, v]) => v)))
  messages.value = unwrapList(res).items
}

const retryAll = async () => {
  await retryFailed()
  useToastStore().show('Failed messages re-queued.', 'info')
  await load()
}

const resend = async (id) => {
  await resendMessage(id)
  useToastStore().show('Message re-queued.', 'success')
  await load()
}

</script>
