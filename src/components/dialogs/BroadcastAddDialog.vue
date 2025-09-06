<script setup>

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

onMounted(() => {
  document.addEventListener('focusin', handleFocusIn, true)
})

onBeforeUnmount(() => {
  document.removeEventListener('focusin', handleFocusIn, true)
})

const handleFocusIn = (e) => {
  const isFlatpickrInput = e.target?.classList?.contains('flatpickr-input')
  const isFlatpickrPopup = e.target?.closest('.flatpickr-calendar')

  if (isFlatpickrInput || isFlatpickrPopup) {
    e.stopImmediatePropagation()
  }
}

const dataBroadcasts =reactive({
  broadcasts_name:'',
  receivers_id:[],
  templates_id:[],
  scheduled_at:'',
})

const onFormSubmit = async () => {
  const validation = await refForm.value?.validate();
  
  if (validation.valid) {
    try {
      const res = await useApi('/add_broadcasts', {
        method: 'POST',
        body: JSON.stringify(dataBroadcasts),
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
      }
    } catch (err) {
      console.error(err)
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

const receiversList = [];

const receiversListApi = await useApi(createUrl('/list_receivers_data'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (receiversListApi?.data?.value && Array.isArray(receiversListApi.data.value)) {
  receiversListApi.data.value.forEach(data => {
    if (data.receivers_name && data.id) {
      receiversList.push({
        title: data.receivers_name,
        value: data.id,
      });
    }
  });
}

const templatesList = [];

const templatesListApi = await useApi(createUrl('/list_templates_data'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (templatesListApi?.data?.value && Array.isArray(templatesListApi.data.value)) {
  templatesListApi.data.value.forEach(data => {
    if (data.whatsapp_templates_name && data.id) {
      templatesList.push({
        title: data.whatsapp_templates_name,
        value: data.id,
      });
    }
  });
}

const scheduled = ref([]);

const sendList = [
  {
    title: 'Send Now',
    value: 'send',
  },
  {
    title: 'Schedule For Later',
    value: 'scheduled',
  }
]

watch(() => scheduled.value, (newVal) => {
  resetSchedule(newVal)
})

const resetSchedule = () =>{
  dataBroadcasts.scheduled_at = '';
}

const now = new Date()
const yesterday = new Date(now)
yesterday.setDate(now.getDate() - 1)
const formatDate = date => date.toISOString().slice(0, 10)
const yesterdayFormatted = formatDate(yesterday)

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
          Create New Broadcast
        </h4>
        <p class="text-body-1 text-center mb-6">
          create list broadcast for broadcasting
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
                <AppTextField
                  v-model="dataBroadcasts.broadcasts_name"
                  label="Broadcasts Name"
                  placeholder="Insert Broadcasts Name"
                  required
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppSelect
                  v-model="dataBroadcasts.receivers_id"
                  :items="receiversList"
                  label="Receivers"
                  placeholder="Select List Receivers"
                  required
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppSelect
                  v-model="dataBroadcasts.templates_id"
                  :items="templatesList"
                  label="Templates"
                  placeholder="Select List Templates"
                  required
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppSelect
                  v-model="scheduled"
                  :items="sendList"
                  placeholder="Select when send it ?"
                  required
                  :rules="[requiredValidator]"
                  @select="resetSchedule()"
                />
              </VCol>

              <VCol
                v-if="scheduled == 'scheduled'"
                cols="12"
                md="12"
              >
                <AppDateTimePicker
                  v-model="dataBroadcasts.scheduled_at"
                  label="Scheduled"
                  placeholder="Select date and time"
                  :config="{ 
                    enableTime: true, 
                    dateFormat: 'Y-m-d H:i', 
                    disable: [{ from: `1119-01-20`, to: `${yesterdayFormatted}` }]
                  }"
                  required
                  :rules="[requiredValidator]"
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
    </VCard>
  </VDialog>

  <VSnackbar
      v-model="isToastErrorVisible"
      location="bottom start"
      variant="flat"
      color="error"
      :close-on-content-click=true
    >
      Failed Add Receivers
    </VSnackbar>

</template>
