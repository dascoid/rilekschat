<script setup>
import WaBotPanel from '@/views/apps/whatsapp/WaBotPanel.vue';

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

const route = useRoute('apps-whatsapp-uuid');
const accessToken = useCookie('accessToken');

const {
  data: waBotData
} = await useApi(createUrl('/detail_wa_bot', {
    query: {
      wa_bot_uuid: route.params.uuid,
      },
    }),
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    }
);

</script>

<template>
  <VRow v-if="waBotData">
    <VCol
      cols="12"
      md="5"
      lg="12"
    >
      <WaBotPanel :wa-bot-data="waBotData" />
    </VCol>

  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Whatsapp Bot with UUID  {{ route.params.uuid }} not found!
    </VAlert>
  </div>
</template>
