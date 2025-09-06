import { createFetch } from '@vueuse/core';
import { destr } from 'destr';
import { useModalStore } from '../@core/stores/modal';
import { store } from '../plugins/2.pinia';

export const useApi = createFetch({
  baseUrl: import.meta.env.VITE_API_BASE_URL || '/api',
  fetchOptions: {
    headers: {
      Accept: 'application/json',
    },
  },
  options: {
    refetch: true,
    async beforeFetch({ options }) {
      const accessToken = useCookie('accessToken').value
      if (accessToken) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${accessToken}`,
        }
      }
      
      return { options }
    },
    afterFetch(ctx) {
      const { data, response } = ctx

      // Parse data if it's JSON
      let parsedData = null
      try {
        parsedData = destr(data)
      }
      catch (error) {
        console.error(error)
      }
      
      return { data: parsedData, response }
    },
    onFetchError(ctx) {
      console.error("Fetch Error:", ctx);

      const { data, response } = ctx

      // Parse data if it's JSON
      let parsedData = null
      try {
        parsedData = destr(data)
      }
      catch (error) {
        console.error(error)
      }

      if (response && response.status === 401) {
        console.warn("Unauthorized! Redirecting to login...");

        // Clear access token
        useCookie('accessToken').value = null;

        window.location.href = '/login';
        
      }

      // Tangani lisensi expired
      if (response && response.status === 403 && parsedData?.license_expired) {
        console.warn("Lisensi expired dari server")
        
        const modalStore = useModalStore(store)
        modalStore.showLicenseModal()
      }
      
      return ctx;
    }
  },
})
