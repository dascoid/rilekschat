<script setup>

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

const route = useRoute('apps-broadcast-view-uuid')
const accessToken = useCookie('accessToken');

const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const searchStatus = ref([])
const searchMessage = ref('')
const searchName = ref('')
const searchPhone = ref('')

const {
  data: broadcastData,
  execute: fetchBroadcastData,
} = await useApi(createUrl('/detail_broadcasts', {
      query: {
        broadcasts_uuid: route.params.uuid,
        message: searchMessage,
        status: searchStatus,
        name: searchName,
        phone: searchPhone,
        itemsPerPage,
        page,
        sortBy,
        orderBy,
      },
    }),
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    },
);

onMounted(() => {
  fetchBroadcastData();
});

const broadcastDataFix = computed(() => broadcastData.value.data)
const totalbroadcastData = computed(() => broadcastData.value.totalBroadcasts)

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Contacts Name',
    key: 'contacts_name',
  },
  {
    title: 'Phone Number',
    key: 'contacts_phone',
  },
  {
    title: 'Status Broadcast',
    key: 'status',
    sortable: false,
  },
  {
    title: 'Info Message',
    key: 'info_message',
    sortable: false,
  }
]

const statusList = [
  {
    title: 'Sent',
    value: 'sent',
  },
  {
    title: 'Deliver',
    value: 'deliver',
  },
  {
    title: 'Read',
    value: 'read',
  },
  {
    title: 'Failed',
    value: 'failed',
  }
]

const widgetData = computed(() => {
  const statusCount = broadcastData.value.statusCount

  if (!statusCount) return []

  return [
    {
      title: 'Sent',
      value: statusCount.sent,
      icon: 'tabler-users',
      iconColor: 'primary',
    },
    {
      title: 'Delivered',
      value: statusCount.delivered,
      icon: 'tabler-user-down',
      iconColor: 'info',
    },
    {
      title: 'Read',
      value: statusCount.read,
      icon: 'tabler-user-check',
      iconColor: 'success',
    },
    {
      title: 'Failed',
      value: statusCount.failed,
      icon: 'tabler-user-x',
      iconColor: 'error',
    },
  ]
})

const resolveStatusVariant = stat => {
  if (stat === 'sent')
    return 'primary'
  if (stat === 'delivered')
    return 'info'
  if (stat === 'read')
    return 'success'
  if (stat === 'failed')
    return 'error'
  
  return 'warning'
}

</script>

<template>
  <section v-if="broadcastDataFix">
    <div class="d-flex mb-6">
      <VRow>
        <template
          v-for="(data, id) in widgetData"
          :key="id"
        >
          <VCol
            cols="12"
            md="3"
            sm="6"
          >
            <VCard>
              <VCardText>
                <div class="d-flex justify-space-between">
                  <div class="d-flex flex-column gap-y-1">
                    <div class="text-body-1 text-high-emphasis">
                      {{ data.title }}
                    </div>
                    <div class="d-flex gap-x-2 align-center">
                      <h4 class="text-h4">
                        {{ data.value }}
                      </h4>
                    </div>
                  </div>
                  <VAvatar
                    :color="data.iconColor"
                    variant="tonal"
                    rounded
                    size="42"
                  >
                    <VIcon
                      :icon="data.icon"
                      size="26"
                    />
                  </VAvatar>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </template>
      </VRow>
    </div>

    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>

          <VCol
            cols="12"
            sm="4"
          >
            <AppTextField
              v-model="searchName"
              placeholder="Search Contacts Name"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppTextField
              v-model="searchPhone"
              placeholder="Search Phone Number"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppTextField
              v-model="searchMessage"
              placeholder="Search Message"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="searchStatus"
              :items="statusList"
              clearable
              clear-icon="tabler-x"
              placeholder="Select Status"
            />
          </VCol>

        </VRow>
      </VCardText>

      <VDivider />
      
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' }
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="broadcastDataFix"
        item-value="broadcasts_uuid"
        :items-length="totalbroadcastData"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <template #item.contacts_name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <div class="font-weight-medium text-link">
                  {{ item.contacts_name }}
              </div>
              </h6>
            </div>
          </div>
        </template>

        <template #item.status="{ item }">
          <VChip
            :color="resolveStatusVariant(item.status)"
            size="small"
            class="text-capitalize"
          >
            {{ item.status }}
          </VChip>
        </template>

        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalbroadcastData"
          />
        </template>
      </VDataTableServer>
    </VCard>

  </section>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Contacts with UUID  {{ route.params.id }} not found!
    </VAlert>
  </div>
</template>
