<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const isDialogVisible = ref(false)
const dontShowAgain = ref(false)

const storageKey = 'dontShowDialog'

onMounted(() => {
  const stored = sessionStorage.getItem(storageKey)
  if (!stored) {
    isDialogVisible.value = true
  }
})

// Handle close dialog
const closeDialog = () => {
  if (dontShowAgain.value) {
    sessionStorage.setItem(storageKey, '1')
  }
  isDialogVisible.value = false
}

</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900 "
    v-model="isDialogVisible"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="closeDialog" />

    <!-- Dialog Content -->
    <VCard 
      title="Change Log" 
      class="pa-sm-10 pa-2"
    >
      <!-- <PerfectScrollbar style="overflow-y: auto; scroll-behavior: smooth; scrollbar-width: thin;" class="flex-grow-1"> -->
      <div style="block-size: 400px;">
        <PerfectScrollbar style="block-size: 100%;">
          <VCardText>
            <h4 class="mb-3">💬 Update Harga WhatsApp API</h4>
            <p class="mb-4">Per 1 Juli 2025, tarif pesan WhatsApp mengalami penyesuaian sebagai berikut:</p>

            <div class="table-responsive mb-7">
              <table class="table table-sm table-bordered align-middle custom-bordered">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold py-3 px-3 text-start">Kategori</th>
                    <th class="fw-bold py-3 px-3 text-start">USD</th>
                    <th class="fw-bold py-3 px-3 text-start">Rp</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="py-2 px-3 text-start">Marketing</td>
                    <td class="py-2 px-3 text-start">$0.0411</td>
                    <td class="py-2 px-3 text-start">Rp 617</td>
                  </tr>
                  <tr>
                    <td class="py-2 px-3 text-start">Utility (di luar 24 jam)</td>
                    <td class="py-2 px-3 text-start">$0.0250</td>
                    <td class="py-2 px-3 text-start">Rp 375</td>
                  </tr>
                  <tr>
                    <td class="py-2 px-3 text-start">Authentication (lokal)</td>
                    <td class="py-2 px-3 text-start">$0.0250</td>
                    <td class="py-2 px-3 text-start">Rp 375</td>
                  </tr>
                  <tr>
                    <td class="py-2 px-3 text-start">Authentication (internasional)</td>
                    <td class="py-2 px-3 text-start">$0.1360</td>
                    <td class="py-2 px-3 text-start">Rp 2.040</td>
                  </tr>
                </tbody>
              </table>
            </div>


            <h4>📌 Perubahan & Penambahan Fitur</h4>
            <ul>
              <li>Memperbaiki sistem penilaian kualitas (quality rating) agar lebih akurat.</li>
              <li>Menambahkan notifikasi saat metode pembayaran belum diatur di modul broadcast.</li>
              <li>Menambahkan tombol untuk scroll otomatis ke bagian bawah di tampilan chat.</li>
              <li>Menambahkan tombol sinkronisasi di halaman integrasi WhatsApp.</li>
              <li>Melakukan sinkronisasi template dari META menggunakan Template ID. Template yang tidak ditemukan akan dihapus dari database.</li>
              <li>Status kesehatan (health status) kini ditampilkan berdasarkan data dari entitas WABA.</li>
              <li>Menambahkan fitur subscribe untuk menyimpan kontak secara langsung.</li>
              <li>Menambahkan pengecekan untuk menghindari duplikasi saat menyimpan kontak.</li>
              <li>Menambahkan filter tambahan pada tampilan detail broadcast untuk memudahkan pencarian data.</li>
              <li>Menjadwalkan cron job setiap 1 jam untuk memeriksa status bot WhatsApp secara otomatis.</li>
              <li>Menambahkan dialog konfirmasi saat menghapus list receiver, termasuk opsi untuk menghapus data kontak terkait.</li>
              <li>Membatasi pengiriman broadcast maksimal ke 1000 kontak per sesi pengiriman.</li>
              <li>Menambahkan tombol broadcast langsung di halaman kontak dan tampilan chat.</li>
              <li>Memungkinkan pengguna melihat dan mengedit detail kontak langsung dari tampilan chat.</li>
              <li>Menampilkan informasi batas pengiriman broadcast di modul terkait.</li>
              <li>Menambahkan fitur pratinjau template sebelum broadcast dikirimkan.</li>
            </ul>
            <br>
            <h4>💬 Perbaikan & Peningkatan Modul Chat</h4>
            <ul>
              <li>Mengurangi delay saat berpindah antar chat untuk pengalaman lebih responsif.</li>
              <li>Dukungan pengiriman file dengan format: .jpeg, .jpg, .png, .gif, .pdf, .doc, .docx, .ppt, .pptx, .xls, .xlsx, dan .txt.</li>
              <li>Menambahkan dukungan emoticon di chat serta template emoticon siap pakai.</li>
              <li>Memperbaiki format teks agar enter dan spasi tampil rapi; tekan Shift+Enter untuk baris baru.</li>
              <li>Menampilkan pratinjau gambar langsung di dalam chat.</li>
              <li>Menyediakan tombol unduh untuk gambar dan file lainnya.</li>
              <li>Menghapus animasi loading saat membuka percakapan untuk performa yang lebih baik.</li>
              <li>Menambahkan fitur pencarian nama dan nomor telepon di daftar percakapan.</li>
            </ul>
            <br>
            <h4>🔐 Perubahan Keamanan & Sesi</h4>
            <ul>
              <li>Pengguna hanya dapat login di satu perangkat. Login di perangkat baru akan otomatis mengeluarkan sesi aktif sebelumnya.</li>
              <li>Durasi sesi login ditetapkan selama 1 hari. Jika memilih opsi "Remember Me", sesi akan diperpanjang hingga 7 hari.</li>
            </ul>
            <br>
            <h4>⚠️ Validasi & Informasi Kesalahan</h4>
            <ul>
              <li>Menambahkan pesan kesalahan yang lebih jelas saat proses import kontak gagal.</li>
            </ul>
          </VCardText>
        </PerfectScrollbar>
      </div>


      <VCardText>
        <VCheckbox
          v-model="dontShowAgain"
          label="Jangan tampilkan lagi pada session ini"
        />
      </VCardText>

      <VCardText class="d-flex justify-end flex-wrap gap-3">
        <VBtn @click="closeDialog">
          Close
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
.custom-bordered th,
.custom-bordered td {
  border: 1px solid #dee2e6; /* Bootstrap border color */
}
</style>
