<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  phoneNumber: {
    type: String,
  },
  waName: {
    type: String,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'submit',
])

const isFormValid = ref(false)
const refForm = ref()
const name = ref('')
const email = ref('')
const phone = ref()
const address = ref()
const gender = ref()
const receivers = ref()
const isToastErrorVisible = ref(false)
const accessToken = useCookie('accessToken');
const errorMessage = ref('');

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

onMounted(() => {

  const phoneNumber = props.phoneNumber?.trim();
  if (phoneNumber) {
    phone.value = phoneNumber;
  }

  const waName = props.waName?.trim();
  if (waName) {
    name.value = waName;
  }
  
})

const onSubmit = async () => {
  const validation = await refForm.value?.validate();

  if (validation.valid) {
    try {
      const res = await useApi('/add_contacts', {
        method: 'POST',
        body: JSON.stringify({
          contacts_name: name.value,
          contacts_email: email.value,
          contacts_phone: phone.value,
          contacts_address: address.value,
          contacts_gender: gender.value,
          receivers: receivers.value,
        }),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${accessToken.value}`,
        },
      });

      if (res.statusCode.value === 200) {
        emit('submit', true);
        emit('update:isDrawerOpen', false);

        await nextTick(() => { 
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

const genderList = [
  {
    title: 'Male',
    value: 'male',
  },
  {
    title: 'Female',
    value: 'female',
  },
]

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
      title="Add New Contacts"
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
              <!-- 👉 Full name -->
              <VCol cols="12">
                <AppTextField
                  v-model="name"
                  :rules="[requiredValidator]"
                  label="Name"
                  placeholder="John Doe"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  :rules="[requiredValidator, emailValidator]"
                  label="Email"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <!-- 👉 Phone Number -->
              <VCol cols="12">
                <AppTextField
                  v-model="phone"
                  type="number"
                  :rules="[requiredValidator]"
                  label="Phone Number"
                  placeholder="+1-541-754-3010"
                />
              </VCol>

              <!-- 👉 Role -->
              <VCol cols="12">
                <AppTextField
                  v-model="address"
                  type="text"
                  :rules="[requiredValidator]"
                  label="Address"
                  placeholder="1234 Main Street"
                />
              </VCol>

              <!-- 👉 Plan -->
              <VCol cols="12">
                <AppSelect
                  v-model="gender"
                  label="Gender"
                  placeholder="Select Gender"
                  :rules="[requiredValidator]"
                  :items="genderList"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="receivers"
                  label="Receivers"
                  placeholder="Select List Receivers"
                  :rules="[requiredValidator]"
                  :items="receiversList"
                />
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
