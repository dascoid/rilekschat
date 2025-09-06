<script setup>
import ReceiversAddDialog from '@/components/dialogs/ReceiversAddDialog.vue';
import ReceiversDetailDialog from '@/components/dialogs/ReceiversDetailDialog.vue';

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

// 👉 Store
const searchName = ref('')
const dateRange = ref('')

const isCardDeleteDialogVisible = ref(false)
const isCardDeleteSelectedDialogVisible = ref(false)
const isToastDeleteSuccessVisible = ref(false)
const isToastAddSuccessVisible = ref(false)
const isToastErrorVisible = ref(false)
const selectedUuid = ref(null);
const selectedData = ref(null);
const isReceiversAddDialogVisible = ref(false)
const isReceiversDetailDialogVisible = ref(false)

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

const handleDeleteSelectedClick = () => {
  isCardDeleteSelectedDialogVisible.value = true;
};

const handleShowDetail = async (uuid) => {
  try {
    const response = await useApi(createUrl('/detail_receivers', {
      query: {
        receivers_uuid: uuid,
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

      // isReceiversDetailDialogVisible.value = true;
    });

  } catch (error) {
    console.error('Error fetching data:', error);
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
    key: 'receivers_name',
  },
  {
    title: 'Total Contacts',
    key: 'totalContacts',
  },
  {
    title: 'Created',
    key: 'created_at',
  },
]

const {
  data: receiversData,
  execute: fetchReceivers,
} = await useApi(createUrl('/list_receivers', {
    query: {
        receivers_name: searchName,
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

const receivers = computed(() => receiversData.value.data)
const totalReceivers = computed(() => receiversData.value.totalReceivers)

const deleteReceivers = async ([checkbox, uuid]) => {

  try {
    const res = await useApi('/delete_receivers', {
      method: 'POST',
      body: JSON.stringify({
        receivers_uuid: uuid,
        delete_contacts: checkbox,
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
  fetchReceivers()
}

const deleteSelected = async ([checkbox, uuid]) => {
  
  try {
    const res = await useApi('/delete_selected_receivers', {
      method: 'POST',
      body: JSON.stringify({
        receivers_uuid: selectedRows.value,
        delete_contacts: checkbox
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
  fetchReceivers()
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

const addReceivers  = (value) => {
  fetchReceivers();
  isToastAddSuccessVisible.value = true;
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
              placeholder="Search Name"
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
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">

          <VBtn
            v-if="selectedRows.length > 0"
            prepend-icon="tabler-trash"
            color="error"
            @click="handleDeleteSelectedClick"
          >
            Delete Selected
          </VBtn>

          <VBtn
            prepend-icon="tabler-plus"
            @click="isReceiversAddDialogVisible = true"
          >
            Add New Receivers
          </VBtn>

        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="receivers"
        item-value="receivers_uuid"
        :items-length="totalReceivers"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- User -->
        <template #item.receivers_name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <div
                  @click="handleShowDetail(item.receivers_uuid)"
                  class="font-weight-medium text-link"
                >
                  {{ item.receivers_name }}
              </div>
              </h6>
            </div>
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
          <IconBtn @click="handleDeleteClick(item.receivers_uuid)">
            <VIcon icon="tabler-trash" />
          </IconBtn>

          <!-- <IconBtn  @click="handleShowDetail(item.receivers_uuid)">
            <VIcon icon="tabler-eye" />
          </IconBtn> -->
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalReceivers"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <ReceiversAddDialog
      v-if="isReceiversAddDialogVisible"
      v-model:is-dialog-visible="isReceiversAddDialogVisible"
      @submit="addReceivers"
    />

    <ReceiversDetailDialog
      v-if="isReceiversDetailDialogVisible"
      v-model:is-dialog-visible="isReceiversDetailDialogVisible"
      :template-data="selectedData"
    />
    
    <ConfirmDeleteReceiver
      v-model:is-dialog-visible="isCardDeleteDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      :uuid-data="selectedUuid"
      @confirm="deleteReceivers"
    />

    <ConfirmDeleteReceiver
      v-model:is-dialog-visible="isCardDeleteSelectedDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      :uuid-data="selectedUuid"
      @confirm="deleteSelected"
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

  </section>
</template>
