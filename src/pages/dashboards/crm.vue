<script setup>
import BotStatus from '@/views/dashboards/crm/BotStatus.vue';
import BroadcastHistory from '@/views/dashboards/crm/BroadcastHistory.vue';
import Changelog from '@/views/dashboards/crm/Changelog.vue';

definePage({ 
  meta: {
    requiresAuth: true
  }
})

const accessToken = useCookie('accessToken');

const {
  data: summaryStatsData,
  execute: fetchsummaryStatsData,
} = await useApi(createUrl('/get_summary_stats'),
    {
      method: 'GET',
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    },
);

const summaryData = computed(() => summaryStatsData.value)

</script>

<template>
  <VRow class="match-height">
    <VCol
      cols="12"
      md="12"
      lg="12"
    >
      <BotStatus class="h-100"/>
    </VCol>

    <VCol
      cols="12"
      md="12"
      lg="12"
    >
      <BroadcastHistory />
    </VCol>

    <Changelog/>

    <!-- <VCol
      v-for="demo in summaryData"
      :key="demo.title"
      cols="12"
      sm="6"
      md="4"
      lg="3"
    >
      <VCard>
        <VCardText>
          <div class="d-flex align-center gap-x-4 mb-1">
            <VAvatar
              :color="demo.color"
              variant="tonal"
              rounded
              size="44"
            >
              <VIcon
                :icon="demo.icon"
                size="28"
              />
            </VAvatar>
            <h4 class="text-h4">{{ demo.stat }}</h4>
          </div>

          <h5 class="text-h5 mt-3">
            {{ demo.title }}
          </h5>
          <p class="text-sm">
            List Total Data in {{ demo.title }}
          </p>

        </VCardText>
      </VCard>
    </VCol> -->

    <VCol
      cols="12"
      md="12"
    >
      <VCard title="Statistics">
        <template #append>
          <span class="text-disabled text-subtitle-2">Count Data</span>
        </template>

        <VCardText>
          <VRow>
            <VCol
              v-for="item in summaryData"
              :key="item.title"
              cols="6"
              md="2"
            >
              <div class="d-flex gap-x-4 align-center">
                <VAvatar
                  :color="item.color"
                  variant="tonal"
                  size="40"
                  rounded
                >
                  <VIcon :icon="item.icon" />
                </VAvatar>

                <div class="d-flex flex-column">
                  <h5 class="text-h5">
                    {{ item.stat }}
                  </h5>
                  <div class="text-body-2">
                    {{ item.title }}
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

  </VRow>
</template>
