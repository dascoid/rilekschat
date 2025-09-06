import { defineStore } from 'pinia';

export const useModalStore = defineStore('modal', {
  state: () => ({
    licenseExpiredVisible: false,
  }),
  actions: {
    showLicenseModal() {
      this.licenseExpiredVisible = true
    },
    hideLicenseModal() {
      this.licenseExpiredVisible = false
    }
  }
})
