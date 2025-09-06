<script setup>
const props = defineProps({
  contactsData: {
    type: Object,
    required: false,
    default: () => ({
      id: 0,
      contacts_name: '',
      contacts_email: '',
      contacts_phone: '',
      contacts_address: '',
      contacts_gender: '',
      avatar: '',
      bot_id: ''
    }),
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  botId: {
    type: Number,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const contactsData = ref(structuredClone(toRaw(props.contactsData)))
const accessToken = useCookie('accessToken');

onMounted(() => {
  document.addEventListener('focusin', handleFocusIn, true)

  if (props.botId !== undefined && props.botId !== null) {
    contactsData.value.bot_id = props.botId;
  }
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


watch(() => props, () => {
  contactsData.value = structuredClone(toRaw(props.contactsData))
})

const onFormSubmit = async () => {
  try {
    const res = await useApi('/update_contacts', {
      method: 'POST',
      body: JSON.stringify(contactsData.value),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      emit('update:isDialogVisible', false)
      emit('submit', [contactsData.value,true])
    }
  } catch (err) {
    console.error(err)
  }

}

const onFormReset = () => {
  contactsData.value = structuredClone(toRaw(props.contactsData))
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const gender = [
  {
    title: 'Male',
    value: 'male',
  },
  {
    title: 'Female',
    value: 'female',
  },
]

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
          Edit Contact Information
        </h4>
        <p class="text-body-1 text-center mb-6">
          Updating contact details will receive a privacy audit.
        </p>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <!-- 👉 First Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="contactsData.contacts_name"
                label="Full Name"
                placeholder="John Doe"
              />
            </VCol>

            <!-- 👉 Billing Email -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="contactsData.contacts_email"
                label="Email"
                placeholder="johndoe@email.com"
                readonly
              />
            </VCol>

            <!-- 👉 Contact -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="contactsData.contacts_phone"
                label="Phone Number"
                placeholder="+1 9876543210"
                :readonly="!!contactsData.bot_id"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="contactsData.contacts_address"
                label="Address"
                placeholder="1234 Main Street"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="contactsData.contacts_gender"
                label="Gender"
                placeholder="Male,Female"
                :items="gender"
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
</template>
