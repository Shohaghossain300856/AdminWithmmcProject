<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card-body">
      <div class="d-flex align-items-center mb-5">
        <h5 class="card-header ms-2 mb-0">Item List</h5>
        <a target="_blank" href="/backend/subcategoriespdf" class="btn btn-primary ms-auto" style="color:white;">
          <i class="fa fa-print me-2"></i>
          Print All Data
        </a>
      </div>
    </div>

    <Loading :active="isLoading" :is-full-page="true" />

    <div class="card">
      <div class="card-datatable text-nowrap">

        <div class="row m-2">
          <div class="col-sm-6 col-md-9 mt-1">
            <div class="dataTables_filter">
              <label>
                Search
                <input
                type="search"
                class="form-control"
                placeholder="Search by  \\  all data"
                v-model.trim="searchQuery"
                />
              </label>
            </div>
          </div>

          <div class="col-sm-6 col-md-3 mt-5">
            <div class="text-end dataTables_length d-flex gap-2 justify-content-end">
              <label class="mt-2">Show</label>
              <select v-model.number="itemsPerPage" class="form-select" style="max-width: 90px">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              <label class="mt-2">entries</label>
            </div>
          </div>
        </div>

        <!-- Scrollable table wrapper -->
        <div class="table-scroll">
          <table class="table" id="datatable">
            <thead>
              <tr style="background: #7367f0;">
                <th>Sl</th>
                <th>Memo No</th>
                <th>Fund</th>
                <th>Sub Categories</th>
                <th>Budget</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>
              <tr
              v-for="(item, idx) in paginatedRows"
              :key="item.id"
              :data-row-id="item.id"
              :class="{'row-highlight': highlightedRowId === item.id}"
              >
              <td>{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
              <td>{{ item.memo_no }}</td>
              <td>{{ item?.fund?.fund }}</td>
              <td>{{ item?.category?.name }}</td>
              <td>{{ item.budget }}</td>
              <td>{{ item.balance }}</td>

              <!-- Status Button -->
              <td>
                <button
                @click="openConfirm(item)"
                :class="['btn', item.status === 'active' ? 'btn-primary' : 'btn-warning']"
                :disabled="submitting">

                <i class="fas fa-sync-alt me-1"></i>
                <span v-if="submitting && pendingRowId === item.id">Loading...</span>
                <span v-else>{{ item.status }}</span>
              </button>
            </td>

            <td>{{ item.date }}</td>

            <!-- Delete Button -->
            <!-- Delete Button -->
            <td>
              <button
              class="btn btn-danger"
              @click="openDeleteConfirm(item)"
              :disabled="deletingId === item.id"
              title="Delete this row"
              >
              <i v-if="deletingId !== item.id" class="fas fa-trash"></i>
              <i v-else class="fas fa-spinner fa-spin"></i>
            </button>
          </td>

        </tr>

        <tr v-if="!isLoading && paginatedRows.length === 0">
          <td colspan="9" class="text-center">No data found</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="d-flex justify-content-between align-items-center p-3">
    <div>
      Showing
      <strong>{{ startItem + 1 }}</strong> to
      <strong>{{ endItem }}</strong> of
      <strong>{{ filteredRows.length }}</strong> entries
    </div>

    <nav>
      <ul class="pagination mb-0">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="goPrev" :disabled="currentPage === 1">Previous</button>
        </li>
        <li
        v-for="n in pageNumbers"
        :key="n"
        class="page-item"
        :class="{ active: currentPage === n }"
        >
        <button class="page-link" @click="goTo(n)">{{ n }}</button>
      </li>
      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
        <button class="page-link" @click="goNext" :disabled="currentPage === totalPages">Next</button>
      </li>
    </ul>
  </nav>
</div>
<!-- /Pagination -->
</div>
</div>

<!-- ===== Confirm Modal (Vue-only) ===== -->
<div v-if="showModal" class="modal-backdrop" @click.self="cancelChange">
  <div class="modal-card">
    <div class="modal-header">
      <h5 class="m-0">Confirm Status Change</h5>
      <button class="btn-close" @click="cancelChange" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      <p class="mb-1">
        Are you sure you want to change status for
        <strong>#{{ targetRow?.memo_no || targetRow?.id }}</strong>?
      </p>
      <p class="mb-0">
        <strong>{{ targetRow?.status }}</strong> → <strong>{{ nextStatus }}</strong>
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" @click="cancelChange" :disabled="submitting">Cancel</button>
      <button class="btn btn-primary" @click="confirmChange" :disabled="submitting">
        <span v-if="submitting"><i class="fas fa-spinner fa-spin me-2"></i>Updating...</span>
        <span v-else>Confirm</span>
      </button>
    </div>
  </div>
</div>
<!-- ===== /Confirm Modal ===== -->



<!-- ===== Delete Confirm Modal (Vue-only) ===== -->
<div v-if="showDeleteModal" class="modal-backdrop" @click.self="cancelDelete">
  <div class="modal-card">
    <div class="modal-header">
      <h5 class="m-0 text-danger">
        <i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete
      </h5>
      <button class="btn-close" @click="cancelDelete" aria-label="Close"></button>
    </div>

    <div class="modal-body">
      <p class="mb-1">
        Are you sure you want to <strong class="text-danger">permanently delete</strong>
        <strong>#{{ deleteTarget?.memo_no || deleteTarget?.id }}</strong>?
      </p>
      <p class="mb-0">
        This action cannot be undone.
      </p>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" @click="cancelDelete" :disabled="!!deletingId">Cancel</button>
      <button class="btn btn-danger" @click="confirmDelete" :disabled="!!deletingId">
        <span v-if="!!deletingId"><i class="fas fa-spinner fa-spin me-2"></i>Deleting...</span>
        <span v-else><i class="fas fa-trash me-2"></i>Delete</span>
      </button>
    </div>
  </div>
</div>
<!-- ===== /Delete Confirm Modal ===== -->


</div>
</template>

<script setup>
  import { ref, onMounted, getCurrentInstance, computed, watch, nextTick } from 'vue'
  import { useToast } from 'vue-toastification'

  const { appContext } = getCurrentInstance()
  const http = appContext.config.globalProperties.$http
  const toast = useToast()

  const isLoading = ref(false)
  const submitting = ref(false)

  const searchQuery   = ref('')
  const itemsPerPage  = ref(10)
  const currentPage   = ref(1)
  const subcatagoriesData = ref([])

  const pendingRowId = ref(null)      
  const highlightedRowId = ref(null)  

// ===== status Modal code =====
const showModal = ref(false)
const targetRow = ref(null)
const nextStatus = ref('')

// ===== delete  Modal code =====  
const showDeleteModal = ref(false)
const deleteTarget = ref(null)
const deletingId = ref(null)

// Data fetch
async function getSubCatagoriesData() {
  isLoading.value = true
  try {
    const res = await http.get('/sub-Catagories-list/create')
    subcatagoriesData.value = res.data?.data ?? []
  } catch (e) {
    toast.error('Failed to load subcategories')
  } finally {
    isLoading.value = false
  }
}

// Status button → open confirm modal
function openConfirm(row) {
  targetRow.value = row
  nextStatus.value = row.status === 'active' ? 'panding' : 'active' 
  showModal.value = true
}

// Cancel modal
function cancelChange() {
  if (submitting.value) return
    showModal.value = false
  targetRow.value = null
  nextStatus.value = ''
}

// Confirm modal → call API
async function confirmChange() {
  if (!targetRow.value) return
    submitting.value = true
  pendingRowId.value = targetRow.value.id
  try {
    const res = await http.put(`/sub-Catagories-list/${targetRow.value.id}`, { status: nextStatus.value })
    toast.success(res?.data?.message || 'Status updated')

    // === লোকাল ডাটা আপডেট ===
    const idx = subcatagoriesData.value.findIndex(r => r.id === targetRow.value.id)
    if (idx !== -1) {
      subcatagoriesData.value[idx] = {
        ...subcatagoriesData.value[idx],
        status: nextStatus.value
      }
    }
    showModal.value = false
    await nextTick()
    highlightAndScrollRow(targetRow.value.id)

  } catch (e) {
    toast.error('Failed to update status')
  } finally {
    submitting.value = false
    pendingRowId.value = null
    targetRow.value = null
    nextStatus.value = ''
  }
}

// === Delete modal state ===
function openDeleteConfirm(row) {
  deleteTarget.value = row
  showDeleteModal.value = true
}

// Delete modal বন্ধ করবে
function cancelDelete() {
  if (deletingId.value) return // ডিলিট চললে বন্ধ কোরো না
    showDeleteModal.value = false
  deleteTarget.value = null
}

// ডিলিট কনফার্ম
async function confirmDelete() {
  if (!deleteTarget.value) return
    const id = deleteTarget.value.id
  deletingId.value = id
  try {
    const res = await http.delete(`/subCatagories/${id}`)
    toast.success(res?.data?.message || 'Deleted successfully')
    subcatagoriesData.value = subcatagoriesData.value.filter(r => r.id !== id)
    await nextTick()
    const totalAfter = filteredRows.value.length
    const firstIndex = (currentPage.value - 1) * itemsPerPage.value
    if (totalAfter > 0 && firstIndex >= totalAfter) {
      currentPage.value = Math.max(1, currentPage.value - 1)
    }

    showDeleteModal.value = false

  } catch (e) {
    const msg =
    e?.response?.data?.message ||
    e?.message ||
    'Failed to delete'
    toast.error(msg)
  } finally {
    deletingId.value = null
    deleteTarget.value = null
  }
}



function highlightAndScrollRow(id) {
  const rowEl = document.querySelector(`[data-row-id="${id}"]`)
  const scrollBox = document.querySelector('.table-scroll')
  if (rowEl) {
    rowEl.scrollIntoView({ behavior: 'smooth', block: 'center' })
    highlightedRowId.value = id
    setTimeout(() => (highlightedRowId.value = null), 1800)
  } else if (scrollBox) {
    scrollBox.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

onMounted(getSubCatagoriesData)

// ===== Filter & Pagination =====
const filteredRows = computed(() => {
  const q = (searchQuery.value || '').toLowerCase()
  if (!q) return subcatagoriesData.value

    return subcatagoriesData.value.filter((it) => {
      const memo = String(it?.memo_no ?? '').toLowerCase()
      const fund = String(it?.fund?.fund ?? '').toLowerCase()
      const cat  = String(it?.category?.name ?? '').toLowerCase()
      const date = String(it?.date ?? '').toLowerCase()
      const budget = String(it?.budget ?? '').toLowerCase()
      const balance = String(it?.balance ?? '').toLowerCase()

      return (
        memo.includes(q) ||
        fund.includes(q) ||
        cat.includes(q) ||
        date.includes(q) ||
        budget.includes(q) ||
        balance.includes(q)
        )
    })
})

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredRows.value.length / itemsPerPage.value))
  )

const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredRows.value.slice(start, end)
})

const startItem = computed(() => (currentPage.value - 1) * itemsPerPage.value)
const endItem = computed(() =>
  Math.min(startItem.value + paginatedRows.value.length, filteredRows.value.length)
  )

const pageNumbers = computed(() => Array.from({ length: totalPages.value }, (_, i) => i + 1))

function goTo(n) {
  if (n >= 1 && n <= totalPages.value) currentPage.value = n
}
function goPrev() {
  if (currentPage.value > 1) currentPage.value--
}
function goNext() {
  if (currentPage.value < totalPages.value) currentPage.value++
}
watch([searchQuery, itemsPerPage], () => {
  currentPage.value = 1
})
</script>

<style scoped>
  .table-scroll {
    max-height: 65vh;
    overflow-y: auto;
    border-radius: 0.5rem;
    border: 1px solid rgba(0,0,0,0.08);
  }

/* রো হাইলাইট অ্যানিমেশন */
@keyframes flashHighlight {
  0%   { background-color: rgba(255, 235, 59, 0.35); }
  100% { background-color: transparent; }
}
.row-highlight {
  animation: flashHighlight 1.4s ease-out 1;
}

/* ===== Simple modal (no Bootstrap JS needed) ===== */
.modal-backdrop {
  background: rgba(15, 18, 30, 0.55);
  z-index: 9999;
}
.modal-card {
  width: min(520px, 92vw);
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.25);
  overflow: hidden;
}
.modal-header,
.modal-footer {
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.modal-header {
  justify-content: space-between;
  border-bottom: 1px solid #f0f0f0;
}
.modal-body {
  padding: 16px;
}
.btn-close {
  border: none;
  background: transparent;
  font-size: 1.25rem;
  line-height: 1;
}


.table th {
  text-transform: uppercase;
  font-size: 0.8125rem;
  letter-spacing: 0.2px;
  color: white;
}
</style>
