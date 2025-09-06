<script setup>
import BroadcastsAddDialog from '@/components/dialogs/BroadcastAddDialog.vue';
import BroadcastsDetailDialog from '@/components/dialogs/BroadcastDetailDialog.vue';
import BroadcastsPermissionDialog from '@/components/dialogs/BroadcastsPermissionDialog.vue';
import BroadcastTemplateDrawer from '@/views/apps/broadcast/list/BroadcastTemplateDrawer.vue';

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

// 👉 Store
const searchName = ref('')
const searchReceivers = ref([])
const searchTemplates = ref([])
const dateRange = ref('')

const isCardDeleteDialogVisible = ref(false)
const isCardDeleteSelectedDialogVisible = ref(false)
const isToastTemplateNotFound = ref(false)
const isToastDeleteSuccessVisible = ref(false)
const isToastAddSuccessVisible = ref(false)
const isToastErrorVisible = ref(false)
const selectedUuid = ref(null);
const selectedData = ref(null);
const selectedTemplate = ref(null);
const isBroadcastsAddDialogVisible = ref(false)
const isBroadcastsDetailDialogVisible = ref(false)
const isBroadcastsPermissionDialogVisible = ref(false)
const isBroadcastTemplateDrawerVisible = ref(false)

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])

const accessToken = useCookie('accessToken');

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const handleDeleteClick = (uuid) => {
  selectedUuid.value = uuid;
  isCardDeleteDialogVisible.value = true;
};

// Headers
const headers = [
  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
  {
    title: 'Broadcast Title',
    key: 'broadcasts_name',
  },
  {
    title: 'Receiver Name',
    key: 'receivers_name',
  },
  {
    title: 'Template Name',
    key: 'whatsapp_templates_name',
  },
  {
    title: 'Status',
    key: 'status',
  },
  {
    title: 'Scheduled',
    key: 'scheduled_at',
  },
  {
    title: 'Created',
    key: 'created_at',
  }
]

const {
  data: broadcastsData,
  execute: fetchBroadcasts,
} = await useApi(createUrl('/list_broadcasts', {
    query: {
        broadcasts_name: searchName,
        receivers_id: searchReceivers,
        templates_id: searchTemplates,
        date_range:dateRange,
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

const broadcasts = computed(() => broadcastsData.value.data.data)
const totalBroadcasts = computed(() => broadcastsData.value.data.totalBroadcasts)
const limitBroadcast = computed(() => broadcastsData.value.limit)

const deleteBroadcasts = async uuid => {
  try {
    const res = await useApi('/delete_broadcasts', {
      method: 'POST',
      body: JSON.stringify({
        broadcasts_uuid: uuid
      }),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      isToastDeleteSuccessVisible.value = true;
    }
    else
    {
      isToastErrorVisible.value = true;
    }

  } catch (err) {
    isToastErrorVisible.value = true;
    console.error(err)
  }

  // Delete from selectedRows
  const index = selectedRows.value.findIndex(row => row === uuid)
  if (index !== -1)
    selectedRows.value.splice(index, 1)

  // Refetch
  fetchBroadcasts()
}

const deleteSelected = async () => {
  try {
    const res = await useApi('/delete_selected_broadcasts', {
      method: 'POST',
      body: JSON.stringify({
        broadcasts_uuid: selectedRows.value
      }),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      isToastDeleteSuccessVisible.value = true;
    }
    else
    {
      isToastErrorVisible.value = true;
    }

  } catch (err) {
    isToastErrorVisible.value = true;
    console.error(err)
  }

  selectedRows.value = [];

  // Refetch
  fetchBroadcasts()
}

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

const addBroadcasts  = (value) => {
  fetchBroadcasts();
  isToastAddSuccessVisible.value = true;
};

const resolveStatusVariant = stat => {
  if (stat === 'done')
    return 'success'
  if (stat === 'processing')
    return 'primary'
  
  return 'warning'
}

const receiversList = [];

const receiversListApi = await useApi(createUrl('/list_receivers_data'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (receiversListApi?.data?.value && Array.isArray(receiversListApi.data.value)) {
  receiversListApi.data.value.forEach(data => {
    if (data.receivers_name && data.id) {
      receiversList.push({
        title: data.receivers_name,
        value: data.id,
      });
    }
  });
}

const templatesList = [];

const templatesListApi = await useApi(createUrl('/list_templates_data'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (templatesListApi?.data?.value && Array.isArray(templatesListApi.data.value)) {
  templatesListApi.data.value.forEach(data => {
    if (data.whatsapp_templates_name && data.id) {
      templatesList.push({
        title: data.whatsapp_templates_name,
        value: data.id,
      });
    }
  });
}

const checkBroadcastPermission = () => {
  if(templatesList.length > 0)
  {
    isBroadcastsAddDialogVisible.value = true;
  }
  else
  {
    isBroadcastsPermissionDialogVisible.value = true;
  }
}

const handleBroadcastClick = (data) => {
  
  const matchedTemplate = templatesListApi.data.value.find(template => template.id === data.templates_id)

  if (matchedTemplate) {
    selectedTemplate.value = matchedTemplate 
    isBroadcastTemplateDrawerVisible.value = true;
  } else {
    selectedTemplate.value = null
    isToastTemplateNotFound.value = true
  }
};
</script>

<template>
  <section>

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
              placeholder="Search Title"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="searchReceivers"
              :items="receiversList"
              clearable
              clear-icon="tabler-x"
              placeholder="Select Receivers"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="searchTemplates"
              :items="templatesList"
              clearable
              clear-icon="tabler-x"
              placeholder="Select Templates"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppDateTimePicker
              v-model="dateRange"
              placeholder="Select Range Date Created"
              :config="{ mode: 'range' }"
              clearable
              clear-icon="tabler-x"
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

        <div class="text-body-2 text-medium-emphasis mt-2 text-success">
          LIMIT BROADCAST : {{ limitBroadcast.limit - limitBroadcast.sent }} / {{ limitBroadcast.limit }}
        </div>
        
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">

          <VBtn
            v-if="selectedRows.length > 0"
            prepend-icon="tabler-trash"
            color="error"
            @click="isCardDeleteSelectedDialogVisible = true;"
          >
            Delete Selected
          </VBtn>

          <VBtn
            prepend-icon="tabler-plus"
            @click="checkBroadcastPermission()"
          >
            Add New Broadcasts
          </VBtn>

        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="broadcasts"
        item-value="broadcasts_uuid"
        :items-length="totalBroadcasts"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- User -->
        <template #item.broadcasts_name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'apps-broadcast-view-uuid', params: { uuid: item.broadcasts_uuid } }"
                  class="font-weight-medium text-link"
                >
                  {{ item.broadcasts_name }}
                </RouterLink>
              </h6>
            </div>
          </div>
        </template>

        <template #item.status="{ item }">
          <VChip
            :color="resolveStatusVariant(item.status)"
            size="small"
            class="text-overline"
          >
            {{ item.status }}
          </VChip>
        </template>

        <!-- CreatedAt -->
        <template #item.created_at="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ formatDate(item.created_at) }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="handleDeleteClick(item.broadcasts_uuid)">
            <VIcon icon="tabler-trash" />
          </IconBtn>

          <IconBtn :to="{ name: 'apps-broadcast-view-uuid', params: { uuid: item.broadcasts_uuid } }">
            <VIcon icon="tabler-eye" />
          </IconBtn>
          
          <IconBtn @click="handleBroadcastClick(item)">
            <VIcon icon="tabler-template" />
          </IconBtn>

        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalBroadcasts"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <BroadcastsAddDialog
      v-if="isBroadcastsAddDialogVisible"
      v-model:is-dialog-visible="isBroadcastsAddDialogVisible"
      @submit="addBroadcasts"
    />

    <BroadcastsDetailDialog
      v-if="isBroadcastsDetailDialogVisible"
      v-model:is-dialog-visible="isBroadcastsDetailDialogVisible"
      :template-data="selectedData"
    />
    
    <BroadcastsPermissionDialog
      v-if="isBroadcastsPermissionDialogVisible"
      v-model:is-dialog-visible="isBroadcastsPermissionDialogVisible"
      confirmation-question="You dont have broadcast permision or not set payment method"
    />

    <BroadcastTemplateDrawer
      v-if="isBroadcastTemplateDrawerVisible"
      v-model:is-drawer-open="isBroadcastTemplateDrawerVisible"
      :template-data="selectedTemplate"
    />

    <ConfirmDelete
      v-model:is-dialog-visible="isCardDeleteSelectedDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      @confirm="deleteSelected"
    />

    <ConfirmDelete
      v-model:is-dialog-visible="isCardDeleteDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      :uuid-data="selectedUuid"
      @confirm="deleteBroadcasts(selectedUuid)"
    />

    <VSnackbar
      v-model="isToastDeleteSuccessVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      Success Delete Data
    </VSnackbar>

    <VSnackbar
      v-model="isToastAddSuccessVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      Success Add Data
    </VSnackbar>

    <VSnackbar
      v-model="isToastErrorVisible"
      location="bottom start"
      variant="flat"
      color="error"
      :close-on-content-click=true
    >
      Failed Delete Data
    </VSnackbar>

    <VSnackbar
      v-model="isToastTemplateNotFound"
      location="bottom start"
      variant="flat"
      color="error"
      :close-on-content-click=true
    >
      Template Not Found
    </VSnackbar>

  </section>
</template>
