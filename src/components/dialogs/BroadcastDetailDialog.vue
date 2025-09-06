<script setup>
const props = defineProps({
  templateData: {
    type: Object,
    required: false,
    default: () => ({
      id: 0,
      bot_id: '',
      company_id: '',
      created_at: '',
      updated_at: '',
      wa_bot_name: '',
      whatsapp_templates_body: '',
      whatsapp_templates_button: '',
      whatsapp_templates_category: '',
      whatsapp_templates_footer: '',
      whatsapp_templates_header: '',
      whatsapp_templates_language: '',
      whatsapp_templates_name: '',
      whatsapp_templates_status: '',
      whatsapp_templates_variable: ''
    }),
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const templateData = ref(structuredClone(toRaw(props.templateData)))

watch(() => props, () => {
  templateData.value = structuredClone(toRaw(props.templateData))
})

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const languageText = computed(() => {
  if (!templateData.value?.whatsapp_templates_language) return 'Not Found'

  const statLowerCase = templateData.value.whatsapp_templates_language.toLowerCase()

  if (statLowerCase === 'id') return 'INDONESIA'
  if (statLowerCase === 'en_US') return 'ENGLISH (US)'

  return 'Not Found'
})

const parsedButtons = computed(() => {
  try {
    return JSON.parse(templateData.value.whatsapp_templates_button || '[]')
  } catch {
    return []
  }
})

const parsedVariables = computed(() => {
  try {
    return JSON.parse(templateData.value.whatsapp_templates_variable || '[]')
  } catch {
    return []
  }
})

// Month names in English
const monthNames = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

// Method to format date
const formatDate = (date) => {
  const originalDate = new Date(date);
  
  const day = String(originalDate.getDate()).padStart(2, '0');
  const month = monthNames[originalDate.getMonth()];
  const year = originalDate.getFullYear();
  const hours = String(originalDate.getHours()).padStart(2, '0');
  const minutes = String(originalDate.getMinutes()).padStart(2, '0');

  return `${day} ${month} ${year} ${hours}:${minutes}`;
};

</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Detail Template Information
        </h4>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
        >
          <VRow>
            <VCol
              cols="12"
              md="12"
            >
              <VCardText class="pl-0 pb-0">
                Date Created : {{ formatDate(templateData.created_at) }}
              </VCardText>
            </VCol>

            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="templateData.wa_bot_name"
                label="Whatsapp Bot Name"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <AppTextField
                class="text-overline"
                v-model="templateData.whatsapp_templates_category"
                label="Category"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <AppTextField
                class="text-overline"
                v-model="languageText"
                label="Language"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <AppTextField
                class="text-overline"
                v-model="templateData.whatsapp_templates_status"
                label="Status"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="templateData.whatsapp_templates_name"
                label="Template Name"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="templateData.whatsapp_templates_header"
                label="Title"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="12"
            >
              <AppTextarea
                v-model="templateData.whatsapp_templates_body"
                label="Content"
                readonly
              />
            </VCol>

            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="templateData.whatsapp_templates_footer"
                label="Footer"
                readonly
              />
            </VCol>

            <VCol 
              cols="12" 
              md="12" 
              v-if="parsedVariables.length"
            >
              <VCard title="Variables">
                <VList class="card-list mb-6">
                  <VListItem v-for="(variable, index) in parsedVariables[0]" :key="index">
                    <VListItemTitle v-html="'{{' + (index + 1) + '}} ' + variable" class="font-weight-medium ml-6"></VListItemTitle>
                  </VListItem>
                </VList>
              </VCard>
            </VCol>

            <VCol 
              cols="12" 
              md="12" 
              v-if="parsedButtons.length"
            >
              <VCard title="Buttons">
                <VList class="card-list mb-6">
                  <VListItem
                    v-for="(button, index) in parsedButtons"
                    :key="index"
                  >
                    <VListItemTitle class="font-weight-medium ml-6">
                      Text : {{  button.text }}
                    </VListItemTitle>
                    <VListItemTitle class="font-weight-medium ml-6">
                      URL : {{  button.url }}
                    </VListItemTitle>
                  </VListItem>
                </VList>
              </VCard>
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn
                color="primary"
                variant="tonal"
                @click="dialogModelValueUpdate(false)"
              >
                Close
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
