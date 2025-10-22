<!-- resources/js/components/Backend/Supplier/index.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-2 flex-wrap">
        <h5 class="mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-coins me-2"></i> Suppliers
        </h5>

        <!-- Counts -->
        <span class="badge bg-primary ms-2">
          {{ filteredRows.length }} Items
        </span>

        <!-- Search / Controls -->
        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
          <div class="input-group input-group-sm w-auto">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input
              v-model.trim="searchQuery"
              class="form-control"
              placeholder="Search supplier / phone / address…"
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
          <button class="btn btn-sm btn-outline-primary" @click="refresh">
            <i class="fa fa-rotate"></i>
            <span class="ms-1">Refresh</span>
          </button>

          <!-- Add New -->
          <button type="button" @click="openCreate" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-2"></i>Add New
          </button>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div :class="{ p_loading: isLoading }" class="card">
      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table class="table table-hover align-middle mb-0 subcat-table">
            <thead class="table-head">
              <tr>
                <th style="width:70px">Sl</th>

                <th class="sortable" @click="toggleSort('supplier')">
                  <span class="d-inline-flex align-items-center">
                    Supplier
                    <i class="fa ms-1" :class="sortIcon('supplier')"></i>
                  </span>
                </th>

                <th class="sortable" style="width:180px" @click="toggleSort('phone')">
                  <span class="d-inline-flex align-items-center">
                    Phone
                    <i class="fa ms-1" :class="sortIcon('phone')"></i>
                  </span>
                </th>

                <th class="sortable" @click="toggleSort('address')">
                  <span class="d-inline-flex align-items-center">
                    Address
                    <i class="fa ms-1" :class="sortIcon('address')"></i>
                  </span>
                </th>

                <th class="text-end" style="width:180px">Action</th>
              </tr>
            </thead>

            <tbody>
              <!-- Data rows -->
              <tr v-for="(row, i) in paginatedRows" :key="row.id ?? `tmp-${i}`">
                <td>{{ serialNumber(i) }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-medium">{{ displaySupplier(row) }}</span>
                  </div>
                </td>
                <td>{{ row.phone || '—' }}</td>
                <td>{{ row.address || '—' }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-primary me-2" title="Edit" @click="openEdit(row)">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" title="Delete" @click="openDelete(row)">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>

              <!-- Empty state -->
              <tr v-if="!isLoading && paginatedRows.length === 0">
                <td colspan="5" class="text-center">
                  <span class="muted">No data</span>
                </td>
              </tr>

              <!-- Loading skeleton rows -->
              <tr v-if="isLoading" v-for="n in 4" :key="'sk-'+n">
                <td colspan="5" class="py-3">
                  <div class="skeleton-line"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex align-items-center justify-content-between">
        <div class="text-muted small">
          Showing {{ showingStart }}–{{ showingEnd }} of {{ filteredRows.length }} items
          <span v-if="filteredRows.length !== safeRows.length" class="text-muted">
            (filtered from {{ safeRows.length }} total)
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
            <button class="btn btn-outline-secondary" :disabled="currentPage===1" @click="prevPage">
              <i class="fa fa-chevron-left"></i>
            </button>

            <button
              v-for="p in pageButtons"
              :key="'p'+p"
              class="btn"
              :class="p === currentPage ? 'btn-primary' : 'btn-outline-secondary'"
              @click="goToPage(p)"
            >{{ p }}</button>

            <button class="btn btn-outline-secondary" :disabled="currentPage===pageCount" @click="nextPage">
              <i class="fa fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
      <!-- /Pagination -->
    </div>

    <!-- ===== CREATE MODAL ===== -->
    <div v-if="createOpen" class="modal-backdrop" @click.self="closeCreate">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-plus me-2"></i> Add Supplier</h5>
          <button type="button" class="btn-close" @click="closeCreate" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">Supplier Name</label>
            <input v-model.trim="form.supplier" class="form-input" type="text" placeholder="e.g. ABC Medical" />
          </div>
          <div class="form-row">
            <label class="form-label">Phone Number</label>
            <input v-model.trim="form.phone" class="form-input" type="text" placeholder="e.g. 01xxxxxxxxx" />
          </div>
          <div class="form-row">
            <label class="form-label">Address</label>
            <input v-model.trim="form.address" class="form-input" type="text" placeholder="e.g. Rangpur, Bangladesh" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-ghost" @click="closeCreate">Close</button>
          <button class="btn btn-primary" :disabled="submitting" @click="createSupplier">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span class="ms-2">{{ submitting ? 'Saving…' : 'Create' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ===== EDIT MODAL ===== -->
    <div v-if="editOpen" class="modal-backdrop" @click.self="closeEdit">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-edit me-2"></i> Edit Supplier</h5>
          <button type="button" class="btn-close" @click="closeEdit" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">Supplier Name</label>
            <input v-model.trim="editForm.supplier" class="form-input" type="text" />
          </div>
          <div class="form-row">
            <label class="form-label">Phone Number</label>
            <input v-model.trim="editForm.phone" class="form-input" type="text" />
          </div>
          <div class="form-row">
            <label class="form-label">Address</label>
            <input v-model.trim="editForm.address" class="form-input" type="text" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-ghost" @click="closeEdit">Cancel</button>
          <button class="btn btn-primary" :disabled="submitting" @click="updateSupplier">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span class="ms-2">{{ submitting ? 'Saving…' : 'Update' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ===== DELETE MODAL (improved) ===== -->
    <div v-if="deleteOpen" class="modal-backdrop" @click.self="closeDelete">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header danger">
          <h5 class="m-0"><i class="fa fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
          <button type="button" class="btn-close" @click="closeDelete" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">
            You are about to delete
            <b class="text-danger">{{ (deleteRow && (deleteRow.supplier || deleteRow.name)) || 'this supplier' }}</b>.
          </p>
          <p class="muted mb-3">This action is permanent and cannot be undone.</p>

          <div class="form-check">
            <input
              id="confirmDeleteCheck"
              class="form-check-input"
              type="checkbox"
              v-model="confirmDeleteChecked"
            />
            <label class="form-check-label" for="confirmDeleteCheck">
              I understand this will permanently delete the record.
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-ghost" @click="closeDelete">Cancel</button>
          <button
            class="btn btn-danger"
            :disabled="submitting || !confirmDeleteChecked"
            @click="doDelete"
            title="Delete"
          >
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-trash'"></i>
            <span class="ms-2">{{ submitting ? 'Deleting…' : 'Delete' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance, computed, watch } from 'vue'
import { useToast } from 'vue-toastification'

const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()

const isLoading   = ref(false)
const submitting  = ref(false)
const suppliers   = ref([])

/* ===== List fetch & normalize ===== */
function normalizeApiList (payload) {
  if (!payload) return []
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload.data)) return payload.data
  if (Array.isArray(payload.list)) return payload.list
  return []
}

async function fetchSuppliers () {
  try {
    isLoading.value = true
    const res = await http.get('/Supplier/create')
    suppliers.value = normalizeApiList(res?.data)
  } catch (e) {
    console.error(e)
    suppliers.value = []
    toast.error('Failed to load suppliers')
  } finally {
    isLoading.value = false
  }
}

function refresh () {
  currentPage.value = 1
  fetchSuppliers()
}

/* ===== Safe base rows ===== */
const safeRows = computed(() => {
  const arr = Array.isArray(suppliers.value) ? suppliers.value : []
  return arr
    .filter(r => r && typeof r === 'object')
    .filter(r => (r.id != null) || r.supplier || r.name || r.phone || r.address)
})

/* ===== Search ===== */
const searchQuery  = ref('')
const filteredRows = computed(() => {
  const q = searchQuery.value?.toLowerCase() || ''
  if (!q) return safeRows.value
  return safeRows.value.filter(r => {
    const s = (r.supplier || r.name || '').toString().toLowerCase()
    const p = (r.phone || '').toString().toLowerCase()
    const a = (r.address || '').toString().toLowerCase()
    return s.includes(q) || p.includes(q) || a.includes(q)
  })
})

/* ===== Sorting ===== */
const sortKey = ref('supplier')
const sortDir = ref('asc') // 'asc' | 'desc'

function baseValue (row, key) {
  if (key === 'supplier') return (row.supplier || row.name || '').toString()
  if (key === 'phone')    return (row.phone || '').toString()
  if (key === 'address')  return (row.address || '').toString()
  return (row[key] ?? '').toString()
}

const sortedRows = computed(() => {
  const rows = [...filteredRows.value]
  const k = sortKey.value
  const d = sortDir.value
  rows.sort((a, b) => {
    const va = baseValue(a, k).toLowerCase()
    const vb = baseValue(b, k).toLowerCase()
    if (va < vb) return d === 'asc' ? -1 : 1
    if (va > vb) return d === 'asc' ? 1 : -1
    return 0
  })
  return rows
})

function toggleSort (key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDir.value = 'asc'
  }
}

function sortIcon (key) {
  if (sortKey.value !== key) return 'fa-sort'
  return sortDir.value === 'asc' ? 'fa-sort-up' : 'fa-sort-down'
}

/* ===== Pagination ===== */
const itemsPerPage = ref(10)
const currentPage  = ref(1)

watch([itemsPerPage, filteredRows], () => {
  currentPage.value = 1
})

const pageCount = computed(() => {
  const total = sortedRows.value.length
  return Math.max(1, Math.ceil(total / itemsPerPage.value))
})

const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return sortedRows.value.slice(start, start + itemsPerPage.value)
})

function goToPage (p) {
  if (p < 1 || p > pageCount.value) return
  currentPage.value = p
}
function prevPage () { goToPage(currentPage.value - 1) }
function nextPage () { goToPage(currentPage.value + 1) }

const showingStart = computed(() => (sortedRows.value.length === 0 ? 0 : (currentPage.value - 1) * itemsPerPage.value + 1))
const showingEnd   = computed(() => Math.min(currentPage.value * itemsPerPage.value, sortedRows.value.length))

// Pagination buttons (1…N with window)
const pageButtons = computed(() => {
  const total = pageCount.value
  const cur   = currentPage.value
  const span  = 2 // current ±2
  const start = Math.max(1, cur - span)
  const end   = Math.min(total, cur + span)
  const list  = []
  for (let i = start; i <= end; i++) list.push(i)
  if (!list.includes(1)) list.unshift(1)
  if (!list.includes(total)) list.push(total)
  return [...new Set(list)].sort((a,b)=>a-b)
})

/* ===== Helpers ===== */
function displaySupplier (row) {
  return (row.supplier || row.name) || '—'
}
function serialNumber (indexOnPage) {
  return (currentPage.value - 1) * itemsPerPage.value + indexOnPage + 1
}

/* ===== CREATE ===== */
const createOpen = ref(false)
const form = ref({ supplier: '', phone: '', address: '' })
function openCreate(){ createOpen.value = true }
function closeCreate(){ createOpen.value = false; form.value = { supplier:'', phone:'', address:'' } }

async function createSupplier(){
  if(!form.value.supplier?.trim()){ toast.error('Supplier name is required'); return }
  try{
    submitting.value = true
    await http.post('/Supplier', {
      supplier: form.value.supplier,
      phone: form.value.phone || null,
      address: form.value.address || null
    })
    toast.success('Created successfully')
    closeCreate()
    await fetchSuppliers()
  }catch(e){
    console.error(e)
    toast.error(e?.response?.data?.message || 'Create failed')
  }finally{
    submitting.value = false
  }
}

/* ===== EDIT ===== */
const editOpen = ref(false)
const editId   = ref(null)
const editForm = ref({ supplier: '', phone: '', address: '' })

function openEdit(row){
  editId.value = row?.id
  editForm.value = {
    supplier: row?.supplier || row?.name || '',
    phone: row?.phone || '',
    address: row?.address || ''
  }
  editOpen.value = true
}
function closeEdit(){ editOpen.value = false; editId.value = null; editForm.value = { supplier:'', phone:'', address:'' } }

async function updateSupplier(){
  if(!editId.value) return
  if(!editForm.value.supplier?.trim()){ toast.error('Supplier name is required'); return }
  try{
    submitting.value = true
    await http.put(`/Supplier/${editId.value}`, {
      supplier: editForm.value.supplier,
      phone: editForm.value.phone || null,
      address: editForm.value.address || null
    })
    toast.success('Updated successfully')
    closeEdit()
    await fetchSuppliers()
  }catch(e){
    console.error(e)
    toast.error(e?.response?.data?.message || 'Update failed')
  }finally{
    submitting.value = false
  }
}

/* ===== DELETE ===== */
const deleteOpen = ref(false)
const deleteRow  = ref(null)
const confirmDeleteChecked = ref(false)

function openDelete(row){
  deleteRow.value = row
  confirmDeleteChecked.value = false
  deleteOpen.value = true
}
function closeDelete(){
  deleteOpen.value = false
  deleteRow.value = null
  confirmDeleteChecked.value = false
}

async function doDelete(){
  if(!(deleteRow.value && deleteRow.value.id)) return
  try{
    submitting.value = true
    await http.delete(`/Supplier/${deleteRow.value.id}`)
    toast.success('Deleted')
    closeDelete()
    await fetchSuppliers()
  }catch(e){
    console.error(e)
    toast.error(e?.response?.data?.message || 'Delete failed')
  }finally{
    submitting.value = false
  }
}

onMounted(fetchSuppliers)
</script>

<style scoped>
/* Top CTA */
.p_create-btn{ background:#7367f0; border:none; box-shadow:0 6px 14px rgba(115,103,240,.3); color:#fff; }
.p_create-btn:hover{ filter:brightness(1.05); transform:translateY(-1px); }

.p_loading{ opacity:.6; pointer-events:none; }
.skeleton-line{
  height:10px; width:100%; background:linear-gradient(90deg,#eee,#f7f7f7,#eee);
  border-radius:6px; animation:pulse 1.2s infinite;
}
@keyframes pulse{ 0%{opacity:.6} 50%{opacity:1} 100%{opacity:.6} }

/* ===== Modal System ===== */
.modal-backdrop{ position:fixed; inset:0; display:grid; place-items:center; background:rgba(15,18,30,.55); z-index:99999; padding:12px; }
.modal-card{ width:min(640px,96vw); background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.25); overflow:hidden; max-height:92vh; display:flex; flex-direction:column; position:relative; }
.modal-header{ position:sticky; top:0; z-index:10; background:#fff; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f0f0f0; }
.modal-header.danger{ background:#fff5f5; border-bottom-color:#fecaca; }
.modal-body{ flex:1 1 auto; min-height:0; overflow:auto; padding:16px; background:#fff; }
.modal-footer{ flex:0 0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; border-top:1px solid #f0f0f0; background:#fff; }

/* Inputs */
.form-row{ display:flex; flex-direction:column; gap:6px; margin-bottom:12px; }
.form-label{ font-weight:700; color:#334155; margin-bottom:4px; font-size:14px; }
.form-input{ height:44px; border:1px solid #e5e7eb; border-radius:12px; padding:0 12px; outline:none; background:#fff; width:100%; }
.form-input:focus{ border-color:#7367f0; box-shadow:0 0 0 4px rgba(115,103,240,.15); }

/* Buttons */
.btn{ display:inline-flex; align-items:center; justify-content:center; gap:6px; height:40px; padding:0 12px; border-radius:10px; border:1px solid transparent; font-weight:600; cursor:pointer; background:#f3f4f6; color:#111827; transition:transform .12s ease, filter .12s ease, background .12s ease; }
.btn:hover{ background:#e5e7eb; }
.btn:disabled{ opacity:.6; cursor:not-allowed; }
.btn-sm{ height:34px; padding:0 10px; font-size:.875rem; border-radius:8px; }
.btn-primary{ background:#7367f0; color:#fff; box-shadow:0 6px 14px rgba(115,103,240,.25); }
.btn-primary:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-danger{ background:#ef4444; color:#fff; }
.btn-danger:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-close{ border:none; background:transparent; font-size:1.25rem; line-height:1; color:#64748b; border-radius:10px; padding:6px; }
.btn-close:hover{ background:#eef2ff; color:#3730a3; }

.muted{ color:#94a3b8; font-size:.9rem; }
.table-scroll { overflow-x: auto; max-width: 100%; }
.table thead th { color: #fff; position: sticky; top: 0; z-index: 1; }
.table-head { background: #7367f0; color: #fff; }
.sortable { cursor: pointer; user-select: none; }
.input-group .form-control { min-width: 260px; }
.badge { font-weight: 600; }
.subcat-table tfoot th { background: #f5f6f8; }

/* Simple checkbox styling if Bootstrap form-check unavailable */
.form-check{ display:flex; align-items:center; gap:.5rem; }
.form-check-input{ width:1rem; height:1rem; }
.form-check-label{ user-select:none; }

.text-danger{ color:#ef4444; }
</style>
