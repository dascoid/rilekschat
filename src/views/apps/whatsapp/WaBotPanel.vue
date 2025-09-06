<script setup>

const props = defineProps({
  waBotData: {
    type: Object,
    required: true,
  },
})

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
  const hours = String(originalDate.getHours()).padStart(2, '0');
  const minutes = String(originalDate.getMinutes()).padStart(2, '0');

  return `${day} ${month} ${year} ${hours}:${minutes}`;
};

</script>

<template>
  <VRow>
    <!-- SECTION User Details -->
    <VCol cols="12">
      <VCard v-if="props.waBotData">
        <VCardText>
          <!-- 👉 Details -->
          <h5 class="text-h5">
            Details Whatsapp Bot
          </h5>

          <VDivider class="my-4" />

          <!-- 👉 User Details list -->
          <VList class="card-list mt-2">
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Whatsapp Bot Name :
                  <div class="d-inline-block text-body-1">
                    {{ props.waBotData.wa_bot_name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <span class="text-h6">
                  Whatsapp Bot Number :
                </span>
                <span class="text-body-1">
                  {{ props.waBotData.wa_bot_number }}
                </span>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Whatsapp Bot Status :
                  <div class="d-inline-block text-body-1 text-capitalize">
                    {{ props.waBotData.wa_bot_status_text }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Whatsapp Bot URL :
                  <div class="d-inline-block text-capitalize text-body-1">
                    {{ props.waBotData.wa_bot_base_url }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Date Create :
                  <div class="d-inline-block text-body-1">
                    {{ formatDate(props.waBotData.created_at) }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

          </VList>
        </VCardText>

      </VCard>
    </VCol>
    <!-- !SECTION -->

  </VRow>

</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.5rem;
}

.text-capitalize {
  text-transform: capitalize !important;
}
</style>
