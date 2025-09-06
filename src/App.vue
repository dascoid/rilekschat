<script setup>
import { useTheme } from 'vuetify'
import ScrollToTop from '@core/components/ScrollToTop.vue'
import initCore from '@core/initCore'
import {
  initConfigStore,
  useConfigStore,
} from '@core/stores/config'
import { hexToRgb } from '@core/utils/colorConverter'
import { useModalStore } from '@core/stores/modal'
import { storeToRefs } from 'pinia'
import LicenseExpiredModal from './components/ModalLicenseExpired.vue'

const { global } = useTheme()

// ℹ️ Sync current theme with initial loader theme
initCore()
initConfigStore()

const configStore = useConfigStore()

const modalStore = useModalStore()
const { licenseExpiredVisible } = storeToRefs(modalStore)
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <RouterView />
      <ScrollToTop />
    </VApp>
    <Teleport to="body">
      <LicenseExpiredModal v-if="licenseExpiredVisible" />
    </Teleport>
  </VLocaleProvider>
</template>
