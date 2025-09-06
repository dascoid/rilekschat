<script setup>
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { themeConfig } from '@themeConfig';

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const items11 = [
  '- Personal Data: Name, email address, phone number.',
  '- Transaction Data: If you make payments or purchases through our services.'
]
const items12 = [
  '- Technical Data: IP address, device type, browser, operating system.',
  '- Usage Data: Pages visited, access times, and interactions with the site.',
  '- Cookies & Tracking Technologies: Used for analytics and personalization.'
]
const items2 = [
  '- Provide, manage, and improve our services.',
  '- Process transactions and payments.',
  '- Respond to inquiries and customer support requests.',
  '- Send service-related notifications and promotions.',
  '- Prevent fraud and other harmful activities.'
]
const items3 = [
  '- Third-party service providers, such as hosting providers and payment processors.',
  '- Law enforcement, if required by law.'
]
const items51 = [
  '- Right to Access: Request a copy of your personal data.',
  '- Right to Rectification: Correct inaccurate data.',
  '- Right to Erasure: Request deletion of your data.',
  '- Right to Object: Refuse processing of your data for certain purposes.',
  '- Right to Data Portability: Request transfer of data to another provider.'
]
const items52 = [
  '- Right to Know: Request details about data we collect and how we use it.',
  '- Right to Delete: Request deletion of your personal data.',
  '- Right to Opt-Out of Data Sales: We do not sell user data to third parties.'
]
const items6 = [
  '- Essential Cookies: Necessary for the website to function properly.',
  '- Analytics Cookies: To understand how users interact with the site.',
  '- Advertising Cookies: To display relevant ads.'
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
            Privacy Policy
          </h4>
          <div class="text-body-1">
            Effective Date: 19 March 2025
          </div>
          <VDivider class="my-6" />
          <div class="mb-6 text-body-1">
            PT RAMBU ADIYAKSA STUDIO ("we," "our," or "the company") is committed to protecting your privacy. 
            This Privacy Policy explains how we collect, use, and safeguard your personal information when you use 
            our services on https://rambuchat.online/.
          </div>
          <div class="mb-6 text-body-1">
            By using our services, you agree to the collection and use of information as described in this policy.
          </div>
          <div class="mb-6 text-body-1">
            1. Information We Collect
            <div class="mt-2 ml-2 text-body-1">
              We may collect different types of information, including:
            </div>
            <div class="mt-2 ml-4 text-body-1">
              1.1 Information You Provide Directly
            </div>
            <div class="ml-5 text-body-1">
              <VList :items="items11" />
            </div>
            <div class="mt-2 ml-4 text-body-1">
              1.2 Information Collected Automatically
            </div>
            <div class="ml-5 text-body-1">
              <VList :items="items12" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            2. How We Use Your Information
            <div class="mt-2 ml-2 text-body-1">
              We use the collected information to:
            </div>
            <div class="mt-2 ml-4 text-body-1">
              <VList :items="items2" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            3. Sharing Your Information
            <div class="mt-2 ml-2 text-body-1">
              We do not sell, rent, or trade your personal information. However, we may share your data with:
            </div>
            <div class="mt-2 ml-4 text-body-1">
              <VList :items="items3" />
            </div>
          </div>
          <div class="mb-6 text-body-1">
            4. Data Security
            <div class="mt-2 ml-2 text-body-1">
              We implement technical and organizational security measures to protect your personal data from unauthorized access, alteration, or deletion.
            </div>
          </div>
          <div class="mb-6 text-body-1">
            5. Your Data Rights
            <div class="mt-2 ml-2 text-body-1">
              You have the following rights under the GDPR (European Union) and CCPA (California, USA):
            </div>
            <div class="mt-2 ml-4 text-body-1">
              5.1 GDPR Rights (For European Union Users)
            </div>
            <div class="ml-5 text-body-1">
              <VList :items="items51" />
            </div>
            <div class="mt-2 ml-4 text-body-1">
              If you wish to exercise these rights, please contact us at ramdani.work86@gmail.com.
            </div>
            <div class="mt-2 ml-4 text-body-1">
              5.2 CCPA Rights (For California, USA Users)
            </div>
            <div class="ml-5 text-body-1">
              <VList :items="items52" />
            </div>
            <div class="mt-2 ml-4 text-body-1">
              To make a request under CCPA, contact us at ramdani.work86@gmail.com.
            </div>
          </div>
          <div class="mb-6 text-body-1">
            6. Cookies & Tracking Technologies
            <div class="mt-2 ml-2 text-body-1">
              We use cookies to enhance user experience, including:
            </div>
            <div class="mt-2 ml-4 text-body-1">
              <VList :items="items6" />
            </div>
            <div class="mt-2 ml-2 text-body-1">
              You can manage cookie preferences through your browser settings.
            </div>
          </div>
          <div class="mb-6 text-body-1">
            7. Changes to This Privacy Policy
            <div class="mt-2 ml-2 text-body-1">
              We may update this policy from time to time. Changes will be posted on our website and take effect immediately upon publication.
            </div>
          </div>
          <div class="mb-6 text-body-1">
            8. Contact Us
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
