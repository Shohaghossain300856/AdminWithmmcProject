<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header (Stock Summary style) -->
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-2 flex-wrap">
        <h5 class="mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-layer-group me-2"></i> Sub Categories
        </h5>

        <!-- Count -->
        <span class="badge bg-primary ms-2">
          {{ items.length }} Items
        </span>

        <!-- Right controls -->
        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
          <!-- Search (visual only to match style) -->
          <div class="input-group input-group-sm w-auto">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input class="form-control" placeholder="Search subcategory / code" />
          </div>

          <!-- Per page (visual only) -->
          <select class="form-select form-select-sm w-auto">
            <option selected>25 / page</option>
            <option>50 / page</option>
            <option>100 / page</option>
          </select>

          <!-- Refresh -->
          <button class="btn btn-sm btn-outline-primary" @click="fetchItems" :disabled="isLoading">
            <i :class="['fa', isLoading ? 'fa-spinner fa-spin' : 'fa-rotate']"></i>
            <span class="ms-1">{{ isLoading ? 'Loading…' : 'Refresh' }}</span>
          </button>

          <!-- Add New -->
          <button class="btn btn-primary btn-sm" @click="openCreate">
            <i class="fa fa-plus me-2"></i> New
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card" :class="{ p_loading: isLoading }">
      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table class="table table-hover align-middle mb-0 subcat-table">
            <thead class="table-head">
              <tr>
                <th style="width:80px">Sl</th>
                <th>Subcategory</th>
                <th style="width:220px">Code</th>
                <th style="width:180px" class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in items" :key="row?.id ?? i">
                <td>{{ i + 1 }}</td>
                <td>{{ row?.sub_category || '—' }}</td>
                <td>{{ row?.sub_category_code || row?.code || '—' }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-primary me-2" title="Edit" @click="openEdit(row)">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" title="Delete" @click="openDelete(row)">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!items.length">
                <td colspan="4" class="text-center py-4 text-muted">
                  <i class="fa fa-inbox me-2"></i>No data
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination footer (visual match; logic untouched) -->
        <div class="card-footer d-flex align-items-center justify-content-between" v-if="items.length">
          <div class="text-muted small">
            Showing 1–{{ items.length }} of {{ items.length }} items
          </div>
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm w-auto">
              <option selected>25 / page</option>
              <option>50 / page</option>
              <option>100 / page</option>
            </select>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-secondary" disabled>
                <i class="fa fa-chevron-left"></i>
              </button>
              <button class="btn btn-primary">1</button>
              <button class="btn btn-outline-secondary" disabled>
                <i class="fa fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
        <!-- /Pagination footer -->
      </div>
    </div>

    <!-- CREATE MODAL -->
    <div v-if="createOpen" class="modal-backdrop" @click.self="closeCreate">
      <div class="modal-card animate-pop" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-plus me-2"></i> New Subcategory</h5>
          <button type="button" class="btn-close" @click="closeCreate"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">Subcategory</label>
            <input v-model.trim="form.sub_category" class="form-input" type="text" placeholder="e.g. Syringe" />
          </div>
          <div class="form-row">
            <label class="form-label">Code</label>
            <input v-model.trim="form.sub_category_code" class="form-input" type="text" placeholder="e.g. SYR-01" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-ghost" @click="closeCreate">Close</button>
          <button class="btn btn-primary" :disabled="submitting" @click="createItem">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span class="ms-2">{{ submitting ? 'Saving…' : 'Create' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="editOpen" class="modal-backdrop" @click.self="closeEdit">
      <div class="modal-card animate-pop" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-edit me-2"></i> Edit Subcategory</h5>
          <button type="button" class="btn-close" @click="closeEdit"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">Subcategory</label>
            <input v-model.trim="editForm.sub_category" class="form-input" type="text" />
          </div>
          <div class="form-row">
            <label class="form-label">Code</label>
            <input v-model.trim="editForm.sub_category_code" class="form-input" type="text" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-ghost" @click="closeEdit">Cancel</button>
          <button class="btn btn-primary" :disabled="submitting" @click="updateItem">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span class="ms-2">{{ submitting ? 'Saving…' : 'Update' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- DELETE MODAL -->
    <div v-if="deleteOpen" class="modal-backdrop" @click.self="closeDelete">
      <div class="modal-card animate-pop" role="dialog" aria-modal="true">
        <div class="modal-header danger">
          <h5 class="m-0"><i class="fa fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" @click="closeDelete"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">
            Are you sure you want to delete
            <b>{{ deleteRow?.sub_category || 'this subcategory' }}</b>?
          </p>
          <p class="muted">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-ghost" @click="closeDelete">Cancel</button>
          <button class="btn btn-danger" :disabled="submitting" @click="doDelete">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-trash'"></i>
            <span class="ms-2">{{ submitting ? 'Deleting…' : 'Delete' }}</span>
          </button>
        </div>
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

// state
const isLoading = ref(false)
const submitting  = ref(false)

// data
const items = ref([])

// create
const createOpen = ref(false)
const form = ref({ sub_category: '', sub_category_code: '' })

// edit
const editOpen = ref(false)
const editId = ref(null)
const editForm = ref({ sub_category: '', sub_category_code: '' })

// delete
const deleteOpen = ref(false)
const deleteRow = ref(null)

async function fetchItems () {
  try {
    isLoading.value = true
    const res = await http.get('/subCatagories/create')
    items.value = res?.data?.data ?? res?.data ?? []
    console.log('data', items.value)
  } catch (e) {
    console.error(e)
    toast.error('Failed to load')
  } finally {
    isLoading.value = false
  }
}

// Create
function openCreate(){ createOpen.value = true }
function closeCreate(){ createOpen.value = false; form.value = { sub_category:'', sub_category_code:'' } }
async function createItem(){
  if(!form.value.sub_category?.trim()){ toast.error('Subcategory required'); return }
  try{
    submitting.value = true
    await http.post('/subCatagories', {
      sub_category: form.value.sub_category,
      sub_category_code: form.value.sub_category_code || null
    })
    toast.success('Created successfully')
    closeCreate()
    await fetchItems()
  }catch(e){
    toast.error(e?.response?.data?.message || 'Create failed')
  }finally{
    submitting.value = false
  }
}

// Edit
function openEdit(row){
  editId.value = row.id
  editForm.value = {
    sub_category: row.sub_category ?? '',
    sub_category_code: row.sub_category_code ?? row.code ?? ''
  }
  editOpen.value = true
}
function closeEdit(){ editOpen.value = false; editId.value = null; editForm.value = { sub_category:'', sub_category_code:'' } }
async function updateItem(){
  if(!editForm.value.sub_category?.trim()){ toast.error('Subcategory required'); return }
  try{
    submitting.value = true
    await http.put(`/subCatagories/${editId.value}`, {
      sub_category: editForm.value.sub_category,
      sub_category_code: editForm.value.sub_category_code || null
    })
    toast.success('Updated Created successfully')
    closeEdit()
    await fetchItems()
  }catch(e){
    toast.error(e?.response?.data?.message || 'Update failed')
  }finally{
    submitting.value = false
  }
}

// Delete
function openDelete(row){ deleteRow.value = row; deleteOpen.value = true }
function closeDelete(){ deleteOpen.value = false; deleteRow.value = null }
async function doDelete(){
  if(!deleteRow.value?.id) return
  try{
    submitting.value = true
    await http.delete(`/subCatagories/${deleteRow.value.id}`)
    toast.success('Deleted')
    closeDelete()
    await fetchItems()
  }catch(e){
    toast.error(e?.response?.data?.message || 'Delete failed')
  }finally{
    submitting.value = false
  }
}

onMounted(fetchItems)
</script>

<style scoped>
/* ========== Header / Quick Actions ========== */
.p_create-btn{ background:#7367f0; color:#fff; border:none; box-shadow:0 6px 14px rgba(115,103,240,.3); }
.p_create-btn:hover{ filter:brightness(1.05); transform:translateY(-1px); }

/* ========== Table (Stock Summary Style) ========== */
.table-scroll { overflow-x: auto; max-width: 100%; }
.table thead th { color: #fff; position: sticky; top: 0; z-index: 1; }
.table-head { background: #7367f0; color: #fff; }
.input-group .form-control { min-width: 260px; }
.badge { font-weight: 600; }
.subcat-table tfoot th { background: #f5f6f8; }

/* Loading lock on card */
.p_loading{ opacity:.6; pointer-events:none; }

/* ========== Modal System (FULL CSS) ========== */
.modal-backdrop{
  position:fixed; inset:0;
  display:grid; place-items:center;
  background:rgba(15,18,30,.55);
  z-index:99999; padding:12px;
  overflow:auto;
}
.modal-card{
  width:min(640px,96vw);
  background:#fff; border-radius:14px;
  box-shadow:0 10px 30px rgba(0,0,0,.25);
  overflow:hidden; max-height:92vh;
  display:flex; flex-direction:column;
  animation:pop .14s ease-out;
}
@keyframes pop{
  from{ transform:scale(.98); opacity:.6 }
  to{ transform:scale(1); opacity:1 }
}
.modal-card.animate-pop{ animation:pop .14s ease-out }

.modal-header{
  position:sticky; top:0; z-index:10;
  background:#fff; padding:12px 16px;
  display:flex; align-items:center; justify-content:space-between;
  border-bottom:1px solid #f0f0f0;
}
.modal-header.danger {
  background: #fff;            /* white background */
  color: #111827;              /* dark text */
  border-bottom-color: #f0f0f0;
}
.modal-body{
  flex:1 1 auto; min-height:0;
  overflow:auto; padding:16px; background:#fff;
}
.modal-footer{
  flex:0 0 auto; padding:12px 16px;
  display:flex; align-items:center; gap:12px;
  border-top:1px solid #f0f0f0; background:#fff;
}

/* Danger header close button look */
.btn-close.btn-close-white {
  filter: none;                /* normal close button */
}
/* Form elements inside modal */
.form-row{ display:flex; flex-direction:column; gap:6px; margin-bottom:12px; }
.form-label{ font-weight:700; color:#334155; margin-bottom:4px; font-size:14px; }
.form-input{
  height:44px; border:1px solid #e5e7eb;
  border-radius:12px; padding:0 12px; outline:none; background:#fff;
}
.form-input:focus{ border-color:#7367f0; box-shadow:0 0 0 4px rgba(115,103,240,.15); }

/* ========== Buttons ========== */
.btn{
  display:inline-flex; align-items:center; justify-content:center;
  gap:6px; height:34px; padding:0 12px;
  border-radius:8px; border:1px solid transparent;
  font-weight:600; cursor:pointer;
  background:#f3f4f6; color:#111827;
  transition:transform .12s ease, filter .12s ease, background .12s ease;
}
.btn:hover{ background:#e5e7eb; }
.btn:disabled{ opacity:.6; cursor:not-allowed; }
.btn-primary{ background:#7367f0; color:#fff; box-shadow:0 6px 14px rgba(115,103,240,.25); }
.btn-primary:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-danger{ background:#ef4444; color:#fff; }
.btn-danger:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-ghost{ background:#f8fafc; color:#111827; border:1px solid #e5e7eb; }
.btn-ghost:hover{ background:#eef2ff; }

/* Icon spacing helper (if needed) */
.ms-2{ margin-left:.5rem; }
.me-2{ margin-right:.5rem; }
</style>
