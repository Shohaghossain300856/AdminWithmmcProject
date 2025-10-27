<template>
  <div id="items-page" class="container-xxl flex-grow-1 container-p-y">
    <!-- Header (Stock Summary style) -->
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-2 flex-wrap">
        <h5 class="mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-boxes-stacked me-2"></i> Product List
        </h5>

        <!-- Count -->
        <span class="badge bg-primary ms-2">{{ products.length }} Product</span>

        <!-- Right controls (visual only: search/per-page) -->
        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
          <div class="input-group input-group-sm w-auto">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input class="form-control" placeholder="Search category / subcategory / product…" />
          </div>

          <select class="form-select form-select-sm w-auto">
            <option selected>25 / page</option>
            <option>50 / page</option>
            <option>100 / page</option>
          </select>

          <button class="btn btn-sm btn-outline-primary" @click="productData" :disabled="submitting">
            <i :class="['fa', submitting ? 'fa-spinner fa-spin' : 'fa-rotate']"></i>
            <span class="ms-1">{{ submitting ? 'Loading…' : 'Refresh' }}</span>
          </button>

          <button class="btn btn-primary btn-sm" @click="openCreate">
            <i class="fa fa-plus me-2"></i> Add Product
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table class="table table-hover align-middle mb-0 subcat-table">
            <thead class="table-head">
              <tr>
                <th style="width:70px">Sl</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Product</th>
                <th>Country</th>
                <th>Unit</th>
                <th style="width:180px" class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in products" :key="row?.id ?? i">
                <td>{{ i + 1 }}</td>
                <td>{{ row?.category?.name || row?.category_name || findCatName(row?.category_id) || '—' }}</td>
                <td>{{ row?.subcategory?.sub_category || row?.subcategory_name || findSubcatName(row?.subCatagorie_id) || '—' }}</td>
                <td>{{ row?.Product || row?.product || '—' }}</td>
                <td>{{ row?.country?.name || findCountryName(row?.country_id) || '—' }}</td>
                <td>{{ row?.unit || '—' }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-primary me-2" title="Edit" @click="openEdit(row)">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" title="Delete" @click="openDelete(row)">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!products.length">
                <td colspan="7" class="text-center py-4 text-muted">
                  <i class="fa fa-inbox me-2"></i>No data
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination footer (visual match; logic untouched) -->
        <div class="card-footer d-flex align-items-center justify-content-between" v-if="products.length">
          <div class="text-muted small">
            Showing 1–{{ products.length }} of {{ products.length }} Product
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
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-plus me-2"></i> New Product</h5>
          <button type="button" class="btn-close" @click="closeCreate"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">Category</label>
            <Multiselect
              v-model="form.category_id"
              :options="categoriesOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Search category…"
              class="w-100 p_ms"
            />
          </div>

          <div class="form-row">
            <label class="form-label">Subcategory</label>
            <Multiselect
              v-model="form.subCatagorie_id"
              :options="subCatagoriesOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Search subcategory…"
              class="w-100 p_ms"
            />
          </div>

          <div class="form-row">
            <label class="form-label">Country</label>
            <Multiselect
              v-model="form.country_id"
              :options="countryDataOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Search country…"
              class="w-100 p_ms"
            />
          </div>

          <div class="form-row">
            <label class="form-label">Unit</label>
            <Multiselect
              v-model="form.unit"
              :options="unitOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Select unit…"
              class="w-100 p_ms"
            />
          </div>

          <div class="form-row">
            <label class="form-label">Type</label>
            <div class="d-flex gap-3 align-items-center">
              <label class="form-check">
                <input class="form-check-input" type="radio" value="1" v-model="form.type" />
                <span class="ms-1">Warranty</span>
              </label>
              <label class="form-check">
                <input class="form-check-input" type="radio" value="2" v-model="form.type" />
                <span class="ms-1">Validity</span>
              </label>
            </div>
          </div>

          <div class="form-row">
            <label class="form-label">Product</label>
            <input v-model.trim="form.Product" class="form-input" type="text" placeholder="e.g. SYR-01" />
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
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h5 class="m-0"><i class="fa fa-edit me-2"></i> Edit Product</h5>
          <button type="button" class="btn-close" @click="closeEdit"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <label class="form-label">Category</label>
            <Multiselect
              v-model="editForm.category_id"
              :options="categoriesOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Search category…"
              class="w-100 p_ms"
            />
          </div>

          <div class="form-row">
            <label class="form-label">Subcategory</label>
            <Multiselect
              v-model="editForm.subCatagorie_id"
              :options="subCatagoriesOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Search subcategory…"
              class="w-100 p_ms"
            />
          </div>

          <div class="form-row">
            <label class="form-label">Country</label>
            <Multiselect
              v-model="editForm.country_id"
              :options="countryDataOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Search country…"
              class="w-100 p_ms"
            />
          </div>

          <div class="form-row">
            <label class="form-label">Unit</label>
            <Multiselect
              v-model="editForm.unit"
              :options="unitOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Select unit…"
              class="w-100 p_ms"
            />
          </div>

          <div class="form-row">
            <label class="form-label">Product</label>
            <input v-model.trim="editForm.Product" class="form-input" type="text" />
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
      <div class="modal-card" role="dialog" aria-modal="true">
        <!-- ⚠️ Delete header stays as your original light red style -->
        <div class="modal-header danger">
          <h5 class="m-0"><i class="fa fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
          <button type="button" class="btn-close" @click="closeDelete"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">
            Are you sure you want to delete
            <b>{{ deleteRow?.Product || deleteRow?.name || 'this item' }}</b>?
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
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const toast = useToast()

// $http fallback
const inst = getCurrentInstance()
const http =
  inst?.appContext?.config?.globalProperties?.$http ??
  (typeof window !== 'undefined' ? window.axios : null)

if (!http) {
  throw new Error('HTTP client not found: provide app.config.globalProperties.$http or window.axios')
}

const submitting = ref(false)
const products = ref([])
const categories = ref([])
const subCatagories = ref([])
const countryData = ref([])

/* options (computed for Multiselect) */
const categoriesOptions = computed(() => {
  const rows = Array.isArray(categories.value) ? categories.value : []
  return rows.map(r => ({ value: Number(r.id), label: String(r.name ?? '') }))
})

const subCatagoriesOptions = computed(() => {
  const rows = Array.isArray(subCatagories.value) ? subCatagories.value : []
  return rows.map(r => ({
    value: Number(r.id),
    label: String((r.sub_category ?? '') + (r.sub_category_code ? ` — [${r.sub_category_code}]` : ''))
  }))
})

const countryDataOptions = computed(() => {
  const rows = Array.isArray(countryData.value) ? countryData.value : []
  return rows.map(r => ({
    value: Number(r.id),
    label: String(r.name ?? ''),
  }))
})

const unitOptions = [
  { value: 'Pc', label: 'Pc' },
  { value: 'Ml', label: 'Ml' },
]

/* helpers */
function findCatName (id) {
  if (!id) return ''
  const hit = categories.value.find(c => Number(c.id) === Number(id))
  return hit?.name || ''
}
function findSubcatName (id) {
  if (!id) return ''
  const hit = subCatagories.value.find(s => Number(s.id) === Number(id))
  return hit?.sub_category || ''
}
function findCountryName (id) {
  if (!id) return ''
  const hit = countryData.value.find(c => Number(c.id) === Number(id))
  return hit?.name || ''
}

/* CREATE */
const createOpen = ref(false)
const form = ref({
  type: 1,
  category_id: null,
  subCatagorie_id: null,
  Product: '',
  unit: '',
  country_id: null
})
function openCreate () { createOpen.value = true }
function closeCreate () {
  createOpen.value = false
  form.value = { type: 1, category_id: null, subCatagorie_id: null, Product: '', unit: '', country_id: null }
}
async function createItem () {
  if (!form.value.category_id) return toast.error('Select a category')
  if (!form.value.subCatagorie_id) return toast.error('Select a subcategory')
  if (!form.value.Product?.trim()) return toast.error('Product is required')

  try {
    submitting.value = true
    const payload = {
      type: Number(form.value.type),
      unit: form.value.unit || null,
      category_id: Number(form.value.category_id),
      subCatagorie_id: Number(form.value.subCatagorie_id),
      Product: form.value.Product,
      country_id: form.value.country_id ? Number(form.value.country_id) : null
    }
    const res = await http.post('/product', payload)
    const created = res?.data?.data ?? res?.data
    if (created && created.id) {
      const normalized = {
        ...created,
        category_id: created.category_id ?? payload.category_id,
        subCatagorie_id: created.subCatagorie_id ?? payload.subCatagorie_id,
        country_id: created.country_id ?? payload.country_id,
        unit: created.unit ?? payload.unit,
        Product: created.Product ?? payload.Product,
        category: created.category || { id: created.category_id ?? payload.category_id, name: findCatName(created.category_id ?? payload.category_id) },
        subcategory: created.subcategory || { id: created.subCatagorie_id ?? payload.subCatagorie_id, sub_category: findSubcatName(created.subCatagorie_id ?? payload.subCatagorie_id) },
        country: created.country || (created.country_id ? { id: created.country_id, name: findCountryName(created.country_id) } : null)
      }
      products.value = [normalized, ...products.value]
    }
    toast.success('Created successfully')
    closeCreate()
  } catch (e) {
    console.error(e)
    toast.error(e?.response?.data?.message || 'Create failed')
  } finally {
    submitting.value = false
  }
}

/* EDIT */
const editOpen = ref(false)
const editId = ref(null)
const editForm = ref({
  type: '1',
  category_id: null,
  subCatagorie_id: null,
  country_id: null,
  unit: '',
  Product: ''
})

function openEdit (row) {
  if (!categories.value?.length) fetchCategories()
  if (!subCatagories.value?.length) subCategorys()
  if (!countryData.value?.length) fetchCountry()

  editId.value = row?.id
  editForm.value = {
    type: String(row?.type ?? '1'),
    category_id: Number(row?.category_id ?? row?.category?.id ?? null),
    subCatagorie_id: Number(row?.subCatagorie_id ?? row?.subcategory?.id ?? null),
    country_id: row?.country_id != null
      ? Number(row.country_id)
      : (row?.country?.id != null ? Number(row.country.id) : null),
    unit: row?.unit ?? '',
    Product: row?.Product ?? row?.product ?? ''
  }
  editOpen.value = true
}

function closeEdit () {
  editOpen.value = false
  editId.value = null
  editForm.value = { type: '1', category_id: null, subCatagorie_id: null, country_id: null, unit: '', Product: '' }
}

async function updateItem () {
  if (!editId.value) return
  if (!editForm.value.category_id) return toast.error('Select a category')
  if (!editForm.value.subCatagorie_id) return toast.error('Select a subcategory')
  if (!editForm.value.Product?.trim()) return toast.error('Product is required')

  try {
    submitting.value = true
    const payload = {
      type: String(editForm.value.type),
      category_id: Number(editForm.value.category_id),
      subCatagorie_id: Number(editForm.value.subCatagorie_id),
      country_id: editForm.value.country_id ? Number(editForm.value.country_id) : null,
      unit: editForm.value.unit || null,
      Product: editForm.value.Product
    }
    const res = await http.put(`/product/${editId.value}`, payload)
    const updated = res?.data?.data ?? res?.data
    if (updated && updated.id) {
      const normalized = {
        ...products.value.find(p => Number(p.id) === Number(updated.id)),
        ...updated,
        category_id: updated.category_id ?? payload.category_id,
        subCatagorie_id: updated.subCatagorie_id ?? payload.subCatagorie_id,
        country_id: updated.country_id ?? payload.country_id,
        unit: updated.unit ?? payload.unit,
        Product: updated.Product ?? payload.Product,
        category: updated.category || { id: (updated.category_id ?? payload.category_id), name: findCatName(updated.category_id ?? payload.category_id) },
        subcategory: updated.subcategory || { id: (updated.subCatagorie_id ?? payload.subCatagorie_id), sub_category: findSubcatName(updated.subCatagorie_id ?? payload.subCatagorie_id) },
        country: updated.country || ((updated.country_id ?? payload.country_id) ? { id: (updated.country_id ?? payload.country_id), name: findCountryName(updated.country_id ?? payload.country_id) } : null)
      }

      const idx = products.value.findIndex(p => Number(p.id) === Number(updated.id))
      if (idx !== -1) products.value.splice(idx, 1, normalized)
    }
    toast.success('Updated successfully')
    closeEdit()
  } catch (e) {
    console.error(e)
    const resp = e?.response
    const msg =
      resp?.data?.message ||
      (resp?.status === 422 ? 'Validation failed' :
       resp?.status === 404 ? 'Item not found' :
       'Update failed')
    toast.error(msg)
  } finally {
    submitting.value = false
  }
}

/* DELETE */
const deleteOpen = ref(false)
const deleteRow = ref(null)

function openDelete (row) { deleteRow.value = row; deleteOpen.value = true }
function closeDelete () { deleteOpen.value = false; deleteRow.value = null }

async function doDelete () {
  if (!deleteRow.value?.id) return
  try {
    submitting.value = true
    await http.delete(`/product/${deleteRow.value.id}`)
    products.value = products.value.filter(p => Number(p.id) !== Number(deleteRow.value.id))
    toast.success('Deleted')
    closeDelete()
  } catch (e) {
    console.error(e)
    const resp = e?.response
    const msg =
      resp?.data?.message ||
      (resp?.status === 404 ? 'Item not found' :
       resp?.status === 409 ? 'Cannot delete: in use' :
       'Delete failed')
    toast.error(msg)
  } finally {
    submitting.value = false
  }
}

/* fetchers */
async function fetchCategories () {
  try {
    const res = await http.get('/catagories/create')
    categories.value = res?.data?.data ?? []
  } catch (e) {
    console.error(e); toast.error('Failed to load categories')
  }
}

async function fetchCountry () {
  try {
    const res = await http.get('/country-get')
    const rows = Array.isArray(res?.data?.data)
      ? res.data.data
      : (Array.isArray(res?.data) ? res.data : [])
    countryData.value = rows
  } catch (e) {
    console.error(e); toast.error('Failed to load countries')
  }
}

async function subCategorys () {
  try {
    const res = await http.get('/subCatagories/create')
    subCatagories.value = res?.data?.data ?? []
  } catch (e) {
    console.error(e); toast.error('Failed to load subcategories')
  }
}

async function productData () {
  try {
    const res = await http.get('/product/create')
    const rows = res?.data?.data ?? res?.data ?? []

    products.value = rows.map(r => {
      const cid = (r.country_id != null) ? Number(r.country_id)
               : (r?.country?.id != null ? Number(r.country.id) : null)
      return {
        ...r,
        category_id: Number(r.category_id ?? r?.category?.id ?? null),
        subCatagorie_id: Number(r.subCatagorie_id ?? r?.subcategory?.id ?? null),
        country_id: cid,
        country: r.country ?? (cid ? { id: cid, name: findCountryName(cid) } : null),
        unit: r.unit ?? ''
      }
    })
  } catch (e) {
    console.error(e); toast.error('Failed to load items')
  }
}

onMounted(async () => {
  await Promise.all([productData(), fetchCountry(), fetchCategories(), subCategorys()])
})
</script>

<style scoped>
/* Header button */
.p_create-btn{background:#7367f0;border:none;box-shadow:0 6px 14px rgba(115,103,240,.3);color:#fff}
.p_create-btn:hover{filter:brightness(1.05);transform:translateY(-1px)}

/* Table (Stock Summary style) */
.table-scroll{max-height:65vh;overflow-y:auto;border-radius:.5rem;border:1px solid rgba(0,0,0,.08)}
.table thead th{color:#fff;position:sticky;top:0;z-index:1}
.table-head{background:#7367f0;color:#fff}
.table th{text-transform:uppercase;font-size:.8125rem;letter-spacing:.2px}

/* Modal system */
.modal-backdrop{position:fixed;inset:0;display:grid;place-items:center;background:rgba(15,18,30,.55);z-index:99999;padding:12px}
.modal-card{width:min(640px,96vw);background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.25);overflow:hidden;max-height:92vh;display:flex;flex-direction:column;position:relative}
.modal-header{position:sticky;top:0;z-index:10;background:#fff;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f0f0f0}
/* ✅ Delete modal header stays light-red like your original */
.modal-header.danger{background:#fff5f5;border-bottom-color:#fecaca}
.modal-body{flex:1 1 auto;min-height:0;overflow:auto;padding:16px;background:#fff}
.modal-footer{flex:0 0 auto;padding:12px 16px;display:flex;align-items:center;gap:12px;border-top:1px solid #f0f0f0;background:#fff}

/* Form */
.form-row{display:flex;flex-direction:column;gap:6px;margin-bottom:12px}
.form-label{font-weight:700;color:#334155;margin-bottom:4px;font-size:14px}
.form-input,.p_input{height:44px;border:1px solid #e5e7eb;border-radius:12px;padding:0 12px;outline:none;background:#fff;width:100%}
.form-input:focus,.p_input:focus{border-color:#7367f0;box-shadow:0 0 0 4px rgba(115,103,240,.15)}

/* Buttons */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:40px;padding:0 12px;border-radius:10px;border:1px solid transparent;font-weight:600;cursor:pointer;background:#f3f4f6;color:#111827;transition:transform .12s ease,filter .12s ease,background .12s ease}
.btn:hover{background:#e5e7eb}
.btn:disabled{opacity:.6;cursor:not-allowed}
.btn-sm{height:34px;padding:0 10px;font-size:.875rem;border-radius:8px}
.btn-primary{background:#7367f0;color:#fff;box-shadow:0 6px 14px rgba(115,103,240,.25)}
.btn-primary:hover{filter:brightness(1.05);transform:translateY(-1px)}
.btn-danger{background:#ef4444;color:#fff}
.btn-danger:hover{filter:brightness(1.05);transform:translateY(-1px)}
.btn-close{border:none;background:transparent;font-size:1.25rem;line-height:1;color:#64748b;border-radius:10px;padding:6px}
.btn-close:hover{background:#eef2ff;color:#3730a3}
</style>
