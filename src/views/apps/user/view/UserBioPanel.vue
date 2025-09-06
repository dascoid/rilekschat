<script setup>
const props = defineProps({
  userData: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'reloadData',
])

const isUserInfoEditDialogVisible = ref(false)

const resolveUserRoleVariant = role => {
  if (role === 'subscriber')
    return {
      color: 'warning',
      icon: 'tabler-user',
    }
  if (role === 'author')
    return {
      color: 'success',
      icon: 'tabler-circle-check',
    }
  if (role === 'maintainer')
    return {
      color: 'primary',
      icon: 'tabler-chart-pie-2',
    }
  if (role === 'editor')
    return {
      color: 'info',
      icon: 'tabler-pencil',
    }
  if (role === 'admin')
    return {
      color: 'secondary',
      icon: 'tabler-server-2',
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

const editUser  = (updatedUser) => {
  if (updatedUser && updatedUser[1]) {
    isToastSuccessVisible.value = true
    emit('reloadData')
  } else {
    console.warn('No updates detected.');
  }
};
const isToastSuccessVisible = ref(false)

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

  return `${day} ${month} ${year}`;
};
</script>

<template>
  <VRow>
    <!-- SECTION User Details -->
    <VCol cols="12">
      <VCard v-if="props.userData">
        <VCardText class="text-center pt-12">
          <!-- 👉 Avatar -->
          <VAvatar
            rounded
            :size="100"
            :color="!props.userData.avatar ? 'primary' : undefined"
            :variant="!props.userData.avatar ? 'tonal' : undefined"
          >
            <VImg
              v-if="props.userData.avatar"
              :src="props.userData.avatar"
            />
            <span
              v-else
              class="text-5xl font-weight-medium"
            >
              {{ avatarText(props.userData.name) }}
            </span>
          </VAvatar>

          <!-- 👉 User name -->
          <h5 class="text-h5 mt-4">
            {{ props.userData.name }}
          </h5>

          <!-- 👉 Role chip -->
          <VChip
            label
            :color="resolveUserRoleVariant(props.userData.role).color"
            size="small"
            class="text-capitalize mt-4"
          >
            {{ props.userData.role }}
          </VChip>
        </VCardText>

        <VCardText>

          <!-- 👉 Details -->
          <h5 class="text-h5">
            Details
          </h5>

          <VDivider class="my-4" />

          <!-- 👉 User Details list -->
          <VList class="card-list mt-2">
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Name:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.name ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <span class="text-h6">
                  Email:
                </span>
                <span class="text-body-1">
                  {{ props.userData.email ?? '-' }}
                </span>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Status:
                  <div class="d-inline-block text-body-1 text-capitalize">
                    <VChip
                      :color="resolveUserStatusVariant(props.userData.activated)"
                      size="small"
                      label
                      class="text-capitalize"
                    >
                      {{ statusVariant(props.userData.activated) }}
                    </VChip>
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Role:
                  <div class="d-inline-block text-capitalize text-body-1">
                    {{ props.userData.role ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Gender:
                  <div class="d-inline-block text-capitalize text-body-1">
                    {{ props.userData.gender ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Contact:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.phone_number ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Date of Birth:
                  <div class="d-inline-block text-body-1">
                    {{ formatDate(props.userData.date_of_birth) ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Address:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.address ?? '-' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>
          </VList>
        </VCardText>

        <!-- 👉 Edit and Suspend button -->
        <VCardText class="d-flex justify-center gap-x-4">
          <VBtn
            variant="elevated"
            @click="isUserInfoEditDialogVisible = true"
          >
            Edit
          </VBtn>

        </VCardText>
      </VCard>
    </VCol>
    <!-- !SECTION -->

  </VRow>

  <!-- 👉 Edit user info dialog -->
  <UserInfoEditDialog
    v-if="isUserInfoEditDialogVisible"
    v-model:is-dialog-visible="isUserInfoEditDialogVisible"
    :user-data="props.userData"
    @submit="editUser"
  />

  <VSnackbar
    v-model="isToastSuccessVisible"
    location="bottom start"
    variant="flat"
    color="success"
    :close-on-content-click=true
  >
    Success Delete Data
  </VSnackbar>

</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.5rem;
}

.text-capitalize {
  text-transform: capitalize !important;
}
</style>
