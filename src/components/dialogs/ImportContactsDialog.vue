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
const file = ref(null)
const receiversSelected = ref()
const isLoadingDialogVisible = ref(false)

const fileUrl = "/storage/import_contacts_example.xlsx";

const downloadFile = () => {
  const link = document.createElement("a");
  link.href = fileUrl;
  link.setAttribute("download", "import_contacts_example.xlsx");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const onFormSubmit = async () => {
  const validation = await refForm.value?.validate();
  
  if (validation.valid) {
    
    isLoadingDialogVisible.value = true

    try {
      const formData = new FormData()
      formData.append('file', file.value)
      formData.append('receivers', receiversSelected.value)

      const res = await useApi('/import_contacts', {
        method: 'POST',
        body: formData,
        headers: {
          'Authorization': `Bearer ${accessToken.value}`,
        },
      })

      if(res.statusCode.value == 200)
      {
        emit('update:isDialogVisible', false)
        emit('submit', true)
      }
      
      isLoadingDialogVisible.value = false
      
    } catch (err) {
      isLoadingDialogVisible.value = false
      console.error(err)
    }
  }

}

const onFormReset = () => {
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const receiversList = [];

const listReceiversApi = await useApi(createUrl('/list_receivers_contacts'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (listReceiversApi?.data?.value && Array.isArray(listReceiversApi.data.value)) {
  listReceiversApi.data.value.forEach(receivers => {
    if (receivers.receivers_name && receivers.id) {
      receiversList.push({
        title: receivers.receivers_name,
        value: receivers.id,
      });
    }
  });
}

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
          Upload Contact File
        </h4>
        <p class="text-body-1 text-center mb-6">
          add contact with file.
        </p>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          ref="refForm"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            
            <VCol
              cols="12"
              md="8"
            >
              <AppSelect
                v-model="receiversSelected"
                label="Receivers"
                placeholder="Select List Receivers"
                :rules="[requiredValidator]"
                required
                :items="receiversList"
              />
            </VCol>

            <VCol
              cols="12"
              md="8"
            >
              <VFileInput
                v-model="file"
                placeholder="Upload your documents"
                label="File input"
                prepend-icon="tabler-paperclip"
                accept=".csv,.xlsx"
                required
                :rules="[requiredValidator]"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VBtn color="primary" @click="downloadFile">
                Download File Example
              </VBtn>
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
                @click="onFormReset"
              >
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>

  <VDialog
    v-model="isLoadingDialogVisible"
    width="300"
  >
    <VCard
      color="info"
      width="300"
    >
      <VCardText class="pt-3">
        Please stand by
        <VProgressLinear
          indeterminate
          bg-color="rgba(var(--v-theme-surface), 0.1)"
          :height="8"
          class="mb-0 mt-4"
        />
      </VCardText>
    </VCard>
  </VDialog>

</template>
