<script setup>

const router = useRouter()
const ability = useAbility()
const accountData = ref({})

onMounted(() => {
  fetchCompanyData();
});

const accessToken = useCookie('accessToken');
const isConfirmDialogOpen = ref(false)
const isAccountDeactivated = ref(false)
const isToastEditSuccessVisible = ref(false)
const validateAccountDeactivation = [v => !!v || 'Please confirm account deactivation']

const fetchCompanyData = async () => {
  try {
    const response = await useApi(createUrl('/detail_company'), {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    });

    await nextTick(() => {
      if(response.statusCode.value == 200)
      {
        accountData.value = response.data.value.data;
      }
    });

  } catch (error) {
    console.error('Error fetching company data:', error);
  }
};

const updateCompany = async () => {
  try {
    const response = await useApi(createUrl('/update_company', {
      query: {
       accountData,
      },
    }),{
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${accessToken.value}`,
        'Content-Type': 'application/json',
      },
    });

    await nextTick(() => {
      if(response.statusCode.value == 200)
      {
        accountData.value = response.data.value.data;
        isToastEditSuccessVisible.value = true
      }
    });

  } catch (error) {
    console.error('Error fetching company data:', error);
  }
};

const deleteCompany = async () => {
  try {
    const res = await useApi('/delete_company', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${accessToken.value}`,
      },
    })

    if(res.statusCode.value == 200)
    {
      useCookie('accessToken').value = null

      userData.value = null

      await router.push('/login')

      useCookie('userAbilityRules').value = null

      ability.update([])
    }

  } catch (err) {
    console.error(err)
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="pt-2">
          <!-- 👉 Form -->
          <VForm class="mt-3">
            <VRow>
              <!-- 👉 Company Name -->
              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="accountData.company_name"
                  placeholder="PT. Demo"
                  label="Company Name"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountData.email"
                  label="E-mail"
                  placeholder="johndoe@gmail.com"
                  type="email"
                />
              </VCol>

              <!-- 👉 Organization -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountData.website"
                  label="Website"
                  placeholder="www.demo.com"
                />
              </VCol>

              <!-- 👉 Phone -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountData.phone"
                  label="Phone Number"
                  placeholder="+1 (917) 543-9876"
                />
              </VCol>

              <!-- 👉 Address -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountData.address"
                  label="Address"
                  placeholder="123 Main St, New York, NY 10001"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountData.city"
                  label="City"
                  placeholder="East Rochester"
                />
              </VCol>

              <!-- 👉 State -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountData.state"
                  label="State"
                  placeholder="New York"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountData.country"
                  label="Country"
                  placeholder="USA"
                />
              </VCol>

              <!-- 👉 Form Actions -->
              <VCol
                cols="12"
                class="d-flex flex-wrap gap-4"
                @click="updateCompany"
              >
                <VBtn>Save changes</VBtn>

              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <!-- 👉 Delete Account -->
      <VCard title="Delete Company">
        <VCardText>
          <!-- 👉 Checkbox and Button  -->
          <div>
            <VCheckbox
              v-model="isAccountDeactivated"
              :rules="validateAccountDeactivation"
              label="I confirm my account deactivation"
            />
          </div>

          <VBtn
            :disabled="!isAccountDeactivated"
            color="error"
            class="mt-6"
            @click="isConfirmDialogOpen = true"
          >
            Delete Company
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- Confirm Dialog -->
  <ConfirmDialog
    v-model:is-dialog-visible="isConfirmDialogOpen"
    confirmation-question="Are you sure you want to delete your company?"
    confirm-title="Deleted!"
    confirm-msg="Your company has been deleted successfully."
    cancel-title="Cancelled"
    cancel-msg="Company Deleted Cancelled!"
    @confirm="deleteCompany"
  />

  <VSnackbar
    v-model="isToastEditSuccessVisible"
    location="bottom start"
    variant="flat"
    color="success"
    :close-on-content-click=true
  >
    Success Update Data
  </VSnackbar>
</template>
