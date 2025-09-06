<script setup>
const props = defineProps({
  userData: {
    type: Object,
    required: false,
    default: () => ({
      id: 0,
      name: '',
      role: '',
      email: '',
      activated: '',
      avatar: '',
      gender: '',
      date_of_birth: '',
      phone_number: '',
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

const userData = ref(structuredClone(toRaw(props.userData)))
const accessToken = useCookie('accessToken');

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

watch(() => props, () => {
  userData.value = structuredClone(toRaw(props.userData))
})

const onFormSubmit = async () => {
  try {
    const res = await useApi('/update_users', {
      method: 'POST',
      body: JSON.stringify(userData.value),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      emit('update:isDialogVisible', false)
      emit('submit', [userData.value,true])
    }
  } catch (err) {
    console.error(err)
  }

}

const onFormReset = () => {
  userData.value = structuredClone(toRaw(props.userData))
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const status = [
  {
    title: 'Active',
    value: '1',
  },
  {
    title: 'Deactive',
    value: '0',
  },
]

const roles = [
  {
    title: 'Admin',
    value: 'admin',
  },
  {
    title: 'Employee',
    value: 'employee',
  },
]

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
          Edit User Information
        </h4>
        <p class="text-body-1 text-center mb-6">
          Updating user details will receive a privacy audit.
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
                v-model="userData.name"
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
                v-model="userData.email"
                label="Email"
                placeholder="johndoe@email.com"
                readonly
              />
            </VCol>

            <!-- 👉 Language -->
            <VCol
              cols="12"
              md="6"
            >
              <AppDateTimePicker
                v-model="userData.date_of_birth"
                label="Date of Birth"
                placeholder="Select date"
              />
            </VCol>

            <!-- 👉 Status -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="userData.activated"
                label="Status"
                placeholder="Active"
                :items="status"
              />
            </VCol>

            <!-- 👉 Tax Id -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="userData.role"
                label="Role"
                placeholder="Admin/Employee"
                :items="roles"
              />
            </VCol>

            <!-- 👉 Contact -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="userData.phone_number"
                label="Phone Number"
                placeholder="+1 9876543210"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="userData.address"
                label="Address"
                placeholder="1234 Main Street"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="userData.gender"
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
