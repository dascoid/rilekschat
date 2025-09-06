<script setup>

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

// 👉 Store
const dateRange = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const isToastErrorVisible = ref(false)
const accessToken = useCookie('accessToken');

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Name',
    key: 'name',
  },
  {
    title: 'File',
    key: 'file_name',
  },
  {
    title: 'Status',
    key: 'status',
  },
  {
    title: 'Module',
    key: 'module',
  },
  {
    title: 'Created',
    key: 'created_at',
  },
  {
    title: 'Error Message',
    key: 'error_message',
  }
]

const {
  data: importData,
  execute: fetchContacts,
} = await useApi(createUrl('/list_import', {
    query: {
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

const imports = computed(() => importData.value.data)
const totalImport = computed(() => importData.value.totalImport)

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

const resolveImportStatusVariant = stat => {
  if (stat === 'completed')
    return 'success'
  if (stat === 'processing')
    return 'primary'
  if (stat === 'failed')
    return 'error'
  
  return 'warning'
}

const downloadfile = async (file) => {
  try {
    const res = await useApi('/download_file', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
      body: JSON.stringify({ filename: file[0],name: file[1] }),
    })

    if (!res.ok) {
      const err = await res.json()
      throw new Error(err.message || 'Download failed')
    }

    const blob = await res.blob()

    const contentDisposition = res.headers.get('Content-Disposition')
    const filename = contentDisposition?.split('filename=')[1]?.replace(/"/g, '') || 'file.xlsx'

    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

  } catch (err) {
    console.error('Download error:', err)
  }
}
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

      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="imports"
        item-value="id"
        :items-length="totalImport"
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
                <div class="text-body-1 text-high-emphasis text-capitalize">
                  {{ item.name }}
                </div>
              </h6>
            </div>
          </div>
        </template>
        
        <!-- Email -->
        <template #item.file_name="{ item }">
          <div class="text-body-1 text-high-emphasis">
            <VChip
              color="success"
              variant="outlined"
              @click="downloadfile([item.file_name,item.name])"
            >
              Download File
            </VChip>
          </div>
        </template>

        <template #item.module="{ item }">
          <span class="text-overline">
            {{ item.module }}
          </span>
        </template>

        <template #item.status="{ item }">
          <VChip
            :color="resolveImportStatusVariant(item.status)"
            size="small"
            label
            class="text-capitalize"
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

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalImport"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>
