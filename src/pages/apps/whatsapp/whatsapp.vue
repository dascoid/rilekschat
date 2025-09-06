<script setup>
import ManualWhatsApp from '@/components/dialogs/ManualWhatsApp.vue';

definePage({ 
  meta: {
    requiresAuth: true,
    role: 'admin'
  } 
})

// 👉 Store
const searchBotName = ref('')
const searchBotNumber = ref('')
const selectedStatus = ref()

const isCardDeleteDialogVisible = ref(false)
const isManualWhatsappDialogVisible = ref(false)
const isToastSuccessVisible = ref(false)
const isToastErrorVisible = ref(false)
const isLoadingDialogVisible = ref(false)
const msgError = ref('');
const msgSuccess = ref('');
const selectedUuid = ref(null);

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])

const isCardFetchDialogVisible = ref(false);

const client_id = import.meta.env.VITE_CLIENT_ID;
const client_secret = import.meta.env.VITE_CLIENT_SECRET;
const config_id = import.meta.env.VITE_CONFIG_ID;
const version_id = import.meta.env.VITE_META_VERSION;
const token_waba = import.meta.env.VITE_META_TOKEN;

const accessToken = useCookie('accessToken');
const menu = ref(false)

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const handleDeleteClick = (uuid) => {
  selectedUuid.value = uuid;
  isCardDeleteDialogVisible.value = true;
};

const handleFetchClick = () => {
  isCardFetchDialogVisible.value = true;
};

// Headers
const headers = [
  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
  {
    title: 'Whatsapp Bot Name',
    key: 'wa_bot_name',
  },
  {
    title: 'Whatsapp Bot Number',
    key: 'wa_bot_number',
  },
  {
    title: 'Whatsapp Bussiness Name',
    key: 'whatsapp_bussiness_name',
  },
  {
    title: 'Send Message',
    key: 'can_send_message',
  },
  {
    title: 'Broadcast',
    key: 'broadcast_check',
  },
  {
    title: 'Quality Rating',
    key: 'quality_rating',
    sortable: false,
  },
]

const {
  data: waBotData,
  execute: fetchWaBot,
} = await useApi(createUrl('/list_wa_bot', {
    query: {
        botName: searchBotName,
        botNumber: searchBotNumber,
        status: selectedStatus,
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

const waBot = computed(() => waBotData.value?.data?.data ?? [])
const totalWaBotCount = computed(() => waBotData.value?.data?.totalWaBot ?? 0)
const isDisable = computed(() => totalWaBotCount.value >= 1)

const status = [
  {
    title: 'Pending',
    value: 'pending',
  },
  {
    title: 'Verified',
    value: 'verified',
  },
]

const WaBotSendMessage = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'blocked')
    return 'error'
  if (statLowerCase === 'limited')
    return 'warning'
  if (statLowerCase === 'available')
    return 'success'

  return 'secondary'
}

const broadcastCheck = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'available')
    return 'success'

  return 'error'
}

const WaBotQualityRatingVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'green')
    return 'success'
  if (statLowerCase === 'yellow')
    return 'warning'
  if (statLowerCase === 'red')
    return 'error'

  return 'secondary'
}

const WaBotQualityRatingText = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'green')
    return 'HIGH'
  if (statLowerCase === 'yellow')
    return 'MEDIUM'
  if (statLowerCase === 'red')
    return 'LOW'

  return 'UNKNOWN'
}

const deleteWaBot = async uuid => {
  try {
    const res = await useApi('/delete_wa_bot', {
      method: 'POST',
      body: JSON.stringify({
        wa_bot_uuid: uuid
      }),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      isToastSuccessVisible.value = true;
      msgSuccess.value = 'Success to delete bot';
    }
    else
    {
      isToastErrorVisible.value = true;
      msgError.value = 'Failed to delete bot';
    }

  } catch (err) {
    isToastErrorVisible.value = true;
    msgError.value = 'Failed to delete bot';
    console.error(err)
  }

  // Delete from selectedRows
  const index = selectedRows.value.findIndex(row => row === uuid)
  if (index !== -1)
    selectedRows.value.splice(index, 1)

  // Refetch Wa Bot
  fetchWaBot()
}


const fetchManualWaBot = async () => {

  isLoadingDialogVisible.value = true

  try {
    const res = await useApi('/fetch_wa_bot', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      isToastSuccessVisible.value = true;
      msgSuccess.value = 'Success to fetch bot';
    }
    else
    {
      isToastErrorVisible.value = true;
      msgError.value = 'Failed to fetch bot';
    }

    isLoadingDialogVisible.value = false

  } catch (err) {
    isToastErrorVisible.value = true;
    isLoadingDialogVisible.value = false
    msgError.value = 'Failed to fetch bot';
    console.error(err)
  }

  // Refetch Wa Bot
  fetchWaBot()
};

const fbLoginCallback = async (response) => {
  if (response.authResponse) {
    const code = response.authResponse.code;

    if (response.status === 'connected' && code != null && code !== "") {
      try {

        FB.api('/oauth/access_token', 'POST', {
          "client_id": client_id,
          "client_secret": client_secret,
          "code": code,
          "grant_type": "authorization_code",
          "redirect_uri": ""
        }, async (res) => {
          if (!res || res.error) {
            console.error('Error:', res.error);
            return;
          }
          
          if (res.access_token) {
            try {
              const apiResponse = await useApi('/set_webhook', {
                method: 'POST',
                body: JSON.stringify({
                  access_token: res.access_token,
                  token_waba: token_waba,
                }),
                headers: {
                  'Content-Type': 'application/json',
                  'Authorization': `Bearer ${accessToken.value}`,
                },
              });

              fetchWaBot();

            } catch (apiError) {
              console.error('API Error:', apiError);
            }
          } else {
            console.error('Access token not found in response');
          }
        
        });

      } catch (err) {
        console.error('Error:', err);
      }

    } else {
      console.error('response error : ', response);
    }
  } else {
    console.error('response : ', response);
  }
};

const launchWhatsAppSignup = () => {
  
  if(client_id == '' || client_secret == '')
  {
    console.error('Client id or client secret can not be blank');
  }
  else
  {
    FB.init({
        appId: client_id,
        cookie: true,
        xfbml: true,
        version: version_id
    });
    
    FB.login((response) => {
      fbLoginCallback(response).catch(err => console.error('Callback error:', err));
    }, {
      config_id: config_id,
      response_type: 'code',
      override_default_response_type: true,
      extras: {
        setup: {},
        featureType: '',
        sessionInfoVersion: '3',
      }
    });
  }
}

const selectMethod = method => {
  if (method === 'embed') launchWhatsAppSignup()
  else if (method === 'manual') isManualWhatsappDialogVisible.value = true
}

</script>

<template>
  <section>

    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 font-weight-medium">
          Whatsapp Integration
        </h4>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <!-- <VBtn prepend-icon="tabler-plus" @click="launchWhatsAppSignup" :disabled="isDisable">Whatsapp</VBtn>/ -->
         <VMenu v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y>
          <template #activator="{ props }">
            <VBtn
              v-bind="props"
              prepend-icon="tabler-plus"
              :disabled="isDisable"
            >
              Whatsapp
            </VBtn>
          </template>

          <VList>
            <VListItem @click="selectMethod('embed')">
              <VListItemTitle>Embed</VListItemTitle>
            </VListItem>
            <VListItem @click="selectMethod('manual')">
              <VListItemTitle>Manual</VListItemTitle>
            </VListItem>
          </VList>
        </VMenu>
      </div>
    </div>
    
    <VCard class="mb-6">

      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- 👉 Select Status -->
          <!-- <VCol
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
          </VCol> -->

          <VCol
            cols="12"
            sm="4"
          >
            <AppTextField
              v-model="searchBotName"
              placeholder="Search Whatsapp Bot Name"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <AppTextField
              v-model="searchBotNumber"
              placeholder="Search Whatsapp Bot Number"
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
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">

          <!-- 👉 Export button -->
          <VBtn
            variant="tonal"
            color="warning"
            prepend-icon="tabler-refresh"
            @click="handleFetchClick()"
          >
            Fetch
          </VBtn>

        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="waBot"
        item-value="wa_bot_uuid"
        :items-length="totalWaBotCount"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- wa_bot_name -->
        <template #item.wa_bot_name="{ item }">
          <div class="d-flex align-center gap-x-4">
                {{ item.wa_bot_name }}
          </div>
        </template>

        <template #item.whatsapp_bussiness_name="{ item }">
          <div class="d-flex align-center gap-x-4" v-if="item.whatsapp_bussiness_name == 'unknown'">
                UNKNOWN
          </div>
          <div class="d-flex align-center gap-x-4" v-else>
                {{ item.whatsapp_bussiness_name }}
          </div>
        </template>

        <template #item.wa_bot_number="{ item }">
          <div class="d-flex align-center gap-x-4">
                {{ item.wa_bot_number }}
          </div>
        </template>

        <template #item.can_send_message="{ item }">
          <VChip
            :color="WaBotSendMessage(item.can_send_message)"
            size="small"
            label
            class="text-overline"
          >
            {{ item.can_send_message }}
          </VChip>
        </template>

        <template #item.broadcast_check="{ item }">
          <VChip
            :color="broadcastCheck(item.can_send_message)"
            size="small"
            label
            class="text-overline"
          >
            {{ item.can_send_message.toLowerCase() == 'available' ? 'YES': 'NO' }}
          </VChip>
        </template>

        <template #item.quality_rating="{ item }">
          <VChip
            :color="WaBotQualityRatingVariant(item.quality_rating)"
            size="small"
            label
            class="text-overline"
          >
            {{ WaBotQualityRatingText(item.quality_rating) }}
          </VChip>
        </template>
      
        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="handleDeleteClick(item.wa_bot_uuid)">
            <VIcon icon="tabler-trash" />
          </IconBtn>

          <IconBtn :to="{ name: 'apps-whatsapp-uuid', params: { uuid: item.wa_bot_uuid } }">
            <VIcon icon="tabler-eye" />
          </IconBtn>
          
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalWaBotCount"
          />
        </template>

      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    
    <ConfirmDelete
      v-model:is-dialog-visible="isCardDeleteDialogVisible"
      confirmation-question="Are you sure to delete this data?"
      :uuid-data="selectedUuid"
      @confirm="deleteWaBot(selectedUuid)"
    />

    <ManualWhatsApp
      v-model:is-dialog-visible="isManualWhatsappDialogVisible"
      @submit="fetchWaBot()"
    />

    <VSnackbar
      v-model="isToastSuccessVisible"
      location="bottom start"
      variant="flat"
      color="success"
      :close-on-content-click=true
    >
      {{ msgSuccess }}
    </VSnackbar>

    <VSnackbar
      v-model="isToastErrorVisible"
      location="bottom start"
      variant="flat"
      color="error"
      :close-on-content-click=true
    >
      {{ msgError }}
    </VSnackbar>
    
    <ConfirmFetchWaBot
      v-model:is-dialog-visible="isCardFetchDialogVisible"
      confirmation-question="Are you sure to feth whatsapp bot ?"
      @confirm="fetchManualWaBot()"
    />

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
