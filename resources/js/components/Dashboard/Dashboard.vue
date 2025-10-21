<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Optional: Loader & Error Message -->
    <div v-if="loading" class="alert alert-info py-2 my-2">Loading dashboard data...</div>
    <div v-else-if="errorMsg" class="alert alert-danger py-2 my-2">{{ errorMsg }}</div>

    <div class="row g-6 mb-6">

      <!-- Total Users -->
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{ counts.users }}</h4>
                </div>
                <small class="mb-0">Total Users</small>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti ti-users ti-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Funds -->
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{ counts.funds }}</h4>
                </div>
                <small class="mb-0">Total Funds</small>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="ti ti-user-plus ti-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Categories -->
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{ counts.categories }}</h4>
                </div>
                <small class="mb-0">Total Categories</small>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ti ti-user-check ti-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Subcategories -->
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{ counts.subcategories }}</h4>
                </div>
                <small class="mb-0">Total Subcategories</small>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti ti-user-search ti-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Items -->
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{ counts.items }}</h4>
                </div>
                <small class="mb-0">Total Items</small>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti ti-box ti-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'
import { useToast } from 'vue-toastification'

// Access global axios instance ($http)
const { appContext } = getCurrentInstance()
const http = appContext?.config?.globalProperties?.$http
const toast = useToast()

// Reactive state
const counts = ref({
  users: 0,
  funds: 0,
  categories: 0,
  subcategories: 0,
  items: 0
})

const loading = ref(false)
const errorMsg = ref(null)

// Fetch counts from Laravel backend
const loadCounts = async () => {
  loading.value = true
  errorMsg.value = null
  try {
    const { data } = await http.get('/dashboard/counts')
    if (data?.status && data?.data) {
      counts.value = data.data
    } else {
      toast.error('Invalid response from server')
    }
  } catch (error) {
    console.error(error)
    errorMsg.value = 'Failed to load dashboard data.'
    toast.error(errorMsg.value)
  } finally {
    loading.value = false
  }
}

onMounted(loadCounts)
</script>
