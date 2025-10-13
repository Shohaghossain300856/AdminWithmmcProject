<!-- resources/js/components/Backend/Subcategories/index.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center m-3 mb-3">
      <h5 class="card-header ms-0 mb-0 p-0">Subcategories</h5>
      <button class="btn ms-auto p_create-btn" style="color:#fff; background:#7367f0;" @click="openCreate">
        <i class="fa fa-plus me-2"></i> New Item
      </button>
    </div>

    <!-- Table (uses getItems) -->
    <div class="card" :class="{ p_loading: isLoading }">
      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table class="table">
            <thead>
              <tr style="background:#7367f0;">
                <th>Sl</th>
                <th>Fund</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Code</th>
                <th style="width:180px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, idx) in getItems" :key="row?.id ?? idx">
                <td>{{ idx + 1 }}</td>
                <td>{{ row?.fund?.fund || findFundName(row?.fund_id) }}</td>
                <td>{{ row?.category?.name || findCategoryName(row?.categorie_id) }}</td>
                <td class="d-flex align-items-center gap-2">
                  <span>{{ row?.sub_category || '—' }}</span>
                </td>
                <td>{{ row?.sub_category_code || row?.code || '—' }}</td>
                <td class="d-flex gap-2">
                  <button class="btn btn-sm btn-primary" title="Edit" @click.stop="openEdit(row)">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-primary" title="Show" @click.stop="openShow(row)">
                    <i class="fa fa-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" title="Delete" @click.stop="openDelete(row)">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="getItems.length === 0">
                <td colspan="6" class="text-center">No data</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SHOW MODAL -->
    <div v-if="showModal.open" class="smx-backdrop" @click.self="closeShow">
      <div class="smx-card smx-card-xxl" role="dialog" aria-modal="true">
        <div class="smx-header">
          <h5 class="smx-title">
            <i class="fa fa-eye smx-title-icon"></i> Item Details
          </h5>

          <div class="smx-chip-wrap">
            <div class="smx-chip smx-chip-primary" v-if="showModal.data?.sub_category_code || showModal.data?.code">
              <i class="fa fa-barcode smx-chip-ic"></i>{{ showModal.data?.sub_category_code || showModal.data?.code }}
            </div>
            <div class="smx-chip" v-if="showModal.data?.fund_id">
              <i class="fa fa-database smx-chip-ic"></i>{{ showModal.data?.fund?.fund || findFundName(showModal.data?.fund_id) }}
            </div>
            <div class="smx-chip" v-if="showModal.data?.categorie_id">
              <i class="fa fa-folder smx-chip-ic"></i>{{ showModal.data?.category?.name || findCategoryName(showModal.data?.categorie_id) }}
            </div>
          </div>

          <button class="smx-close" @click="closeShow" aria-label="Close">
            <i class="fa fa-times"></i>
          </button>
        </div>

        <div class="smx-body">
          <div class="smx-grid">
            <div class="smx-panel">
              <div class="smx-panel-hd"><i class="fa fa-info-circle"></i> General</div>
              <div class="smx-panel-bd">
                <div class="smx-kv-grid">
                  <div class="smx-kv">
                    <div class="k">Fund</div>
                    <div class="v">{{ showModal.data?.fund?.fund || findFundName(showModal.data?.fund_id) }}</div>
                  </div>
                  <div class="smx-kv">
                    <div class="k">Category</div>
                    <div class="v">{{ showModal.data?.category?.name || findCategoryName(showModal.data?.categorie_id) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="smx-panel">
              <div class="smx-panel-hd"><i class="fa fa-clipboard-list"></i> Meta</div>
              <div class="smx-panel-bd">
                <div class="smx-kv-grid">
                  <div class="smx-kv">
                    <div class="k">Sub Category</div>
                    <div class="v">{{ showModal.data?.sub_category || '—' }}</div>
                  </div>
                  <div class="smx-kv">
                    <div class="k">Code</div>
                    <div class="v">{{ showModal.data?.sub_category_code || showModal.data?.code || '—' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div> <!-- /smx-grid -->
        </div> <!-- /smx-body -->

        <div class="smx-footer">
          <button class="smx-btn smx-btn-ghost" @click="closeShow">
            <i class="fa fa-times me-2"></i> Close
          </button>
          <button class="smx-btn smx-btn-primary" @click="openEdit(showModal.data)">
            <i class="fa fa-edit me-2"></i> Edit This Item
          </button>
        </div>
      </div>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="editModal.open" class="modal-backdrop" @click.self="closeEdit">
      <div class="modal-card modal-wide" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-edit me-2"></i>Edit Item</h5>
          <button class="btn-close" @click="closeEdit" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="p_filters-grid p_cols-2" v-if="editModal?.form">
            <!-- Fund -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Fund</label>
                <select class="p_input" v-model="editModal.form.fund_id" @change="onEditFundChange">
                  <option :value="null" disabled>Select fund</option>
                  <option v-for="f in funds" :key="f.id" :value="num(f.id)">{{ f.fund }}</option>
                </select>
              </div>
            </div>

            <!-- Category -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Category</label>
                <select class="p_input" v-model="editModal.form.categorie_id" :disabled="!editModal.form.fund_id">
                  <option :value="null" disabled>Select category</option>
                  <option v-for="c in editModal.categories" :key="c.id" :value="num(c.id)">{{ c.name }}</option>
                </select>
              </div>
            </div>

            <!-- Item -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Item</label>
                <input v-model.trim="editModal.form.sub_category" class="p_input" type="text" />
              </div>
            </div>

            <!-- Code -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Code</label>
                <input v-model.trim="editModal.form.sub_category_code" class="p_input" type="text" />
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="smx-btn smx-btn-ghost" @click="closeEdit">
            <i class="fa fa-times me-2"></i> Cancel
          </button>
          <button class="smx-btn smx-btn-primary" :disabled="submitting" @click="updateItem">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span class="ms-2">{{ submitting ? 'Saving...' : 'Update' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- DELETE MODAL -->
    <div v-if="deleteModal.open" class="modal-backdrop" @click.self="closeDelete">
      <div class="modal-card" style="width:min(460px,95vw)" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0 text-danger">
            <i class="fa fa-exclamation-triangle me-2"></i> Confirm Delete
          </h5>
          <button class="btn-close" @click="closeDelete" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">
            Are you sure you want to delete <b>{{ deleteModal.data?.sub_category || 'this item' }}</b>?</p>
          <p class="text-muted">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="smx-btn smx-btn-ghost" @click="closeDelete">Cancel</button>
          <button class="smx-btn smx-btn-danger" :disabled="submitting" @click="doDelete">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-trash'"></i>
            <span class="ms-2">{{ submitting ? 'Deleting...' : 'Delete' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- CREATE MODAL -->
    <div v-if="showCreate" class="modal-backdrop" @click.self="closeCreate">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-plus me-2"></i> New Item</h5>
          <button class="btn-close" @click="closeCreate" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="p_filters-grid p_cols-2">
            <!-- Fund -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Fund</label>
                <select class="p_input" v-model="form.fund_id" @change="onCreateFundChange">
                  <option :value="null" disabled>Select a fund</option>
                  <option v-for="f in funds" :key="f.id" :value="num(f.id)">{{ f.fund }}</option>
                </select>
              </div>
            </div>

            <!-- Category -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Category</label>
                <select class="p_input" v-model="form.categorie_id" :disabled="!form.fund_id">
                  <option :value="null" disabled>Select a category</option>
                  <option v-for="c in categories" :key="c.id" :value="num(c.id)">{{ c.name }}</option>
                </select>
              </div>
            </div>

            <!-- Item -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Item</label>
                <input v-model.trim="form.sub_category" class="p_input" type="text" />
              </div>
            </div>

            <!-- Code -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Code</label>
                <input v-model.trim="form.sub_category_code" class="p_input" type="text" />
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="smx-btn smx-btn-ghost" @click="closeCreate">Close</button>
          <button class="smx-btn smx-btn-primary" :disabled="submitting" @click="createItem">
            <i class="fa" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span class="ms-2">{{ submitting ? 'Saving…' : 'Create' }}</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance, nextTick, watch } from 'vue'
import { useToast } from 'vue-toastification'

const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()

// utils
const num = (v) => (v === null || v === undefined || v === '' ? null : Number(v))

// state
const isLoading = ref(false)
const submitting = ref(false)
const funds = ref([])
const categories = ref([])
const getItems = ref([])

// forms
const form = ref({ fund_id: null, categorie_id: null, sub_category: '', sub_category_code: '' })

// modals
const showModal = ref({ open: false, data: {} })
const editModal = ref({
  open: false,
  id: null,
  form: { fund_id: null, categorie_id: null, sub_category: '', sub_category_code: '' },
  categories: []
})
const deleteModal = ref({ open: false, data: null })
const showCreate = ref(false)

// helpers
function findFundName(id) {
  const fid = num(id)
  return funds.value.find(f => num(f.id) === fid)?.fund || '-'
}
function findCategoryName(id) {
  const cid = num(id)
  const list = editModal.value.open ? editModal.value.categories : categories.value
  return list.find(c => num(c.id) === cid)?.name || '-'
}

// api
async function getFunds() {
  try {
    isLoading.value = true
    const res = await http.get('/fund/create')
    funds.value = res?.data?.funds ?? []
  } catch {
    toast.error('Failed to load funds')
  } finally {
    isLoading.value = false
  }
}

// categories for CREATE (endpoint unified)
async function getCategories(fid) {
  const fundId = num(fid)
  if (!fundId) { categories.value = []; form.value.categorie_id = null; return }
  try {
    isLoading.value = true
    // ✅ Use the same endpoint everywhere
    const res = await http.get('/subCatagories/show', { params: { fund_id: fundId } })
    categories.value = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
  } catch (e) {
    console.error('Error loading categories:', e)
    toast.error('Failed to load categories')
  } finally {
    isLoading.value = false
  }
}

// categories for EDIT (endpoint unified + keep current)
async function getEditCategories(fid, keepId = null) {
  const fundId = num(fid)
  if (!fundId) {
    editModal.value.categories = []
    editModal.value.form.categorie_id = null
    return
  }
  try {
    const res = await http.get('/subCatagories/show', { params: { fund_id: fundId } })
    const list = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
    editModal.value.categories = list

    // If current category is not in the list, keep it as fallback
    if (keepId && !list.some(c => num(c.id) === num(keepId))) {
      const fallbackName =
        showModal.value?.data?.category?.name ||
        findCategoryName(keepId) || 'Current Category'
      editModal.value.categories = [
        ...list,
        { id: num(keepId), name: fallbackName }
      ]
    }
  } catch (e) {
    console.error('getEditCategories error:', e)
    toast.error('Failed to load categories')
  }
}

// list items
async function getItem () {
  try {
    isLoading.value = true
    const res = await http.get('/subCatagories/create')
    getItems.value = res?.data?.data ?? res?.data ?? []
  } catch (e) {
    console.error('getItem error:', e?.response?.data || e)
    toast.error('Failed to load items')
  } finally {
    isLoading.value = false
  }
}

// actions (create/update/delete)
async function createItem() {
  if (!num(form.value.fund_id) || !num(form.value.categorie_id) || !form.value.sub_category.trim()) {
    toast.error('All fields required'); return
  }
  try {
    submitting.value = true
    await http.post('/subCatagories', {
      fund_id: num(form.value.fund_id),
      categorie_id: num(form.value.categorie_id),
      sub_category: form.value.sub_category,
      sub_category_code: form.value.sub_category_code || null
    })
    toast.success('Created successfully')
    await getItem()
    resetForm(false)
    showCreate.value = false
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Create failed')
  } finally {
    submitting.value = false
  }
}

function openShow(row) {
  showModal.value.data = { ...row }
  nextTick(() => { showModal.value.open = true })
}
function closeShow() { showModal.value.open = false; showModal.value.data = {} }

async function openEdit(row) {
  // normalize row ids to numbers
  const fid = num(row.fund_id)
  const cid = num(row.categorie_id)

  editModal.value.id = row.id
  editModal.value.form = {
    fund_id: fid,
    categorie_id: cid,
    sub_category: row.sub_category ?? '',
    sub_category_code: row.sub_category_code ?? row.code ?? ''
  }

  // Load categories for existing fund and keep current category if missing
  await getEditCategories(fid, cid)
  nextTick(() => { editModal.value.open = true })
}

function onEditFundChange() {
  const fid = num(editModal.value.form.fund_id)
  editModal.value.form.categorie_id = null
  editModal.value.categories = []
  if (fid) getEditCategories(fid)
}

function closeEdit() {
  editModal.value.open = false
  editModal.value.id = null
  editModal.value.form = { fund_id: null, categorie_id: null, sub_category: '', sub_category_code: '' }
  editModal.value.categories = []
}

async function updateItem() {
  try {
    const f = editModal.value.form
    if (!num(f.fund_id)) throw new Error('Fund is required')
    if (!num(f.categorie_id)) throw new Error('Category is required')
    if (!String(f.sub_category || '').trim()) throw new Error('Item name is required')

    submitting.value = true
    await http.put(`/subCatagories/${editModal.value.id}`, {
      fund_id: num(f.fund_id),
      categorie_id: num(f.categorie_id),
      sub_category: f.sub_category,
      sub_category_code: f.sub_category_code || null
    })

    toast.success('Updated successfully')
    closeEdit()
    await getItem()
  } catch (e) {
    const msg = e?.response?.data?.message || e?.message || 'Update failed'
    toast.error(msg)
  } finally {
    submitting.value = false
  }
}

function openDelete(row) {
  deleteModal.value.data = row
  nextTick(() => { deleteModal.value.open = true })
}
function closeDelete() { deleteModal.value.open = false; deleteModal.value.data = null }

async function doDelete() {
  if (!deleteModal.value.data?.id) return
  try {
    submitting.value = true
    const res = await http.delete(`/subCatagories/${deleteModal.value.data.id}`)
    if (res?.data?.success) {
      toast.success(res.data.message || 'Deleted successfully')
    } else {
      toast.success('Deleted successfully')
    }
    closeDelete()
    await getItem()
  } catch (e) {
    console.error(e)
    toast.error(e?.response?.data?.message || 'Delete failed')
  } finally {
    submitting.value = false
  }
}

// misc
function resetForm(clearAll = true) {
  form.value.sub_category = ''
  form.value.sub_category_code = ''
  if (clearAll) {
    form.value.fund_id = null
    form.value.categorie_id = null
    categories.value = []
  }
}
function onCreateFundChange() {
  getCategories(form.value.fund_id)
}
function openCreate() { showCreate.value = true }
function closeCreate() { showCreate.value = false }

// watchers
watch(() => form.value.fund_id, (nv, ov) => { if (nv !== ov) onCreateFundChange() })
watch(() => editModal.value.form.fund_id, (nv, ov) => {
  // extra guard in case browser autofill or programmatic set happens
  if (editModal.value.open && nv !== ov) onEditFundChange()
})

// optional: log list size
watch(getItems, v => console.log('items:', v?.length ?? 0), { deep: true })

onMounted(async () => {
  await getFunds()
  await getItem()
})
</script>

<style scoped>
/* Top CTA */
.p_create-btn{ background:#7367f0; border:none; box-shadow:0 6px 14px rgba(115,103,240,.3); }
.p_create-btn:hover{ filter:brightness(1.05); transform:translateY(-1px); }

/* Filters grid */
.p_filters-grid{ display:grid; gap:12px; align-items:end; }
.p_cols-2{ grid-template-columns: repeat(2, minmax(220px, 1fr)); }
.p_span-2{ grid-column:1 / -1; display:flex; gap:10px; align-items:center; }
@media (max-width:640px){ .p_cols-2{ grid-template-columns:1fr; } }

.p_form-label{ font-weight:700; color:#334155; margin-bottom:6px; display:block; }
.p_select-input,.p_input{ width:100%; height:44px; border:1px solid #e5e7eb; border-radius:12px; padding:0 12px; outline:none; }
.p_select-input:focus,.p_input:focus{ border-color:#7367f0; box-shadow:0 0 0 4px rgba(115,103,240,.15); }

/* Loading lock */
.p_loading{ opacity:.6; pointer-events:none; }

/* Table */
.table-scroll{ max-height:65vh; overflow-y:auto; border-radius:.5rem; border:1px solid rgba(0,0,0,.08); }
.table th{ text-transform:uppercase; font-size:.8125rem; letter-spacing:.2px; color:#fff; }

/* Backdrop (SHOW) */
.smx-backdrop{ position:fixed; inset:0; z-index:99999; display:grid; place-items:center; background:rgba(15,18,30,.58); padding:12px; animation:smxFadeIn .15s ease-out; }
@keyframes smxFadeIn{ from{opacity:0} to{opacity:1} }

/* Show card */
.smx-card{ width:min(880px,96vw); max-height:92vh; background:#fff; border-radius:16px; box-shadow:0 18px 48px rgba(0,0,0,.35); display:flex; flex-direction:column; overflow:hidden; transform:translateY(4px); animation:smxPop .18s ease-out forwards; }
.smx-card-xxl{ width:min(1120px,98vw); }
@keyframes smxPop{ to{ transform:translateY(0) } }

.smx-header{ display:grid; grid-template-columns:1fr auto auto; gap:10px; align-items:center; padding:12px 16px; border-bottom:1px solid #eef0f4; background:linear-gradient(180deg,#fafafa,#f4f6fb); }
.smx-title{ margin:0; display:flex; align-items:center; gap:10px; font-weight:800; color:#111827; }
.smx-title-icon{ color:#6f6bef; }

.smx-chip-wrap{ display:flex; flex-wrap:wrap; gap:8px; justify-self:center; }
.smx-chip{ display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:999px; background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; font-weight:600; font-size:.9rem; }
.smx-chip-primary{ background:#eef2ff; color:#3730a3; border-color:#c7d2fe; }
.smx-chip-ic{ opacity:.85; }

.smx-close{ border:none; background:transparent; font-size:1.25rem; line-height:1; color:#64748b; padding:6px; border-radius:10px; justify-self:end; }
.smx-close:hover{ background:#eef2ff; color:#3730a3; }

.smx-body{ padding:16px; flex:1 1 auto; overflow:auto; display:flex; flex-direction:column; gap:16px; }
.smx-grid{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media (max-width: 992px){ .smx-grid{ grid-template-columns:1fr; } }

.smx-panel{ border:1px solid #e5e7eb; border-radius:14px; overflow:hidden; background:#fff; }
.smx-panel-hd{ padding:10px 14px; font-weight:800; color:#111827; background:linear-gradient(180deg,#ffffff,#f5f6fa); border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:10px; }
.smx-panel-hd i{ color:#6f6bef; }
.smx-panel-bd{ padding:14px; }

.smx-kv-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }
@media (max-width:576px){ .smx-kv-grid{ grid-template-columns:1fr; } }
.smx-kv{ display:grid; grid-template-columns:180px 1fr; gap:10px; align-items:center; padding:10px 12px; border:1px dashed #e5e7eb; border-radius:12px; background:#fff; }
.smx-kv .k{ font-weight:700; color:#475569; }
.smx-kv .v{ font-weight:600; color:#111827; word-break:break-word; }

/* Generic modal (Edit, Delete, Create) */
.modal-backdrop{ position:fixed; inset:0; display:grid; place-items:center; background:rgba(15,18,30,.55); z-index:99999; padding:12px; }
.modal-card{ width:min(640px,96vw); background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.25); overflow:hidden; max-height:92vh; display:flex; flex-direction:column; }
.modal-card.modal-wide{ width:min(880px,96vw); }
.modal-header, .modal-footer{ padding:12px 16px; display:flex; align-items:center; gap:12px; }
.modal-header{ justify-content:space-between; border-bottom:1px solid #f0f0f0; }
.modal-body{ padding:16px; overflow:auto; flex:1 1 auto; }

/* Buttons */
.smx-btn{
  display:inline-flex; align-items:center; gap:8px;
  height:42px; padding:0 14px; border-radius:12px;
  border:1px solid transparent; font-weight:700; cursor:pointer;
  transition:transform .15s ease, filter .15s ease, box-shadow .15s ease, background .15s ease;
  user-select:none;
}
.smx-btn i{ font-size:0.95rem; }
.smx-btn:disabled{ opacity:.6; cursor:not-allowed; pointer-events:none; }
.smx-btn-primary{ background:#7367f0; color:#fff; box-shadow:0 6px 14px rgba(115,103,240,.30); }
.smx-btn-primary:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.smx-btn-ghost{ background:#fff; color:#334155; border-color:#e5e7eb; }
.smx-btn-ghost:hover{ background:#f8fafc; }
.smx-btn-danger{ background:#ef4444; color:#fff; }
.smx-btn-danger:hover{ filter:brightness(1.05); transform:translateY(-1px); }

/* Mini button system */
.btn{
  display:inline-flex; align-items:center; justify-content:center; gap:6px;
  height:40px; padding:0 12px; border-radius:10px; border:1px solid transparent;
  font-weight:600; cursor:pointer; background:#f3f4f6; color:#111827;
  transition:transform .12s ease, filter .12s ease, background .12s ease;
}
.btn:hover{ background:#e5e7eb; }
.btn:disabled{ opacity:.6; cursor:not-allowed; }
.btn-sm{ height:34px; padding:0 10px; font-size:.875rem; border-radius:8px; }
.btn-primary{ background:#7367f0; color:#fff; box-shadow:0 6px 14px rgba(115,103,240,.25); }
.btn-primary:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-danger{ background:#ef4444; color:#fff; }
.btn-danger:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-close{ border:none; background:transparent; font-size:1.25rem; line-height:1; color:#64748b; border-radius:10px; padding:6px; }
.btn-close:hover{ background:#eef2ff; color:#3730a3; }
</style>
