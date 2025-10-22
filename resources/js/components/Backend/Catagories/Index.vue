<!-- resources/js/components/Backend/Subcategories/index.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header (supplier/fund style) -->
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-2 flex-wrap">
        <h5 class="mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-tags me-2"></i> Categories Management
        </h5>

        <!-- Count -->
        <span class="badge bg-primary ms-2">
          {{ filteredCategories.length }} Items
        </span>

        <!-- Right controls -->
        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
          <!-- Search -->
          <div class="input-group input-group-sm w-auto">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input
              v-model.trim="Search_Categories"
              class="form-control"
              placeholder="Search code or name…"
            />
          </div>

          <!-- Per page -->
          <select v-model.number="itemsPerPage" class="form-select form-select-sm w-auto">
            <option :value="10">10 / page</option>
            <option :value="25">25 / page</option>
            <option :value="50">50 / page</option>
            <option :value="100">100 / page</option>
          </select>

          <!-- Refresh -->
          <button class="btn btn-sm btn-outline-primary" @click="getCategories" :disabled="isLoading">
            <i :class="['fa', isLoading ? 'fa-spinner fa-spin' : 'fa-rotate']"></i>
            <span class="ms-1">{{ isLoading ? 'Loading…' : 'Refresh' }}</span>
          </button>

          <!-- Add New -->
          <button @click="openModal" type="button" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-2"></i>Add New
          </button>
        </div>
      </div>
    </div>

    <!-- Global Loader -->
    <Loading :active="isLoading" :is-full-page="true" />

    <div class="card" :class="{ p_loading: isLoading }">
      <div class="card-datatable text-nowrap">
        <!-- ===== Table (supplier/fund style) ===== -->
        <div class="table-scroll">
          <table class="table table-hover align-middle mb-0 subcat-table">
            <thead class="table-head">
              <tr>
                <th style="width:70px">Sl</th>
                <th class="sortable">Code <i class="fa fa-sort ms-1"></i></th>
                <th class="sortable">Category <i class="fa fa-sort ms-1"></i></th>
                <th class="text-end" style="width:180px;">Action</th>
              </tr>
            </thead>

            <tbody>
              <!-- Inline loader fallback (kept) -->
              <tr v-if="isLoading">
                <td colspan="4" class="py-3">
                  <div class="skeleton-line"></div>
                </td>
              </tr>

              <!-- Rows -->
              <tr v-for="(category, index) in visibleCategories" :key="category.id" v-else>
                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td>{{ category.code || '—' }}</td>
                <td>{{ category.name || '—' }}</td>
                <td class="text-end">
                  <button @click="editModeData(category)" class="btn btn-sm btn-primary me-2" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="DeleteModeData(category.id)" class="btn btn-sm btn-danger" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>

              <!-- No matches -->
              <tr v-if="!isLoading && getCategory.length > 0 && filteredCategories.length === 0">
                <td colspan="4" class="text-center text-muted py-4">
                  No categories found matching your search criteria.
                </td>
              </tr>

              <!-- No data -->
              <tr v-if="getCategory.length === 0 && !isLoading">
                <td colspan="4" class="text-center text-muted py-4">
                  No categories found. Please add some categories.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ===== Pagination (compact footer like supplier/fund) ===== -->
        <div class="card-footer d-flex align-items-center justify-content-between" v-if="!isLoading && filteredCategories.length">
          <div class="text-muted small">
            Showing {{ showingInfo.start }}–{{ showingInfo.end }} of {{ showingInfo.total }} items
            <span v-if="Search_Categories.trim() && filteredCategories.length !== getCategory.length">
              (filtered from {{ getCategory.length }} total)
            </span>
          </div>

          <div class="d-flex align-items-center gap-2">
            <select v-model.number="itemsPerPage" class="form-select form-select-sm w-auto">
              <option :value="10">10 / page</option>
              <option :value="25">25 / page</option>
              <option :value="50">50 / page</option>
              <option :value="100">100 / page</option>
            </select>

            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-secondary" :disabled="currentPage === 1" @click="previousPage">
                <i class="fa fa-chevron-left"></i>
              </button>

              <!-- first + left ellipsis -->
              <button
                v-if="visiblePages[0] > 1"
                class="btn btn-outline-secondary"
                @click="goToPage(1)"
              >1</button>
              <button v-if="visiblePages[0] > 2" class="btn btn-outline-secondary" disabled>…</button>

              <!-- windowed pages -->
              <button
                v-for="p in visiblePages"
                :key="p"
                class="btn"
                :class="p === currentPage ? 'btn-primary' : 'btn-outline-secondary'"
                @click="goToPage(p)"
              >{{ p }}</button>

              <!-- right ellipsis + last -->
              <button
                v-if="visiblePages[visiblePages.length - 1] < totalPages - 1"
                class="btn btn-outline-secondary"
                disabled
              >…</button>
              <button
                v-if="visiblePages[visiblePages.length - 1] < totalPages"
                class="btn btn-outline-secondary"
                @click="goToPage(totalPages)"
              >{{ totalPages }}</button>

              <button class="btn btn-outline-secondary" :disabled="currentPage === totalPages" @click="nextPage">
                <i class="fa fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
        <!-- /Pagination -->
      </div>
    </div>
  </div>

  <!-- ===== CREATE MODAL (supplier/fund style) ===== -->
  <div
    v-if="createModal"
    ref="createModalRef"
    class="modal-backdrop"
    @click.self="closeModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createTitle"
  >
    <div class="modal-card">
      <div class="modal-header">
        <h5 id="createTitle" class="m-0"><i class="fa fa-plus me-2"></i> Add New Category</h5>
        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="SubmitCatagories">
          <div class="form-row">
            <label for="catCode" class="form-label">Category Code</label>
            <input id="catCode" type="text" v-model="formData.code" class="form-input" placeholder="Enter code" />
            <small v-if="formErrors.code" class="text-danger">{{ formErrors.code[0] }}</small>
          </div>
          <div class="form-row">
            <label for="catName" class="form-label">Category Name</label>
            <input id="catName" type="text" v-model="formData.name" class="form-input" placeholder="Enter category name" />
            <small v-if="formErrors.name" class="text-danger">{{ formErrors.name[0] }}</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" @click="closeModal" :disabled="isLoading">Close</button>
        <button type="button" class="btn btn-primary" @click="SubmitCatagories" :disabled="isLoading">
          <i class="fa" :class="isLoading ? 'fa-spinner fa-spin' : 'fa-check'"></i>
          <span class="ms-2">{{ isLoading ? 'Submitting…' : 'Submit' }}</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ===== EDIT MODAL (supplier/fund style) ===== -->
  <div
    v-if="editModel"
    ref="editModalRef"
    class="modal-backdrop"
    @click.self="editModel = false"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editTitle"
  >
    <div class="modal-card">
      <div class="modal-header">
        <h5 id="editTitle" class="m-0"><i class="fa fa-edit me-2"></i> Edit Category</h5>
        <button type="button" class="btn-close" @click="editModel = false" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="updateCategory">
          <div class="form-row">
            <label for="editCatCode" class="form-label">Category Code</label>
            <input id="editCatCode" type="text" v-model="formData.code" class="form-input" placeholder="Enter code" />
          </div>
          <div class="form-row">
            <label class="form-label">Category Name</label>
            <input type="text" class="form-input" v-model="formData.name" placeholder="Name" required />
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" @click="editModel = false" :disabled="isLoading">Cancel</button>
        <button type="button" class="btn btn-primary" @click="updateCategory" :disabled="isLoading">
          <i class="fa" :class="isLoading ? 'fa-spinner fa-spin' : 'fa-check'"></i>
          <span class="ms-2">{{ isLoading ? 'Submitting…' : 'Submit' }}</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ===== DELETE MODAL (supplier/fund style with confirm checkbox) ===== -->
  <div
    v-if="deleteModel"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="deleteTitle"
    @keydown.esc="deleteModel = false"
  >
    <div class="modal-card">
      <div class="modal-header danger">
        <h5 id="deleteTitle" class="m-0">
          <i class="fa fa-exclamation-triangle me-2"></i> Confirm Delete
        </h5>
        <button @click="deleteModel = false" type="button" class="btn-close" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">
          You’re about to permanently delete this item.
        </p>
        <div class="form-check mt-2">
          <input
            id="confirmDeleteCat"
            class="form-check-input"
            type="checkbox"
            v-model="confirmDeleteChecked"
          />
          <label class="form-check-label" for="confirmDeleteCat">
            I understand this will permanently delete the record.
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" @click="deleteModel = false" class="btn btn-ghost">Cancel</button>
        <button
          type="button"
          class="btn btn-danger"
          :disabled="!confirmDeleteChecked || isLoading"
          @click="confirmDelete()"
        >
          <i class="fa" :class="isLoading ? 'fa-spinner fa-spin' : 'fa-trash'"></i>
          <span class="ms-2">{{ isLoading ? 'Deleting…' : 'Delete Item' }}</span>
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

// modals
const createModal = ref(false);
const editModel = ref(false);
const deleteModel = ref(false);
const editId = ref(null);
const deleteId = ref(null);

// confirm checkbox (UI-only; no API change)
const confirmDeleteChecked = ref(false);

// refs
const createModalRef = ref(null)
const editModalRef   = ref(null)

// state
const formErrors = ref({})
const getCategory = ref([]);
const Search_Categories = ref('')
const itemsPerPage = ref(10)
const currentPage = ref(1)

// form
const formData = ref({ name:'', code:'' })

function openModal(){ createModal.value = true }
function closeModal(){ createModal.value = false }

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
    await http.post("/catagories", {
      name: (formData.value.name || '').trim(),
      code: (formData.value.code || '').trim()
    })
    toast.success(" category created successfully!")
    await getCategories()
    closeModal()
    formData.value = { name:'', code:'' }
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

async function editModeData(category){
  if(!category) return
  editId.value = category.id ?? ''
  formData.value = {
    name: category.name ?? '',
    code: category.code ?? ''
  }
  editModel.value = true
  await nextTick()
}

const updateCategory = async () => {
  isLoading.value = true
  try {
    const payload = {
      name: (formData.value.name || '').trim(),
      code: (formData.value.code || '').trim()
    }
    const { data } = await http.put(`/catagories/${editId.value}`, payload)
    const updatedFromApi = data?.data ?? data
    const idx = getCategory.value.findIndex(c => c.id === editId.value)
    if (idx !== -1) {
      getCategory.value[idx] = { ...getCategory.value[idx], name: payload.name, code: payload.code }
    } else if (updatedFromApi?.id) {
      const i2 = getCategory.value.findIndex(c => c.id === updatedFromApi.id)
      if (i2 !== -1) getCategory.value[i2] = updatedFromApi
      else await getCategories()
    } else {
      await getCategories()
    }
    toast.success(' category updated successfully!')
    editModel.value = false
    editId.value = null
    formData.value = { name:'', code:'' }
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to update category.')
  } finally {
    isLoading.value = false
  }
}

const DeleteModeData = (id) => {
  deleteId.value = id
  confirmDeleteChecked.value = false
  deleteModel.value = true
}

const confirmDelete = async () => {
  isLoading.value = true
  try {
    const res = await http.delete(`/catagories/${deleteId.value}`)
    getCategory.value = getCategory.value.filter(c => c.id !== deleteId.value)
    toast.success(res?.data?.message ?? "✅ Category deleted successfully!")
    deleteModel.value = false
    deleteId.value = null
  } catch (error) {
    const errMsg = error?.response?.data?.message || error?.message || "❌ Something went wrong while deleting."
    const cleanMsg = errMsg.length > 100 ? errMsg.slice(0, 100) + "..." : errMsg
    toast.error(cleanMsg)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await getCategories()
  await nextTick()
})

// filters & pagination (unchanged)
const filteredCategories = computed(() => {
  const list = getCategory.value || []
  const q = Search_Categories.value?.toLowerCase().trim() || ''
  if (!q) return list
  return list.filter(c => {
    const code = (c?.code || '').toString().toLowerCase()
    const name = (c?.name || '').toLowerCase()
    return code.includes(q) || name.includes(q)
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
/* Loading state + skeleton (like fund) */
.p_loading{ opacity:.6; pointer-events:none; }
.skeleton-line{
  height:10px; width:100%; background:linear-gradient(90deg,#eee,#f7f7f7,#eee);
  border-radius:6px; animation:pulse 1.2s infinite;
}
@keyframes pulse{ 0%{opacity:.6} 50%{opacity:1} 100%{opacity:.6} }

/* Table polish (supplier/fund style) */
.table-scroll { overflow-x: auto; max-width: 100%; }
.table thead th { color: #fff; position: sticky; top: 0; z-index: 1; }
.table-head { background: #7367f0; color: #fff; }
.sortable { cursor: pointer; user-select: none; }
.input-group .form-control { min-width: 260px; }
.badge { font-weight: 600; }
.subcat-table tfoot th { background: #f5f6f8; }

/* Modal system (supplier/fund style) */
.modal-backdrop{ position:fixed; inset:0; display:grid; place-items:center; background:rgba(15,18,30,.55); z-index:99999; padding:12px; }
.modal-card{ width:min(640px,96vw); background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.25); overflow:hidden; max-height:92vh; display:flex; flex-direction:column; position:relative; }
.modal-header{ position:sticky; top:0; z-index:10; background:#fff; flex:0 0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; justify-content:space-between; border-bottom:1px solid #f0f0f0; }
.modal-header.danger{ background:#fff5f5; border-bottom-color:#fecaca; }
.modal-body{ flex:1 1 auto; min-height:0; overflow:auto; padding:16px; background:#fff; }
.modal-footer{ flex:0 0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; border-top:1px solid #f0f0f0; background:#fff; }

/* Inputs (match fund style) */
.form-row{ display:flex; flex-direction:column; gap:6px; margin-bottom:12px; }
.form-label{ font-weight:700; color:#334155; margin-bottom:4px; font-size:14px; }
.form-input{ height:44px; border:1px solid #e5e7eb; border-radius:12px; padding:0 12px; outline:none; background:#fff; width:100%; }
.form-input:focus{ border-color:#7367f0; box-shadow:0 0 0 4px rgba(115,103,240,.15); }

/* Buttons (supplier/fund style) */
.btn{ display:inline-flex; align-items:center; justify-content:center; gap:6px; height:34px; padding:0 12px; border-radius:8px; border:1px solid transparent; font-weight:600; cursor:pointer; background:#f3f4f6; color:#111827; transition:transform .12s ease, filter .12s ease, background .12s ease; }
.btn:hover{ background:#e5e7eb; }
.btn:disabled{ opacity:.6; cursor:not-allowed; }
.btn-primary{ background:#7367f0; color:#fff; box-shadow:0 6px 14px rgba(115,103,240,.25); }
.btn-primary:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-danger{ background:#ef4444; color:#fff; }
.btn-danger:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-ghost{ background:transparent; border:1px solid #e5e7eb; }
.btn-ghost:hover{ background:#f8fafc; }

/* Misc */
.text-danger{ color:#ef4444; }
.form-check{ display:flex; align-items:center; gap:.5rem; }
.form-check-input{ width:1rem; height:1rem; }
.form-check-label{ user-select:none; }
</style>
