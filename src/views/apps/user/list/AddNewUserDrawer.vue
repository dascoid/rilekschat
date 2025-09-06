<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
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
const date_of_birth = ref('')
const role = ref('')
const phone_number = ref()
const address = ref()
const gender = ref()
const isToastErrorVisible = ref(false)
role.value = 'employee';
const accessToken = useCookie('accessToken');

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
      const res = await useApi('/add_users', {
        method: 'POST',
        body: JSON.stringify({
          name: name.value,
          email: email.value,
          date_of_birth: date_of_birth.value,
          role: role.value,
          phone_number: phone_number.value,
          address: address.value,
          gender: gender.value
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
      }

    } catch (err) {
      isToastErrorVisible.value = true;
      console.error(err);
    }
  }
};

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

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
      title="Add New User"
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

              <!-- 👉 date of birth -->
              <VCol cols="12">
                <AppDateTimePicker
                  v-model="date_of_birth"
                  :rules="[requiredValidator]"
                  label="Date of Birth"
                  placeholder="Select date"
                />
              </VCol>

              <!-- 👉 Role -->
              <VCol cols="12">
                <AppSelect
                  v-model="role"
                  label="Role"
                  placeholder="Admin/Employee"
                  :items="roles"
                />
              </VCol>

              <!-- 👉 Phone Number -->
              <VCol cols="12">
                <AppTextField
                  v-model="phone_number"
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
    Failed Add Data
  </VSnackbar>
</template>
