<script setup>
import { themes } from '@/plugins/vuetify/theme'
import BroadcastTemplateDrawer from '@/views/apps/chat/BroadcastTemplateDrawer.vue'
import ChatActiveChatUserProfileSidebarContent from '@/views/apps/chat/ChatActiveChatUserProfileSidebarContent.vue'
import ChatLeftSidebarContent from '@/views/apps/chat/ChatLeftSidebarContent.vue'
import ChatLog from '@/views/apps/chat/ChatLog.vue'
import ChatUserProfileSidebarContent from '@/views/apps/chat/ChatUserProfileSidebarContent.vue'
import { useChat } from '@/views/apps/chat/useChat'
import { useChatStore } from '@/views/apps/chat/useChatStore'
import debounce from 'lodash/debounce'
import EmojiPicker from 'vue3-emoji-picker'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import {
  useDisplay,
  useTheme,
} from 'vuetify'

definePage({ meta: { layoutWrapperClasses: 'layout-content-height-fixed' } })

// composables
const vuetifyDisplays = useDisplay()
const store = useChatStore()
const { isLeftSidebarOpen } = useResponsiveLeftSidebar(vuetifyDisplays.smAndDown)
const { resolveAvatarBadgeVariant } = useChat()
const isBroadcastTemplateDrawerVisible = ref(false)
const isToastSuccessVisible = ref(false)
const isToastErrorVisible = ref(false)
const roomId = ref('')

// file input
const refInputEl = ref()
const selectedFile = ref(null)
const previewImageUrl = ref(null)
const { name } = useTheme()
const isEmojiPickerVisible = ref(false)
const isDeleteVisible = ref(false)

const selectedStatus = ref('')
const accessToken = useCookie('accessToken');

const toggleEmojiPicker = () => {
  isEmojiPickerVisible.value = !isEmojiPickerVisible.value
}

const onSelectEmoji = (emoji) => {
  msg.value += emoji.i
}

onMounted(() => {
  store.initPusher();
});

const showChatLog = ref(true)

watch(() => store.activeChat?.data_users?.id, async () => {
  showChatLog.value = false
  await nextTick()
  showChatLog.value = true

  await nextTick()
  scrollToBottomInChatLog()
})

// Perfect scrollbar
const chatLogPS = ref()
const showScrollButton = ref(false)

const scrollToBottomInChatLog = () => {
  const scrollEl = chatLogPS.value?.$el || chatLogPS.value
  
  if (!scrollEl) return

  scrollEl.style.scrollBehavior = 'auto'

  scrollEl.scrollTop = scrollEl.scrollHeight

  // requestAnimationFrame(() => scrollEl.style.scrollBehavior = '')

  if(store.countNewMessage > 0)
  {
    store.countNewMessage = 0;
  }

  showScrollButton.value = false;
}

watch(() => store.countNewMessage, newVal => {
  if (newVal > 0) {
    showScrollButton.value = true
  }
})

// Search query
const q = ref('')

watch(q, val => store.fetchChatsAndContacts(val), { immediate: true })

// Open Sidebar in smAndDown when "start conversation" is clicked
const startConversation = () => {
  if (vuetifyDisplays.mdAndUp.value)
    return
  isLeftSidebarOpen.value = true
}

// Chat message
const msg = ref('')

const sendMessage = async () => {
  if (!msg.value && !refInputEl.value?.files?.length)
    return

  if (store.isSending)
    return
  
  const tempMsg = msg.value
  const tempFile = refInputEl.value.files[0] || null
  // Reset message input
  msg.value = ''
  refInputEl.value.value = null
  selectedFile.value = null

  await store.sendMsg({
    message: tempMsg,
    file: tempFile,
  })

  // Scroll to bottom
  nextTick(() => {
    scrollToBottomInChatLog()
  })
}

const openChatOfContact = async dataRoom => {
  
  await store.getChat(dataRoom[0])

  // Reset message input
  msg.value = ''

  roomId.value = dataRoom[0].room_id;
  // Set unseenMsgs to 0
  const contact = store.chatsContacts.find(c => c.room_id === dataRoom[0].room_id)
  if (contact)
    contact.unread_messages = 0

    if (contact.whatsapp_status !== 'done') {
      selectedStatus.value = 'open'
    }
    else
    {
      selectedStatus.value = 'close'
    }

  // if smAndDown =>  Close Chat & Contacts left sidebar
  if (vuetifyDisplays.smAndDown.value)
    isLeftSidebarOpen.value = false

  // Scroll to bottom
  nextTick(() => {
    scrollToBottomInChatLog()
  })
}

const statusOption = [
  { value: 'open', title: 'Open' },
  { value: 'done', title: 'Close' }
]

const handleStatusChange = async () => {
  try {
      const res = await useApi('/change_status_room', {
        method: 'POST',
        body: JSON.stringify({
          room_id: roomId.value,
          status: selectedStatus.value,
        }),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${accessToken.value}`,
        },
      });

      if (res.statusCode.value === 200) {
        store.pageList = 1
        store.fetchChatsAndContacts({ q: '', loadMore: false, status: store.selectedTab });
        store.activeChat = null
      }
    } catch (err) {
      console.error(err);
      store.isError = true
      store.msgError = err?.message
    }
}

// User profile sidebar
const isUserProfileSidebarOpen = ref(false)

// Active chat user profile sidebar
const isActiveChatUserProfileSidebarOpen = ref(false)

const chatContentContainerBg = computed(() => {
  let color = 'transparent'
  if (themes)
    color = themes?.[name.value].colors?.background
  
  return color
})

const handleFileChange = (e) => {
  const file = e.target.files[0]

  if (!file) return

  // Reset preview
  selectedFile.value = null
  previewImageUrl.value = null

  // Batas maksimal 16MB
  // if (file.size > 16 * 1024 * 1024) {
  //   alert('File terlalu besar, maksimal 16MB')
  //   refInputEl.value.value = ''
  //   return
  // }

  // Cek type yang diizinkan
  const allowedTypes = [
    'image/jpeg', 'image/png', 'image/gif',
    'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain', 'video/mp4', 'video/3gpp',
    'audio/aac', 'audio/m4a', 'audio/amr', 'audio/mpeg', 'audio/ogg', 'audio/opus'
  ]

  if (!allowedTypes.includes(file.type)) {
    alert('Format file tidak didukung.')
    refInputEl.value.value = ''
    return
  }

  // Simpan file
  selectedFile.value = file

  // Kalau image → buat preview
  if (file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = (event) => {
      previewImageUrl.value = event.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removeSelectedFile = () => {
  selectedFile.value = null
  previewImageUrl.value = null
  refInputEl.value.value = ''
}

const handleBroadcastClick = () => {
  isBroadcastTemplateDrawerVisible.value = true;
};

const sendTemplateMessage  = (value) => {
  if (value) {
    isToastSuccessVisible.value = true
  } else {
    console.warn('No updates detected.');
  }
};

const handleEnter = (e) => {
  if (e.shiftKey) {
    return
  }

  e.preventDefault(); // Hindari submit form
  sendMessage();      // Kirim pesan
}

const handleChatScrollMessage = debounce(() => {
  const el = chatLogPS.value?.$el || chatLogPS.value
  if (!el) return

  if (el.scrollTop <= 50) {
    const userData = { ...store.activeChat.data_users }
    const prevHeight = el.scrollHeight

    userData.room_id = roomId.value
    userData.whatsapp_users = store.activeChat.data_users.whatsapp_users_id

    store.loadMoreMessages(userData).then(() => {
      nextTick(() => {
        const newHeight = el.scrollHeight
        el.scrollTop = newHeight - prevHeight
      })
    })
  }
}, 300)

const deleteChat = async () => {

  try {
      const res = await useApi('/delete_room', {
        method: 'POST',
        body: JSON.stringify({
          bot_id: store.activeChat.data_users.bot_id,
          room_id: store.activeChat.data_users.room_id,
          whatsapp_users_id: store.activeChat.data_users.whatsapp_users_id,
        }),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${accessToken.value}`,
        },
      });

      if (res.statusCode.value === 200) {
        store.pageList = 1
        store.fetchChatsAndContacts({ q: '', loadMore: false, status: store.selectedTab });
        store.activeChat = null
      }
    } catch (err) {
      console.error(err);
      store.isError = true
      store.msgError = err?.message
    }
};
</script>

<template>
  <VLayout
    class="chat-app-layout"
    style="z-index: 0;"
  >
    <!-- 👉 user profile sidebar -->
    <VNavigationDrawer
      v-model="isUserProfileSidebarOpen"
      data-allow-mismatch
      temporary
      touchless
      absolute
      class="user-profile-sidebar"
      location="start"
      width="370"
    >
      <ChatUserProfileSidebarContent @close="isUserProfileSidebarOpen = false" />
    </VNavigationDrawer>

    <!-- 👉 Active Chat sidebar -->
    <VNavigationDrawer
      v-if="isActiveChatUserProfileSidebarOpen"
      v-model="isActiveChatUserProfileSidebarOpen"
      data-allow-mismatch
      width="374"
      absolute
      temporary
      location="end"
      touchless
      class="active-chat-user-profile-sidebar"
    >
      <ChatActiveChatUserProfileSidebarContent 
        @close="isActiveChatUserProfileSidebarOpen = false" 
        @success-add="isToastSuccessVisible = true" 
        @error-delete="isToastErrorVisible = true" 
      />
    </VNavigationDrawer>

    <!-- 👉 Left sidebar   -->
    <VNavigationDrawer
      v-model="isLeftSidebarOpen"
      data-allow-mismatch
      absolute
      touchless
      location="start"
      width="370"
      :temporary="$vuetify.display.smAndDown"
      class="chat-list-sidebar"
      :permanent="$vuetify.display.mdAndUp"
    >
      <ChatLeftSidebarContent
        v-model:is-drawer-open="isLeftSidebarOpen"
        v-model:search="q"
        @open-chat-of-contact="openChatOfContact"
        @show-user-profile="isUserProfileSidebarOpen = true"
        @close="isLeftSidebarOpen = false"
      />
    </VNavigationDrawer>

    <!-- 👉 Chat content -->
    <VMain class="chat-content-container">
      <!-- 👉 Right content: Active Chat -->
      <div
        v-if="store.activeChat"
        class="d-flex flex-column h-100"
      >
        <!-- 👉 Active chat header -->
        <div class="active-chat-header d-flex align-center text-medium-emphasis bg-surface">
          <!-- Sidebar toggler -->
          <IconBtn
            class="d-md-none me-3"
            @click="isLeftSidebarOpen = true"
          >
            <VIcon icon="tabler-menu-2" />
          </IconBtn>

          <!-- avatar -->
          <div
            class="d-flex align-center cursor-pointer"
            @click="isActiveChatUserProfileSidebarOpen = true"
          >
            <VAvatar
              size="40"
              :variant="'tonal'"
              :color="resolveAvatarBadgeVariant('online')"
              class="cursor-pointer"
            >
              <span>{{ avatarText(store.activeChat.data_users.whatsapp_users_username) }}</span>
            </VAvatar>

            <div class="flex-grow-1 ms-4 overflow-hidden">
              <div class="text-h6 mb-0 font-weight-regular">
                {{ store.activeChat.data_users.whatsapp_users_username }}
              </div>
              <p class="mb-0 text-truncate text-body-2">
                {{ store.activeChat.data_users.whatsapp_users_id }}
              </p>
            </div>
          </div>

          <VSpacer />

          <!-- Header right content -->
          <div class="d-sm-flex align-center d-none text-medium-emphasis">
            <span class="mr-4">
              {{ store.activeChat.data_users.wa_bot_name }}
            </span>
            <span class="mr-2">
              <AppSelect
                v-model="selectedStatus"
                @update:model-value="handleStatusChange()"
                :items="statusOption"
                density="compact"
                :style="{
                  backgroundColor: selectedStatus === 'open' ? 'rgba(0, 128, 0, 0.5)' : 'rgba(255, 0, 0, 0.4)',
                  color: '#fff',
                  borderRadius: '8px',
                  minWidth: '90px',
                  textTransform: 'capitalize',
                  border: 'none',
                  boxShadow: 'none',
                  outline: 'none',
                }"
              />
            </span>
            <IconBtn
              @click="isDeleteVisible = true;"
              title="Delete Chat"
              style="color: red;"
              class="hover-opacity"
            >
              <VIcon icon="tabler-trash" />
            </IconBtn>
            <!-- <IconBtn>
              <VIcon icon="tabler-phone" />
            </IconBtn>
            <IconBtn>
              <VIcon icon="tabler-video" />
            </IconBtn>
            <IconBtn>
              <VIcon icon="tabler-search" />
            </IconBtn>
            <IconBtn>
              <VIcon icon="tabler-dots-vertical" />
            </IconBtn> -->
          </div>
        </div>

        <VDivider />

        <!-- Chat log -->
        <PerfectScrollbar
          v-if="showChatLog"
          ref="chatLogPS"
          tag="ul"
          :options="{ wheelPropagation: false }"
          class="flex-grow-1"
          @ps-scroll-y="handleChatScrollMessage"
        >
          <ChatLog />
        </PerfectScrollbar>

        <!-- Message form -->
        <VForm
          class="chat-log-message-form mb-5 mx-5"
          @submit.prevent="sendMessage"
        >

          <div class="d-flex align-start gap-2 chat-input-wrapper">
            
            <VTextarea
              :key="store.activeChat?.data_users.id"
              v-model="msg"
              density="default"
              class="chat-message-input flex-grow-1"
              placeholder="Type your message..."
              rows="1"
              max-rows="5"
              auto-grow
              autofocus
              @keydown.enter="handleEnter"
            />

            <div class="d-flex align-end gap-1" style="align-self: flex-end;">

              <IconBtn @click="toggleEmojiPicker">
                <VIcon icon="tabler-mood-smile" size="22" />
              </IconBtn>
              
              <IconBtn @click="refInputEl?.click()">
                <VIcon icon="tabler-paperclip" size="22" />
              </IconBtn>

              <IconBtn @click="handleBroadcastClick">
                <VIcon icon="tabler-plane-tilt" size="22" />
              </IconBtn>

              <div class="d-none d-md-block">
                <VBtn
                  append-icon="tabler-send"
                  @click="sendMessage"
                  :disabled="store.isSending"
                >
                  Send
                </VBtn>
              </div>

              <IconBtn
                class="d-block d-md-none"
                @click="sendMessage"
                :disabled="store.isSending"
              >
                <VIcon icon="tabler-send" />
              </IconBtn>
            </div>
          </div>

          <div v-if="isEmojiPickerVisible" class="overflow-y-auto mt-4" style="position: relative; max-block-size: 150px;">
            <EmojiPicker
              :native="true"
              :hide-search="false"
              :theme="'auto'"
              @select="onSelectEmoji"
            />
          </div>
          
          <input
            ref="refInputEl"
            type="file"
            name="file"
            hidden
            accept=".jpeg,.jpg,.png,.gif,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt"
            @change="handleFileChange"
          />
          
          <div v-if="selectedFile" class="mt-4 relative rounded-lg p-3" style=" position: relative; background-color: #2c2f48;">        

            <button
              @click="removeSelectedFile"
              class="text-white mt-4"
              style="transform: translate(50%, -50%);"
            >
              <VIcon icon="tabler-x" size="20" />
            </button>
            
            <!-- Preview Gambar -->
            <div v-if="previewImageUrl" class="mt-2">
              <img
                :src="previewImageUrl"
                alt="Preview"
                style="border-radius: 8px;max-inline-size: 120px;"
                class="ml-4 mb-4"
              />
            </div>

            <!-- Preview File Biasa -->
            <div v-else class="d-flex align-center gap-2 mt-2">
              <VIcon icon="tabler-file-description" size="40" class="mb-4 ml-2"/>
              <div>
                <p class="mb-0 text-sm font-weight-medium">{{ selectedFile.name }}</p>
                <p class="text-xs text-grey">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
              </div>
            </div>
          </div>
        </VForm>
      </div>

      <!-- 👉 Start conversation -->
      <div
        v-else
        class="d-flex h-100 align-center justify-center flex-column"
      >
        <VAvatar
          size="98"
          variant="tonal"
          color="primary"
          class="mb-4"
        >
          <VIcon
            size="50"
            class="rounded-0"
            icon="tabler-message-2"
          />
        </VAvatar>
        <VBtn
          v-if="$vuetify.display.smAndDown"
          rounded="pill"
          @click="startConversation"
        >
          Start Conversation
        </VBtn>

        <p
          v-else
          style="max-inline-size: 40ch; text-wrap: balance;"
          class="text-center text-disabled"
        >
          Start connecting with the people by selecting one of the contact on left
        </p>
      </div>
      
      <!-- Jika ada pesan baru, tampilkan tombol dengan badge -->
      <VBadge
        v-if="showScrollButton && store.countNewMessage > 0"
        :content="store.countNewMessage"
        bordered
        class="scroll-to-bottom-btn"
        color="error"
      >
        <VBtn
          variant="tonal"
          icon="tabler-arrow-down"
          color="success"
          @click="scrollToBottomInChatLog"
        />
      </VBadge>

      <!-- Jika tidak ada pesan baru, tampilkan tombol tanpa badge -->
      <VBtn
        v-else-if="showScrollButton"
        variant="tonal"
        icon="tabler-arrow-down"
        color="success"
        class="scroll-to-bottom-btn"
        @click="scrollToBottomInChatLog"
      />
      
    </VMain>
  </VLayout>

  <VSnackbar
    v-model="store.isError"
    location="bottom start"
    variant="flat"
    color="error"
    :close-on-content-click=true
  >
    {{ store.msgError }}
  </VSnackbar>

  <BroadcastTemplateDrawer
    v-if="isBroadcastTemplateDrawerVisible"
    v-model:is-drawer-open="isBroadcastTemplateDrawerVisible"
    :bot-id="store.activeChat.data_users.bot_id"
    :wa-users="store.activeChat.data_users.whatsapp_users_id"
    :room-Id="roomId"
    @submit="sendTemplateMessage"
  />

  <ConfirmDelete
    v-model:is-dialog-visible="isDeleteVisible"
    confirmation-question="Are you sure to delete this room?"
    @confirm="deleteChat"
  />
  
  <VSnackbar
    v-model="isToastSuccessVisible"
    location="bottom start"
    variant="flat"
    color="success"
    :close-on-content-click=true
  >
    Success
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
</template>

<style lang="scss">
@use "@styles/variables/vuetify";
@use "@core-scss/base/mixins";
@use "@layouts/styles/mixins" as layoutsMixins;

// Variables
$chat-app-header-height: 76px;

// Placeholders
%chat-header {
  display: flex;
  align-items: center;
  min-block-size: $chat-app-header-height;
  padding-inline: 1.5rem;
}

.chat-start-conversation-btn {
  cursor: default;
}

.chat-app-layout {
  border-radius: vuetify.$card-border-radius;

  @include mixins.elevation(vuetify.$card-elevation);

  $sel-chat-app-layout: &;

  @at-root {
    .skin--bordered {
      @include mixins.bordered-skin($sel-chat-app-layout);
    }
  }

  .active-chat-user-profile-sidebar,
  .user-profile-sidebar {
    .v-navigation-drawer__content {
      display: flex;
      flex-direction: column;
    }
  }

  .chat-list-header,
  .active-chat-header {
    @extend %chat-header;
  }

  .chat-list-sidebar {
    .v-navigation-drawer__content {
      display: flex;
      flex-direction: column;
    }
  }
}

.chat-content-container {
  /* stylelint-disable-next-line value-keyword-case */
  background-color: v-bind(chatContentContainerBg);

  // Adjust the padding so text field height stays 48px
  .chat-message-input {
    .v-field__input {
      font-size: 0.9375rem !important;
      line-height: 1.375rem !important;
      padding-block: 0.6rem 0.5rem;
    }

    .v-field__append-inner {
      align-items: center;
      padding-block-start: 0;
    }

    .v-field--appended {
      padding-inline-end: 8px;
    }
  }
}

.chat-user-profile-badge {
  .v-badge__badge {
    /* stylelint-disable liberty/use-logical-spec */
    min-width: 12px !important;
    height: 0.75rem;
    /* stylelint-enable liberty/use-logical-spec */
  }
}

.scroll-to-bottom-btn {
  position: absolute;
  z-index: 10;
  /* stylelint-disable-next-line liberty/use-logical-spec */
  right: 1.25rem;
  /* stylelint-disable-next-line liberty/use-logical-spec */
  bottom: 5.625rem;
}

.ps {
  scroll-behavior: auto !important;
}

.chat-message-input {
  /* stylelint-disable-next-line no-descending-specificity */
  .v-field__input {
    resize: none !important;
    scrollbar-width: thin; /* Firefox */
  }
}

/* Untuk Chrome, Edge */
.chat-message-input .v-field__input::-webkit-scrollbar {
  block-size: 4px;
  inline-size: 4px;
}

.chat-message-input .v-field__input::-webkit-scrollbar-thumb {
  border-radius: 3px;
  background-color: rgba(255, 255, 255, 20%);
}

.chat-message-input .v-field__input::-webkit-scrollbar-track {
  background: transparent;
}
</style>
