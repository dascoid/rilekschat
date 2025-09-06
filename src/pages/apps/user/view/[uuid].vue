<script setup>
import UserBioPanel from '@/views/apps/user/view/UserBioPanel.vue';
import UserTabSecurity from '@/views/apps/user/view/UserTabSecurity.vue';

definePage({ 
  meta: {
    requiresAuth: true
  } 
})

onMounted(() => {
  checkPermission()
});

const route = useRoute('apps-user-view-uuid')
const router = useRouter()
const accessToken = useCookie('accessToken');
const userTab = ref(null)
const userData = ref({})
const currentUser = useCookie('userData')

const tabs = [
  {
    icon: 'tabler-lock',
    title: 'Security',
  },
]

const fetchUserData = async () => {
  try {
    const response = await useApi(createUrl('/detail_users', {
      query: {
        users_uuid: route.params.uuid,
      },
    }), {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    });

    await nextTick(() => {
      userData.value = response.data.value || {};
    });

  } catch (error) {
    console.error('Error fetching user data:', error);
  }
};

const checkPermission = () => {
  const targetUuid = route.params.uuid
  const currentUserUuid = currentUser.value?.users_uuid
  const isAdmin = currentUser.value?.role === 'admin'
  
  // Admin bisa akses semua profile
  if (isAdmin) {
    fetchUserData()
    return
  }
  
  // Non-admin hanya bisa akses profile sendiri
  if (currentUserUuid === targetUuid) {
    fetchUserData()
    return
  }
  
  // Redirect jika tidak punya akses
  router.push({ name: 'not-authorized' })
}

watch(() => route.params.uuid, () => {
  checkPermission()
})

</script>

<template>
  <VRow v-if="userData">
    <VCol
      cols="12"
      md="5"
      lg="4"
    >
      <UserBioPanel :user-data="userData"  @reloadData="fetchUserData"/>
    </VCol>

    <VCol
      cols="12"
      md="7"
      lg="8"
    >
      <VTabs
        v-model="userTab"
        class="v-tabs-pill"
      >
        <VTab
          v-for="tab in tabs"
          :key="tab.icon"
        >
          <VIcon
            :size="18"
            :icon="tab.icon"
            class="me-1"
          />
          <span>{{ tab.title }}</span>
        </VTab>
      </VTabs>

      <VWindow
        v-model="userTab"
        class="mt-6 disable-tab-transition"
        :touch="false"
      >

        <VWindowItem>
          <UserTabSecurity :user-data="userData"/>
        </VWindowItem>

      </VWindow>
    </VCol>
  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      User with UUID  {{ route.params.id }} not found!
    </VAlert>
  </div>
</template>
