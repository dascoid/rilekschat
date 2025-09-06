<script setup>
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2ForgotPasswordIllustrationDark from '@images/pages/auth-v2-forgot-password-illustration-dark.png'
import authV2ForgotPasswordIllustrationLight from '@images/pages/auth-v2-forgot-password-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useReCaptcha } from 'vue-recaptcha-v3'

const authThemeImg = useGenerateImageVariant(authV2ForgotPasswordIllustrationLight, authV2ForgotPasswordIllustrationDark)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const route = useRoute()
const router = useRouter()
const isToastErrorVisible = ref(false)
const errorMessage = ref('')
const isToastSuccessVisible = ref(false)
const successMessage = ref('')
const emailUsers = ref('')
const errors = {};
const { executeRecaptcha } = useReCaptcha()

const onSubmit = async () => {

  try {

    // Jalankan Google reCAPTCHA
    const recaptchaToken = await executeRecaptcha('forgotPassword')

    // Cek token hasil reCAPTCHA
    if (!recaptchaToken) {
      isToastErrorVisible.value = true
      console.error('Gagal mendapatkan token reCAPTCHA')
      return
    }

    const res = await $api('/auth/forgot-password', {
      method: 'POST',
      body: JSON.stringify({
        email : emailUsers.value,
        recaptcha_token: recaptchaToken,
        website:  import.meta.env.VITE_APP_URL,
      }),
      headers: {
        'Content-Type': 'application/json',
      },
    })
    
    if (!errors.value || errors.value === '') {
      isToastSuccessVisible.value = true;
      successMessage.value = 'Link reset password telah dikirim ke email Anda'
    }

    setTimeout(() => {
      if (!errors.value || errors.value === '') {
        router.replace('login');
      }
    }, 2000)

  } catch (err) {
      isToastErrorVisible.value = true;

      if (err.response && err.response._data?.errors) 
      {
        errors.value = err.response._data.errors;
        
        if (errors.value.email && errors.value.email.length) {
          errorMessage.value = errors.value.email[0];
        } else {
          errorMessage.value = 'Please double check your input.';
        }
      }
      else 
      {
        errorMessage.value = 'An error occurred, please try again.';
        console.error(err);
      }

  }
}

</script>

<template>
  <RouterLink to="/">
    <div class="auth-logo d-flex align-center gap-x-3">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="auth-title">
        {{ themeConfig.app.title }}
      </h1>
    </div>
  </RouterLink>

  <VRow
    class="auth-wrapper bg-surface"
    no-gutters
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative bg-background w-100 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="padding-inline: 150px;"
        >
          <VImg
            max-width="468"
            :src="authThemeImg"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <img
          class="auth-footer-mask"
          :src="authThemeMask"
          alt="auth-footer-mask"
          height="280"
          width="100"
        >
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            Forgot Password? 🔒
          </h4>
          <p class="mb-0">
            Enter your email and we'll send you instructions to reset your password
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="onSubmit">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="emailUsers"
                  autofocus
                  label="Email"
                  type="email"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <!-- Reset link -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                >
                  Send Reset Link
                </VBtn>
              </VCol>

              <!-- back to login -->
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
            </VRow>
          </VForm>

          <div class="mb-0 mt-16">
            <p class="text-caption text-medium-emphasis">
              This site is protected by reCAPTCHA and the Google
              <a href="https://policies.google.com/privacy" target="_blank" class="text-primary">Privacy Policy</a> and
              <a href="https://policies.google.com/terms" target="_blank" class="text-primary">Terms of Service</a> apply.
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

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
/* stylelint-disable-next-line scss/load-partial-extension */
@use "@core-scss/template/pages/page-auth.scss";
</style>
