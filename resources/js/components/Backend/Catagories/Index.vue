<template>
  <div class="container-xxl flex-grow-1 container-p-y">
           <div class="card-body">
          <!-- Header & Add New Button -->
          <div class="d-flex align-items-center mb-5">
            <h5 class="card-header mb-0">Categories Management</h5>
            <button @click="openModal" type="button" class="btn btn-primary ms-auto"><i class="fa fa-plus me-2"></i>
              Add New
            </button>
          </div>
        </div>
    <div class="card">
      <div class="card-datatable text-nowrap">
 

        <!-- Loader -->
    <Loading :active="isLoading" :is-full-page="true" />


        <!-- Table -->
        <div class="card-datatable text-nowrap">
          <table class="table" id="datatable">
            <thead>
              <tr>
                <th>Sl</th>
                <th>Code</th>
                <th>Fund</th>
                <th>Categories</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="category,index in getCategory" :key="category.id" >
                <td>{{index + 1}}</td>
                <td>{{category.code}}</td>
                <td>{{category.fund.fund}}</td>
                <td>{{category.name}}</td>
                <td>
                  <button @click="editModeData(category)" class="btn btn-warning me-2">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="DeleteModeData(category.id)"  class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

<!-- Create Modal -->
<div
  v-if="createModal"
  ref="createModalRef"
  class="modal fade show d-block"
  tabindex="-1"
  style="background: rgba(0,0,0,0.5);"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="mb-0">Add New</h5>
        <button type="button" class="btn-close" @click="closeModal"></button>
      </div>

      <div class="modal-body">
       <form @submit.prevent="SubmitCatagories">
          <!-- Fund -->
  <div class="mb-3">
    <label for="fundSelect" class="form-label">Fund</label>
    <select ref="createFundSelectRef" class="form-control" style="width:100%"></select>
    <small v-if="formErrors.fund_id" class="text-danger">
      {{ formErrors.fund_id[0] }}
    </small>
  </div>
  <!-- Code -->
  <div class="mb-3">
    <label for="catCode" class="form-label">Category Code</label>
    <input
      id="catCode"
      type="text"
      v-model="formData.code"
      class="form-control"
      placeholder="Enter code"
    />
    <small v-if="formErrors.code" class="text-danger">
      {{ formErrors.code[0] }}
    </small>
  </div>

  <!-- Name -->
  <div class="mb-3">
    <label for="catName" class="form-label">Category Name</label>
    <input
      id="catName"
      type="text"
      v-model="formData.name"
      class="form-control"
      placeholder="Enter category name"
    />
    <small v-if="formErrors.name" class="text-danger">
      {{ formErrors.name[0] }}
    </small>
  </div>

  <button type="submit" class="btn btn-primary">Submit</button>
</form>

      </div>
    </div>
  </div>
</div>


<!-- Edit Modal -->
<div v-if="editModel" ref="editModalRef" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Edit Category</h5>
        <button type="button" class="btn-close" @click="editModel = false"></button>
      </div>

      <div class="modal-body">
        <form @submit.prevent="updateFund">
          <div class="mb-3">
            <label for="fundSelect" class="form-label">Fund</label>
          <select ref="editFundSelectRef" class="form-control" style="width:100%"></select>
          </div>

     <div class="mb-3">
    <label for="catCode" class="form-label">Category Code</label>
    <input
      id="catCode"
      type="text"
      v-model="formData.code"
      class="form-control"
      placeholder="Enter code"
    />
  </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" class="form-control" v-model="formData.name" placeholder="Name" required/>
          </div>

          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="loading">Submitting...</span><span v-else>Submit</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>



     <!-- Delete Modal -->
<div
  v-if="deleteModel"
  class="modal fade show d-block modal-danger"
  role="dialog"
  aria-modal="true"
  aria-labelledby="deleteTitle"
  @keydown.esc="deleteModel = false"
  tabindex="-1"
  style="background-color: rgba(17, 24, 39, 0.65);"
>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3 animate-pop">
      <!-- Header -->
      <div class="modal-header border-0 text-white gradient-header">
        <div class="d-flex align-items-center gap-2">
          <!-- Warning Icon -->
          <svg class="text-white" width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M12 9v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="17" r="1.2" fill="currentColor"/>
            <path d="M10.3 3.9 1.8 18.3A2 2 0 0 0 3.6 21h16.8a2 2 0 0 0 1.8-2.7L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.2" fill="none"/>
          </svg>
          <h5 style="color:white;" id="deleteTitle" class="modal-title fw-semibold mb-0">Confirm Deletion</h5>
        </div>
        <button style="color:white;" @click="deleteModel = false" type="button" class="btn-close btn-close-white"></button>
      </div>

      <!-- Body -->
      <div class="modal-body py-4">
        <div class="d-flex align-items-start gap-3">
          <div class="danger-chip">Delete</div>
          <div class="flex-grow-1">
            <p class="mb-1 fs-6 text-muted">
              You’re about to permanently delete this item.
            </p>
            <p class="mb-0 fw-medium">
              ID: <span class="text-dark">{{ deleteId }}</span>
            </p>
          </div>
        </div>
        <div class="small text-muted mt-3">
          This action cannot be undone. Please confirm to proceed.
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0 pt-0 pb-4 px-4">
        <button type="button" @click="deleteModel = false" class="btn btn-light px-4">
          Cancel
        </button>
        <button
          type="button"
          @click="confirmDelete()"
          class="btn btn-danger btn-danger-glow px-4"
        >
          <span class="me-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M3 6h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
              <path d="M8 6v14a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V6" stroke="currentColor" stroke-width="1.6"/>
              <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="1.6"/>
            </svg>
          </span>
          Delete Item
        </button>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted, nextTick, onBeforeUnmount, watch, getCurrentInstance } from "vue";
const isLoading = ref(false);
const { appContext } = getCurrentInstance();
const http = appContext.config.globalProperties.$http;
import { useToast } from "vue-toastification";
const toast = useToast();
import $ from 'jquery'
import 'select2/dist/css/select2.min.css'
import 'select2'


const createModal = ref(false);
const editModel = ref(false);
const deleteModel = ref(false);
const editId = ref(null);
const deleteId = ref(null);


const createModalRef = ref(null)
const editModalRef   = ref(null)

const createFundSelectRef = ref(null) // create modal select
const editFundSelectRef   = ref(null) // edit modal select
const formErrors = ref({})


const getCategory = ref([]);
const funds = ref([]);

const formData = ref({
  name: '',
  fund_id: '',
  code: '',
});

function openModal() {
  createModal.value = true;
}
function closeModal() {
  createModal.value = false;
}

async function getFunds() {
  isLoading.value = true;
  try {
    const res = await http.get("/fund/create");
    funds.value = res.data.funds;
    console.log(funds.value)
  } catch (e) {
    console.error("Error loading funds:", e);
  }finally{
      isLoading.value = false;
  }
}

async function getCategories() {
  isLoading.value = true;
  try {
    const res = await http.get("/catagories/create");
    getCategory.value = res.data.data;
  } catch (e) {
    console.error("Error loading funds:", e);
  }finally{
      isLoading.value = false;
  }
}

async function SubmitCatagories() {
  isLoading.value = true
  formErrors.value = {} 
  try {
    const res = await http.post("/catagories", formData.value)
    toast.success("Categories created successfully!")
    getCategories()
    closeModal()
    formData.value = { name: '', fund_id: '', code: '' }
  } catch (error) {
    if (error.response?.status === 422) {
      formErrors.value = error.response.data.errors || {}
    } else {
      const errormsg = error?.response?.data?.message || 'Failed to create category.'
      toast.error(errormsg)
    }
  } finally {
    isLoading.value = false
  }
}

 function initSelect2Edit () {
  if (!editFundSelectRef.value || !editModalRef.value) return

  const $el = $(editFundSelectRef.value)

  if ($el.data('select2')) {
    $el.off('change.select2')
    $el.select2('destroy')
  }

  const selectData = (funds.value || []).map(f => ({ id: String(f.id), text: f.fund }))

  $el.select2({
    width: '100%',
    placeholder: 'Select or search fund',
    allowClear: true,
    minimumResultsForSearch: 0,
    data: selectData,
    dropdownParent: $(editModalRef.value).find('.modal-content'),
  })

  // Select -> Vue
  $el.on('change.select2', function () {
    formData.value.fund_id = this.value || ''
  })

  // Vue -> Select (prefill)
  $el.val(formData.value.fund_id ? String(formData.value.fund_id) : '').trigger('change')
}

async function editModeData (category) {
  if (!category) return
  editId.value = category.id ?? ''

  formData.value = {
    name: category.name ?? '',
    code: category.code ?? '',
    fund_id: category.fund_id ?? category.fund?.id ?? ''
  }

  if (!Array.isArray(funds.value) || funds.value.length === 0) {
    await getFunds()
  }

  editModel.value = true
  await nextTick()
  initSelect2Edit()
}

// Create modal open/close
watch(createModal, async (open) => {
  if (open) {
    await nextTick()
    initSelect2Create()
  } else if (createFundSelectRef.value) {
    const $el = $(createFundSelectRef.value)
    if ($el.data('select2')) {
      $el.off('change.select2')
      $el.select2('destroy')
    }
  }
})

// Edit modal open/close
watch(editModel, async (open) => {
  if (open) {
    await nextTick()
    initSelect2Edit()
  } else if (editFundSelectRef.value) {
    const $el = $(editFundSelectRef.value)
    if ($el.data('select2')) {
      $el.off('change.select2')
      $el.select2('destroy')
    }
  }
})

// funds আপডেট হলে—modal খোলা থাকলে রিফ্রেশ
watch(funds, async () => {
  if (createModal.value) {
    await nextTick()
    initSelect2Create()
  }
  if (editModel.value) {
    await nextTick()
    initSelect2Edit()
  }
})

const updateFund = async () => {
  isLoading.value = true
  try {
    // 1) Build correct payload
    const payload = {
      name: (formData.value.name || '').trim(),
      code: (formData.value.code || '').trim(),
      fund_id: formData.value.fund_id ? Number(formData.value.fund_id) : null
    }


    // 2) Call API
    const { data } = await http.put(`/catagories/${editId.value}`, payload)
      console.log(data)
    const updatedFromApi = data?.data ?? data 
    const idx = getCategory.value.findIndex(c => c.id === editId.value)
    if (idx !== -1) {
      const fundObj = funds.value.find(f => String(f.id) === String(payload.fund_id))
      getCategory.value[idx] = {
        ...getCategory.value[idx],
        name: payload.name,
        fund_id: payload.fund_id,
        fund: fundObj ? fundObj : (updatedFromApi?.fund ?? getCategory.value[idx].fund)
      }
    } else {
      await getCategories()
    }

    toast.success('Category updated successfully!')
    editModel.value = false
    editId.value = null
    formData.value = { name: '', fund_id: '' }

  } catch (error) {
    const msg = error?.response?.data?.message || 'Failed to update category.'
    toast.error(msg)
  } finally {
    isLoading.value = false
  }
}


const DeleteModeData = async (category) => {
 deleteId.value = category
 deleteModel.value = true

}


const confirmDelete = async () => {
  isLoading.value = true
  try {
    const res = await http.delete(`/catagories/${deleteId.value}`)
    getCategory.value = getCategory.value.filter((f) => f.id !== deleteId.value)
    toast.success(res.data.message || "Category deleted successfully!")
    deleteModel.value = false
    deleteId.value = null

  } catch (error) {
    const msg = error?.response?.data?.message || "Failed to delete category."
    toast.error(msg)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await getCategories();
  await getFunds();
  await nextTick();   
});


function initSelect2Create () {
  if (!createFundSelectRef.value || !createModalRef.value) return

  const $el = $(createFundSelectRef.value)

  if ($el.data('select2')) {
    $el.off('change.select2')
    $el.select2('destroy')
  }

  const selectData = (funds.value || []).map(f => ({ id: String(f.id), text: f.fund }))

  $el.select2({
    width: '100%',
    placeholder: 'Select or search fund',
    allowClear: true,
    minimumResultsForSearch: 0,
    data: selectData,
    dropdownParent: $(createModalRef.value).find('.modal-content'),
  })

  $el.on('change.select2', function () {
    formData.value.fund_id = this.value || ''
  })

  $el.val(formData.value.fund_id ? String(formData.value.fund_id) : '').trigger('change')
}



</script>
<style scoped>
.my-toast {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
  color: #fff !important;
  font-weight: bold;
  border-radius: 8px;
}

.gradient-header {
    padding: 10px;
    background: linear-gradient(135deg, #7367f0, #0009d9);
}

/* Little red chip label */
.danger-chip {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fecaca;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  letter-spacing: .3px;
}

/* Glow + press effect on Delete */
.btn-danger-glow {
  box-shadow: 0 8px 20px rgba(239, 68, 68, .35);
  transition: transform .08s ease, box-shadow .2s ease;
}
.btn-danger-glow:active {
  transform: translateY(1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, .25);
}

/* Subtle pop-in animation */
@keyframes pop {
  0%   { transform: scale(.96); opacity: .0; }
  100% { transform: scale(1);    opacity: 1; }
}
.animate-pop {
  animation: pop .15s ease-out;
}

</style>