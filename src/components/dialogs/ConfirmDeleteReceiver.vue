<script setup>
const props = defineProps({
  confirmationQuestion: {
    type: String,
    required: true,
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  uuidData: {
    type: String
  },
})

const checkbox = ref(0)

const emit = defineEmits([
  'update:isDialogVisible',
  'confirm',
])

const cancelled = ref(false)

const updateModelValue = val => {
  emit('update:isDialogVisible', val)
}

const onConfirmation = () => {
  emit('confirm', [checkbox.value,props.uuidData])
  updateModelValue(false)
}

const onCancel = () => {
  emit('update:isDialogVisible', false)
  checkbox.value = 0;
}
</script>

<template>
  <!-- 👉 Confirm Dialog -->
  <VDialog
    max-width="600"
    :model-value="props.isDialogVisible"
    @update:model-value="updateModelValue"
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VBtn
          icon
          variant="outlined"
          color="warning"
          class="my-4"
          style=" block-size: 88px;inline-size: 88px; pointer-events: none;"
        >
          <span class="text-5xl">!</span>
        </VBtn>

        <h6 class="text-lg font-weight-medium">
          {{ props.confirmationQuestion }}
        </h6>

      </VCardText>

      <VDivider />

      <VCheckbox
        v-model="checkbox"
        :true-value="1"
        :false-value="0"
        label="Check this if you want to delete contacts in this receiver"
      />

      <VDivider />

      <VCardText class="d-flex align-center justify-center gap-2">
        <VBtn
          variant="elevated"
          @click="onConfirmation"
        >
          Confirm
        </VBtn>

        <VBtn
          color="secondary"
          variant="tonal"
          @click="onCancel"
        >
          Cancel
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>

</template>
