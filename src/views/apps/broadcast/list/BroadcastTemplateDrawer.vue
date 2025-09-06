<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  templateData: {
    type: Object
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'submit',
])

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const formatMessage = (message = '') => {
  const withLineBreaks = message.replace(/\n/g, '<br>')

  // Regex untuk https:// atau http://
  const urlRegex = /(https?:\/\/[^\s<]+)/g

  // Regex untuk www. tanpa http
  const wwwRegex = /\bwww\.[^\s<]+/g

  // Ganti https URL jadi link
  let withLinks = withLineBreaks.replace(urlRegex, url => {
    return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-underline">${url}</a>`
  })

  // Ganti www. jadi link dengan menambahkan http://
  withLinks = withLinks.replace(wwwRegex, url => {
    return `<a href="http://${url}" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-underline">${url}</a>`
  })

  return withLinks
}
</script>

<template>
  <VNavigationDrawer
    data-allow-mismatch
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title -->
    <AppDrawerHeaderSection
      title="Detail Template"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm>
            <VRow>
              <VCol cols="12" v-if="props.templateData">
                <VCard variant="tonal" class="pa-4">
                  <div class="mb-2 text-subtitle-1 font-weight-medium">📋 Template Preview</div>

                  <div class="mb-2">
                    <strong>Body:</strong>
                    <p
                      class="mb-0 text-base"
                      v-html="formatMessage(props.templateData.whatsapp_templates_body)"
                    ></p>
                  </div>

                  <div v-if="props.templateData.whatsapp_templates_button">
                    <strong>Buttons:</strong>
                      <div v-for="(btn, index) in JSON.parse(props.templateData.whatsapp_templates_button)" :key="index">
                        {{ btn.text }} - {{ btn.url || btn.phone_number || btn }}
                      </div>
                  </div>
                </VCard>
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="error"
                  @click="closeNavigationDrawer"
                >
                  Close
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>

</template>
