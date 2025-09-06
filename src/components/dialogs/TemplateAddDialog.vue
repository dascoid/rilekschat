<script setup>
import EmojiPicker from 'vue3-emoji-picker';

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const accessToken = useCookie('accessToken');

const refForm = ref();
const isToastErrorVisible = ref(false)
const errorMessage = ref('')

const isEmojiPickerVisible = ref(false)

const toggleEmojiPicker = () => {
  isEmojiPickerVisible.value = !isEmojiPickerVisible.value
}

const onSelectEmoji = (emoji) => {
  dataTemplate.template_body += emoji.i
}

const dataTemplate =reactive({
  bot:[],
  category : "marketing",
  language : "id",
  template_name : "",
  template_title : "",
  template_footer : "",
  template_body : "Hello",
  variables: [],
  button: {type:'URL',text:'Example',url:'https://www.example.com'}
})

const addButtonCheck = ref(false)

watch(addButtonCheck, (newVal) => {
  if (!newVal) {
    dataTemplate.button.text = ''
    dataTemplate.button.url = ''
  }
})

watch(() => dataTemplate.template_name, (newVal) => {
  if (newVal) {
    dataTemplate.template_name = newVal.replace(/\s+/g, '_')
  }
})

const syncPlaceholders = () => {
  const regex = /\{\{(\d+)\}\}/g
  const foundIndexes = new Set()

  let match
  while ((match = regex.exec(dataTemplate.template_body)) !== null) {
    foundIndexes.add(parseInt(match[1], 10))
  }

  const sortedIndexes = Array.from(foundIndexes).sort((a, b) => a - b)

  const placeholderMap = new Map()
  sortedIndexes.forEach((oldIndex, newIndex) => {
    placeholderMap.set(oldIndex, `{{${newIndex + 1}}}`)
  })

  dataTemplate.template_body = dataTemplate.template_body.replace(regex, (_, num) => placeholderMap.get(parseInt(num, 10)))

  dataTemplate.variables = sortedIndexes.map(index =>
    dataTemplate.variables[index - 1] || ""
  )
}

watch(() => dataTemplate.template_body, () => {
  syncPlaceholders()
})

const onFormSubmit = async () => {
  const validation = await refForm.value?.validate();
  
  if (validation.valid) {
    try {
      const res = await useApi('/add_template', {
        method: 'POST',
        body: JSON.stringify(dataTemplate),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${accessToken.value}`,
        },
      })

      if(res.statusCode.value == 200)
      {
        emit('update:isDialogVisible', false)
        emit('submit')
      }
      else
      {
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
      console.error('Full error:', err);
    }
  }
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const categoryList = [
  {
    title: 'Marketing',
    value: 'MARKETING',
  },
  {
    title: 'Utility',
    value: 'UTILITY',
  }
]

const languageList = [
  {
    title: 'English (US)',
    value: 'en_US',
  },
  {
    title: 'Indonesia',
    value: 'id',
  }
]

const botList = [];

const listBotApi = await useApi(createUrl('/list_bot_company'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (listBotApi?.data?.value && Array.isArray(listBotApi.data.value)) {
  listBotApi.data.value.forEach(bot => {
    if (bot.wa_bot_name && bot.id) {
      botList.push({
        title: bot.wa_bot_name,
        value: bot.id,
      });
    }
  });
}

const addVariable = () => {
  const newIndex = dataTemplate.variables.length + 1
  dataTemplate.variables.push("")

  dataTemplate.template_body += ` {{${newIndex}}}`

  syncPlaceholders()
};

const removeVariable = (index) => {
  const regex = new RegExp(`\\{\\{${index + 1}\\}\\}`, 'g')
  dataTemplate.template_body = dataTemplate.template_body.replace(regex, '')

  dataTemplate.variables.splice(index, 1)

  syncPlaceholders()
}

const renderedTemplate = computed(() => {
  let content = dataTemplate.template_body || ''

  // Ganti variable placeholder dengan isinya
  dataTemplate.variables.forEach((val, index) => {
    const placeholder = `{{${index + 1}}}`
    content = content.replaceAll(placeholder, val || placeholder)
  })

  // Ganti line break dengan <br>
  return content.replace(/\n/g, '<br>')
})

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
          Create New Template
        </h4>
        <p class="text-body-1 text-center mb-6">
          Select the category that best describes your message template. Then select the type of message you want to send.
        </p>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
          ref="refForm"
          lazy-validation
        >
          <VRow>

              <VCol
                cols="12"
                md="12"
              >
                <AppSelect
                  v-model="dataTemplate.bot"
                  :items="botList"
                  label="Whatsapp Bot"
                  placeholder="Select Whatsapp Bot"
                  required
                  :rules="[requiredValidator]"
                />
              </VCol>
            
              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="dataTemplate.category"
                  :items="categoryList"
                  label="Category"
                  placeholder="Select Category"
                  required
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="dataTemplate.language"
                  :items="languageList"
                  label="Language"
                  placeholder="Select Language"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppTextField
                  v-model="dataTemplate.template_name"
                  label="Template Name"
                  placeholder="Insert Template Name"
                  required
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppTextField
                  v-model="dataTemplate.template_title"
                  label="Title"
                  placeholder="Insert Title"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppTextarea
                  v-model="dataTemplate.template_body"
                  label="Content"
                  placeholder="Insert Content"
                  required
                  :rules="[requiredValidator]"
                />

                <IconBtn @click="toggleEmojiPicker">
                  <VIcon icon="tabler-mood-smile" size="22" />
                </IconBtn>
                
                <div v-if="isEmojiPickerVisible" class="overflow-y-auto mt-2" style="position: relative; max-block-size: 150px;">
                  <EmojiPicker
                    :native="true"
                    :hide-search="false"
                    :theme="'auto'"
                    @select="onSelectEmoji"
                  />
                </div>
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppTextField
                  v-model="dataTemplate.template_footer"
                  label="Footer"
                  placeholder="Insert Footer"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <VBtn
                  color="warning"
                  variant="tonal"
                  @click="addVariable"
                >
                  Add Variable
                </VBtn>
              </VCol>

              <p class="text-caption mt-2 mb-0 ml-4">
                (*) Use <strong>contacts_name</strong>, <strong>contacts_phone</strong>, <strong>contacts_email</strong>, or <strong>contacts_address</strong> dynamically by mapping them to contact data.
              </p>

              <!-- Dynamic Variable Fields -->
              <VCol
                v-for="(variable, index) in dataTemplate.variables"
                :key="index"
                cols="12"
                md="12"
                class="d-flex align-center mt-0"
              >
                <AppTextField
                  v-model="dataTemplate.variables[index]"
                  :label="'Variable ' + (index + 1)"
                  placeholder="Enter Variable"
                  required
                  :rules="[requiredValidator]"
                />

                <IconBtn
                  color="error"
                  icon
                  variant="tonal"
                  class="d-flex"
                  :style="'margin-left:10px;margin-top:20px;'"
                >
                  <VIcon icon="tabler-x" @click="removeVariable(index)"/>
                  
                </IconBtn>
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <VCheckbox
                  v-model="addButtonCheck"
                  label="Add Button"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
                v-if="addButtonCheck"
              >
                <AppTextField
                  v-model="dataTemplate.button.text"
                  label="Teks Button"
                  placeholder="Insert Teks Button"
                  required
                  :rules="[requiredValidator]"
                /> 
              </VCol>

              <VCol
                cols="12"
                md="12"
                v-if="addButtonCheck"
              >
                <AppTextField
                  v-model="dataTemplate.button.url"
                  label="URL Button"
                  placeholder="https://..."
                  required
                  :rules="[requiredValidator,urlValidator]"
                /> 
              </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Submit
              </VBtn>

              <VBtn
                color="secondary"
                variant="tonal"
                @click="dialogModelValueUpdate(false)"
              >
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <!-- <VCol cols="12" md="6">
        <div class="bg-white rounded-lg border shadow overflow-hidden">
          <div class="bg-gray-100 px-4 py-2 font-semibold border-b">
            Pratinjau Template
          </div>
          <div class="bg-[url('https://static.whatsapp.net/rsrc.php/v3/yo/r/8kF87eEJS7i.png')] bg-cover p-4 rounded-lg shadow max-w-[350px]">

            <div class="relative bg-white px-4 py-3 rounded-xl shadow text-sm text-gray-800 leading-relaxed">
              <div v-html="renderedTemplate" class="whitespace-normal"></div>
              <span class="absolute bottom-1 right-2 text-[10px] text-gray-500">06.19</span>
            </div>

            <div
              v-if="addButtonCheck && dataTemplate.button.text && dataTemplate.button.url"
              class="mt-3 ml-2"
            >
              <a
                :href="dataTemplate.button.url"
                target="_blank"
                class="text-sky-600 text-sm font-medium flex items-center hover:underline"
              >
                <VIcon icon="tabler-link" class="mr-1" />
                {{ dataTemplate.button.text }}
              </a>
            </div>

          </div>
        </div>
      </VCol> -->

    </VCard>
  </VDialog>

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

<!-- <style>
.chat-bubble::before {
  position: absolute;
  border-radius: 50% 0 0 50%;
  background-color: white;
  block-size: 10px;
  content: "";
  inline-size: 10px;
  inset-block-end: 0;
  inset-inline-start: -7px;
}
</style> -->
