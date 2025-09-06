<script setup>
import AddNewContactsDrawer from '@/views/apps/contacts/list/AddNewContactsDrawer.vue'
import BroadcastTemplateDrawer from '@/views/apps/contacts/list/BroadcastTemplateDrawer.vue'

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

// 👉 Store
const searchName = ref('')
const searchEmail = ref('')
const searchPhone = ref('')
const dateRange = ref('')

const isCardDeleteDialogVisible = ref(false)
const isCardDeleteSelectedDialogVisible = ref(false)
const isToastDeleteSuccessVisible = ref(false)
const isToastEditSuccessVisible = ref(false)
const isToastAddSuccessVisible = ref(false)
const isToastImportVisible = ref(false)
const isToastErrorVisible = ref(false)
const selectedUuid = ref(null);
const selectedData = ref(null);
const isContactsInfoEditDialogVisible = ref(false)
const receiversSelected = ref()
const subscribeSelected = ref()
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

const handleBroadcastClick = (uuid) => {
  selectedUuid.value = uuid;
  isBroadcastTemplateDrawerVisible.value = true;
};

const handleEditClick = async (uuid) => {
  try {
    const response = await useApi(createUrl('/detail_contacts', {
      query: {
        contacts_uuid: uuid,
      },
    }), {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    });

    await nextTick(() => {

      selectedData.value = response.data.value || {};

      isContactsInfoEditDialogVisible.value = true;
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
    key: 'contacts_name',
  },
  {
    title: 'Email',
    key: 'contacts_email',
  },
  {
    title: 'Phone',
    key: 'contacts_phone',
  },
  {
    title: 'Receiver',
    key: 'receivers_name',
  },
  {
    title: 'Subscribe',
    key: 'subscribe',
  },
  {
    title: 'Created',
    key: 'created_at',
  }
]

const {
  data: contactsData,
  execute: fetchContacts,
} = await useApi(createUrl('/list_contacts', {
    query: {
        contacts_name: searchName,
        contacts_email: searchEmail,
        contacts_phone: searchPhone,
        date_range:dateRange,
        receivers:receiversSelected,
        subscribe:subscribeSelected,
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
    }
);

const contacts = computed(() => contactsData.value.data)
const totalContacts = computed(() => contactsData.value.totalContacts)

const isAddNewContactsDrawerVisible = ref(false)
const isImportNewContactsDialogVisible = ref(false)

const deleteContacts = async uuid => {
  try {
    const res = await useApi('/delete_contacts', {
      method: 'POST',
      body: JSON.stringify({
        contacts_uuid: uuid
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

  // Refetch User
  fetchContacts()
}

const deleteSelected = async () => {
  try {
    const res = await useApi('/delete_selected_contacts', {
      method: 'POST',
      body: JSON.stringify({
        contacts_uuid: selectedRows.value
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

  // Refetch User
  fetchContacts()
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

const editContacts  = (updatedContacts) => {
  if (updatedContacts && updatedContacts[1]) {
    isToastEditSuccessVisible.value = true
    fetchContacts()
  } else {
    console.warn('No updates detected.');
  }
};

const addContacts  = (value) => {
  if (value) {
    isToastAddSuccessVisible.value = true
    fetchContacts()
  } else {
    console.warn('No updates detected.');
  }
};


const isToastSuccessVisible = ref(false)

const sendTemplateMessage  = (value) => {
  if (value) {
    isToastSuccessVisible.value = true
  } else {
    console.warn('No updates detected.');
  }
};

const receiversList = [];

const listReceiversApi = await useApi(createUrl('/list_receivers_contacts'), {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken.value}`,
    'Content-Type': 'application/json',
  },
});

if (listReceiversApi?.data?.value && Array.isArray(listReceiversApi.data.value)) {
  listReceiversApi.data.value.forEach(receivers => {
    if (receivers.receivers_name && receivers.id) {
      receiversList.push({
        title: receivers.receivers_name,
        value: receivers.id,
      });
    }
  });
}

const resolveStatusVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'subscribe')
    return 'success'
  
  return 'error'
}

const subscribeList = [
  {
    title: 'Subscribe',
    key: 'subscribe',
  },
  {
    title: 'Unsubscribe',
    key: 'unsubscribe',
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
            <AppTextField
              v-model="searchEmail"
              placeholder="Search Email"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppTextField
              v-model="searchPhone"
              placeholder="Search Phone"
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
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="receiversSelected"
              :items="receiversList"
              clearable
              clear-icon="tabler-x"
              placeholder="Select Receivers Name"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="subscribeSelected"
              :items="subscribeList"
              clearable
              clear-icon="tabler-x"
              placeholder="Select Subscribe"
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
            prepend-icon="tabler-plus"
            @click="isAddNewContactsDrawerVisible = true"
          >
            Add New Contacts
          </VBtn>

          <VBtn
            prepend-icon="tabler-plus"
            @click="isImportNewContactsDialogVisible = true"
          >
            Import Contacts
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="contacts"
        item-value="contacts_uuid"
        :items-length="totalContacts"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- User -->
        <template #item.contacts="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'apps-contacts-view-uuid', params: { id: item.contacts_uuid } }"
                  class="font-weight-medium text-link"
                >
                  {{ item.name }}
                </RouterLink>
              </h6>
            </div>
          </div>
        </template>
        
        <!-- Email -->
        <template #item.email="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ item.email }}
          </div>
        </template>

        <template #item.phone="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ item.phone }}
          </div>
        </template>

        <!-- CreatedAt -->
        <template #item.created_at="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ formatDate(item.created_at) }}
          </div>
        </template>

        <template #item.subscribe="{ item }">
          <div class="text-overline">
            <VChip
              :color="resolveStatusVariant(item.subscribe)"
              size="small"
              label
              class="text-overline"
            >
              {{ item.subscribe }}
            </VChip>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="handleEditClick(item.contacts_uuid)">
            <VIcon icon="tabler-pencil" />
          </IconBtn>

          <IconBtn @click="handleDeleteClick(item.contacts_uuid)">
            <VIcon icon="tabler-trash" />
          </IconBtn>

          <IconBtn :to="{ name: 'apps-contacts-view-uuid', params: { uuid: item.contacts_uuid } }">
            <VIcon icon="tabler-eye" />
          </IconBtn>

          <IconBtn @click="handleBroadcastClick(item.contacts_uuid)">
            <VIcon icon="tabler-plane-tilt" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalContacts"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New User -->
    <AddNewContactsDrawer
      v-if="isAddNewContactsDrawerVisible"
      v-model:is-drawer-open="isAddNewContactsDrawerVisible"
      @submit="addContacts"
    />

    <BroadcastTemplateDrawer
      v-if="isBroadcastTemplateDrawerVisible"
      v-model:is-drawer-open="isBroadcastTemplateDrawerVisible"
      :uuid-data="selectedUuid"
      @submit="sendTemplateMessage"
    />

    <ImportContactsDialog
      v-if="isImportNewContactsDialogVisible"
      v-model:is-dialog-visible="isImportNewContactsDialogVisible"
      @submit="isToastImportVisible = true"
    />

    <ContactsInfoEditDialog
      v-if="isContactsInfoEditDialogVisible"
      v-model:is-dialog-visible="isContactsInfoEditDialogVisible"
      :contacts-data="selectedData"
      @submit="editContacts"
    />
    
    <ConfirmDelete
      v-model:is-dialog-visible="isCardDeleteDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      :uuid-data="selectedUuid"
      @confirm="deleteContacts(selectedUuid)"
    />

    <ConfirmDelete
      v-model:is-dialog-visible="isCardDeleteSelectedDialogVisible"
      confirmation-question="Are you sure to delete this data?"
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
      v-model="isToastEditSuccessVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      Success Update Data
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
      v-model="isToastImportVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      Import Contacts In Queue 
    </VSnackbar>

      
    <VSnackbar
      v-model="isToastSuccessVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      Success send template message
    </VSnackbar>
    
  </section>
</template>
