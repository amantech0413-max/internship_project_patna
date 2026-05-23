<template>
  <div class="rich-text-editor-wrap">
    <QuillEditor
      v-model:content="model"
      content-type="html"
      theme="snow"
      :toolbar="toolbar"
      :placeholder="placeholder"
    />
  </div>
</template>

<script setup>
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const model = defineModel({ type: String, default: '' })

defineProps({
  placeholder: { type: String, default: 'Write content...' },
})

const toolbar = [
  [{ header: [1, 2, 3, false] }],
  ['bold', 'italic', 'underline', 'strike'],
  [{ color: [] }, { background: [] }],
  [{ list: 'ordered' }, { list: 'bullet' }],
  [{ align: [] }],
  ['blockquote', 'code-block'],
  ['link', 'image', 'video'],
  ['clean'],
]
</script>

<style scoped>
.rich-text-editor-wrap :deep(.ql-container) {
  min-height: 220px;
  font-size: 0.95rem;
}

.rich-text-editor-wrap :deep(.ql-editor) {
  min-height: 220px;
}
</style>
