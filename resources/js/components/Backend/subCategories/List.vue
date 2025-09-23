<template>
  <div class="container-xxl flex-grow-1 container-p-y">
           <div class="card-body">
          <!-- Header & Add New Button -->
          <div class="d-flex align-items-center mb-5">
          <h5 class="card-header ms-2 mb-0">Sub categories Management</h5>

          <a :href="`/backend/subcategoriesAllPdf`" style="color:white;" class=" btn btn-primary ms-auto"
      >
        <i class="fa fa-print me-2"></i>
        Print All Data
      </a>
          </div>
        </div>

        <!-- Loader -->
    <Loading :active="isLoading" :is-full-page="true" />
    <div class="card">
     <!-- Table -->
<div class="card-datatable text-nowrap">
  <table class="table" id="datatable">
    <thead>
      <tr>
        <th>Sl</th>
        <th>Memo No</th>
        <th>Fund</th>
        <th>Categories</th>
        <th>Budget</th>
        <th>Balance</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Dynamic Rows -->
      <tr v-for="(item, index) in subcatagoriesData" :key="item.id">
        <td>{{ index + 1 }}</td>
        <td>{{ item.memo_no }}</td>
        <td>{{ item.fund.fund }}</td>
        <td>{{ item.category.name }}</td>
        <td>{{ item.budget }}</td>
        <td>{{ item.balance }}</td>
        <td>{{ item.date }}</td>
        <td>
   <a :href="`/backend/subcategoriespdf/${item.id}/${item.fund_id}/${item.categorie_id}`" 
   class="btn btn-primary me-2" 
   target="_blank">
  <i class="far fa-file-pdf"></i>
</a>
          <button class="btn btn-danger">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
 

    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'
import { useToast } from 'vue-toastification'

const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()

const isLoading = ref(false)
const subcatagoriesData = ref([])

async function getSubCatagoriesData() {
  isLoading.value = true
  try {
    const res = await http.get('/sub-Catagories-list/create') 
    subcatagoriesData.value = res.data?.data ?? []
    console.log('Subcategories:', subcatagoriesData.value)
  } catch (e) {
    toast.error('Failed to load subcategories')
  } finally {
    isLoading.value = false
  }
}

onMounted(getSubCatagoriesData)
</script>

