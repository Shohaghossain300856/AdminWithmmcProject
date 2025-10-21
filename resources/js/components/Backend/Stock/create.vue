<!-- resources/js/components/Backend/Stock/AddProductsToStock.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="p_filters-card">
      <!-- Header -->
      <div class="p_filters-header">
        <div class="d-flex align-items-center gap-2">
          <i class="fa fa-list-check"></i>
          <h5 class="mb-0">Add Products to Stock</h5>
          <span class="badge bg-primary ms-2">{{ products.length }} Items</span>
        </div>
      </div>

      <!-- Top form -->
      <div class="p_memo-section">
        <div class="row g-3">
          <!-- Memo -->
          <div class="col-md-3">
            <label class="p_label"><i class="fa fa-hashtag me-1"></i> Memo No</label>
            <div class="p_input-group">
              <span class="p_input-icon"><i class="fa fa-receipt"></i></span>
              <input
                class="form-control p_input"
                v-model.trim="memo_no"
                placeholder="Enter memo no (optional)"
              />
            </div>
          </div>

          <!-- Fund (required) -->
          <div class="col-md-3">
            <label class="p_label"><i class="fa fa-link me-1"></i> Fund</label>
            <div class="p_input-group">
              <span class="p_input-icon"><i class="fa fa-paperclip"></i></span>
              <Multiselect
                v-model="fund_id"
                :options="fundsOptions"
                :searchable="true"
                :close-on-select="true"
                :can-clear="true"
                placeholder="Search fund..."
                class="w-100 p_ms p_like-input"
              />
            </div>
            <small class="text-danger" v-if="submitTried && !fund_id">Fund is required</small>
          </div>

          <!-- Supplier (required) -->
          <div class="col-md-3">
            <label class="p_label"><i class="fa fa-user-tie me-1"></i> Supplier</label>
            <div class="p_input-group">
              <span class="p_input-icon"><i class="fa fa-truck-field"></i></span>
              <Multiselect
                v-model="supplier_id"
                :options="suppliersOptions"
                :searchable="true"
                :close-on-select="true"
                :can-clear="true"
                placeholder="Search supplier..."
                class="w-100 p_ms p_like-input"
              />
            </div>
            <small class="text-danger" v-if="submitTried && !supplier_id">Supplier is required</small>
          </div>

          <!-- Date (required) -->
          <div class="col-md-3">
            <label class="p_label"><i class="fa fa-calendar me-1"></i> Date</label>
            <div class="p_input-group">
              <span class="p_input-icon"><i class="fa fa-calendar-days"></i></span>
              <input type="date" class="form-control p_input" v-model="date" />
            </div>
            <small class="text-danger" v-if="submitTried && !date">Date is required</small>
          </div>
        </div>
      </div>

      <!-- Subcategory picker -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="p_label"><i class="fa fa-layer-group me-1"></i> Subcategory</label>
          <div class="p_input-group">
            <span class="p_input-icon"><i class="fa fa-list"></i></span>
            <Multiselect
              v-model="subCatagorie_id"
              :options="subCatagoriesOptions"
              :searchable="true"
              :close-on-select="true"
              :can-clear="true"
              placeholder="Search subcategory…"
              class="w-100 p_ms p_like-input"
            />
          </div>
        </div>
      </div>

      <!-- Products table -->
      <div class="card mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h6 class="mb-0">
            <i class="fa fa-layer-group me-2"></i> Products in this Subcategory
          </h6>
          <span class="badge bg-info">{{ productsInSelectedSubcat.length }} Items</span>
        </div>

        <div class="card-datatable text-nowrap">
          <div class="table-scroll">
            <table class="table table-hover align-middle mb-0">
              <thead class="p_thead">
                <tr>
                  <th style="width:60px">Sl</th>
                  <th style="width:260px">Product</th>
                  <th style="width:120px">Qty</th>
                  <th style="width:180px">Warranty/Validity Start</th>
                  <th style="width:180px">Warranty/Validity End</th>
                </tr>
              </thead>

              <tbody v-if="subCatagorie_id">
                <tr v-for="(p,i) in productsInSelectedSubcat" :key="p.id">
                  <td>{{ i+1 }}</td>
                  <td class="fw-semibold">
                    {{ p.Product ?? p.product ?? p.name }}
                    <small class="ms-2">
                      <span class="badge bg-label-primary">
                        {{ productTypeById(p.id) === 1 ? 'Warranty' : productTypeById(p.id) === 2 ? 'Validity' : 'Other' }}
                      </span>
                    </small>
                  </td>

                  <!-- Qty -->
                  <td>
                    <input
                      type="number"
                      class="form-control"
                      min="0"
                      v-model.number="rs(p.id).qty"
                      @focus="$event.target.select()"
                    />
                  </td>

                  <!-- One pair of dates; will be mapped to warranty OR validity based on type -->
                  <td><input type="date" class="form-control" v-model="rs(p.id).warranty_start" /></td>
                  <td><input type="date" class="form-control" v-model="rs(p.id).warranty_end" /></td>
                </tr>

                <tr v-if="!productsInSelectedSubcat.length">
                  <td colspan="5" class="text-center py-4">No products found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="text-end mt-3">
        <button class="btn btn-success" :disabled="!canSubmit" @click="submitStock">
          <i class="fa fa-floppy-disk me-1"></i> Submit Stock
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance, computed } from 'vue'
import { useToast } from 'vue-toastification'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

/* essentials */
const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()

/* state */
const submitting = ref(false)
const submitTried = ref(false)

const date = ref('')
const memo_no = ref('')

const products = ref([])
const subCatagories = ref([])
const funds = ref([])
const suppliers = ref([])

const subCatagorie_id = ref('')
const fund_id = ref('')
const supplier_id = ref('')

const rowState = ref({})
function rs (pid) {
  if (!rowState.value[pid]) {
    rowState.value[pid] = {
      qty: 0,
      warranty_start: '',
      warranty_end: '',
      validity_start: '',
      validity_end: ''
    }
  }
  return rowState.value[pid]
}

/* helpers */
function productTypeById (pid) {
  const prod = (products.value || []).find(p => String(p.id) === String(pid))
  return Number(prod?.type || 0)
}

const fundsOptions = computed(() =>
  (funds.value || []).map(f => ({ value: f.id, label: String(f.fund ?? '') }))
)

const subCatagoriesOptions = computed(() =>
  (Array.isArray(subCatagories.value) ? subCatagories.value : []).map(r => ({
    value: Number(r.id),
    label: String((r.sub_category ?? '') + (r.sub_category_code ? ` — [${r.sub_category_code}]` : ''))
  }))
)

const suppliersOptions = computed(() =>
  (suppliers.value || []).map(s => ({ value: s.id, label: String(s.supplier ?? s.name ?? '') }))
)

const productsInSelectedSubcat = computed(() => {
  if (!subCatagorie_id.value) return []
  return (products.value || []).filter(p => String(p.subCatagorie_id) === String(subCatagorie_id.value))
})

/* qty > 0 check */
const hasAnyQty = computed(() =>
  Object.values(rowState.value).some(r => Number(r.qty) > 0)
)

const canSubmit = computed(() =>
  !!fund_id.value && !!supplier_id.value && !!date.value && hasAnyQty.value && !submitting.value
)

/* submit: payload matches your store() validator 1:1, with type-based mapping */
async function submitStock () {
  submitTried.value = true
  if (!canSubmit.value) return toast.error('Please fill required fields and set qty for at least one product.')

  submitting.value = true
  try {
    const items = []
    for (const [pid, r] of Object.entries(rowState.value)) {
      const qty = Number(r.qty || 0)
      if (qty <= 0) continue

      const typeId = productTypeById(pid)
      const start = r.warranty_start || null
      const end   = r.warranty_end   || null

      items.push({
        product_id: Number(pid),
        qty,
        warranty_start: typeId === 1 ? start : null,
        warranty_end  : typeId === 1 ? end   : null,
        validity_start: typeId === 2 ? start : null,
        validity_end  : typeId === 2 ? end   : null,
      })
    }

    if (!items.length) {
      submitting.value = false
      return toast.error('No items with quantity.')
    }

    const payload = {
      fund_id: Number(fund_id.value),
      supplier_id: Number(supplier_id.value),
      memo_no: memo_no.value ? String(memo_no.value) : null,
      date: date.value,
      products: items
    }

    const res = await http.post('/stock-create', payload)
    if (res?.data?.status === false) throw new Error(res?.data?.message || 'Save failed')

    toast.success(res?.data?.message || 'Stock saved successfully')

    memo_no.value = ''
    rowState.value = {}
  } catch (e) {
    console.error(e)
    toast.error(e?.message || 'Failed to save stock')
  } finally {
    submitting.value = false
  }
}

/* API */
async function fetchProducts () {
  try {
    const res = await http.get('/product/create')
    products.value = res?.data?.data ?? res?.data ?? []
  } catch (e) { console.error(e); toast.error('Failed to load products') }
}

async function fetchSubcats () {
  try {
    const res = await http.get('/subCatagories/create')
    subCatagories.value = res?.data?.data ?? []
  } catch (e) { console.error(e); toast.error('Failed to load subcategories') }
}

async function fetchFunds () {
  try {
    const res = await http.get('/fund/create')
    funds.value = Array.isArray(res?.data?.funds) ? res.data.funds : (res?.data?.data ?? [])
  } catch (e) { console.error(e); toast.error('Failed to load funds') }
}

async function fetchSuppliers () {
  try {
    const { data } = await http.get('/Supplier/create') // capital S as per your route
    suppliers.value = Array.isArray(data?.data) ? data.data : (Array.isArray(data?.suppliers) ? data.suppliers : data)
  } catch (e) { console.error(e); suppliers.value = []; toast.error('Failed to load suppliers') }
}

/* lifecycle */
onMounted(() => {
  const today = new Date().toISOString().split('T')[0]
  if (!date.value) date.value = today
  fetchProducts()
  fetchSubcats()
  fetchFunds()
  fetchSuppliers()
})
</script>

<style scoped>
/* Card + header */
.p_filters-card {
  border:1px solid #e0e7ff; border-radius:16px; padding:16px; background:#fff;
  box-shadow:0 4px 12px rgba(0,0,0,.05);
}
.p_filters-header {
  display:flex; align-items:center; justify-content:space-between;
  padding-bottom:12px; border-bottom:1px dashed #cbd5e1; margin-bottom:14px;
}
.p_memo-section { border-radius:14px; padding:16px; margin-bottom:16px; }

/* Labels */
.p_label { font-weight:700; color:#1e293b; margin-bottom:6px; display:block; }

/* Input group (shared look) */
.p_input-group { position:relative; }
.p_input-group .p_input-icon {
  position:absolute; top:50%; left:10px; transform:translateY(-50%);
  pointer-events:none; opacity:.65; font-size:14px;
}
.p_input-group .p_input, .p_input-group .form-control {
  padding-left:36px; height:44px; border:1px solid #e5e7eb; background:#fff;
  transition: box-shadow .15s, border-color .15s;
}
.p_input-group .p_input:focus, .p_input-group .form-control:focus {
  border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12);
}

/* Make Multiselect look like the above inputs */
:deep(.p_like-input .multiselect) {
  min-height:40px; border-radius:10px; border:1px solid #e5e7eb; background:#fff;
  padding-left:26px; /* since we already have left 36px on the wrapper, multiselect inner needs some space */
}
:deep(.p_like-input .multiselect.is-active) {
  border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12);
}
:deep(.p_like-input .multiselect-search) {
  height:38px;
}
:deep(.p_like-input .multiselect-single-label),
:deep(.p_like-input .multiselect-placeholder) {
  padding-left:10px;
}

/* Older selector kept for safety with your existing class */
:deep(.p_ms .multiselect) { min-height:40px; border-radius:10px; border:1px solid #e5e7eb; background:#fff; }
:deep(.p_ms .multiselect:focus-within) { border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12); }
:deep(.p_ms .multiselect-input) { padding-left:34px; }

/* Table */
.table-scroll { overflow-x:auto; max-width:100%; }
.table thead th { position:sticky; top:0; z-index:1; }

/* Force header bg + white text reliably */
.p_thead {
  background:#7367f0 !important;
}
.p_thead th {
  color:#fff !important;
  border-color:#7367f0 !important;
}

/* Cells */
.table td input.form-control { height: 38px; }

/* Badges */
.badge { font-size:.75rem; padding:.35em .65em; }
.bg-label-primary { background-color:#e7e7ff; color:#7367f0; }
</style>
