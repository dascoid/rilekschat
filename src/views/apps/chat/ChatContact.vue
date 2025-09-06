<script setup>
import { useChat } from '@/views/apps/chat/useChat'
import { useChatStore } from '@/views/apps/chat/useChatStore'

const props = defineProps({
  isChatContact: {
    type: Boolean,
    required: false,
    default: false,
  },
  user: {
    type: null,
    required: true,
  },
})

const store = useChatStore()
const { resolveAvatarBadgeVariant } = useChat()

const isChatContactActive = computed(() => {
  const active = store.activeChat?.data_users

  const isActive = (
    active?.whatsapp_users_id === props.user.whatsapp_users_id &&
    active?.bot_id === props.user.whatsapp_bot_id &&
    active?.room_id === props.user.room_id
  )

  if (!props.isChatContact) {
    return !store.activeChat?.data_message && isActive
  }

  return isActive
})

onMounted(() => {
  store.initPusher();
});
</script>

<template>
  <li
    :key="store.chatsContacts.length"
    class="chat-contact cursor-pointer d-flex align-center"
    :class="{ 'chat-contact-active': isChatContactActive }"
  >  
    <VAvatar
      size="40"
      :variant="'tonal'"
      :color="resolveAvatarBadgeVariant('online')"
    >
      <span>{{ avatarText(props.user?.whatsapp_users_username || '') }}</span>
    </VAvatar>
    <div class="flex-grow-1 ms-4 overflow-hidden">
      <p class="text-base text-high-emphasis mb-0">
        {{ props.user?.whatsapp_users_username || 'Unknown User' }}
      </p>
      <p class="mb-0 text-truncate text-body-2">
        {{ props.user?.message_text || '' }}
      </p>
    </div>
    <div
      v-if="props.isChatContact && props.user"
      class="d-flex flex-column align-self-start"
    >
      <div class="text-body-2 text-disabled whitespace-no-wrap">
        {{ formatDateToMonthShort(props.user?.created_at || '') }}
      </div>
      <VBadge
        v-if="props.user?.unread_messages"
        color="error"
        inline
        :content="props.user.unread_messages"
        class="ms-auto"
      />
    </div>
  </li>
</template>

<style lang="scss">
@use "@core-scss/template/mixins" as templateMixins;
@use "@styles/variables/vuetify";
@use "@core-scss/base/mixins";
@use "vuetify/lib/styles/tools/states" as vuetifyStates;

.chat-contact {
  border-radius: vuetify.$border-radius-root;
  padding-block: 8px;
  padding-inline: 12px;

  @include mixins.before-pseudo;
  @include vuetifyStates.states($active: false);

  &.chat-contact-active {
    @include templateMixins.custom-elevation(var(--v-theme-primary), "sm");

    background: rgb(var(--v-theme-primary));
    color: #fff;

    --v-theme-on-background: #fff;
  }

  .v-badge--bordered .v-badge__badge::after {
    color: #fff;
  }
}
</style>
