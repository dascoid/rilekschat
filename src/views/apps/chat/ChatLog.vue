<script setup>
import { useChatStore } from '@/views/apps/chat/useChatStore'

const store = useChatStore()
const imageLoaded = ref({})
const previewDialog = ref(false)
const previewImage = ref(null)
const isLoadingDialogVisible = ref(false)

const openImagePreview = (url) => {
  previewImage.value = url
  previewDialog.value = true
}

const contact = computed(() => ({
  id: store.activeChat?.data_users.id,
  avatar: '',
}))

const resolveFeedbackIcon = feedback => {
  if (feedback == 'sent')
    return {
      icon: 'tabler-check',
      color: undefined,
    }
  else if (feedback == 'delivered')
    return {
      icon: 'tabler-checks',
      color: undefined,
    }
  else if (feedback == 'read')
    return {
      icon: 'tabler-checks',
      color: 'success',
    }
  else if (feedback == 'failed')
    return {
      icon: 'tabler-exclamation-circle',
      color: 'error',
    }
  else
    // return {
    //   icon: 'tabler-clock-hour-3',
    //   color: undefined,
    // }
    return {
      icon: 'tabler-check',
      color: undefined,
    }
}

const msgGroups = computed(() => {
  let messages = []
  const _msgGroups = []

  isLoadingDialogVisible.value = true;

  if (store.activeChat.data_message) {
    messages = store.activeChat.data_message

    if (messages.length === 0) return []
    
    let currentGroup = {
      senderId: store.activeChat.data_message[0].whatsapp_message_status_user,
      messages: [],
      statusChat: '',
    };
    
    messages.forEach((msg, index) => {
      if (msg.whatsapp_message_status_user == currentGroup.senderId) {
        // Add message to the current group
        currentGroup.messages.push({
          message: msg.whatsapp_message_text,
          time: msg.created_at,
          statusChat: msg.whatsapp_message_status_chat,
          senderId: msg.whatsapp_message_status_user,
          type_file: msg.type_file,
          file_url: msg.file_url,
          file_name: msg.file_name,
        });
      } else {
        // Push the completed group to the array
        _msgGroups.push(currentGroup);

        // Start a new group
        currentGroup = {
          senderId: msg.whatsapp_message_status_user,
          messages: [{ 
                      message: msg.whatsapp_message_text,
                      time: msg.created_at,
                      statusChat: msg.whatsapp_message_status_chat, 
                      senderId: msg.whatsapp_message_status_user,
                      type_file: msg.type_file,
                      file_url: msg.file_url,
                      file_name: msg.file_name,
                    }],
          statusChat: msg.whatsapp_message_status_chat,
          type_file: msg.type_file,
          file_url: msg.file_url,
          file_name: msg.file_name,
        };
      }

      // If it's the last message, push the last group
      if (index === messages.length - 1) {
        _msgGroups.push(currentGroup);
      }
    })
  }
  
  isLoadingDialogVisible.value = false

  return _msgGroups
})

onMounted(() => {
  store.initPusher();
});

const formatDateChat = (dateStr, options) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-GB", options);
};

// Function to Check if Date Separator is Needed
const shouldShowDate = (index) => {
  if (index === 0) return true;
  
  const prevDate = new Date(msgGroups.value[index - 1].messages[0].time).toDateString();
  const currDate = new Date(msgGroups.value[index].messages[0].time).toDateString();
  
  return prevDate !== currDate;
};

const handleImageLoaded = (e, index) => {
  imageLoaded.value[index] = true
  e.target.classList.remove('loading')
}

const handleImageError = (e, index) => {
  imageLoaded.value[index] = true
  e.target.src = '/storage/no-image.png'
  e.target.classList.remove('loading')
}

const handlePreviewImageError = (e) => {
  e.target.src = '/storage/no-image.png'
}

const formatMessage = (message = '') => {
  const withLineBreaks = message.replace(/\n/g, '<br>')

  // Regex untuk https:// atau http://
  const urlRegex = /(https?:\/\/[^\s<]+)/g

  // Regex untuk www. tanpa http
  const wwwRegex = /\bwww\.[^\s<]+/g

  // Ganti https URL jadi link
  let withLinks = withLineBreaks.replace(urlRegex, url => {
    return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-underline">${url}</a>`
  })

  // Ganti www. jadi link dengan menambahkan http://
  withLinks = withLinks.replace(wwwRegex, url => {
    return `<a href="http://${url}" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-underline">${url}</a>`
  })

  return withLinks
}

</script>

<template>
  <div class="chat-log pa-6">
    
    <div v-if="isLoadingDialogVisible" class="chat-loading-overlay">
        <div class="chat-loading-card">
          <p>Loading...</p>
          <VProgressLinear
            indeterminate
            bg-color="rgba(var(--v-theme-surface), 0.1)"
            :height="8"
            class="mt-4"
            color="info"
          />
        </div>
      </div>

    <template v-for="(msgGrp, index) in msgGroups" :key="msgGrp.senderId + String(index)">

      <div v-if="shouldShowDate(index) && msgGrp.messages[index] != null && msgGrp.messages[index].time != null" class="date-separator text-center my-3">
        <span class="date-label px-3 py-1 rounded-lg bg-gray-300 text-gray-700">
          {{ formatDateChat(msgGrp.messages[index].time, { day: 'numeric', month: 'long', year: 'numeric' }) }}
        </span>
      </div>

      <div
        class="chat-group d-flex align-start"
        :class="[{
          'flex-row-reverse': msgGrp.senderId !== '1',
          'mb-6': msgGroups.length - 1 !== index,
        }]"
      >
        <div
          class="chat-body d-inline-flex flex-column"
          :class="msgGrp.senderId !== '1' ? 'align-end' : 'align-start'"
        >
          <div
            v-for="(msgData, msgIndex) in msgGrp.messages"
            :key="`${msgData.whatsapp_message_status_user}-${msgData.time}-${msgIndex}`"
            class="chat-content py-2 px-4 elevation-2"
            style="background-color: rgb(var(--v-theme-surface));"
            :class="[
              msgGrp.senderId === '1' ? 'chat-left' : 'bg-primary text-white chat-right',
              msgGrp.messages.length - 1 !== msgIndex ? 'mb-2' : 'mb-1',
            ]"
          >
            <!-- Preview untuk file -->
            <div v-if="msgData.file_url && msgData.type_file !== 'text'" class="mb-2">
              <!-- Kalau file image -->
              <template v-if="msgData.type_file === 'image'">
                <!-- <div style="position: relative; inline-size: 200px;" class="mb-2"> -->
                <div style="position: relative; block-size: 200px; inline-size: 200px;" class="mb-2">
                  <img
                    :src="msgData.file_url"
                    alt="Image"
                    style="border-radius: 8px;background-color: #f0f0f0;block-size: 100%; inline-size: 100%;object-fit: contain;"
                    @load="e => handleImageLoaded(e, msgIndex)"
                    @error="e => handleImageError(e, msgIndex)"
                    @click="openImagePreview(msgData.file_url)"
                    class="loading"
                  />

                  <VProgressCircular
                    v-if="!imageLoaded[msgIndex]"
                    indeterminate
                    color="warning"
                    size="32"
                    class="absolute-center"
                  />
                </div>

                <!-- Download Gambar -->
                <div class="d-flex align-center gap-2">
                  <VIcon size="22" icon="tabler-photo" />
                  
                  <div>
                    <a
                      :href="msgData.file_url"
                      target="_blank"
                      rel="noopener"
                      download
                      class="text-white text-decoration-underline"
                    >
                      {{ 'Download image' }}
                      <!-- {{ msgData.file_name.split('/').pop() || 'Download image' }} -->
                    </a>
                    <div class="text-xs text-disabled">
                      {{ msgData.file_name.split('.').pop()?.toUpperCase() }} file
                    </div>
                  </div>
                </div>
              </template>

              <!-- Kalau file selain image -->
              <template v-else>
                <div class="d-flex align-center gap-2 mt-2">
                  <VIcon size="22" icon="tabler-file-description" />
                  <div>
                    <a
                      :href="msgData.file_url"
                      target="_blank"
                      rel="noopener"
                      download
                      class="text-white text-decoration-underline"
                    >
                      {{ 'Download file' }}
                    </a>
                    <div class="text-xs text-disabled">
                      {{ msgData.file_name.split('.').pop()?.toUpperCase() }} file
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <p
              class="mb-0 text-base"
              v-html="formatMessage(msgData.message)"
            ></p>

            <div
              v-if="msgData.senderId == '0'"
              class="text-right"
            >
              <span class="text-sm ms-2 text-disabled mr-1">{{ formatDate(msgData.time, { hour: 'numeric', minute: 'numeric' }) }}</span>
              
              <VIcon
                size="15"
                :color="resolveFeedbackIcon(msgData.statusChat).color"
              >
                {{ resolveFeedbackIcon(msgData.statusChat).icon }}
              </VIcon>
            </div>
            <div
              v-else
              class="text-right"
            >
              <span class="text-sm ms-2 text-disabled">{{ formatDate(msgData.time, { hour: 'numeric', minute: 'numeric' }) }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>
    
  </div>

  <VDialog v-model="previewDialog" max-width="700px" persistent>
    <VCard class="pa-4" style=" position: relative;background-color: #1e1e2f;">

      <!-- Tombol X di pojok kanan atas -->
      <VBtn icon @click="previewDialog = false" class="dialog-close-btn">
        <VIcon icon="tabler-x" />
      </VBtn>

      <!-- Konten gambar -->
      <div style=" display: flex; align-items: center; justify-content: center; max-block-size: 80vh;max-inline-size: 100%;">
        <img
          :src="previewImage"
          alt="Preview"
          style=" border-radius: 8px; max-block-size: 80vh;max-inline-size: 100%; object-fit: contain;"
          @error="handlePreviewImageError"
        />
      </div>
    </VCard>
  </VDialog>

</template>

<style lang=scss>
.chat-log {
  .chat-body {
    max-inline-size: calc(100% - 6.75rem);

    .chat-content {
      border-end-end-radius: 6px;
      border-end-start-radius: 6px;

      p {
        overflow-wrap: anywhere;
      }

      &.chat-left {
        border-start-end-radius: 6px;
      }

      &.chat-right {
        border-start-start-radius: 6px;
      }
    }
  }
}
/* stylelint-disable-next-line rule-empty-line-before */
.loading {
  filter: blur(4px);
  opacity: 0.7;
}
/* stylelint-disable-next-line rule-empty-line-before */
.absolute-center {
  position: absolute;
  inset-block-start: 50%;
  inset-inline-start: 50%;
  transform: translate(-50%, -50%);
}

.dialog-close-btn {
  position: absolute;
  z-index: 10;
  color: white;
  inset-block-start: 8px;
  inset-inline-end: 8px;
}

.chat-loading-overlay {
  position: absolute;
  z-index: 5;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  background-color: rgba(30, 30, 47, 85%);
  inset: 0;
}

.chat-loading-card {
  padding: 1.5rem;
  border-radius: 0.5rem;
  background-color: #1e1e2f;
  color: white;
  inline-size: 280px;
  text-align: center;
}
</style>
