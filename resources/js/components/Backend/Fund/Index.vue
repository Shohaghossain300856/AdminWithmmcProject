<!-- resources/js/components/Backend/Fund/index.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-2 flex-wrap">
        <h5 class="mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-coins me-2"></i> Fund Management
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
              v-model.trim="Search_fund"
              class="form-control"
              placeholder="Search fund name…"
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
          <button class="btn btn-sm btn-outline-primary" @click="refresh" :disabled="isLoading">
            <i :class="['fa', isLoading ? 'fa-spinner fa-spin' : 'fa-rotate']"></i>
            <span class="ms-1">{{ isLoading ? 'Loading…' : 'Refresh' }}</span>
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

                <th class="sortable" @click="toggleSort('fund')">
                  <span class="d-inline-flex align-items-center">
                    Fund
                    <i class="fa ms-1" :class="sortIcon('fund')"></i>
                  </span>
                </th>

                <th class="text-end" style="width:160px">Action</th>
              </tr>
            </thead>

            <tbody>
              <!-- Loading skeleton rows (supplier-style) -->
              <tr v-if="isLoading" v-for="n in 4" :key="'sk-'+n">
                <td colspan="3" class="py-3">
                  <div class="skeleton-line"></div>
                </td>
              </tr>

              <!-- Data rows -->
              <tr v-for="(row, i) in paginatedRows" :key="row.id ?? `tmp-${i}`" v-else>
                <td>{{ serialNumber(i) }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-medium">{{ row.fund || '—' }}</span>
                  </div>
                </td>
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
                <td colspan="3" class="text-center">
                  <span class="muted">No data</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination (supplier-style compact) -->
      <div class="card-footer d-flex align-items-center justify-content-between" v-if="!isLoading">
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

    <!-- ===== CREATE MODAL (supplier-style) ===== -->
    <div v-if="createOpen" class="modal-backdrop" @click.self="closeCreate">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-plus me-2"></i> Create Fund</h5>
          <button type="button" class="btn-close" @click="closeCreate" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">Fund Name</label>
            <input v-model.trim="form.fund" class="form-input" type="text" placeholder="e.g. Development Fund" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-ghost" @click="closeCreate">Close</button>
          <button class="btn btn-primary" :disabled="submitting || !form.fund?.trim()" @click="createFund">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span class="ms-2">{{ submitting ? 'Saving…' : 'Create' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ===== EDIT MODAL (supplier-style) ===== -->
    <div v-if="editOpen" class="modal-backdrop" @click.self="closeEdit">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-edit me-2"></i> Edit Fund</h5>
          <button type="button" class="btn-close" @click="closeEdit" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">Fund Name</label>
            <input v-model.trim="editForm.fund" class="form-input" type="text" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-ghost" @click="closeEdit">Cancel</button>
          <button class="btn btn-primary" :disabled="submitting || !editForm.fund?.trim()" @click="updateFund">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span class="ms-2">{{ submitting ? 'Saving…' : 'Update' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ===== DELETE MODAL (supplier-style with confirm checkbox) ===== -->
    <div v-if="deleteOpen" class="modal-backdrop" @click.self="closeDelete">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header danger">
          <h5 class="m-0"><i class="fa fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
          <button type="button" class="btn-close" @click="closeDelete" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">
            You are about to delete <b class="text-danger">{{ deleteRow?.fund || 'this fund' }}</b>.
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
/* ===== Supplier-style logic adapted for Fund; endpoints আপনার fund API ===== */
import { ref, onMounted, getCurrentInstance, computed, watch } from 'vue'
import { useToast } from 'vue-toastification'

const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()

/* Loaders / flags */
const isLoading  = ref(false)
const submitting = ref(false)

/* Data */
const funds = ref([])

/* Normalize list like supplier */
function normalizeApiList (payload) {
  if (!payload) return []
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload.funds)) return payload.funds
  if (Array.isArray(payload.data)) return payload.data
  if (Array.isArray(payload.list)) return payload.list
  return []
}

/* Fetch */
async function fetchFunds () {
  try {
    isLoading.value = true
    const res = await http.get('/fund/create') // expects: { funds: [...] } or array
    funds.value = normalizeApiList(res?.data)
  } catch (e) {
    console.error(e)
    funds.value = []
    toast.error('Failed to load funds')
  } finally {
    isLoading.value = false
  }
}
function refresh(){ currentPage.value = 1; fetchFunds() }

/* Safe base rows */
const safeRows = computed(() => {
  const arr = Array.isArray(funds.value) ? funds.value : []
  return arr.filter(r => r && typeof r === 'object').filter(r => (r.id != null) || r.fund)
})

/* Search (same as supplier style) */
const Search_fund = ref('')
const filteredRows = computed(() => {
  const q = Search_fund.value?.toLowerCase().trim() || ''
  if (!q) return safeRows.value
  return safeRows.value.filter(r => (r.fund || '').toString().toLowerCase().includes(q))
})

/* Sorting (supplier-style) */
const sortKey = ref('fund')
const sortDir = ref('asc') // 'asc' | 'desc'
function baseValue (row, key) {
  if (key === 'fund') return (row.fund || '').toString()
  return (row[key] ?? '').toString()
}
const sortedRows = computed(() => {
  const rows = [...filteredRows.value]
  const k = sortKey.value, d = sortDir.value
  rows.sort((a,b) => {
    const va = baseValue(a,k).toLowerCase()
    const vb = baseValue(b,k).toLowerCase()
    if (va < vb) return d==='asc' ? -1 : 1
    if (va > vb) return d==='asc' ?  1 : -1
    return 0
  })
  return rows
})
function toggleSort (key) {
  if (sortKey.value === key) sortDir.value = (sortDir.value === 'asc' ? 'desc' : 'asc')
  else { sortKey.value = key; sortDir.value = 'asc' }
}
function sortIcon (key) {
  if (sortKey.value !== key) return 'fa-sort'
  return sortDir.value === 'asc' ? 'fa-sort-up' : 'fa-sort-down'
}

/* Pagination (supplier-style) */
const itemsPerPage = ref(10)
const currentPage  = ref(1)
watch([itemsPerPage, filteredRows], () => { currentPage.value = 1 })

const pageCount = computed(() => Math.max(1, Math.ceil(sortedRows.value.length / itemsPerPage.value)))
const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return sortedRows.value.slice(start, start + itemsPerPage.value)
})
function goToPage (p){ if (p>=1 && p<=pageCount.value) currentPage.value = p }
function prevPage () { goToPage(currentPage.value - 1) }
function nextPage () { goToPage(currentPage.value + 1) }

const showingStart = computed(() => (sortedRows.value.length === 0 ? 0 : (currentPage.value - 1) * itemsPerPage.value + 1))
const showingEnd   = computed(() => Math.min(currentPage.value * itemsPerPage.value, sortedRows.value.length))
const pageButtons  = computed(() => {
  const total = pageCount.value, cur = currentPage.value, span = 2
  const start = Math.max(1, cur - span), end = Math.min(total, cur + span)
  const list = []
  for (let i=start;i<=end;i++) list.push(i)
  if (!list.includes(1)) list.unshift(1)
  if (!list.includes(total)) list.push(total)
  return [...new Set(list)].sort((a,b)=>a-b)
})
function serialNumber (indexOnPage){
  return (currentPage.value - 1) * itemsPerPage.value + indexOnPage + 1
}

/* ===== CREATE ===== */
const createOpen = ref(false)
const form = ref({ fund: '' })
function openCreate(){ form.value = { fund:'' }; createOpen.value = true }
function closeCreate(){ createOpen.value = false; form.value = { fund:'' } }

async function createFund(){
  if(!form.value.fund?.trim()){ toast.error('Fund name is required'); return }
  try{
    submitting.value = true
    await http.post('/fund', { fund: form.value.fund })
    toast.success('Created successfully')
    closeCreate()
    await fetchFunds()
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
const editForm = ref({ fund: '' })

function openEdit(row){
  editId.value = row?.id
  editForm.value = { fund: row?.fund || '' }
  editOpen.value = true
}
function closeEdit(){ editOpen.value = false; editId.value = null; editForm.value = { fund:'' } }

async function updateFund(){
  if(!editId.value) return
  if(!editForm.value.fund?.trim()){ toast.error('Fund name is required'); return }
  try{
    submitting.value = true
    await http.put(`/fund/${editId.value}`, { fund: editForm.value.fund })
    toast.success('Updated successfully')
    closeEdit()
    await fetchFunds()
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
    await http.delete(`/fund/${deleteRow.value.id}`)
    toast.success('Deleted')
    closeDelete()
    await fetchFunds()
  }catch(e){
    console.error(e)
    toast.error(e?.response?.data?.message || 'Delete failed')
  }finally{
    submitting.value = false
  }
}

onMounted(fetchFunds)
</script>

<style scoped>
/* Top CTA (supplier-style) */
.p_loading{ opacity:.6; pointer-events:none; }
.skeleton-line{
  height:10px; width:100%; background:linear-gradient(90deg,#eee,#f7f7f7,#eee);
  border-radius:6px; animation:pulse 1.2s infinite;
}
@keyframes pulse{ 0%{opacity:.6} 50%{opacity:1} 100%{opacity:.6} }

/* Table & header */
.table-scroll { overflow-x: auto; max-width: 100%; }
.table thead th { color: #fff; position: sticky; top: 0; z-index: 1; }
.table-head { background: #7367f0; color: #fff; }
.sortable { cursor: pointer; user-select: none; }
.input-group .form-control { min-width: 260px; }
.badge { font-weight: 600; }
.subcat-table tfoot th { background: #f5f6f8; }

/* Modal System (supplier-style) */
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
.text-danger{ color:#ef4444; }

/* Simple checkbox styling */
.form-check{ display:flex; align-items:center; gap:.5rem; }
.form-check-input{ width:1rem; height:1rem; }
.form-check-label{ user-select:none; }
</style>
