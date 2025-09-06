<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import TemplateMessagePreview from '@/components/dialogs/TemplateMessagePreview.vue';

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  uuidData: {
    type: String
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'submit',
])

const isFormValid = ref(false)
const refForm = ref()
const templates = ref()
const isToastErrorVisible = ref(false)
const accessToken = useCookie('accessToken');
const errorMessage = ref('');
const selectedTemplate = ref([]);

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = async () => {
  const validation = await refForm.value?.validate();

  if (validation.valid) {
    try {
      const res = await useApi('/send_broadcast_contacts', {
        method: 'POST',
        body: JSON.stringify({
          templates_uuid: templates.value,
          contacts_uuid: props.uuidData,
        }),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${accessToken.value}`,
        },
      });

      if (res.statusCode.value === 200) {
        
          emit('submit', true);
          emit('update:isDrawerOpen', false);

          nextTick(() => { 
            refForm.value?.reset();
            refForm.value?.resetValidation();
          });

      } else {
        isToastErrorVisible.value = true;

        const clonedResponse = res.response.value.clone()

        try {
          const json = await clonedResponse.json()
          errorMessage.value = json.message || 'Something went wrong.'
        } catch (e) {
          errorMessage.value = 'Gagal membaca response dari server.'
        }
      }

    } catch (err) {
      console.error(err);
    }
  }
};

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const templatesList = [];

const listTemplatesApi = await useApi(createUrl('/list_templates_contacts'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (listTemplatesApi?.data?.value && Array.isArray(listTemplatesApi.data.value)) {
  listTemplatesApi.data.value.forEach(templates => {
    if (templates.whatsapp_templates_name && templates.whatsapp_templates_uuid) {
      templatesList.push({
        title: templates.whatsapp_templates_name,
        value: templates.whatsapp_templates_uuid,
      });
    }
  });
}

watch(templates, (val) => {
  const found = listTemplatesApi.data.value.find(t => t.whatsapp_templates_uuid === val)
  selectedTemplate.value = found || null
})

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
      title="Send Message Template"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>

              <VCol cols="12">
                <AppSelect
                  v-model="templates"
                  label="Templates"
                  placeholder="Select List Template"
                  :rules="[requiredValidator]"
                  :items="templatesList"
                />
              </VCol>

              <VCol cols="12" v-if="selectedTemplate">

                <TemplateMessagePreview :templateData="selectedTemplate"></TemplateMessagePreview>
                
                <!-- <VCard variant="tonal" class="pa-4">
                  <div class="mb-2 text-subtitle-1 font-weight-medium">📋 Template Preview</div>

                  <div class="mb-2">
                    <strong>Body:</strong>
                    <p
                      class="mb-0 text-base"
                      v-html="formatMessage(selectedTemplate.whatsapp_templates_body)"
                    ></p>
                  </div>

                  <div v-if="selectedTemplate.whatsapp_templates_button">
                    <strong>Buttons:</strong>
                      <div v-for="(btn, index) in JSON.parse(selectedTemplate.whatsapp_templates_button)" :key="index">
                        {{ btn.text }} - {{ btn.url || btn.phone_number || btn }}
                      </div>
                  </div>
                </VCard> -->
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Submit
                </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="error"
                  @click="closeNavigationDrawer"
                >
                  Cancel
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>

  <VSnackbar
    v-model="isToastErrorVisible"
    location="bottom start"
    variant="flat"
    color="error"
    :close-on-content-click=true
  >
    {{ errorMessage }}
  </VSnackbar>

</template>
