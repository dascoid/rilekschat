<script setup>
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useReCaptcha } from 'vue-recaptcha-v3'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const route = useRoute('reset-password-uuid')
const router = useRouter()
const token = route.params.uuid;
const isToastErrorVisible = ref(false)
const isToastSuccessVisible = ref(false)
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const password = ref('')
const cPassword = ref('')
const errorMessage = ref('')
const successMessage = ref('')
const { executeRecaptcha } = useReCaptcha()

const onSubmit = async () => {

  try {

    // Jalankan Google reCAPTCHA
    const recaptchaToken = await executeRecaptcha('register')

    // Cek token hasil reCAPTCHA
    if (!recaptchaToken) {
      isToastErrorVisible.value = true
      errorMessage.value = 'Gagal mendapatkan token reCAPTCHA'
      return 
    }

    // Cek token hasil reCAPTCHA
    if (password.value != cPassword.value) {
      isToastErrorVisible.value = true
      errorMessage.value = 'Password tidak sama';
      return 
    }

    const res = await $api('/auth/reset-password/'+token, {
      method: 'POST',
      body: JSON.stringify({
        password : password.value,
        recaptcha_token: recaptchaToken
      }),
      headers: {
        'Content-Type': 'application/json',
      },
    })

    if (res.success) 
    {
      isToastSuccessVisible.value = true;
      successMessage.value = res.message;
    }

    setTimeout(() => {
      if (res.success) {
        window.location.href = '/login';
      }
    }, 2000)

  } catch (err) {
    isToastErrorVisible.value = true;

    if (err.response && err.response._data?.errors) 
    {
      errorMessage.value = 'Please double check your input.';
    }
    else 
    {
      errorMessage.value = err.response && err.response._data && err.response._data.message ? err.response._data.message : 'An error occurred, please try again.';
    }
  }
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth card -->
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-2'"
      >
        <VCardItem class="justify-center">
          <VCardTitle>
            <RouterLink to="/">
              <div class="app-logo">
                <VNodeRenderer :nodes="themeConfig.app.logo" />
                <h1 class="app-logo-title">
                  {{ themeConfig.app.title }}
                </h1>
              </div>
            </RouterLink>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <VCol
                cols="12"
                md="12"
              >
                <AppTextField
                  v-model="password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  :rules="[requiredValidator, passwordValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
              >
                <AppTextField
                  v-model="cPassword"
                  label="Confirm Password"
                  autocomplete="confirm-password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                  :rules="[requiredValidator, confirmedValidator(cPassword, password)]"
                  @paste.prevent
                />
              </VCol>

              <VCol>
                <VBtn
                  block
                  type="submit"
                >
                  Reset Password
                </VBtn>
              </VCol>

              <VCol cols="12">
                <RouterLink
                  class="d-flex align-center justify-center"
                  :to="{ name: 'login' }"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    size="20"
                    class="me-1 flip-in-rtl"
                  />
                  <span>Back to login</span>
                </RouterLink>
              </VCol>

              <VCol
                cols="12"
                class="text-center mt-16"
              >
                <p class="text-caption text-medium-emphasis">
                  This site is protected by reCAPTCHA and the Google
                  <a href="https://policies.google.com/privacy" target="_blank" class="text-primary">Privacy Policy</a> and
                  <a href="https://policies.google.com/terms" target="_blank" class="text-primary">Terms of Service</a> apply.
                </p>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>

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

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
