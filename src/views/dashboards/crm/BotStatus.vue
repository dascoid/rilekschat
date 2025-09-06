<script setup>

const accessToken = useCookie('accessToken');

const {
  data: botStatusData,
  execute: fetchBotStatusData,
} = await useApi(createUrl('/get_bot_status'),
    {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    },
);

const dataBot = computed(() => botStatusData.value)

const resolveStatusVariant = stat => {
  const statLowerCase = stat.value.toLowerCase()
  if (statLowerCase === 'green' || statLowerCase === 'connected')
    return 'success'
  if (statLowerCase === 'yellow' || statLowerCase === 'connected')
    return 'warning'
  if (statLowerCase === 'red' || statLowerCase === 'connected')
    return 'error'
  if (statLowerCase === 'unknown' || statLowerCase === 'not connected')
    return 'secondary'
  
  return stat.color
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

</script>

<template>
  <VCard title="Bot Whatsapp">
    <template #append>
      <span class="text-sm text-disabled">List Status Bot</span>
    </template>

    <VCardText>
      <VRow
        v-if="dataBot && dataBot.length && dataBot[0].length"
        v-for="(data,index) in dataBot"
        :key="index"
      >
        <VCol
          v-for="item in data"
          :key="item.title"
          cols="6"
          md="3"
        >
          <div class="d-flex align-center gap-4 mt-md-9 mt-0">
            <VAvatar
              :color="resolveStatusVariant(item)"
              variant="tonal"
              rounded
              size="40"
            >
              <VIcon :icon="item.icon" />
            </VAvatar>

            <div class="d-flex flex-column">
              <h5 class="text-h5">
                {{ item.title }}
              </h5>
              <div class="text-overline" v-if="item.title == 'Quality Rating'">
                {{ WaBotQualityRatingText(item.value) }}
              </div>
              <div class="text-overline" v-else-if="item.title == 'Number Status'">
                {{ item.value }}
              </div>
              <div class="text-sm" v-else>
                {{ item.value }}
              </div>
            </div>
          </div>
        </VCol>
      </VRow>
      <div
        v-else
        class="text-center text-medium-emphasis py-6"
      >
        <VIcon icon="tabler-database-off" size="28" class="mb-2" />
        <div class="text-body-1">Data not found</div>
      </div>
    </VCardText>
  </VCard>
</template>
