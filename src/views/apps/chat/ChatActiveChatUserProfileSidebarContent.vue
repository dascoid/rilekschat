<script setup>
import { useChatStore } from '@/views/apps/chat/useChatStore'
import AddNewContactsDrawer from '@/views/apps/contacts/list/AddNewContactsDrawer.vue'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useChat } from './useChat'

const emit = defineEmits(['close','success-add','error-delete'])

const store = useChatStore()
const { resolveAvatarBadgeVariant } = useChat()
const accessToken = useCookie('accessToken');
const contactsData = ref({})
const isAddNewContactsDrawerVisible = ref(false)
const isCardDeleteDialogVisible = ref(false)
const isContactsInfoEditDialogVisible = ref(false)
const selectedUuid = ref(null);
const selectedData = ref(null);

const fetchContactsData = async () => {
  
  try {
    const response = await useApi(createUrl('/get_contacts_chat', {
      query: {
        whatsapp_users_id: store.activeChat.data_users.whatsapp_users_id
      },
    }), {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    });

    await nextTick(() => {
      contactsData.value = response.data.value || {};
    });

  } catch (error) {
    console.error('Error fetching user data:', error);
  }
};

onMounted(() => {
  fetchContactsData();
});

const addContacts  = (value) => {

  if (value) {
    isAddNewContactsDrawerVisible.value = false
    emit('close')
    emit('success-add')
  } else {
    console.warn('No updates detected.');
  }
};

const resolveStatusVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'subscribe')
    return 'success'
  
  return 'error'
}

const handleDeleteClick = (uuid) => {
  selectedUuid.value = uuid;
  isCardDeleteDialogVisible.value = true;
};

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
      emit('close')
      emit('success-add')
    }
    else
    {
      emit('error-delete')
    }

  } catch (err) {
    console.error(err)
  }
}

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

const editContacts  = (updatedContacts) => {
  if (updatedContacts && updatedContacts[1]) {
    fetchContactsData()
  } else {
    console.warn('No updates detected.');
  }
};
</script>

<template>
  <template v-if="Object.keys(contactsData).length > 0">
    <!-- Close Button -->
    <div
      class="pt-6 px-6"
      :class="$vuetify.locale.isRtl ? 'text-left' : 'text-right'"
    >
      <IconBtn @click="$emit('close')">
        <VIcon
          icon="tabler-x"
          class="text-medium-emphasis"
        />
      </IconBtn>
    </div>

    <!-- User Avatar + Name + Role -->
    <div class="text-center px-6">
      <VBadge
        location="bottom right"
        offset-x="7"
        offset-y="4"
        bordered
        :color="resolveAvatarBadgeVariant('online')"
        class="chat-user-profile-badge mb-5"
      >
        <VAvatar
          size="84"
          :variant="'tonal'"
          :color="resolveAvatarBadgeVariant('online')"
        >
          <span
            class="text-3xl"
          >{{ avatarText(contactsData.contacts_name) }}</span>
        </VAvatar>
      </VBadge>
      <h5 class="text-h5">
        {{ contactsData.contacts_name }}
      </h5>
    </div>

    <!-- User Data -->
    <PerfectScrollbar
      class="ps-chat-user-profile-sidebar-content text-medium-emphasis pb-6 px-6"
      :options="{ wheelPropagation: false }"
    >
      <!-- About -->
      <!-- <div class="my-6">
        <div class="text-sm text-disabled">
          ABOUT
        </div>
      </div> -->

      <!-- Personal Information -->
      <div class="mb-6">
        <div class="text-sm text-disabled mb-1">
          PERSONAL INFORMATION
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            class="me-2"
            icon="tabler-mail"
            size="22"
          />
          <div class="text-base">
            {{ contactsData.contacts_email }}
          </div>
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            class="me-2"
            icon="tabler-phone"
            size="22"
          />
          <div class="text-base">
            {{ contactsData.contacts_phone }}
          </div>
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            class="me-2"
            icon="tabler-user-plus"
            size="22"
          />
          <VChip
            :color="resolveStatusVariant(contactsData.subscribe)"
            size="small"
            label
            class="text-overline"
          >
            {{ contactsData.subscribe }}
          </VChip>
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            class="me-2"
            :icon="contactsData.contacts_gender == 'male' ? 'tabler-man' : 'tabler-woman'"
            size="22"
          />
          <div class="text-base text-overline">
            {{ contactsData.contacts_gender }}
          </div>
        </div>
      </div>

      <!-- Options -->
      <div>
        <!-- <div class="text-sm text-disabled mb-1">
          OPTIONS
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            class="me-2"
            icon="tabler-badge"
            size="22"
          />
          <div class="text-base">
            Add Tag
          </div>
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            class="me-2"
            icon="tabler-star"
            size="22"
          />
          <div class="text-base">
            Important Contact
          </div>
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            class="me-2"
            icon="tabler-photo"
            size="22"
          />
          <div class="text-base">
            Shared Media
          </div>
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            class="me-2"
            icon="tabler-trash"
            size="22"
          />
          <div class="text-base">
            Delete Contact
          </div>
        </div>
        <div class="d-flex align-center text-high-emphasis pa-2">
          <VIcon
            icon="tabler-ban"
            class="me-2"
            size="22"
          />
          <div class="text-base">
            Block Contact
          </div>
        </div> -->

        <VBtn
          block
          color="warning"
          append-icon="tabler-pencil"
          class="mt-6"
          @click="handleEditClick(contactsData.contacts_uuid)"
        >
          Edit Contact
        </VBtn>

        <VBtn
          block
          color="error"
          append-icon="tabler-trash"
          class="mt-6"
          @click="handleDeleteClick(contactsData.contacts_uuid)"
        >
          Delete Contact
        </VBtn>
      </div>
    </PerfectScrollbar>
  </template>
  <template v-else>
      <!-- Close Button -->
      <div
        class="pt-6 px-6"
        :class="$vuetify.locale.isRtl ? 'text-left' : 'text-right'"
      >
        <IconBtn @click="$emit('close')">
          <VIcon
            icon="tabler-x"
            class="text-medium-emphasis"
          />
        </IconBtn>
      </div>
      <div class="d-flex justify-center align-center" style="block-size: 100vh;">
        <VBtn
          color="primary"
          append-icon="tabler-plus"
          class="mt-6"
          @click="isAddNewContactsDrawerVisible = true"
        >
          Add Contacts
        </VBtn>
      </div>
  </template>

  <AddNewContactsDrawer
    v-if="isAddNewContactsDrawerVisible"
    v-model:is-drawer-open="isAddNewContactsDrawerVisible"
    v-model:phone-number="store.activeChat.data_users.whatsapp_users_id"
    v-model:wa-name="store.activeChat.data_users.whatsapp_users_username"
    @submit="addContacts"
  />

  <ConfirmDelete
    v-model:is-dialog-visible="isCardDeleteDialogVisible"
    confirmation-question="Are you sure to delete this data?"
    :uuid-data="selectedUuid"
    @confirm="deleteContacts(selectedUuid)"
  />

  <ContactsInfoEditDialog
    v-if="isContactsInfoEditDialogVisible"
    v-model:is-dialog-visible="isContactsInfoEditDialogVisible"
    :contacts-data="selectedData"
    :bot-id="store.activeChat.data_users.bot_id"
    @submit="editContacts"
  />
</template>
