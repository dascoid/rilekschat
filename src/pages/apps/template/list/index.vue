<script setup>
import TemplateDetailDialog from '@/components/dialogs/TemplateDetailDialog.vue';

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

// 👉 Store
const searchName = ref('')
const searchStatus = ref()
const searchCategory = ref()
const dateRange = ref('')
const botSelected = ref()

const isCardDeleteDialogVisible = ref(false)
const isCardDeleteSelectedDialogVisible = ref(false)
const isLoadingDialogVisible = ref(false)
const isToastDeleteSuccessVisible = ref(false)
const isToastAddSuccessVisible = ref(false)
const isToastFetchSuccessVisible = ref(false)
const isToastFetchFailedVisible = ref(false)
const isToastErrorVisible = ref(false)
const selectedUuid = ref(null);
const selectedData = ref(null);
const isTemplateAddDialogVisible = ref(false)
const isTemplateDetailDialogVisible = ref(false)
const isFetchTemplateDialogVisible = ref(false)

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

const handleShowDetail = async (uuid) => {
  try {
    const response = await useApi(createUrl('/detail_template', {
      query: {
        templates_uuid: uuid,
      },
    }), {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    });

    await nextTick(() => {

      selectedData.value = response.data.value || {};

      isTemplateDetailDialogVisible.value = true;
    });

  } catch (error) {
    console.error('Error fetching user data:', error);
  }
};

// Headers
const headers = [
  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
  {
    title: 'Name',
    key: 'whatsapp_templates_name',
  },
  {
    title: 'Status',
    key: 'whatsapp_templates_status',
  },
  {
    title: 'Category',
    key: 'whatsapp_templates_category',
  },
  {
    title: 'Bot Name',
    key: 'wa_bot_name',
  },
  {
    title: 'Created',
    key: 'created_at',
  },
]

const {
  data: templatesData,
  execute: fetchTemplates,
} = await useApi(createUrl('/list_template', {
    query: {
        whatsapp_templates_name: searchName,
        whatsapp_templates_status: searchStatus,
        whatsapp_templates_category: searchCategory,
        date_range:dateRange,
        bot_name: botSelected,
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

const templates = computed(() => templatesData.value.data)
const totalTemplates = computed(() => templatesData.value.totalTemplates)

const deleteTemplates = async uuid => {
  try {
    const res = await useApi('/delete_template', {
      method: 'POST',
      body: JSON.stringify({
        templates_uuid: uuid
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
  fetchTemplates()
}

const deleteSelected = async () => {
  try {
    const res = await useApi('/delete_selected_templates', {
      method: 'POST',
      body: JSON.stringify({
        templates_uuid: selectedRows.value
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
  fetchTemplates()
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

const addTemplates  = (value) => {
  fetchTemplates();
  isToastAddSuccessVisible.value = true;
};

const templateStatusVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'approved')
    return 'success'
  if (statLowerCase === 'rejected')
    return 'danger'

  return 'warning'
}

const botList = [];

const listBotApi = await useApi(createUrl('/list_bot_company'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (listBotApi?.data?.value && Array.isArray(listBotApi.data.value)) {
  listBotApi.data.value.forEach(bot => {
    if (bot.wa_bot_name && bot.id) {
      botList.push({
        title: bot.wa_bot_name,
        value: bot.id,
      });
    }
  });
}

const fetchDataTemplates = async () => {

  isLoadingDialogVisible.value = true

  try {
    const res = await useApi('/fetch_template', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      isToastFetchSuccessVisible.value = true;
    }
    else
    {
      isToastFetchFailedVisible.value = true;
    }

    isLoadingDialogVisible.value = false

  } catch (err) {
    isLoadingDialogVisible.value = false
    isToastFetchFailedVisible.value = true;
    console.error(err)
  }

  selectedRows.value = [];

  // Refetch
  fetchTemplates()
}

const statusList = [
  {
    title: 'APPROVED',
    value: 'APPROVED',
  },
  {
    title: 'PENDING',
    value: 'PENDING',
  },
  {
    title: 'REJECTED',
    value: 'REJECTED',
  }
]

const categoryList = [
  {
    title: 'UTILITY',
    value: 'UTILITY',
  },
  {
    title: 'MARKETING',
    value: 'MARKETING',
  },
  {
    title: 'AUTHENTICATION',
    value: 'AUTHENTICATION',
  }
]

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
              placeholder="Search Name"
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

          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="searchCategory"
              :items="categoryList"
              clearable
              clear-icon="tabler-x"
              placeholder="Select Category"
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

          <VCol
            cols="12"
            sm="8"
          >
            <AppSelect
              v-model="botSelected"
              :items="botList"
              clearable
              clear-icon="tabler-x"
              placeholder="Select Bot Name"
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
            prepend-icon="tabler-refresh"
            @click="isFetchTemplateDialogVisible = true"
            color="warning"
          >
            Fetch Template
          </VBtn>

          <VBtn
            prepend-icon="tabler-plus"
            @click="isTemplateAddDialogVisible = true"
          >
            Add New Template
          </VBtn>

        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="templates"
        item-value="whatsapp_templates_uuid"
        :items-length="totalTemplates"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- User -->
        <template #item.whatsapp_templates_name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <div
                  @click="handleShowDetail(item.whatsapp_templates_uuid)"
                  class="font-weight-medium text-link"
                >
                  {{ item.whatsapp_templates_name }}
              </div>
              </h6>
            </div>
          </div>
        </template>
        
        <!-- Email -->
        <template #item.whatsapp_templates_status="{ item }">
          <VChip
            :color="templateStatusVariant(item.whatsapp_templates_status)"
            size="small"
            label
            class="text-overline"
          >
            {{ item.whatsapp_templates_status }}
          </VChip>
        </template>

        <template #item.whatsapp_templates_category="{ item }">
          <div class="text-overline">
            {{ item.whatsapp_templates_category }}
          </div>
        </template>

        <template #item.wa_bot_name="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ item.wa_bot_name }}
          </div>
        </template>

        <!-- CreatedAt -->
        <template #item.created_at="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ formatDate(item.created_at) }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="handleDeleteClick(item.whatsapp_templates_uuid)">
            <VIcon icon="tabler-trash" />
          </IconBtn>

          <IconBtn  @click="handleShowDetail(item.whatsapp_templates_uuid)">
            <VIcon icon="tabler-eye" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalTemplates"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <TemplateAddDialog
      v-if="isTemplateAddDialogVisible"
      v-model:is-dialog-visible="isTemplateAddDialogVisible"
      @submit="addTemplates"
    />

    <TemplateDetailDialog
      v-if="isTemplateDetailDialogVisible"
      v-model:is-dialog-visible="isTemplateDetailDialogVisible"
      :template-data="selectedData"
    />
    
    <ConfirmDelete
      v-model:is-dialog-visible="isCardDeleteDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      :uuid-data="selectedUuid"
      @confirm="deleteTemplates(selectedUuid)"
    />

    <ConfirmDelete
      v-model:is-dialog-visible="isCardDeleteSelectedDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      @confirm="deleteSelected"
    />

    <ConfirmFetchTemplates
      v-model:is-dialog-visible="isFetchTemplateDialogVisible"
      confirmation-question="Are you sure to fetch template from whatsapp bussiness ?"
      @confirm="fetchDataTemplates()"
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
      v-model="isToastFetchSuccessVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      Success Fetch Data
    </VSnackbar>

    <VSnackbar
      v-model="isToastFetchFailedVisible"
      location="bottom start"
      variant="flat"
      color="error"
      :close-on-content-click=true
    >
      Failed Fetch Data
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

  </section>

  <VDialog
    v-model="isLoadingDialogVisible"
    width="300"
  >
    <VCard
      color="info"
      width="300"
    >
      <VCardText class="pt-3">
        Please stand by
        <VProgressLinear
          indeterminate
          bg-color="rgba(var(--v-theme-surface), 0.1)"
          :height="8"
          class="mb-0 mt-4"
        />
      </VCardText>
    </VCard>
  </VDialog>

</template>
