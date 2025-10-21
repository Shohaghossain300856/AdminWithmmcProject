<!-- resources/js/components/Backend/Supplier/index.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex align-items-center m-3 mb-3">
      <h5 class="card-header ms-0 mb-0 p-0">Suppliers</h5>
      <button class="btn p_create-btn ms-auto" @click="openCreate">
        <i class="fa fa-plus me-2"></i> New
      </button>
    </div>

    <!-- Table -->
    <div class="card" :class="{ p_loading: isLoading }">
      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table class="table table-hover align-middle mb-0 subcat-table">
            <thead>
              <tr style="background:#7367f0;">
                <th style="width:80px">Sl</th>
                <th>Supplier</th>
                <th style="width:180px">Phone</th>
                <th>Address</th>
                <th style="width:180px" class="text-end">Action</th>
              </tr>
            </thead>

            <tbody>
              <!-- Data rows -->
              <tr v-for="(row, i) in safeRows" :key="row.id ?? `tmp-${i}`">
                <td>{{ i + 1 }}</td>
                <td>{{ (row.supplier || row.name) || '—' }}</td>
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
              <tr v-if="!isLoading && safeRows.length === 0">
                <td colspan="5" class="text-center">
                  <span class="muted">No data</span>
                </td>
              </tr>

              <!-- (Optional) Loading skeleton rows -->
              <tr v-if="isLoading" v-for="n in 4" :key="'sk-'+n">
                <td colspan="5" class="py-3">
                  <div class="skeleton-line"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- CREATE MODAL -->
    <div v-if="createOpen" class="modal-backdrop" @click.self="closeCreate">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-plus me-2"></i> Add Supplier</h5>
          <button type="button" class="btn-close" @click="closeCreate"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">সাপ্লায়ার নাম</label>
            <input v-model.trim="form.supplier" class="form-input" type="text" placeholder="e.g. ABC Medical" />
          </div>
          <div class="form-row">
            <label class="form-label">মোবাইল নম্বর</label>
            <input v-model.trim="form.phone" class="form-input" type="text" placeholder="e.g. 01xxxxxxxxx" />
          </div>
          <div class="form-row">
            <label class="form-label">এড্রেস</label>
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

    <!-- EDIT MODAL -->
    <div v-if="editOpen" class="modal-backdrop" @click.self="closeEdit">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-edit me-2"></i> Edit Supplier</h5>
          <button type="button" class="btn-close" @click="closeEdit"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">സাপ্লায়ার নাম</label>
            <input v-model.trim="editForm.supplier" class="form-input" type="text" />
          </div>
          <div class="form-row">
            <label class="form-label">মোবাইল নম্বর</label>
            <input v-model.trim="editForm.phone" class="form-input" type="text" />
          </div>
          <div class="form-row">
            <label class="form-label">এড্রেস</label>
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

    <!-- DELETE MODAL -->
    <div v-if="deleteOpen" class="modal-backdrop" @click.self="closeDelete">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header danger">
          <h5 class="m-0"><i class="fa fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
          <button type="button" class="btn-close" @click="closeDelete"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">
            Are you sure you want to delete
            <b>{{ (deleteRow && (deleteRow.supplier || deleteRow.name)) || 'this supplier' }}</b>?
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
import { ref, onMounted, getCurrentInstance, computed } from 'vue'
import { useToast } from 'vue-toastification'

const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()

const isLoading = ref(false)
const submitting = ref(false)
const suppliers = ref([])

/* Computed safe rows: remove falsy/empty objects */
const safeRows = computed(() => {
  const arr = Array.isArray(suppliers.value) ? suppliers.value : []
  return arr
    .filter(r => r && typeof r === 'object')
    .filter(r => (r.id != null) || r.supplier || r.name || r.phone || r.address)
})

const createOpen = ref(false)
const form = ref({ supplier: '', phone: '', address: '' })

const editOpen = ref(false)
const editId = ref(null)
const editForm = ref({ supplier: '', phone: '', address: '' })

const deleteOpen = ref(false)
const deleteRow = ref(null)

function normalizeApiList(payload) {
  // Handles: {data:[...]}, {list:[...]}, or direct array
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
    console.log(suppliers.value);
  } catch (e) {
    console.error(e)
    suppliers.value = []
    toast.error('Failed to load suppliers')
  } finally {
    isLoading.value = false
  }
}

/* ===== CREATE ===== */
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
function openDelete(row){ deleteRow.value = row; deleteOpen.value = true }
function closeDelete(){ deleteOpen.value = false; deleteRow.value = null }

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

/* Loading lock */
.p_loading{ opacity:.6; pointer-events:none; }

/* Table */
.table-scroll{ max-height:65vh; overflow-y:auto; border-radius:.5rem; border:1px solid rgba(0,0,0,.08); }
.table th{ text-transform:uppercase; font-size:.8125rem; letter-spacing:.2px; color:#fff; }

/* Skeleton */
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
</style>
