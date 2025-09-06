<script setup>
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { default as registerMultiStepIllustrationDark, default as registerMultiStepIllustrationLight } from '@images/illustrations/sitting-girl-with-laptop.png'
import registerMultiStepBgDark from '@images/pages/register-multi-step-bg-dark.png'
import registerMultiStepBgLight from '@images/pages/register-multi-step-bg-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useReCaptcha } from 'vue-recaptcha-v3'

const registerMultiStepBg = useGenerateImageVariant(registerMultiStepBgLight, registerMultiStepBgDark)

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const route = useRoute()
const router = useRouter()
const currentStep = ref(0)
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const registerMultiStepIllustration = useGenerateImageVariant(registerMultiStepIllustrationLight, registerMultiStepIllustrationDark)
const refAccountForm = ref()
const refPersonalForm = ref()
const isCurrentStepValid = ref(true)
const isToastErrorVisible = ref(false)
const errorMessage = ref('')

const isSuccessDialogVisible = ref(false)
const successCountdown = ref(3)
let countdownTimer = null
let redirectTimer = null

const { executeRecaptcha } = useReCaptcha()

const errors = ref({})

const items = [
  {
    title: 'Account',
    subtitle: 'Account Details',
    icon: 'tabler-file-analytics',
  },
  {
    title: 'Personal',
    subtitle: 'Enter Information',
    icon: 'tabler-user',
  },
]

const accountForm = ref({
  fullname: '',
  email: '',
  password: '',
  cPassword: '',
})

const personalForm = ref({
  mobile: '',
  address: '',
  company_name: '',
})

const validateAccountForm = () => {
  refAccountForm.value?.validate().then(valid => {
    if (valid.valid) {
      currentStep.value++
      isCurrentStepValid.value = true
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const validatePersonalForm = () => {
  refPersonalForm.value?.validate().then(valid => {
    if (valid.valid) {
      currentStep.value++
      isCurrentStepValid.value = true
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const isSubmitting = ref(false)
const TOTAL_SECONDS = 8 

const onSubmit = async () => {

  try {

    isSubmitting.value = true

    // Jalankan Google reCAPTCHA
    const recaptchaToken = await executeRecaptcha('register')

    // Cek token hasil reCAPTCHA
    if (!recaptchaToken) {
      isToastErrorVisible.value = true
      console.error('Gagal mendapatkan token reCAPTCHA')
      return 
    }

    const res = await $api('/auth/register', {
      method: 'POST',
      body: JSON.stringify({
        fullname : accountForm.value.fullname,
        email : accountForm.value.email,
        password : accountForm.value.password,
        company_name : personalForm.value.company_name,
        mobile : personalForm.value.mobile,
        address : personalForm.value.address,
        recaptcha_token: recaptchaToken,
        website:  import.meta.env.VITE_APP_URL,
      }),
      headers: {
        'Content-Type': 'application/json',
      },
    })

    isSuccessDialogVisible.value = true
    await nextTick()

    successCountdown.value = TOTAL_SECONDS
    countdownTimer = setInterval(() => {
      successCountdown.value -= 1
    }, 1000)
    redirectTimer = setTimeout(() => {
      goToLoginNow()
    }, TOTAL_SECONDS * 1000)

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

const goToLoginNow = () => {
  if (countdownTimer) clearInterval(countdownTimer)
  if (redirectTimer) clearTimeout(redirectTimer)
  router.replace({ name: 'login' })
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
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="4"
      class="d-none d-md-flex"
    >
      <!-- here your illustration -->
      <div class="d-flex justify-center align-center w-100 position-relative">
        <VImg
          :src="registerMultiStepIllustration"
          class="illustration-image flip-in-rtl"
        />

        <img
          class="bg-image position-absolute w-100 flip-in-rtl"
          :src="registerMultiStepBg"
          alt="register-multi-step-bg"
          height="340"
        >
      </div>
    </VCol>

    <VCol
      cols="12"
      md="8"
      class="auth-card-v2 d-flex align-center justify-center pa-10"
      style="background-color: rgb(var(--v-theme-surface));"
    >
      
      <VCard
        flat
        class="mt-12 mt-sm-10"
      >

        <VCol cols="12">
          <RouterLink
           class="d-flex align-center justify-left"
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

        <VDivider />
          <span class="mx-4"></span>

        <AppStepper
          v-model:current-step="currentStep"
          :items="items"
          :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
          icon-size="22"
          class="stepper-icon-step-bg mb-12"
        />

        <VWindow
          v-model="currentStep"
          class="disable-tab-transition"
          style="max-inline-size: 681px;"
        >
            <VWindowItem>
              <VForm
                ref="refAccountForm"
                @submit.prevent="validateAccountForm"
              >
                  <h4 class="text-h4">
                    Account Information
                  </h4>
                  <p class="text-body-1 mb-6">
                    Enter Your Account Details
                  </p>
    
                  <VRow>
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="accountForm.fullname"
                        label="Fullname"
                        placeholder="John Doe"
                        :rules="[requiredValidator]"
                      />
                    </VCol>
    
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="accountForm.email"
                        label="Email"
                        placeholder="johndoe@email.com"
                        :rules="[requiredValidator, emailValidator]"
                      />
                    </VCol>
    
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="accountForm.password"
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
                      md="6"
                    >
                      <AppTextField
                        v-model="accountForm.cPassword"
                        label="Confirm Password"
                        autocomplete="confirm-password"
                        placeholder="············"
                        :type="isConfirmPasswordVisible ? 'text' : 'password'"
                        :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                        :rules="[requiredValidator, confirmedValidator(accountForm.cPassword, accountForm.password)]"
                        @paste.prevent
                      />
                    </VCol>
    
                    <VCol cols="12">
                      <div class="d-flex flex-wrap gap-4 justify-sm-space-between justify-center mt-8">
                        <VBtn
                          color="secondary"
                          variant="tonal"
                          disabled
                        >
                          <VIcon
                            icon="tabler-arrow-left"
                            start
                            class="flip-in-rtl"
                          />
                          Previous
                        </VBtn>

                        <VBtn type="submit">
                          Next
                          <VIcon
                            icon="tabler-arrow-right"
                            end
                            class="flip-in-rtl"
                          />
                        </VBtn>
                      </div>
                    </VCol>
                  </VRow>
              </VForm>
            </VWindowItem>

            <VWindowItem>
              <VForm
                ref="refPersonalForm"
                @submit.prevent="validatePersonalForm"
              >
                <h4 class="text-h4">
                  Personal Information
                </h4>
                <p>
                  Enter Your Personal Information
                </p>

                <VRow>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <AppTextField
                      v-model="personalForm.company_name"
                      label="Company Name"
                      placeholder="PT. John Doe"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <AppTextField
                      v-model="personalForm.mobile"
                      type="number"
                      label="Whatsapp Number"
                      placeholder="+1 123 456 7890"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol cols="12">
                    <AppTextField
                      v-model="personalForm.address"
                      label="Address"
                      placeholder="1234 Main St, New York, NY 10001, USA"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol cols="12">
                    <div class="d-flex flex-wrap gap-4 justify-sm-space-between justify-center mt-8">
                      <VBtn
                        color="secondary"
                        variant="tonal"
                        @click="currentStep--"
                        :disabled="isSubmitting"
                      >
                        <VIcon
                          icon="tabler-arrow-left"
                          start
                          class="flip-in-rtl"
                        />
                        Previous
                      </VBtn>

                      <VBtn
                        color="success"
                        type="submit"
                        :loading="isSubmitting"
                        :disabled="isSubmitting"
                        @click="onSubmit"
                      >
                        submit
                      </VBtn>
                    </div>
                  </VCol>
                </VRow>
              </VForm>
            </VWindowItem>

        </VWindow>

        <div class="mb-0 mt-16">
          <p class="text-caption text-medium-emphasis">
            This site is protected by reCAPTCHA and the Google
            <a href="https://policies.google.com/privacy" target="_blank" class="text-primary">Privacy Policy</a> and
            <a href="https://policies.google.com/terms" target="_blank" class="text-primary">Terms of Service</a> apply.
          </p>
        </div>

      </VCard>
    </VCol>
  </VRow>

  <VDialog
    v-model="isSuccessDialogVisible"
    width="640"
    eager
    scrim="rgba(0,0,0,.70)"
    transition="dialog-bottom-transition"
    class="success-dialog"
  >
    <VCard class="success-card" elevation="16" rounded="xl">
      <!-- Hero title dengan badge -->
      <VCardTitle class="success-title d-flex align-center gap-3">
        <VAvatar size="44" class="success-badge">
          <VIcon icon="tabler-circle-check" size="26" />
        </VAvatar>
        <div>
          <div class="text-h5 font-weight-medium">Registration Successful</div>
          <div class="text-body-2 text-medium-emphasis">You’re almost there!</div>
        </div>
      </VCardTitle>

      <VDivider class="my-2" />

      <VCardText class="text-medium-emphasis pt-4">
        Your account has been created. Please check your email to verify your address.
        You will be redirected to the login page in
        <strong>{{ successCountdown }}</strong> seconds.
        <VProgressLinear
          class="mt-4 rounded-lg"
          height="6"
          color="primary"
          :model-value="(successCountdown / TOTAL_SECONDS) * 100"
        />
      </VCardText>

      <VCardActions class="justify-center pt-2">
        <VBtn variant="flat" color="primary"  size="x-large" class="px-8 py-4" @click="goToLoginNow">Log in now</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <VOverlay
    :model-value="isSubmitting"
    class="align-center justify-center"
    contained
    persistent
  >
    <div class="d-flex flex-column align-center">
      <VProgressCircular indeterminate size="42" width="4" />
      <div class="mt-3 text-medium-emphasis">Submitting…</div>
    </div>
  </VOverlay>

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

<style lang="scss">
@use "@core-scss/template/pages/page-auth";

.illustration-image {
  block-size: 550px;
  inline-size: 248px;
}

.bg-image {
  inset-block-end: 0;
}

/* Overlay lebih gelap & sedikit blur agar dialog “pop” */
.success-dialog .v-overlay__scrim {
  backdrop-filter: blur(4px);
  background: rgba(0, 0, 0, 70%) !important;
}

/* Card lebih tinggi + kontras + efek accent bar */
.success-card {
  position: relative;
  border: 1px solid rgba(var(--v-theme-primary), 0.35);
  background: rgb(var(--v-theme-surface));
  box-shadow:
    0 14px 42px rgba(0, 0, 0, 32%),
    0 0 0 4px rgba(var(--v-theme-primary), 0.08);
  min-block-size: 360px;                 /* 🔼 tinggi minimum */

  /* Accent bar di sisi atas */
  &::after {
    position: absolute;
    background:
      linear-gradient(
        90deg,
        rgb(var(--v-theme-primary)) 0%,
        rgba(var(--v-theme-primary), 0.5) 60%,
        transparent 100%
      );
    block-size: 4px;
    border-start-end-radius: inherit;
    border-start-start-radius: inherit;
    content: "";
    inset-block-start: 0;
    inset-inline: 0;
  }
}

/* Judul: hilangkan border lama, tambah spacing */
.success-title {
  padding-block: 12px 4px;
  padding-inline: 18px;
}

/* Badge icon dengan nuansa primary soft */
.success-badge {
  background: rgba(var(--v-theme-primary), 0.12);
  box-shadow: 0 8px 20px rgba(var(--v-theme-primary), 0.15);
  color: rgb(var(--v-theme-primary));
}

/* Dark theme tweak */
.v-theme--dark .success-card {
  border-color: rgba(var(--v-theme-primary), 0.45);
  background: rgb(var(--v-theme-surface));
  box-shadow:
    0 16px 44px rgba(0, 0, 0, 46%),
    0 0 0 4px rgba(var(--v-theme-primary), 0.1);
}
</style>
