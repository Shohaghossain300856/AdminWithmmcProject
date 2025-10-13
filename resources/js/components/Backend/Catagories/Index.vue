<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card-body">
      <!-- Header & Add New Button -->
      <div class="d-flex align-items-center mb-5">
        <h5 class="card-header mb-0">Sub Categories Management</h5>
        <button @click="openModal" type="button" class="btn btn-primary ms-auto">
          <i class="fa fa-plus me-2"></i> Add New
        </button>
      </div>
    </div>

    <!-- Global Loader -->
    <Loading :active="isLoading" :is-full-page="true" />

    <div class="card">
      <div class="card-datatable text-nowrap">
        <!-- Search + ItemsPerPage -->
        <div class="row m-2">
          <div class="col-sm-6 col-md-9 mt-1">
            <div class="dataTables_filter">
              <label>
                Search
                <input
                  type="search"
                  class="form-control"
                  placeholder="Search by"
                  v-model="Search_Categories"
                />
              </label>
            </div>
          </div>

          <div class="col-sm-6 col-md-3 mt-5">
            <div class="text-end dataTables_length d-flex gap-2 justify-content-end">
              <label class="mt-2">Show</label>
              <select v-model.number="itemsPerPage" class="form-select" style="max-width: 90px">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <label class="mt-2">entries</label>
            </div>
          </div>
        </div>

        <!-- ===== Table (SMX style) ===== -->
        <div class="card-datatable text-nowrap">
          <div class="table-scroll">
            <table class="table p_table">
              <thead class="p_thead">
                <tr>
                  <th>Sl</th>
                  <th>Code</th>
                  <th>Fund</th>
                  <th>Categories</th>
                  <th style="width:180px;">Action</th>
                </tr>
              </thead>

              <tbody>
                <!-- Rows -->
                <tr v-for="(category, index) in visibleCategories" :key="category.id">
                  <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                  <td>{{ category.code || '—' }}</td>
                  <td>{{ category.fund?.fund || '—' }}</td>
                  <td>{{ category.name || '—' }}</td>
                  <td class="p_actions">
                    <button @click="editModeData(category)" class="btn btn-sm btn-primary me-2" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button @click="DeleteModeData(category.id)" class="btn btn-sm btn-danger btn-danger-glow" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>

                <!-- No matches -->
                <tr v-if="!isLoading && getCategory.length > 0 && filteredCategories.length === 0">
                  <td colspan="5" class="text-center text-muted">
                    No categories found matching your search criteria.
                  </td>
                </tr>

                <!-- No data -->
                <tr v-if="getCategory.length === 0 && !isLoading">
                  <td colspan="5" class="text-center text-muted">
                    No categories found. Please add some categories.
                  </td>
                </tr>

                <!-- Inline loader fallback -->
                <tr v-if="isLoading">
                  <td colspan="5" class="text-center">
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading categories...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination + Showing info -->
        <div class="row align-items-center p-3">
          <div class="col-md-6 mb-2 mb-md-0">
            <div class="dataTables_info">
              Showing {{ showingInfo.start }} to {{ showingInfo.end }} of {{ showingInfo.total }} entries
            </div>
          </div>
          <div class="col-md-6">
            <nav class="d-flex justify-content-md-end">
              <ul class="pagination mb-0">
                <li :class="['page-item', { disabled: currentPage === 1 }]">
                  <a class="page-link" href="javascript:void(0)" @click="goToPage(1)">First</a>
                </li>
                <li :class="['page-item', { disabled: currentPage === 1 }]">
                  <a class="page-link" href="javascript:void(0)" @click="previousPage">Prev</a>
                </li>

                <li
                  v-for="p in visiblePages"
                  :key="p"
                  :class="['page-item', { active: currentPage === p }]"
                >
                  <a class="page-link" href="javascript:void(0)" @click="goToPage(p)">{{ p }}</a>
                </li>

                <li :class="['page-item', { disabled: currentPage === totalPages }]">
                  <a class="page-link" href="javascript:void(0)" @click="nextPage">Next</a>
                </li>
                <li :class="['page-item', { disabled: currentPage === totalPages }]">
                  <a class="page-link" href="javascript:void(0)" @click="goToPage(totalPages)">Last</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ===== Create Modal ===== -->
  <div
    v-if="createModal"
    ref="createModalRef"
    class="modal-backdrop"
    @click.self="closeModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createTitle"
  >
    <div class="modal-card animate-pop">
      <!-- Header -->
      <div class="modal-header">
        <h5 id="createTitle" class="m-0">Add New Sub Category</h5>
        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form @submit.prevent="SubmitCatagories">
          <!-- Fund -->
          <div class="mb-3">
            <label class="form-label">Fund</label>
            <select ref="createFundSelectRef" class="form-control" style="width:100%"></select>
            <small v-if="formErrors.fund_id" class="text-danger">
              {{ formErrors.fund_id[0] }}
            </small>
          </div>

          <!-- Code -->
          <div class="mb-3">
            <label for="catCode" class="form-label">Sub Category Code</label>
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
            <label for="catName" class="form-label">Sub Category Name</label>
            <input
              id="catName"
              type="text"
              v-model="formData.name"
              class="form-control"
              placeholder="Enter Sub category name"
            />
            <small v-if="formErrors.name" class="text-danger">
              {{ formErrors.name[0] }}
            </small>
          </div>
        </form>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isLoading">Cancel</button>
        <button type="button" class="btn btn-primary" @click="SubmitCatagories" :disabled="isLoading">
          <span v-if="isLoading"><i class="fas fa-spinner fa-spin me-2"></i>Submitting...</span>
          <span v-else>Submit</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ===== Edit Modal ===== -->
  <div
    v-if="editModel"
    ref="editModalRef"
    class="modal-backdrop"
    @click.self="editModel = false"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editTitle"
  >
    <div class="modal-card animate-pop">
      <!-- Header -->
      <div class="modal-header">
        <h5 id="editTitle" class="m-0">Edit Category</h5>
        <button type="button" class="btn-close" @click="editModel = false" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form @submit.prevent="updateFund">
          <!-- Fund -->
          <div class="mb-3">
            <label class="form-label">Fund</label>
            <select ref="editFundSelectRef" class="form-control" style="width:100%"></select>
          </div>

          <!-- Code -->
          <div class="mb-3">
            <label for="editCatCode" class="form-label">Category Code</label>
            <input
              id="editCatCode"
              type="text"
              v-model="formData.code"
              class="form-control"
              placeholder="Enter code"
            />
          </div>

          <!-- Name -->
          <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" class="form-control" v-model="formData.name" placeholder="Name" required />
          </div>
        </form>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="editModel = false" :disabled="isLoading">Cancel</button>
        <button type="button" class="btn btn-primary" @click="updateFund" :disabled="isLoading">
          <span v-if="isLoading"><i class="fas fa-spinner fa-spin me-2"></i>Submitting...</span>
          <span v-else>Submit</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ===== Delete Modal ===== -->
  <div
    v-if="deleteModel"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="deleteTitle"
    @keydown.esc="deleteModel = false"
  >
    <div class="modal-card animate-pop">
      <!-- Header (danger variant) -->
      <div class="modal-header danger">
        <div class="d-flex align-items-center gap-2">
          <i class="fas fa-exclamation-triangle"></i>
          <h5 id="deleteTitle" class="m-0">Confirm Deletion</h5>
        </div>
        <button @click="deleteModel = false" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <div class="d-flex align-items-start gap-3">
          <div class="danger-chip">Delete</div>
          <div class="flex-grow-1">
            <p class="mb-1 fs-6 text-muted">You’re about to permanently delete this item.</p>
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
      <div class="modal-footer">
        <button type="button" @click="deleteModel = false" class="btn btn-secondary">Cancel</button>
        <button type="button" @click="confirmDelete()" class="btn btn-danger btn-danger-glow">
          <span class="me-2"><i class="fas fa-trash"></i></span>
          Delete Item
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, watch, getCurrentInstance } from "vue";
const isLoading = ref(false);
const { appContext } = getCurrentInstance();
const http = appContext.config.globalProperties.$http;
import { useToast } from "vue-toastification";
const toast = useToast();
import $ from 'jquery'
import 'select2/dist/css/select2.min.css'
import 'select2'

// modals
const createModal = ref(false);
const editModel = ref(false);
const deleteModel = ref(false);
const editId = ref(null);
const deleteId = ref(null);

// refs
const createModalRef = ref(null)
const editModalRef   = ref(null)
const createFundSelectRef = ref(null)
const editFundSelectRef   = ref(null)

// state
const formErrors = ref({})
const getCategory = ref([]);
const Search_Categories = ref('')
const itemsPerPage = ref(10)
const currentPage = ref(1)
const funds = ref([])
const formData = ref({ name:'', fund_id:'', code:'' })

function openModal(){ createModal.value = true }
function closeModal(){ createModal.value = false }

async function getFunds() {
  isLoading.value = true;
  try {
    const res = await http.get("/fund/create");
    funds.value = res.data.funds || [];
  } catch (e) {
    console.error("Error loading funds:", e);
  } finally {
    isLoading.value = false;
  }
}

async function getCategories() {
  isLoading.value = true;
  try {
    const res = await http.get("/catagories/create");
    getCategory.value = res.data.data || [];
  } catch (e) {
    console.error("Error loading categories:", e);
  } finally {
    isLoading.value = false;
  }
}

async function SubmitCatagories() {
  isLoading.value = true
  formErrors.value = {}
  try {
    await http.post("/catagories", formData.value)
    toast.success("Categories created successfully!")
    await getCategories()
    closeModal()
    formData.value = { name:'', fund_id:'', code:'' }
  } catch (error) {
    if (error.response?.status === 422) {
      formErrors.value = error.response.data.errors || {}
    } else {
      toast.error(error?.response?.data?.message || 'Failed to create category.')
    }
  } finally {
    isLoading.value = false
  }
}

function initSelect2Create () {
  if (!createFundSelectRef.value || !createModalRef.value) return
  const $el = $(createFundSelectRef.value)
  if ($el.data('select2')) { $el.off('change.select2'); $el.select2('destroy') }
  const selectData = (funds.value || []).map(f => ({ id:String(f.id), text:f.fund }))
  $el.select2({
    width:'100%',
    placeholder:'Select or search fund',
    allowClear:true,
    minimumResultsForSearch:0,
    data: selectData,
    dropdownParent: $(createModalRef.value).find('.modal-card'),
  })
  $el.on('change.select2', function(){ formData.value.fund_id = this.value || '' })
  $el.val(formData.value.fund_id ? String(formData.value.fund_id) : '').trigger('change')
}

function initSelect2Edit () {
  if (!editFundSelectRef.value || !editModalRef.value) return
  const $el = $(editFundSelectRef.value)
  if ($el.data('select2')) { $el.off('change.select2'); $el.select2('destroy') }
  const selectData = (funds.value || []).map(f => ({ id:String(f.id), text:f.fund }))
  $el.select2({
    width:'100%',
    placeholder:'Select or search fund',
    allowClear:true,
    minimumResultsForSearch:0,
    data: selectData,
    dropdownParent: $(editModalRef.value).find('.modal-card'),
  })
  $el.on('change.select2', function(){ formData.value.fund_id = this.value || '' })
  $el.val(formData.value.fund_id ? String(formData.value.fund_id) : '').trigger('change')
}

async function editModeData(category){
  if(!category) return
  editId.value = category.id ?? ''
  formData.value = {
    name: category.name ?? '',
    code: category.code ?? '',
    fund_id: category.fund_id ?? category.fund?.id ?? ''
  }
  if(!Array.isArray(funds.value) || funds.value.length===0){ await getFunds() }
  editModel.value = true
  await nextTick()
  initSelect2Edit()
}

const updateFund = async () => {
  isLoading.value = true
  try {
    const payload = {
      name: (formData.value.name || '').trim(),
      code: (formData.value.code || '').trim(),
      fund_id: formData.value.fund_id ? Number(formData.value.fund_id) : null
    }
    const { data } = await http.put(`/catagories/${editId.value}`, payload)
    const updatedFromApi = data?.data ?? data
    const idx = getCategory.value.findIndex(c => c.id === editId.value)
    if (idx !== -1) {
      const fundObj = funds.value.find(f => String(f.id) === String(payload.fund_id))
      getCategory.value[idx] = {
        ...getCategory.value[idx],
        name: payload.name,
        code: payload.code,
        fund_id: payload.fund_id,
        fund: fundObj ? fundObj : (updatedFromApi?.fund ?? getCategory.value[idx].fund)
      }
    } else {
      await getCategories()
    }
    toast.success('Category updated successfully!')
    editModel.value = false
    editId.value = null
    formData.value = { name:'', fund_id:'', code:'' }
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to update category.')
  } finally {
    isLoading.value = false
  }
}

const DeleteModeData = (id) => { deleteId.value = id; deleteModel.value = true }
const confirmDelete = async () => {
  isLoading.value = true
  try {
    const res = await http.delete(`/catagories/${deleteId.value}`)
    getCategory.value = getCategory.value.filter(c => c.id !== deleteId.value)
    toast.success(res.data.message || "Category deleted successfully!")
    deleteModel.value = false
    deleteId.value = null
  } catch (error) {
    toast.error(error?.response?.data?.message || "Failed to delete category.")
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await getCategories()
  await getFunds()
  await nextTick()
})

// watchers for select2 lifecycle
watch(createModal, async (open) => {
  if (open) { await nextTick(); initSelect2Create() }
  else if (createFundSelectRef.value) {
    const $el = $(createFundSelectRef.value)
    if ($el.data('select2')) { $el.off('change.select2'); $el.select2('destroy') }
  }
})
watch(editModel, async (open) => {
  if (open) { await nextTick(); initSelect2Edit() }
  else if (editFundSelectRef.value) {
    const $el = $(editFundSelectRef.value)
    if ($el.data('select2')) { $el.off('change.select2'); $el.select2('destroy') }
  }
})
watch(funds, async () => {
  if (createModal.value) { await nextTick(); initSelect2Create() }
  if (editModel.value)   { await nextTick(); initSelect2Edit() }
})

// filters & pagination
const filteredCategories = computed(() => {
  const list = getCategory.value || []
  const q = Search_Categories.value?.toLowerCase().trim() || ''
  if (!q) return list
  return list.filter(c => {
    const code = (c?.code || '').toString().toLowerCase()
    const name = (c?.name || '').toLowerCase()
    const fund = (c?.fund?.fund || '').toLowerCase()
    return code.includes(q) || name.includes(q) || fund.includes(q)
  })
})
const visibleCategories = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredCategories.value.slice(start, start + itemsPerPage.value)
})
const totalPages = computed(() => Math.max(1, Math.ceil((filteredCategories.value.length || 0) / itemsPerPage.value)))
const visiblePages = computed(() => {
  const pages = []; const maxVisible=5; const total=totalPages.value; const current=currentPage.value
  if(total<=maxVisible){ for(let i=1;i<=total;i++) pages.push(i) }
  else{ let start=Math.max(1,current-2); let end=Math.min(total,start+maxVisible-1)
        if(end-start<maxVisible-1) start=Math.max(1,end-maxVisible+1)
        for(let i=start;i<=end;i++) pages.push(i) }
  return pages
})
const showingInfo = computed(() => {
  const total = filteredCategories.value.length
  if (total === 0) return { start: 0, end: 0, total: 0 }
  const start = (currentPage.value - 1) * itemsPerPage.value + 1
  const end = Math.min(currentPage.value * itemsPerPage.value, total)
  return { start, end, total }
})
watch(Search_Categories, () => (currentPage.value = 1))
watch(itemsPerPage, () => (currentPage.value = 1))
const goToPage = (page) => { if(page>=1 && page<=totalPages.value) currentPage.value = page }
const previousPage = () => { if(currentPage.value>1) currentPage.value-- }
const nextPage = () => { if(currentPage.value<totalPages.value) currentPage.value++ }
</script>

<style scoped>
.my-toast {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
  color: #fff !important;
  font-weight: bold;
  border-radius: 8px;
}

/* ===== Reusable modal design (matches your confirm modal) ===== */
.modal-backdrop{
  position: fixed; inset: 0;
  background: rgba(0,0,0,.5);
  display: grid; place-items: center;
  padding: 16px;
  z-index: 9999;
}
.modal-card{
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 15px 40px rgba(0,0,0,.25);
}
.modal-header{
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 1px solid #eef0f3;
  background: linear-gradient(135deg, #f8f9ff, #eef2ff);
}
.modal-header.danger{
  color:#fff;
  background: linear-gradient(135deg, #ef4444, #b91c1c);
  border-bottom-color: transparent;
}
.modal-body{ padding: 16px; }
.modal-footer{
  display: flex; gap: 10px; justify-content: flex-end;
  padding: 12px 16px; border-top: 1px solid #eef0f3;
}

/* Subtle pop-in animation */
@keyframes pop {
  0% { transform: scale(.96); opacity: 0; }
  100%{ transform: scale(1); opacity: 1; }
}
.animate-pop{ animation: pop .15s ease-out; }

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

/* === SMX table look (same as your other page) === */
.table-scroll{
  max-height:65vh;
  overflow:auto;
  border:1px solid rgba(0,0,0,.08);
  border-radius:.5rem;
}

/* Purple header */
.p_thead th{
  background:#7367f0 !important;
  color:#fff !important;
  text-transform:uppercase;
  font-size:.8125rem;
  letter-spacing:.2px;
  position:sticky;
  top:0;
  z-index:2;
}
.p_table th, .p_table td{
  vertical-align:middle;
  padding:.65rem .8rem;
  white-space:nowrap;
  border-bottom:1px solid #eef0f4;
}

.p_table tbody tr:nth-child(odd){ background:#fcfcff; }
.p_table tbody tr:hover{ background:#f6f7ff; }

.p_actions{ display:flex; align-items:center; gap:.5rem; }
.btn{
  display:inline-flex; align-items:center; justify-content:center; gap:6px;
  height:34px; padding:0 10px; border-radius:8px; border:1px solid transparent;
  font-weight:600; cursor:pointer; background:#f3f4f6; color:#111827;
  transition:transform .12s ease, filter .12s ease, background .12s ease;
}
.btn:hover{ background:#e5e7eb; }
.btn-sm{ height:32px; padding:0 10px; font-size:.875rem; border-radius:8px; }
.btn-primary{ background:#7367f0; color:#fff; box-shadow:0 6px 14px rgba(115,103,240,.25); }
.btn-primary:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-danger{ background:#ef4444; color:#fff; }
.btn-danger:hover{ filter:brightness(1.05); transform:translateY(-1px); }
</style>
