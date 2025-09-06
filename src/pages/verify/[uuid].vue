<script setup>
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const route = useRoute('verify-uuid')
const token = route.params.uuid;
const isToastErrorVisible = ref(false)

onMounted(() => {
  verify_email();
});

const verify_email = async () => {

  try {
    const res = await $api('/auth/verify/'+token, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    await nextTick(() => {
      if(res.message === 'Invalid token')
      {
        isToastErrorVisible.value = true;
      }
      else
      {
        window.location.href = '/login';
      }
    })

  } catch (err) {
    isToastErrorVisible.value = true;
    console.error(err)
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
          <h4 class="text-h4 mb-1">
            Verifying your email ✉️
          </h4>
          <p class="text-body-1 mb-0">
            <VProgressLinear
              indeterminate
              color="primary"
            />
          </p>
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
    Failed Verify User
  </VSnackbar>

</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
