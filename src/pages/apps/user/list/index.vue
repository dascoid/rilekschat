<script setup>
import AddNewUserDrawer from '@/views/apps/user/list/AddNewUserDrawer.vue'

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

// 👉 Store
const searchName = ref('')
const searchEmail = ref('')
const selectedRole = ref()
const selectedStatus = ref()
const dateRange = ref('')

const isCardDeleteDialogVisible = ref(false)
const isToastDeleteSuccessVisible = ref(false)
const isToastEmailSuccessVisible = ref(false)
const isToastEmailErrorVisible = ref(false)
const isToastEditSuccessVisible = ref(false)
const isToastAddSuccessVisible = ref(false)
const isToastErrorVisible = ref(false)
const selectedUuid = ref(null);
const selectedData = ref(null);
const isUserInfoEditDialogVisible = ref(false)

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

const handleEditClick = async (uuid) => {
  try {
    const response = await useApi(createUrl('/detail_users', {
      query: {
        users_uuid: uuid,
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

      isUserInfoEditDialogVisible.value = true;
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
    key: 'name',
  },
  {
    title: 'Email',
    key: 'email',
  },
  {
    title: 'Role',
    key: 'role',
  },
  {
    title: 'Status',
    key: 'activated',
  },
  {
    title: 'Created',
    key: 'created_at',
  },
]

const {
  data: usersData,
  execute: fetchUsers,
} = await useApi(createUrl('/list_users', {
    query: {
        name: searchName,
        email: searchEmail,
        activated: selectedStatus,
        role: selectedRole,
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
    }
);

const users = computed(() => usersData.value.data)
const totalUsers = computed(() => usersData.value.totalUsers)

// 👉 search filters
const roles = [
  {
    title: 'Admin',
    value: 'admin',
  },
  {
    title: 'Employee',
    value: 'employee',
  },
]

const status = [
  {
    title: 'Active',
    value: '1',
  },
  {
    title: 'Deactive',
    value: '0',
  },
]

const resolveUserRoleVariant = role => {
  const roleLowerCase = role.toLowerCase()
  if (roleLowerCase === 'admin')
    return {
      color: 'primary',
      icon: 'tabler-crown',
    }
  
  return {
    color: 'primary',
    icon: 'tabler-user',
  }
}

const resolveUserStatusVariant = stat => {
  if (stat === '1')
    return 'success'
  
  return 'danger'
}

const statusVariant = stat => {
  if (stat === '1')
    return 'Active'
  
  return 'Deactive'
}

const isAddNewUserDrawerVisible = ref(false)

const deleteUser = async uuid => {
  try {
    const res = await useApi('/delete_users', {
      method: 'POST',
      body: JSON.stringify({
        users_uuid: uuid
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
  fetchUsers()
}

const sendEmailActivation = async uuid => {
  try {
    const res = await useApi('/send_email_activation', {
      method: 'POST',
      body: JSON.stringify({
        users_uuid: uuid
      }),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      isToastEmailSuccessVisible.value = true;
    }
    else
    {
      isToastEmailErrorVisible.value = true;
    }

  } catch (err) {
    isToastEmailErrorVisible.value = true;
    console.error(err)
  }
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

const editUser  = (updatedUser) => {
  if (updatedUser && updatedUser[1]) {
    isToastEditSuccessVisible.value = true
    fetchUsers()
  } else {
    console.warn('No updates detected.');
  }
};

const addUser  = (value) => {
  if (value) {
    isToastAddSuccessVisible.value = true
    fetchUsers()
  } else {
    console.warn('No updates detected.');
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
          <!-- 👉 Select Role -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedRole"
              placeholder="Select Role"
              :items="roles"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select Status"
              :items="status"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

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
            <AppDateTimePicker
              v-model="dateRange"
              placeholder="Select Range Date Created"
              :config="{ mode: 'range' }"
              clearable
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
              { value: 100, title: '100' },
              { value: -1, title: 'All' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">

          <!-- 👉 Add user button -->
          <VBtn
            prepend-icon="tabler-plus"
            @click="isAddNewUserDrawerVisible = true"
          >
            Add New User
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="users"
        item-value="id"
        :items-length="totalUsers"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- User -->
        <template #item.user="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar
              size="34"
              :variant="!item.avatar ? 'tonal' : undefined"
              :color="!item.avatar ? resolveUserRoleVariant(item.role).color : undefined"
            >
              <VImg
                v-if="item.avatar"
                :src="item.avatar"
              />
              <span v-else>{{ avatarText(item.name) }}</span>
            </VAvatar>
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'apps-user-view-id', params: { id: item.users_uuid } }"
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

        <!-- 👉 Role -->
        <template #item.role="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VIcon
              :size="22"
              :icon="resolveUserRoleVariant(item.role).icon"
              :color="resolveUserRoleVariant(item.role).color"
            />

            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.role }}
            </div>
          </div>
        </template>

        <!-- Status -->
        <template #item.activated="{ item }">
          <VChip
            :color="resolveUserStatusVariant(item.activated)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ statusVariant(item.activated) }}
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
          <IconBtn @click="handleEditClick(item.users_uuid)">
            <VIcon icon="tabler-pencil" />
          </IconBtn>

          <IconBtn @click="handleDeleteClick(item.users_uuid)">
            <VIcon icon="tabler-trash" />
          </IconBtn>

          <IconBtn :to="{ name: 'apps-user-view-uuid', params: { uuid: item.users_uuid } }">
            <VIcon icon="tabler-eye" />
          </IconBtn>

          <IconBtn v-if="item.activated == '0'" @click="sendEmailActivation(item.users_uuid)">
            <VIcon icon="tabler-mail-forward" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalUsers"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New User -->
    <AddNewUserDrawer
      v-model:is-drawer-open="isAddNewUserDrawerVisible"
      @submit="addUser"
    />

    <UserInfoEditDialog
      v-if="isUserInfoEditDialogVisible"
      v-model:is-dialog-visible="isUserInfoEditDialogVisible"
      :user-data="selectedData"
      @submit="editUser"
    />
    
    <ConfirmDelete
      v-model:is-dialog-visible="isCardDeleteDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      :uuid-data="selectedUuid"
      @confirm="deleteUser(selectedUuid)"
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
      v-model="isToastEmailSuccessVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      Success Send Email Activation
    </VSnackbar>

    <VSnackbar
      v-model="isToastEmailErrorVisible"
      location="bottom start"
      variant="flat"
      color="error"
      :close-on-content-click=true
    >
      Failed Send Email Activation
    </VSnackbar>

  </section>
</template>
