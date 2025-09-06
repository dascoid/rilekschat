<script setup>
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const smsVerificationNumber = ref('+1(968) 819-2547')
const isTwoFactorDialogOpen = ref(false)

const props = defineProps({
  userData: {
    type: Object,
    required: true,
  },
})

const password = ref('');
const cpassword = ref('');
const isToastSuccessVisible = ref(false)
const isToastErrorVisible = ref(false)

const recentDeviceHeader = [
  {
    title: 'BROWSER',
    key: 'browser',
  },
  {
    title: 'DEVICE',
    key: 'device',
  },
  {
    title: 'LOCATION',
    key: 'location',
  },
  {
    title: 'RECENT ACTIVITY',
    key: 'activity',
  },
]

const recentDevices = [
  {
    browser: ' Chrome on Windows',
    icon: 'tabler-brand-windows',
    color: 'info',
    device: 'HP Spectre 360',
    location: 'Switzerland',
    activity: '10, July 2021 20:07',
  },
  {
    browser: 'Chrome on Android',
    icon: 'tabler-brand-android',
    color: 'success',
    device: 'Oneplus 9 Pro',
    location: 'Dubai',
    activity: '14, July 2021 15:15',
  },
  {
    browser: 'Chrome on macOS',
    icon: 'tabler-brand-apple',
    color: 'secondary',
    device: 'Apple iMac',
    location: 'India',
    activity: '16, July 2021 16:17',
  },
  {
    browser: 'Chrome on iPhone',
    icon: 'tabler-device-mobile',
    color: 'error',
    device: 'iPhone 12x',
    location: 'Australia',
    activity: '13, July 2021 10:10',
  },
]

const changePassword = async () => {
  try {
    const res = await $api('/change_password', {
      method: 'POST',
      body: JSON.stringify({
        users_uuid : props.userData.users_uuid,
        password : password.value,
      }),
      headers: {
        'Content-Type': 'application/json',
      },
    })

    if (res.code && res.code === 200) {
      isToastSuccessVisible.value = true;
    }

  } catch (err) {
    isToastErrorVisible.value = true;
    console.error(err)
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <!-- 👉 Change password -->
      <VCard title="Change Password">
        <VCardText>
          <VAlert
            closable
            variant="tonal"
            color="warning"
            class="mb-4"
            title="Ensure that these requirements are met"
            text="Minimum 8 characters long, uppercase & symbol"
          />

          <VForm @submit.prevent="() => { }">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  label="New Password"
                  placeholder="············"
                  :type="isNewPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                  :rules="[requiredValidator, passwordValidator]"
                  v-model="password"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  label="Confirm Password"
                  autocomplete="confirm-password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                  :rules="[requiredValidator, confirmedValidator(cpassword, password)]"
                  v-model="cpassword"
                  @paste.prevent
                />
              </VCol>

              <VCol cols="12">
                <VBtn type="submit" @click="changePassword()">
                  Change Password
                </VBtn>
                
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <VSnackbar
      v-model="isToastSuccessVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      Success Change Password
    </VSnackbar>

    <VSnackbar
      v-model="isToastErrorVisible"
      location="bottom start"
      variant="flat"
      color="error"
      :close-on-content-click=true
    >
      Failed Change Password
    </VSnackbar>

</template>
