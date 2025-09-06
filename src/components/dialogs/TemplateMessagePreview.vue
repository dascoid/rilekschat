<template>
  <div class="mb-7">
    <div class="whatsapp-preview">
      <div class="chat-bubble">
        <!-- Header -->
        <div v-if="templateData.whatsapp_templates_header" class="chat-header">
          {{ templateData.whatsapp_templates_header }}
        </div>

        <!-- Body (multiline dan show {{1}} dsb) -->
        <div v-html="formattedBody"></div>

        <!-- Footer -->
        <div v-if="templateData.whatsapp_templates_footer" class="chat-footer">
          {{ templateData.whatsapp_templates_footer }}
        </div>

        <hr v-if="parsedButtons.length" class="my-3" />

        <!-- Buttons -->
        <div v-if="parsedButtons.length" class="text-center mt-3">
          <div
            v-for="(btn, idx) in parsedButtons"
            :key="idx"
            class="d-inline-flex align-items-center justify-content-center gap-1 fw-semibold"
          >
            <span style="color: #0084ff;">
              <i class="bi bi-box-arrow-up-right" style="color: #0084ff;"></i>
              {{ btn.text }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps } from 'vue';

const props = defineProps({
  templateData: {
    type: Object,
    required: true,
  }
});

const parsedButtons = computed(() => {
  try {
    return JSON.parse(props.templateData.whatsapp_templates_button || '[]');
  } catch {
    return [];
  }
});

const formattedBody = computed(() => {
  const body = props.templateData.whatsapp_templates_body || '';
  return body
    .replace(/\n/g, '<br>')
    .replace(/{{(\d+)}}/g, '<span class="text-info">$&</span>');
});
</script>

<style scoped>
.whatsapp-preview {
  position: relative;
  padding: 2rem;
  border-radius: 12px;
  background-image: url("@images/patterns/whatsapp-bg.png");
  background-repeat: no-repeat;
  background-size: cover;
}

.chat-bubble {
  position: relative;
  padding: 1rem;
  border-radius: 10px;
  background-color: #fff;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 20%);
  color: #111;
  font-size: 12px;
  max-inline-size: 100%;
  white-space: pre-wrap;
}

.chat-header {
  font-weight: bold;
  margin-block-end: 0.5rem;
}

.chat-footer {
  color: gray;
  font-size: 12px;
  margin-block-start: 0.75rem;
}
</style>
