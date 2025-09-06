<script setup>
const props = defineProps({
  contactsData: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'reloadData',
])

const isContactsInfoEditDialogVisible = ref(false)

const editContacts  = (updatedContacts) => {
  if (updatedContacts && updatedContacts[1]) {
    isToastSuccessVisible.value = true
    emit('reloadData')
  } else {
    console.warn('No updates detected.');
  }
};
const isToastSuccessVisible = ref(false)

// Month names in English
const monthNames = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

// Method to format date
const formatDate = (date) => {
  const originalDate = new Date(date);
  
  const day = String(originalDate.getDate()).padStart(2, '0');
  const month = monthNames[originalDate.getMonth()];
  const year = originalDate.getFullYear();

  return `${day} ${month} ${year}`;
};
</script>

<template>
  <VRow>
    <!-- SECTION User Details -->
    <VCol cols="12">
      <VCard v-if="props.contactsData">
        <VCardText class="text-center pt-12">
          <!-- 👉 Avatar -->
          <VAvatar
            rounded
            :size="100"
            :color="!props.contactsData.avatar ? 'primary' : undefined"
            :variant="!props.contactsData.avatar ? 'tonal' : undefined"
          >
            <VImg
              v-if="props.contactsData.avatar"
              :src="props.contactsData.avatar"
            />
            <span
              v-else
              class="text-5xl font-weight-medium"
            >
              {{ avatarText(props.contactsData.contacts_name) }}
            </span>
          </VAvatar>

          <!-- 👉 User name -->
          <h5 class="text-h5 mt-4">
            {{ props.contactsData.contacts_name }}
          </h5>

        </VCardText>

        <VCardText>

          <!-- 👉 Details -->
          <h5 class="text-h5">
            Details
          </h5>

          <VDivider class="my-4" />

          <!-- 👉 User Details list -->
          <VList class="card-list mt-2">
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Name:
                  <div class="d-inline-block text-body-1">
                    {{ props.contactsData.contacts_name ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <span class="text-h6">
                  Email:
                </span>
                <span class="text-body-1">
                  {{ props.contactsData.contacts_email ?? '-' }}
                </span>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Gender:
                  <div class="d-inline-block text-capitalize text-body-1">
                    {{ props.contactsData.contacts_gender ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Contact:
                  <div class="d-inline-block text-body-1">
                    {{ props.contactsData.contacts_phone ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Address:
                  <div class="d-inline-block text-body-1">
                    {{ props.contactsData.contacts_address ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Receivers:
                  <div class="d-inline-block text-body-1">
                    {{ props.contactsData.receivers_name ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>
          </VList>
        </VCardText>

        <!-- 👉 Edit and Suspend button -->
        <VCardText class="d-flex justify-center gap-x-4">
          <VBtn
            variant="elevated"
            @click="isContactsInfoEditDialogVisible = true"
          >
            Edit
          </VBtn>

        </VCardText>
      </VCard>
    </VCol>
    <!-- !SECTION -->

  </VRow>

  <!-- 👉 Edit user info dialog -->
  <ContactsInfoEditDialog
    v-if="isContactsInfoEditDialogVisible"
    v-model:is-dialog-visible="isContactsInfoEditDialogVisible"
    :contacts-data="props.contactsData"
    @submit="editContacts"
  />

  <VSnackbar
    v-model="isToastSuccessVisible"
    location="bottom start"
    variant="flat"
    color="success"
    :close-on-content-click=true
  >
    Success Delete Data
  </VSnackbar>

</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.5rem;
}

.text-capitalize {
  text-transform: capitalize !important;
}
</style>
