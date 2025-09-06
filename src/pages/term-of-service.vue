<script setup>
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { themeConfig } from '@themeConfig';

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const items1 = [
  '- You must be at least 18 years old or have parental consent to use our services.',
  '- You agree to use our services lawfully and not for any illegal activities.',
  '- We reserve the right to suspend or terminate your account if you violate these terms.'
]
const items2 = [
  '- You may need to create an account to access certain features.',
  '- You are responsible for maintaining the security of your account.',
  '- You must provide accurate and up-to-date information.'
]
const items3 = [
  '- Some services may require payment. All payments are non-refundable unless stated otherwise.',
  '- We reserve the right to change pricing at any time.'
]
const items4 = [
  '- You agree not to engage in any harmful, abusive, or fraudulent activity.',
  '- You must not interfere with or disrupt our services or servers.'
]
const items5 = [
  '- All content on https://rambuchat.online/ is owned by PT RAMBU ADIYAKSA STUDIO or licensed to us.',
  '- You may not copy, distribute, or modify any content without permission.'
]
const items6 = [
  '- We are not responsible for any losses or damages resulting from the use of our services.',
  '- Our services are provided "as is" without warranties of any kind.'
]
const items7 = [
  '- We may suspend or terminate your access to our services at any time, with or without cause.'
]
const items8 = [
  '- We may update these Terms of Service from time to time. Changes will be effective upon posting on our website.'
]
const contactsUs = [
  {
    title: 'l Sitapen Gang Manggis no 18B, Jl. Kranji No.5, Desa/Kelurahan Purwanegara, Kec. Purwokerto Utara Purwokerto, Jawa Tengah 53116 Indonesia',
    props: {
      prependIcon: 'tabler-map-pins',
      rounded: 'xl',
    },
  },
  {
    title: 'admin@rambuadiyaksa.my',
    props: {
      prependIcon: 'tabler-mail',
      rounded: 'xl',
    },
  },
  {
    title: '+6281316598402',
    props: {
      prependIcon: 'tabler-phone',
      rounded: 'xl',
    },
  }
]
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

  <VContainer class="article-section mt-12">
    <VRow
      no-gutters
      class="auth-wrapper d-flex align-center justify-center pa-6"
    >
      <VCol
        cols="12"
        class="auth-card-v2 d-flex align-center justify-center pa-10"
        style="background-color: rgb(var(--v-theme-surface));"
      >
        <div>
          <h4 class="text-h4 mb-2">
            Terms of Service
          </h4>
          <div class="text-body-1">
            Effective Date: 19 March 2025
          </div>
          <VDivider class="my-6" />
          <div class="mb-6 text-body-1">
            Welcome to https://rambuchat.online/, operated by PT RAMBU ADIYAKSA STUDIO ("we," "our," or "the company"). By using our 
            services, you agree to the following Terms of Service. If you do not agree, please do not use our services.
          </div>
          <div class="mb-6 text-body-1">
            1. Use of Our Services
            <div class="ml-4 text-body-1">
              <VList :items="items1" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            2. Account Registration
            <div class="ml-4 text-body-1">
              <VList :items="items2" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            3. Payment and Refund Policy
            <div class="ml-4 text-body-1">
              <VList :items="items3" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            4. User Conduct
            <div class="ml-4 text-body-1">
              <VList :items="items4" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            5. Intellectual Property
            <div class="ml-4 text-body-1">
              <VList :items="items5" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            6. Limitation of Liability
            <div class="ml-4 text-body-1">
              <VList :items="items6" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            7. Termination of Services
            <div class="ml-4 text-body-1">
              <VList :items="items7" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            8. Changes to These Terms
            <div class="ml-4 text-body-1">
              <VList :items="items8" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            9. Contact Us
            <div class="mt-2 ml-2 text-body-1">
              If you have any questions about this Privacy Policy or wish to exercise your rights, please contact us at:
            </div>
            <div class="mt-2 ml-2 text-body-1">
              PT RAMBU ADIYAKSA STUDIO
            </div>
            <div class="mt-2 ml-4 text-body-1">
              <VList :items="contactsUs" />
            </div>
          </div>
        </div>
      </VCol>
    </VRow>
  </VContainer>

</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";

.illustration-image {
  block-size: 34.375rem;
  inline-size: 15.5rem;
}

.bg-image {
  inset-block-end: 0;
}
</style>
