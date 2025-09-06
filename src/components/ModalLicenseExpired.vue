<template>
  <div
    class="overlay"
    style="position: fixed; z-index: 9999; inset: 0;"
  >
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <h2 id="modal-title">Lisensi Expired</h2>
      <p>Lisensi perusahaan Anda telah habis. Silakan hubungi administrator.</p>
      <button @click="forceLogout">Logout</button>
    </div>
  </div>
</template>

<script setup>
import { useModalStore } from '@core/stores/modal'

const modalStore = useModalStore()

const forceLogout = () => {
  useCookie('accessToken').value = null
  modalStore.hideLicenseModal()
  window.location.href = '/login'
}
</script>

<style scoped>
.overlay {
  position: fixed;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 60%);
  inset: 0;
}

.modal {
  padding: 24px;
  border-radius: 12px;
  background: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 30%);
  inline-size: 90%;
  max-inline-size: 400px;
  text-align: center;
}

.modal h2 {
  color: #1a202c; /* dark gray */
  font-size: 1.5rem;
  font-weight: 600;
  margin-block-end: 16px;
}

.modal p {
  color: #4a5568; /* medium gray */
  font-size: 1rem;
  margin-block-end: 24px;
}

.modal button {
  border: none;
  border-radius: 8px;
  background-color: #e53e3e; /* red 600 */
  color: white;
  cursor: pointer;
  font-weight: 600;
  inline-size: 100%;
  padding-block: 10px;
  padding-inline: 16px;
  transition: background-color 0.2s ease;
}

.modal button:hover {
  background-color: #c53030; /* red 700 */
}
</style>
