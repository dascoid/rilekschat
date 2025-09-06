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
const isToastSuccessVisible = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const webhook_url = import.meta.env.VITE_WEBHOOK_URL
const token_verify = import.meta.env.VITE_VERIFY_TOKEN

const whatsapp_bot =reactive({
  waba_id:'',
  token : ''
})

const onFormSubmit = async () => {
  const validation = await refForm.value?.validate();
  
  if (validation.valid) {
    try {
      const res = await useApi('/set_webhook_manual', {
        method: 'POST',
        body: JSON.stringify(whatsapp_bot),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${accessToken.value}`,
        },
      })

      if (res.error && res.error.value) {
        isToastErrorVisible.value = true
        const errPayload = res.error.value.data || {}
        errorMessage.value =
          errPayload.message ||
          res.error.value.statusMessage ||
          'Terjadi kesalahan saat memproses permintaan.'
        return
      }

      const payload = res?.data?.value ?? res?.data ?? {};

      // wa_bot bisa ada di root atau di data[0]
      const waBot =
        payload?.wa_bot ??
        (Array.isArray(payload?.data) ? payload.data[0] : {}) ??
        {};

      const waStatusNum = Number(waBot?.wa_bot_status);
      const waStatusText = String(
        waBot?.wa_bot_status_text ?? waBot?.status ?? ''
      ).toLowerCase();

      const hasWaBotError =
        waBot?.error !== undefined && waBot?.error !== null && waBot?.error !== '';

      const isWaBotOK =
        waStatusNum === 1 ||
        ['ok', 'active', 'verified', 'connected', 'running', 'success', 'ready'].includes(
          waStatusText
        );

      const isBusinessOK = isWaBotOK && !hasWaBotError;

      if (!isBusinessOK) {
        isToastErrorVisible.value = true;
        errorMessage.value =
          waBot?.message ||
          payload?.message ||
          waBot?.error ||
          payload?.error ||
          'Operasi gagal diproses.';
        return;
      }

      isToastSuccessVisible.value = true;
      successMessage.value = 'Success add manual bot whatsapp';
      emit('update:isDialogVisible', false)
      emit('submit')
    } catch (err) {
      console.error('Full error:', err)
      isToastErrorVisible.value = true
      errorMessage.value = 'Gagal memproses permintaan.'
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
          Manual Add Whatsapp Bot
        </h4>
        <p class="text-body-1 text-center mb-6">
          Fill form to continue integration
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
                  v-model="whatsapp_bot.waba_id"
                  label="Whatsapp Business Account ID"
                  placeholder="Insert Whatsapp Business Account ID"
                  required
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppTextField
                  v-model="whatsapp_bot.token"
                  label="Access Token"
                  placeholder="Insert Access Token"
                  required
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppTextField
                  v-model="webhook_url"
                  label="Webhook Callback URL"
                  readonly
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppTextField
                  v-model="token_verify"
                  label="Verify Token"
                  readonly
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
      {{ errorMessage }}
  </VSnackbar>

  <VSnackbar
    v-model="isToastSuccessVisible"
    location="bottom start"
    variant="flat"
    color="success"
    :close-on-content-click=true
  >
      {{ successMessage }}
  </VSnackbar>

</template>
