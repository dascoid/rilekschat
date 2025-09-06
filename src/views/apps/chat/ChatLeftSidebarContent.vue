<script setup>
import ChatContact from '@/views/apps/chat/ChatContact.vue'
import { useChatStore } from '@/views/apps/chat/useChatStore'
import debounce from 'lodash/debounce'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useChat } from './useChat'

const props = defineProps({
  search: {
    type: String,
    required: true,
  },
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'openChatOfContact',
  'showUserProfile',
  'close',
  'update:search',
])

const { resolveAvatarBadgeVariant } = useChat()
const search = ref('')
const store = useChatStore()

const scrollWrapper = ref(null)
const selectedTab = ref('all')

const tabOptions = [
  { value: 'all', label: 'All', icon: 'tabler-mail' },
  { value: 'read', label: 'Read', icon: 'tabler-mail-check' },
  { value: 'unread', label: 'Unread', icon: 'tabler-mail-opened' },
  { value: 'expired', label: 'Expired', icon: 'tabler-clock-off' },
  { value: 'close', label: 'Close', icon: 'tabler-square-rounded-x' },
]

const handleScroll = debounce(() => {
  const el = scrollWrapper.value?.$el || scrollWrapper.value
  if (!el) return

  const threshold = 100
  if (el.scrollTop + el.clientHeight >= el.scrollHeight - threshold) {
    store.fetchChatsAndContacts({
      q: search.value,
      status: selectedTab.value,
      loadMore: true
    })
    store.selectedTab = selectedTab.value
  }
}, 300)

// fetch data saat awal dan ketika tab/search berubah
watch([selectedTab, search], debounce(() => {
  store.pageList = 1
  store.hasMoreList = true
  store.fetchChatsAndContacts({
    q: search.value,
    status: selectedTab.value,
  })
  store.selectedTab = selectedTab.value
  store.activeChat = null

  nextTick(() => {
    const el = scrollWrapper.value?.$el || scrollWrapper.value
    if (el) el.scrollTop = 0
  })
}, 400))

onMounted(() => {
  store.pageList = 1
  store.hasMoreList = true
  store.selectedTab = selectedTab.value
  store.fetchChatsAndContacts({ q: search.value, status: selectedTab.value }).then(() => {
    setTimeout(() => {
      const el = scrollWrapper.value?.$el || scrollWrapper.value
      if (el) el.scrollTop = 1
    }, 200)
  })
  store.initPusher();
});

</script>

<template>
  <!-- 👉 Chat list header -->
  <div
    v-if="store.profileUser"
  >
    <AppTextField
      id="search"
      v-model="search"
      placeholder="Search..."
      prepend-inner-icon="tabler-search"
      class="ms-3 me-3 mt-3 mb-2 chat-list-search"
    />

    <IconBtn
      v-if="$vuetify.display.smAndDown"
      @click="$emit('close')"
    >
      <VIcon
        icon="tabler-x"
        class="text-medium-emphasis"
      />
    </IconBtn>
    <!-- 🧭 Tab Filters -->
    <div class="chat-tabs d-flex flex-wrap px-3 mb-2">
      <VTooltip
        v-for="tab in tabOptions"
        :key="tab.value"
        location="bottom"
      >
        <template #activator="{ props }">
          <VBtn
            v-bind="props"
            @click="selectedTab = tab.value"
            :variant="selectedTab === tab.value ? 'flat' : 'text'"
            color="primary"
            class="chat-tab"
            size="small"
          >
            <VIcon :icon="tab.icon" size="18" />
          </VBtn>
        </template>
        <span>{{ tab.label }}</span>
      </VTooltip>
    </div>
  </div>

  <VDivider class="mb-2" />

  <PerfectScrollbar
    ref="scrollWrapper"
    tag="ul"
    class="d-flex flex-column gap-y-1 chat-contacts-list px-3 py-2 list-none"
    :options="{ wheelPropagation: false }"
    @ps-scroll-y="handleScroll"
  >
    <li class="list-none">
      <h5 class="chat-contact-header text-primary text-h5">
        Chats
      </h5>
    </li>

    <ChatContact
      v-for="(contact, index) in store.chatsContacts"
      :key="`chat-${contact.whatsapp_users_id}-${contact.room_id}-${index}`"
      :user="contact"
      is-chat-contact
      @click="$emit('openChatOfContact', [contact])"
    />

    <span
      v-show="!store.chatsContacts.length && !store.isLoading"
      class="no-chat-items-text text-disabled"
    >No chats found</span>

    <li v-if="store.isLoading" class="text-center py-2">
      <span>Loading...</span>
    </li>
  </PerfectScrollbar>
</template>

<style lang="scss">
.chat-contacts-list {
  --chat-content-spacing-x: 16px;

  padding-block-end: 0.75rem;

  .chat-contact-header {
    margin-block: 0.5rem 0.25rem;
  }

  .chat-contact-header,
  .no-chat-items-text {
    margin-inline: var(--chat-content-spacing-x);
  }
}

.chat-list-search {
  .v-field--focused {
    box-shadow: none !important;
  }
}

.chat-header {
  background-color: #f9f9f9;
}

.chat-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  padding-block: 0.5rem;

  .chat-tab {
    display: flex;
    flex: 1 1 calc(20% - 0.4rem);
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 0.75rem; // kecilkan ukuran teks
    font-weight: 500;
    padding-block: 0.3rem;
    padding-inline: 0.5rem; // kecilkan padding
    text-transform: capitalize;
    transition: background-color 0.2s;

    i {
      font-size: 1rem; // kecilkan icon jika ada
      margin-inline-end: 0.25rem;
    }

    &:hover {
      background-color: rgba(var(--v-theme-primary), 0.06);
    }

    &.v-btn--variant-flat {
      background-color: rgba(var(--v-theme-primary), 1) !important;
      color: white;
    }
  }
}
</style>
