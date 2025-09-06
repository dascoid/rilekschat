<script setup>
import CompanySettingsAccount from '@/views/pages/company-settings/CompanySettingsAccount.vue';

const route = useRoute('pages-company-settings-tab')

const activeTab = computed({
  get: () => route.params.tab,
  set: () => route.params.tab,
})

// tabs
const tabs = [
  {
    title: 'Account',
    icon: 'tabler-users',
    tab: 'account',
  },
]

definePage({ 
  meta: { 
    navActiveLink: 'pages-company-settings-tab',
    requiresAuth: true,
    role: 'admin'
  } 
})
</script>

<template>
  <div>
    <VTabs
      v-model="activeTab"
      class="v-tabs-pill"
    >
      <VTab
        v-for="item in tabs"
        :key="item.icon"
        :value="item.tab"
        :to="{ name: 'pages-company-settings-tab', params: { tab: item.tab } }"
      >
        <VIcon
          size="20"
          start
          :icon="item.icon"
        />
        {{ item.title }}
      </VTab>
    </VTabs>

    <VWindow
      v-model="activeTab"
      class="mt-6 disable-tab-transition"
      :touch="false"
    >
      <!-- Account -->
      <VWindowItem value="account">
        <CompanySettingsAccount />
      </VWindowItem>

    </VWindow>
  </div>
</template>
