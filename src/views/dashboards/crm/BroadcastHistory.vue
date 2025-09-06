<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const accessToken = useCookie('accessToken');

const resolveStatus = {
  done: 'success',
  failed: 'error',
  pending: 'warning',
  processing: 'info',
}

const getPaddingStyle = index => index ? 'padding-block-end: 1.25rem;' : 'padding-block: 1.25rem;'

const {
  data: broadcastHistoryData,
  execute: fetchbroadcastHistoryData,
} = await useApi(createUrl('/get_broadcast_history'),
    {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    },
);

const dataBroadcastHistory = computed(() => broadcastHistoryData.value)

</script>

<template>
  <VCard title="Last Broadcast">

    <VDivider />
    <VTable class="text-no-wrap transaction-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Status</th>
          <th>Total</th>
          <th>Sent</th>
          <th>Delivered</th>
          <th>Read</th>
          <th>Failed</th>
        </tr>
      </thead>

      <tbody>
        <template v-if="dataBroadcastHistory && dataBroadcastHistory.length">
          <tr
            v-for="(transition, index) in dataBroadcastHistory"
            :key="index"
          >
            <td
              :style="getPaddingStyle(index)"
              style="padding-inline-end: 1.5rem;"
            >
              <div class="d-flex align-center">
                <div>
                  <p class="text-base mb-0 text-high-emphasis">
                    {{ transition.name }}
                  </p>
                </div>
              </div>
            </td>
            <td
              :style="getPaddingStyle(index)"
              style="padding-inline-end: 1.5rem;"
            >
              <VChip
                label
                :color="resolveStatus[transition.status]"
                size="small"
                class="text-overline"
              >
                {{ transition.status }}
              </VChip>
            </td>
            <td
              :style="getPaddingStyle(index)"
              style="padding-inline-end: 1.5rem;"
            >
              <div class="text-high-emphasis text-base">
                {{ transition.total }}
              </div>
            </td>
            <td
              :style="getPaddingStyle(index)"
              style="padding-inline-end: 1.5rem;"
            >
              <div class="text-high-emphasis text-base">
                {{ transition.sent }}
              </div>
            </td>
            <td
              :style="getPaddingStyle(index)"
              style="padding-inline-end: 1.5rem;"
            >
              <div class="text-high-emphasis text-base">
                {{ transition.delivered }}
              </div>
            </td>
            <td
              :style="getPaddingStyle(index)"
              style="padding-inline-end: 1.5rem;"
            >
              <div class="text-high-emphasis text-base">
                {{ transition.read }}
              </div>
            </td>
            <td
              :style="getPaddingStyle(index)"
              style="padding-inline-end: 1.5rem;"
            >
              <div class="text-high-emphasis text-base">
                {{ transition.failed }}
              </div>
            </td>
          </tr>
        </template>
        
        <tr v-else>
          <td colspan="7" class="text-center text-medium-emphasis py-6">
            <VIcon icon="tabler-database-off" size="24" class="mb-2" />
            <div class="text-body-1">No broadcast history found.</div>
          </td>
        </tr>
      </tbody>
    </VTable>
  </VCard>
</template>

<style lang="scss">
.transaction-table {
  &.v-table .v-table__wrapper > table > tbody > tr:not(:last-child) > td,
  &.v-table .v-table__wrapper > table > tbody > tr:not(:last-child) > th {
    border-block-end: none !important;
  }
}
</style>
