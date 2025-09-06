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

const dataReceivers =reactive({
  receivers_name:''
})

const onFormSubmit = async () => {
  const validation = await refForm.value?.validate();
  
  if (validation.valid) {
    try {
      const res = await useApi('/add_receivers', {
        method: 'POST',
        body: JSON.stringify(dataReceivers),
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
          Create New Receiver
        </h4>
        <p class="text-body-1 text-center mb-6">
          Select contacts for fill receiver map
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
                  v-model="dataReceivers.receivers_name"
                  label="Receivers Name"
                  placeholder="Insert Receivers Name"
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
