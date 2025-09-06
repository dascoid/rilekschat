<script setup>
import ContactsBioPanel from '@/views/apps/contacts/view/ContactsBioPanel.vue';

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

onMounted(() => {
  fetchContactsData();
});

const route = useRoute('apps-contacts-view-uuid')
const accessToken = useCookie('accessToken');
const contactsData = ref({})

const fetchContactsData = async () => {
  try {
    const response = await useApi(createUrl('/detail_contacts', {
      query: {
        contacts_uuid: route.params.uuid,
      },
    }), {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    });

    await nextTick(() => {
      contactsData.value = response.data.value || {};
    });

  } catch (error) {
    console.error('Error fetching user data:', error);
  }
};

</script>

<template>
  <VRow v-if="contactsData">
    <VCol
      cols="12"
      md="5"
      lg="4"
    >
      <ContactsBioPanel :contacts-data="contactsData"  @reloadData="fetchContactsData"/>
    </VCol>
  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Contacts with UUID  {{ route.params.id }} not found!
    </VAlert>
  </div>
</template>
