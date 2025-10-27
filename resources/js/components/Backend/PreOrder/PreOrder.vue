<!-- resources/js/components/Backend/Stock/AddProductsToStock.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="p_filters-card">
      <!-- Header -->
      <div class="p_filters-header">
        <div class="d-flex align-items-center gap-2">
          <i class="fa fa-list-check"></i>
          <h5 class="mb-0">Pre Order</h5>
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
              <input class="form-control p_input" v-model.trim="memo_no" placeholder="Enter memo no (optional)" />
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
      <div class="row g-3">
        <div class="col-md-6">
          <label class="p_label"><i class="fa fa-layer-group me-1"></i> Subcategory (multiple)</label>
          <div class="p_input-group">
            <span class="p_input-icon"><i class="fa fa-list"></i></span>
            <Multiselect
              v-model="subCatagorie_ids"
              :options="subCatagoriesOptions"
              :searchable="true"
              :close-on-select="false"
              :can-clear="true"
              mode="multiple"
              placeholder="Select one or more subcategories…"
              class="w-100 p_ms p_like-input"
            />
          </div>
        </div>
      </div>

      <!-- Selected subcategory chips -->
      <div class="card mt-3" v-if="selectedSubcatBadges.length">
        <div class="card-body d-flex flex-wrap align-items-center gap-2">
          <strong class="me-1"><i class="fa fa-tags me-1"></i> Selected:</strong>

          <span
            v-for="sc in selectedSubcatBadges"
            :key="sc.value"
            class="badge rounded-pill text-bg-secondary chip d-inline-flex align-items-center"
            :title="sc.label"
          >
            {{ sc.label }}
            <button
              class="btn-close btn-close-white btn-close-xs ms-2"
              @click="removeSubcat(sc.value)"
              aria-label="Remove subcategory"
            ></button>
          </span>

          <button class="btn btn-sm btn-outline-danger ms-auto" @click="clearAllSubcats">
            <i class="fa fa-xmark me-1"></i> Clear all
          </button>
        </div>
      </div>

      <!-- Products table -->
      <div class="card mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h6 class="mb-0"><i class="fa fa-layer-group me-2"></i> Products in Selected Subcategories</h6>
          <span class="badge bg-info">{{ productsInSelectedSubcats.length }} Items</span>
        </div>

        <div class="card-datatable text-nowrap">
          <div class="table-scroll">
            <table class="table table-hover align-middle mb-0">
              <thead class="p_thead">
                <tr>
                  <th style="width:60px">Sl</th>
                  <th style="width:260px">Product</th>
                  <th style="width:180px">Unit Price</th>
                  <th style="width:120px">Qty</th>
                  <th style="width:180px">Total Price</th>
                </tr>
              </thead>

              <tbody v-if="subCatagorie_ids && subCatagorie_ids.length">
                <tr v-for="(p,i) in productsInSelectedSubcats" :key="p.id">
                  <td>{{ i+1 }}</td>
                  <td class="fw-semibold">{{ p.Product ?? p.product ?? p.name ?? ('#' + p.id) }}</td>

                  <!-- Unit Price -->
                  <td>
                    <input
                      type="number"
                      class="form-control"
                      min="0"
                      step="0.01"
                      placeholder="0.00"
                      v-model.number="rs(p.id).unit_price"
                      @focus="$event.target.select()"
                    />
                  </td>

                  <!-- Qty -->
                  <td>
                    <input
                      type="number"
                      class="form-control"
                      min="0"
                      step="1"
                      v-model.number="rs(p.id).qty"
                      @focus="$event.target.select()"
                    />
                  </td>

                  <!-- Total (readonly) -->
                  <td>
                    <input
                      type="number"
                      class="form-control"
                      min="0"
                      step="0.01"
                      readonly
                      :value="formatMoney(rs(p.id).total_price)"
                    />
                  </td>
                </tr>

                <tr v-if="!productsInSelectedSubcats.length">
                  <td colspan="5" class="text-center py-4">No products found</td>
                </tr>
              </tbody>

              <tbody v-else>
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">Please select one or more subcategories</td>
                </tr>
              </tbody>

            </table>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="text-end mt-3">
        <button class="btn btn-success" :disabled="!canSubmit" @click="submitStock">
          <i class="fa fa-floppy-disk me-1"></i> Submit Preorder
        </button>
      </div>
    </div>

    <!-- ===================== STATIC PreOrder ITEMS LIST ===================== -->
    <div class="card mt-4">
      <div class="card-body d-flex align-items-center gap-2 flex-wrap">
        <h5 class="mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-clipboard-list me-2"></i> Preorder Invoice List
        </h5>
        <!-- Static count badges just for looks -->
        <span class="badge bg-primary ms-2">{{ preOrders.length }}</span>
      </div>

      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table class="table table-hover align-middle mb-0 subcat-table">
            <thead class="table-head">
              <tr>
                <th style="width:70px">Sl</th>
                <th>Memo No</th>
                <th>Date</th>
                <th>Fund</th>
                <th>Supplier</th>
                <th class="text-end" style="width:140px">Action</th>
              </tr>
            </thead>

            <tbody>
             <tr v-for="(preOrder, index) in preOrders" :key="preOrder.id || index">
                <td>{{index + 1}}</td>
                <td class="fw-medium">{{ preOrder.memo_no }}</td>
                <td>{{ preOrder.date }}</td>
                <td>{{ preOrder?.fund?.fund }}</td>
                <td>{{ preOrder?.supplier?.supplier }}</td>
                <td class="text-end">
                  <a
                      :href="`/backend/preorder-pdf/${preOrder.id}`"
                      class="btn btn-sm btn-primary me-2"
                      title="Show"
                      target="_blank"
                    >
                      <i class="fa fa-eye"></i>
                    </a>
                </td>
              </tr>


              <tr v-if="!preOrders.length" >
                <td colspan="6" class="text-center text-muted py-3">Static preview — no actions</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Static footer text -->
      <div class="card-footer d-flex align-items-center justify-content-between">
        <div class="text-muted small">
          Showing 1–3 of 3 items
        </div>
        <div class="text-muted small">Pagination disabled (static)</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance, computed, watch } from 'vue'
import { useToast } from 'vue-toastification'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()

const submitting = ref(false)
const submitTried = ref(false)

const date = ref('')
const memo_no = ref('')

const products = ref([])
const subCatagories = ref([])
const funds = ref([])
const suppliers = ref([])
const preOrders = ref([])

const subCatagorie_ids = ref([]) 
const fund_id = ref('')
const supplier_id = ref('')

/* ------ Row state for product entries ------ */
const rowState = ref({})
function rs (pid) {
  const key = String(pid)
  if (!rowState.value[key]) {
    rowState.value[key] = { qty: 0, unit_price: 0, total_price: 0 }
  }
  return rowState.value[key]
}

watch(rowState, (val) => {
  for (const [pid, r] of Object.entries(val)) {
    const qty  = Number(r.qty || 0)
    const rate = Number(r.unit_price || 0)
    const total = qty * rate
    r.total_price = Number.isFinite(total) ? Number(total.toFixed(2)) : 0
  }
}, { deep: true })

function formatMoney(n) { const x = Number(n || 0); return x.toFixed(2) }
function money(n) { return (Number(n) || 0).toFixed(2) }

/* ------ Dropdown options ------ */
const fundsOptions = computed(() =>
  (funds.value || []).map(f => ({ value: f.id, label: String(f.fund ?? '') }))
)
const subCatagoriesOptions = computed(() =>
  (Array.isArray(subCatagories.value) ? subCatagories.value : []).map(r => ({
    value: Number(r.id),
    label: String((r.sub_category ?? r.name ?? '') + (r.sub_category_code ? ` — [${r.sub_category_code}]` : ''))
  }))
)
const suppliersOptions = computed(() =>
  (suppliers.value || []).map(s => ({ value: s.id, label: String(s.supplier ?? s.name ?? '') }))
)

/* ------ Helpers ------ */
function getSubcatId(p) {
  return Number(
    p?.subCatagorie_id ??
    p?.subCategory_id ??
    p?.sub_category_id ??
    p?.subcategory_id ??
    p?.subcat_id ??
    p?.sub_category ??
    0
  )
}

/* ------ Products filtered by selected subcategories ------ */
const productsInSelectedSubcats = computed(() => {
  const selected = subCatagorie_ids.value || []
  if (!Array.isArray(selected) || selected.length === 0) return []
  const set = new Set(selected.map(Number))
  const filtered = (products.value || []).filter(p => set.has(getSubcatId(p)))

  // unique by product id
  const seen = new Set()
  const unique = []
  for (const p of filtered) {
    if (!seen.has(p.id)) { seen.add(p.id); unique.push(p) }
  }
  return unique
})

/* ------ Selected badges for subcategories ------ */
const selectedSubcatBadges = computed(() => {
  const map = new Map(subCatagoriesOptions.value.map(o => [Number(o.value), o.label]))
  return (subCatagorie_ids.value || []).map(v => ({ value: Number(v), label: map.get(Number(v)) || String(v) }))
})
function removeSubcat(id) {
  subCatagorie_ids.value = (subCatagorie_ids.value || []).filter(v => Number(v) !== Number(id))
}
function clearAllSubcats() {
  subCatagorie_ids.value = []
}

/* ------ Clean row state when list changes ------ */
watch(productsInSelectedSubcats, (list) => {
  const validIds = new Set(list.map(p => String(p.id)))
  for (const key of Object.keys(rowState.value)) {
    if (!validIds.has(key)) delete rowState.value[key]
  }
})

/* ------ Submit control ------ */
const hasAnyQty = computed(() =>
  Object.values(rowState.value).some(r => Number(r.qty) > 0)
)
const canSubmit = computed(() =>
  !!fund_id.value && !!supplier_id.value && !!date.value && hasAnyQty.value && !submitting.value
)

/* ------ Submit preorder ------ */
async function submitStock () {
  submitTried.value = true
  if (!canSubmit.value) return toast.error('Please fill required fields and set qty for at least one product.')

  submitting.value = true
  try {
    const items = []
    for (const [pid, r] of Object.entries(rowState.value)) {
      const qty   = Number(r.qty || 0)
      const price = Number(r.unit_price ?? 0)
      if (qty <= 0) continue
      if (!Number.isFinite(price) || price < 0) continue
      items.push({ product_id: Number(pid), qty, unit_price: price })
    }

    if (!items.length) {
      submitting.value = false
      return toast.error('No valid items to save.')
    }

    const payload = {
      fund_id: Number(fund_id.value),
      supplier_id: Number(supplier_id.value),
      memo_no: memo_no.value ? String(memo_no.value) : null,
      date: date.value,
      products: items
    }

    const res = await http.post('/pre-order', payload)
    if (res?.data?.status === false) throw new Error(res?.data?.message || 'Save failed')
    toast.success(res?.data?.message || 'Preorder saved successfully')

    memo_no.value = ''
    rowState.value = {}
  } catch (e) {
    console.error(e)
    toast.error(e?.message || 'Failed to save preorder')
  } finally {
    submitting.value = false
  }
}

/* ------ Initial data (no preorders list fetching) ------ */
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
    const { data } = await http.get('/Supplier/create')
    suppliers.value = Array.isArray(data?.data) ? data.data : (Array.isArray(data?.suppliers) ? data.suppliers : data)
  } catch (e) { console.error(e); suppliers.value = []; toast.error('Failed to load suppliers') }
}


async function fetchPreOrders () {
  try {
    const { data } = await http.get('/pre-order/create')
    console.log(data)
    preOrders.value = data?.data;

  } catch (e) {
    console.error(e)
    preOrders.value = []
    toast.error('Failed to load PreOrders')
  }
}

onMounted(() => {
  const today = new Date().toISOString().split('T')[0]
  if (!date.value) date.value = today
  fetchProducts()
  fetchSubcats()
  fetchFunds()
  fetchSuppliers()
  fetchPreOrders()
})
</script>

<style scoped>
.p_filters-card {
  border:1px solid #e0e7ff; border-radius:16px; padding:16px; background:#fff;
  box-shadow:0 4px 12px rgba(0,0,0,.05);
}
.p_filters-header {
  display:flex; align-items:center; justify-content:space-between;
  padding-bottom:12px; border-bottom:1px dashed #cbd5e1; margin-bottom:14px;
}
.p_memo-section { border-radius:14px; padding:16px; margin-bottom:16px; }
.p_label { font-weight:700; color:#1e293b; margin-bottom:6px; display:block; }
.p_input-group { position:relative; }
.p_input-group .p_input-icon { position:absolute; top:50%; left:10px; transform:translateY(-50%); pointer-events:none; opacity:.65; font-size:14px; }
.p_input-group .p_input, .p_input-group .form-control { padding-left:36px; height:44px; border:1px solid #e5e7eb; background:#fff; transition: box-shadow .15s, border-color .15s; }
.p_input-group .p_input:focus, .p_input-group .form-control:focus { border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12); }
:deep(.p_like-input .multiselect) { min-height:40px; border-radius:10px; border:1px solid #e5e7eb; background:#fff; padding-left:26px; }
:deep(.p_like-input .multiselect.is-active) { border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12); }
:deep(.p_like-input .multiselect-search) { height:38px; }
:deep(.p_like-input .multiselect-single-label),
:deep(.p_like-input .multiselect-placeholder) { padding-left:10px; }
:deep(.p_ms .multiselect) { min-height:40px; border-radius:10px; border:1px solid #e5e7eb; background:#fff; }
:deep(.p_ms .multiselect:focus-within) { border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12); }
:deep(.p_ms .multiselect-input) { padding-left:34px; }

/* Selected chips */
.chip {
  font-weight: 600;
  padding: 8px 12px;
}
.btn-close-xs { width: .6rem; height: .6rem; }

/* Tables */
.p_thead { background:#7367f0 !important; }
.p_thead th { color:#fff !important; border-color:#7367f0 !important; }
.table-scroll { overflow-x: auto; max-width: 100%; }
.table thead th { color: #fff; position: sticky; top: 0; z-index: 1; }
.table-head { background: #7367f0; color: #fff; }
.sortable { cursor: pointer; user-select: none; }
.input-group .form-control { min-width: 260px; }
.badge { font-weight: 600; }
.subcat-table tfoot th { background: #f5f6f8; }
</style>
