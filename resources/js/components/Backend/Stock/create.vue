<!-- resources/js/components/Backend/Invoice/InvoiceCreate.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm border-0">
      <div class="card-body">

        <!-- ======== TOP FILTERS (New Purchase) ======== -->
        <div class="p_filters-section p_filters-card">
          <div class="p_filters-header">
            <div class="d-flex align-items-center gap-2">
              <i class="fa fa-bag-shopping"></i>
              <h5 class="mb-0">New Purchase</h5>
              <span class="badge bg-light text-dark ms-2">Step 1: Pick Fund → Category → Item</span>
            </div>

            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-sm btn-outline-secondary" @click="clearPicker" :disabled="isLoading || isLoadingCats">
                <i class="fa fa-rotate-left me-1"></i> Clear
              </button>
              <button class="btn btn-sm btn-outline-dark" @click="saveDraftNow">
                <i class="fa fa-save me-1"></i> Save Draft
              </button>
            </div>
          </div>

          <div class="p_filters-grid p_cols-3">
            <!-- Fund -->
            <div class="p_field">
              <label class="p_label">
                <i class="fa fa-vault me-1"></i> Fund
              </label>
              <div class="p_input-wrap" :class="{ 'p_loading': isLoading }">
                <select class="p_input" v-model="filters.fund_id" @change="onFundChange">
                  <option :value="null" disabled>Select a fund</option>
                  <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.fund }}</option>
                </select>
                <button class="p_icon-btn" type="button" @click="onAddFund" :title="'Add Fund'">
                  <i class="fa fa-plus"></i>
                </button>
              </div>
              <small class="p_hint">Choose the budget source.</small>
            </div>

            <!-- Category -->
            <div class="p_field">
              <label class="p_label">
                <i class="fa fa-folder-tree me-1"></i> Category
              </label>
              <div class="p_input-wrap" :class="{ 'p_loading': isLoadingCats }">
                <select
                  class="p_input"
                  v-model="filters.categorie_id"
                  :disabled="!filters.fund_id || isLoadingCats"
                  @change="onCategoryChange"
                >
                  <option :value="null" disabled>Select a Category</option>
                  <option v-for="c in fundByCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <button class="p_icon-btn" type="button" @click="onAddCategory" :disabled="!filters.fund_id" :title="'Add Category'">
                  <i class="fa fa-plus"></i>
                </button>
              </div>
              <small class="p_hint">Filtered by selected Fund.</small>
            </div>

            <!-- Item + Inline Search -->
            <div class="p_field">
              <label class="p_label">
                <i class="fa fa-box-open me-1"></i> Item
              </label>
              <div class="p_input-wrap">
                <select
                  class="p_input"
                  v-model="filters.item_id"
                  :disabled="!filters.fund_id || !filters.categorie_id"
                >
                  <option :value="null" disabled>Select an Item</option>
                  <option
                    v-for="it in filteredItemsFor(filters.fund_id, filters.categorie_id, itemQuickSearch)"
                    :key="it.id"
                    :value="it.id"
                  >
                    {{ it.sub_category }} — {{ it.sub_category_code }}
                  </option>
                </select>
                <button class="btn btn-sm btn-primary p_add-btn" @click="addLineFromPicker" :disabled="!filters.item_id">
                  <i class="fa fa-cart-plus me-1"></i> Add
                </button>
              </div>
              <input
                v-model.trim="itemQuickSearch"
                class="form-control form-control-sm mt-2"
                type="text"
                placeholder="Quick search item by name/code..."
                :disabled="!filters.fund_id || !filters.categorie_id"
              >
              <small class="p_hint">Type to narrow items instantly.</small>
            </div>
          </div>

          <!-- Live selection chips -->
          <div class="d-flex flex-wrap gap-2 mt-2">
            <span v-if="selectedFund" class="p_chip"><i class="fa fa-vault me-1"></i>{{ selectedFund.fund }}</span>
            <span v-if="selectedCategory" class="p_chip"><i class="fa fa-folder-tree me-1"></i>{{ selectedCategory.name }}</span>
            <span v-if="selectedItem" class="p_chip"><i class="fa fa-box-open me-1"></i>{{ selectedItem.sub_category }} ({{ selectedItem.sub_category_code }})</span>
          </div>
        </div>

        <!-- ======== INVOICE LINES ======== -->
        <div class="card mt-3 border-0 p_soft-edge">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <h5 class="card-header mb-0 ps-0">Invoice Items</h5>
              <div class="ms-auto d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" @click="saveDraftNow" :title="'Save draft to your browser'">
                  <i class="fa fa-save me-1"></i> Save Draft (Local)
                </button>
                <button class="btn btn-outline-info btn-sm" @click="restoreDraft" :disabled="!hasLocalDraft" :title="'Restore from browser draft'">
                  <i class="fa fa-undo me-1"></i> Restore Draft
                </button>
                <button class="btn btn-outline-danger btn-sm" @click="clearDraft" :disabled="!hasLocalDraft" :title="'Remove browser draft'">
                  <i class="fa fa-trash me-1"></i> Clear Draft
                </button>
              </div>
            </div>

            <div class="table-responsive p_table-wrap">
              <table class="table table-sm align-middle p_table">
                <thead>
                  <tr>
                    <th style="width:50px">#</th>
                    <th>Item</th>
                    <th>Code</th>
                    <th style="width:120px">Qty</th>
                    <th style="width:160px">Unit Price</th>
                    <th style="width:160px">Line Total</th>
                    <th style="width:60px"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(li, i) in inv.items"
                    :key="li.item_id"
                    :class="['p_row', { 'p_row-highlight': li.item_id === highlightId }]"
                    :ref="el => rowRefs[i] = el"
                  >
                    <td class="text-muted">{{ i+1 }}</td>
                    <td class="fw-600">{{ li.name }}</td>
                    <td><small class="text-muted">{{ li.code }}</small></td>
                    <td class="p_cell-tight">
                      <input type="number" class="form-control form-control-sm"
                             min="0" :value="li.qty"
                             @input="updateLine(i,'qty',$event.target.value)">
                    </td>
                    <td class="p_cell-tight">
                      <input type="number" step="0.01" class="form-control form-control-sm"
                             min="0" :value="li.unit_price"
                             @input="updateLine(i,'unit_price',$event.target.value)">
                    </td>
                    <td class="fw-600">{{ money(Number(li.qty) * Number(li.unit_price || 0)) }}</td>
                    <td class="text-end">
                      <button class="btn btn-sm btn-outline-danger" @click="removeLine(i)" :title="'Remove line'">
                        <i class="fa fa-times"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!inv.items.length">
                    <td colspan="7" class="text-center text-muted py-3">No items added</td>
                  </tr>
                </tbody>

                <tfoot v-if="inv.items.length" class="p_tfoot">
                  <tr>
                    <th colspan="5" class="text-end text-muted">Subtotal:</th>
                    <th class="fw-700">{{ money(invSubtotal) }}</th>
                    <th></th>
                  </tr>
                  <tr>
                    <th colspan="5" class="text-end text-muted">Discount:</th>
                    <th>
                      <input type="number" step="0.01" class="form-control form-control-sm"
                             v-model.number="inv.discount">
                    </th>
                    <th></th>
                  </tr>
                  <tr>
                    <th colspan="5" class="text-end">Grand Total:</th>
                    <th class="fw-800 fs-6">{{ money(invGrand) }}</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- ======== Invoice Meta ======== -->
            <div class="row g-3 mt-2">
              <div class="col-sm-3">
                <label class="form-label">Invoice No</label>
                <input class="form-control" v-model.trim="inv.invoice_no" placeholder="INV-0001">
              </div>
              <div class="col-sm-3">
                <label class="form-label">Date</label>
                <input class="form-control" type="date" v-model="inv.date">
              </div>
              <div class="col-sm-3">
                <label class="form-label">Ref No</label>
                <input class="form-control" v-model.trim="inv.ref_no" placeholder="REF-001">
              </div>

              <!-- Validity Period -->
              <div class="col-sm-3">
                <label class="form-label">Validity Start</label>
                <input class="form-control" type="date"
                       v-model="inv.validity_start"
                       :max="inv.validity_end || undefined">
              </div>
              <div class="col-sm-3">
                <label class="form-label">Validity End</label>
                <input class="form-control" type="date"
                       v-model="inv.validity_end"
                       :min="inv.validity_start || undefined">
              </div>

              <!-- Warranty Period -->
              <div class="col-sm-3">
                <label class="form-label">Warranty Start</label>
                <input class="form-control" type="date"
                       v-model="inv.warranty_start"
                       :max="inv.warranty_end || undefined">
              </div>
              <div class="col-sm-3">
                <label class="form-label">Warranty End</label>
                <input class="form-control" type="date"
                       v-model="inv.warranty_end"
                       :min="inv.warranty_start || undefined">
              </div>

              <div class="col-sm-12">
                <label class="form-label">Notes</label>
                <textarea class="form-control" rows="2" v-model.trim="inv.notes" placeholder="Optional notes..."></textarea>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
              <button class="btn btn-light border" @click="resetInvoice">
                <i class="fa fa-eraser me-2"></i> Clear Form
              </button>

              <button class="btn btn-outline-dark" @click="saveDraftServer" :disabled="btnLoading">
                <i class="fa me-2" :class="btnLoading ? 'fa-spinner fa-spin' : 'fa-cloud-upload'"></i>
                Save Draft (Server)
              </button>

              <button class="btn btn-primary p_btn-elev" @click="finalizeInvoice" :disabled="!inv.invoice_no || !inv.items.length || btnLoading">
                <i class="fa me-2" :class="btnLoading ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                Submit / Finalize
              </button>
            </div>
          </div>
        </div>

        <!-- <pre class="mt-2 p-2 bg-dark text-light rounded" style="max-height:200px;overflow:auto">{{ inv }}</pre> -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, getCurrentInstance } from 'vue'
import { useToast } from 'vue-toastification'

/* -------------------- services -------------------- */
const { appContext } = getCurrentInstance()
const http  = appContext.config.globalProperties.$http
const toast = useToast()

/* -------------------- filters / pickers -------------------- */
const filters = ref({ fund_id: null, categorie_id: null, item_id: null })
const isLoading = ref(false)
const isLoadingCats = ref(false)

const funds = ref([])
const fundByCategories = ref([])
const catCache = ref({})
const items = ref([]) // sub-categories list (aka items)
const rowRefs = ref([])
const highlightId = ref(null)

/* NEW: quick item search & selected chips */
const itemQuickSearch = ref('')

const selectedFund = computed(() =>
  funds.value.find(f => Number(f.id) === Number(filters.value.fund_id))
)
const selectedCategory = computed(() =>
  fundByCategories.value.find(c => Number(c.id) === Number(filters.value.categorie_id))
)
const selectedItem = computed(() =>
  itemById.value.get(Number(filters.value.item_id)) || null
)

/* quick index by id */
const itemById = computed(() => {
  const m = new Map()
  for (const it of items.value) if (it?.id != null) m.set(Number(it.id), it)
  return m
})

/* -------------------- INVOICE STATE (with Local Draft) -------------------- */
const draftKey = 'invoice_draft_v1'
const blankInvoice = {
  fund_id: null,
  category_id: null,
  date: new Date().toISOString().slice(0,10),
  ref_no: '',
  notes: '',
  discount: 0,
  invoice_no: '',
  validity_start: null,
  validity_end: null,
  warranty_start: null,
  warranty_end: null,
  items: []
}
const inv = ref(loadLocalDraft() ?? structuredClone(blankInvoice))
const btnLoading = ref(false)

const invSubtotal = computed(() =>
  inv.value.items.reduce((s, it) => s + (Number(it.qty) * Number(it.unit_price || 0)), 0)
)
const invGrand = computed(() => Math.max(invSubtotal.value - Number(inv.value.discount || 0), 0))
const hasLocalDraft = computed(() => !!localStorage.getItem(draftKey))

// autosave (debounced)
let draftTimer
watch(inv, (v)=>{
  clearTimeout(draftTimer)
  draftTimer = setTimeout(()=>localStorage.setItem(draftKey, JSON.stringify(v)), 400)
},{ deep:true })

watch(()=>filters.value.fund_id, v => { inv.value.fund_id = v })
watch(()=>filters.value.categorie_id, v => { inv.value.category_id = v })

// keep end >= start for validity
watch(() => inv.value.validity_start, (v) => {
  if (v && inv.value.validity_end && inv.value.validity_end < v) {
    inv.value.validity_end = v
  }
})
watch(() => inv.value.validity_end, (v) => {
  if (v && inv.value.validity_start && v < inv.value.validity_start) {
    inv.value.validity_start = v
  }
})

// keep end >= start for warranty
watch(() => inv.value.warranty_start, (v) => {
  if (v && inv.value.warranty_end && inv.value.warranty_end < v) {
    inv.value.warranty_end = v
  }
})
watch(() => inv.value.warranty_end, (v) => {
  if (v && inv.value.warranty_start && v < inv.value.warranty_start) {
    inv.value.warranty_start = v
  }
})

/* -------------------- utils -------------------- */
function money(n){ return Number(n||0).toFixed(2) }
function loadLocalDraft(){
  try{ const raw = localStorage.getItem(draftKey); return raw ? JSON.parse(raw) : null }catch{ return null }
}
function saveDraftNow(){ localStorage.setItem(draftKey, JSON.stringify(inv.value)); toast.success('Draft saved locally') }
function restoreDraft(){
  const d = loadLocalDraft()
  if(!d) return toast.info('No local draft found')
  inv.value = d
  filters.value.fund_id = inv.value.fund_id
  filters.value.categorie_id = inv.value.category_id
  toast.success('Draft restored')
}
function clearDraft(){ localStorage.removeItem(draftKey); toast.success('Local draft cleared') }
function resetInvoice(){ inv.value = structuredClone(blankInvoice) }
function clearPicker(){
  filters.value.fund_id = null
  filters.value.categorie_id = null
  filters.value.item_id = null
}

/* -------------------- picker helpers -------------------- */
function filteredItemsFor(fid, cid, q = ''){
  if(!fid || !cid) return []
  const base = items.value.filter(it =>
    Number(it.fund_id)===Number(fid) && Number(it.categorie_id)===Number(cid)
  )
  if(!q) return base
  const s = q.toLowerCase()
  return base.filter(it =>
    String(it.sub_category || '').toLowerCase().includes(s) ||
    String(it.sub_category_code || '').toLowerCase().includes(s)
  )
}
function ensureCategoryValid(target){
  const list = categoriesForFund(target.fund_id)
  if(!list.find(c=>Number(c.id)===Number(target.category_id))) target.category_id=null
}
function categoriesForFund(fid){
  if(!fid) return []
  if(fid===filters.value.fund_id) return fundByCategories.value
  return catCache.value[fid] || []
}

/* -------------------- fetch pickers data -------------------- */
async function getFunds(){
  isLoading.value=true
  try{
    const r = await http.get('/fund/create')
    funds.value = r?.data?.funds ?? []
    if(!filters.value.fund_id && funds.value.length){
      filters.value.fund_id = funds.value[0].id
      inv.value.fund_id     = filters.value.fund_id
      await getCategoriesByFund(filters.value.fund_id)
    }
  }catch{ toast.error('Failed to load funds') }
  finally{ isLoading.value=false }
}
async function getCategoriesByFund(fid){
  if(!fid){ fundByCategories.value=[]; filters.value.categorie_id=null; return }
  isLoadingCats.value=true
  try{
    const r = await http.get('/subCatagories/show', { params:{ fund_id: fid } })
    const list = Array.isArray(r.data) ? r.data : (r.data?.data ?? [])
    fundByCategories.value = list
    catCache.value[fid] = list
    if(!fundByCategories.value.find(c=>Number(c.id)===Number(filters.value.categorie_id))){
      filters.value.categorie_id = null
      inv.value.category_id = null
    }
  }catch{
    toast.error('Failed to load categories')
    fundByCategories.value=[]; filters.value.categorie_id=null; inv.value.category_id=null
  }finally{ isLoadingCats.value=false }
}
async function getItemsList(){
  try{
    isLoading.value = true
    const r = await http.get('/subCatagories/create')
    items.value = Array.isArray(r?.data?.data) ? r.data.data : (Array.isArray(r?.data) ? r.data : [])
  }catch(e){
    console.error('getItemsList error:', e?.response?.data || e)
    toast.error('Failed to load items')
  }finally{ isLoading.value=false }
}

function onFundChange(){
  filters.value.categorie_id = null
  filters.value.item_id = null
  inv.value.fund_id = filters.value.fund_id
  inv.value.category_id = null
  getCategoriesByFund(filters.value.fund_id)
}
function onCategoryChange(){
  filters.value.item_id = null
  inv.value.category_id = filters.value.categorie_id
}

/* -------------------- line actions -------------------- */
function focusRow(i){
  const el = rowRefs.value[i]
  if(el?.scrollIntoView) el.scrollIntoView({ block:'nearest', behavior:'smooth' })
}
function addLineFromPicker(){
  if (!filters.value.item_id) { toast.error('Select an Item first'); return }
  if (!inv.value.fund_id || !inv.value.category_id) { toast.error('Select Fund & Category'); return }

  const it = itemById.value.get(Number(filters.value.item_id))
  if (!it) return

  const existIndex = inv.value.items.findIndex(x => Number(x.item_id) === Number(it.id))
  if (existIndex !== -1) {
    const exist = inv.value.items[existIndex]
    exist.qty = Number(exist.qty || 0) + 1
    exist.line_total = Number(exist.qty) * Number(exist.unit_price || 0)
    highlightId.value = exist.item_id
    focusRow(existIndex)
  } else {
    inv.value.items.push({
      item_id: it.id,
      name: it.sub_category,
      code: it.sub_category_code,
      qty: 1,
      unit_price: 0,
      line_total: 0
    })
    highlightId.value = it.id
    focusRow(inv.value.items.length - 1)
  }
  // fade highlight after 1.5s
  setTimeout(()=>{ if(highlightId.value===it.id) highlightId.value=null }, 1500)
}
function updateLine(idx, field, val){
  const row = inv.value.items[idx]; if(!row) return
  if(field==='qty') row.qty = Math.max(Number(val||0), 0)
  if(field==='unit_price') row.unit_price = Math.max(Number(val||0), 0)
  row.line_total = Number(row.qty) * Number(row.unit_price || 0)
}
function removeLine(idx){ inv.value.items.splice(idx, 1) }

/* -------------------- submit / server-draft -------------------- */
async function saveDraftServer(){
  try{
    btnLoading.value = true
    const payload = {
      fund_id: inv.value.fund_id,
      category_id: inv.value.category_id,
      invoice_no: inv.value.invoice_no || `DRAFT-${Date.now()}`,
      date: inv.value.date || null,
      ref_no: inv.value.ref_no || null,
      notes: inv.value.notes || null,
      discount: Number(inv.value.discount || 0),
      validity_start: inv.value.validity_start || null,
      validity_end:   inv.value.validity_end   || null,
      warranty_start: inv.value.warranty_start || null,
      warranty_end:   inv.value.warranty_end   || null,
      items: inv.value.items,
      status: 'draft',
      draft_key: 'web-'+Date.now()
    }
    await http.post('/invoices', payload)
    toast.success('Draft saved on server')
  }catch(e){
    toast.error(e?.response?.data?.message || e?.message || 'Failed to save draft')
  }finally{
    btnLoading.value = false
  }
}
async function finalizeInvoice () {
  // tiny helpers
  const num = (v) => {
    const n = typeof v === 'string' ? v.replace(/[, ]/g, '') : v
    const f = parseFloat(n)
    return Number.isFinite(f) ? f : 0
  }
  const clean = (obj) => {
    const out = {}
    Object.entries(obj).forEach(([k, v]) => {
      if (v !== null && v !== undefined && !(typeof v === 'string' && v.trim() === '')) out[k] = v
    })
    return out
  }

  try {
    // ---- high-level form checks
    if (!inv.value?.fund_id || !inv.value?.category_id) {
      throw new Error('Fund & Category required')
    }
    if (!inv.value?.invoice_no) {
      throw new Error('Invoice No is required')
    }
    if (!Array.isArray(inv.value?.items) || inv.value.items.length === 0) {
      throw new Error('Add items before finalize')
    }

    // ---- normalize + validate items
    const normalizedItems = inv.value.items.map((li, idx) => {
      const item_id = li.item_id ?? li.id
      const qty = num(li.qty)
      const unit_price = num(li.unit_price)
      if (!item_id) throw new Error(`Row ${idx + 1}: item_id missing`)
      if (qty <= 0) throw new Error(`Row ${idx + 1}: qty must be > 0`)
      if (unit_price < 0) throw new Error(`Row ${idx + 1}: unit price cannot be negative`)
      return { item_id, qty, unit_price, line_total: +(qty * unit_price).toFixed(2) }
    })

    // ---- compute totals
    const subTotal = normalizedItems.reduce((s, r) => s + r.line_total, 0)
    const discount = num(inv.value.discount || 0)
    const grandTotal = Math.max(0, +(subTotal - discount).toFixed(2))

    btnLoading.value = true

    // ---- build payload (no null/undefined)
    const payload = clean({
      status: 'final',
      fund_id: inv.value.fund_id,
      category_id: inv.value.category_id,
      invoice_no: inv.value.invoice_no,
      date: inv.value.date || null,
      ref_no: inv.value.ref_no || null,
      notes: inv.value.notes || null,
      discount: +discount.toFixed(2),
      sub_total: +subTotal.toFixed(2),
      grand_total: +grandTotal.toFixed(2),
      validity_start: inv.value.validity_start || null,
      validity_end: inv.value.validity_end || null,
      warranty_start: inv.value.warranty_start || null,
      warranty_end: inv.value.warranty_end || null,
      items: normalizedItems.map(({ item_id, qty, unit_price }) => ({
        item_id,
        qty,
        unit_price: +unit_price.toFixed(2),
      })),
    })

    const response = await http.post('/stock-create', payload)
    console.log(response.data)
    const res = response?.data || {}
    if (res.status === true || res.success === true) {
      toast.success(res.message || 'Invoice saved successfully!')
      // optional debug
      console.table(res.data?.invoice ?? {})
      console.log('🧾 Purchases:', res.data?.purchases)
      console.log('📦 Stocks:', res.data?.stocksTouched)
      // clear draft & form
      clearDraft?.()
      resetInvoice?.()
    } else {
      // backend might return Laravel validation errors
      const msg =
        res.message ||
        (res?.errors && Object.values(res.errors).flat().join('\n')) ||
        'Unexpected response structure.'
      throw new Error(msg)
    }
  } catch (e) {
    console.error('❌ Error:', e)
    toast.error(e?.response?.data?.message || e?.message || 'Failed to save invoice')
  } finally {
    btnLoading.value = false
  }
}



/* -------------------- init -------------------- */
onMounted(async ()=>{
  await getFunds()
  if(filters.value.fund_id) await getCategoriesByFund(filters.value.fund_id)
  await getItemsList()
  if(inv.value.fund_id) filters.value.fund_id = inv.value.fund_id
  if(inv.value.category_id) filters.value.categorie_id = inv.value.category_id
})

/* -------------------- placeholders for + buttons -------------------- */
function onAddFund(){ toast.info('Add Fund flow not implemented here.') }
function onAddCategory(){ toast.info('Add Category flow not implemented here.') }
</script>

<style scoped>
/* ---------------- Layout & Typography ---------------- */
.fw-600{ font-weight:600; }
.fw-700{ font-weight:700; }
.fw-800{ font-weight:800; }
h5{ font-weight:700; color:#1e293b; }

/* ---------------- Buttons ---------------- */
.p_btn-elev{
  box-shadow:0 6px 16px rgba(115,103,240,.35);
  transition: transform 0.2s, filter 0.2s, box-shadow 0.2s;
}
.p_btn-elev:hover{
  transform:translateY(-2px);
  filter:brightness(1.05);
  box-shadow:0 10px 20px rgba(115,103,240,.45);
}
.btn-outline-dark:hover{ background:#7367f0; color:#fff; }
.p_add-btn{
  height:36px; background:#7367f0; color:#fff;
  border-radius:12px; font-weight:600;
  transition:transform 0.2s, filter 0.2s;
}
.p_add-btn:hover{ transform:translateY(-2px); filter:brightness(1.05); }

/* ---------------- Pickers Card ---------------- */
.p_filters-card{
  border:1px solid #e0e7ff;
  border-radius:20px;
  padding:18px 16px 14px;
  background:linear-gradient(180deg,#ffffff, #f7f5ff);
  box-shadow:0 8px 24px rgba(0,0,0,.08);
  transition: all 0.3s ease;
}
.p_filters-card:hover{ transform: translateY(-2px); }
.p_filters-header{
  display:flex; align-items:center; justify-content:space-between;
  padding-bottom:12px; border-bottom:1px dashed #cbd5e1; margin-bottom:14px;
}
.p_filters-header h5{ font-weight:700; font-size:1.1rem; color:#1e293b; }

/* ---------------- Grid ---------------- */
.p_cols-3{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
@media (max-width:992px){ .p_cols-3{ grid-template-columns:1fr; } }

/* ---------------- Fields ---------------- */
.p_field{ position:relative; }
.p_label{ font-weight:700; color:#1e293b; margin-bottom:6px; display:block; }
.p_hint{ color:#64748b; font-size:.78rem; margin-top:4px; display:block; }

/* ---------------- Input Wrappers ---------------- */
.p_input-wrap{
  position:relative; display:flex; align-items:center; gap:10px;
  background:#fff; border:1px solid #cbd5e1; border-radius:12px; padding:8px;
  transition:border 0.2s, box-shadow 0.2s;
}
.p_input-wrap:hover{ border-color:#7367f0; box-shadow:0 4px 12px rgba(115,103,240,.15); }
.p_input{
  appearance:none; -webkit-appearance:none;
  width:100%; height:42px; border:none; outline:none; padding:0 10px;
  border-radius:10px; background:transparent; font-size:.95rem;
}
.p_icon-btn{
  height:36px; width:36px; border-radius:12px;
  display:grid; place-items:center; background:#f7f5ff; color:#334155;
  border:1px dashed #cbd5e1;
  transition: background 0.2s, transform 0.2s;
}
.p_icon-btn:hover{ background:#7367f0; color:#fff; transform:scale(1.05); }

/* ---------------- Loading Shimmer ---------------- */
.p_input-wrap.p_loading{ position:relative; overflow:hidden; }
.p_input-wrap.p_loading::after{
  content:''; position:absolute; inset:0;
  background:linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(115,103,240,0.2) 45%, rgba(255,255,255,0) 90%);
  animation:pShimmer 1.2s infinite;
}
@keyframes pShimmer{ 0%{ transform:translateX(-100%);} 100%{ transform:translateX(100%);} }

/* ---------------- Chips ---------------- */
.p_chip{
  background:linear-gradient(90deg,#e0dfff,#d3cfff);
  color:#4c28a0; border:1px solid #7367f0;
  padding:6px 12px; border-radius:999px; font-weight:600; font-size:.85rem;
  transition: transform 0.2s, box-shadow 0.2s;
}
.p_chip:hover{ transform:scale(1.05); box-shadow:0 4px 12px rgba(115,103,240,.25); }

/* ---------------- Table ---------------- */
.p_soft-edge{ border-radius:16px; }
.p_table-wrap{ border:1px solid rgba(0,0,0,.06); border-radius:14px; overflow:hidden; }
.p_table thead th{
  text-transform:uppercase; font-size:.78rem; letter-spacing:.3px; color:#475569;
  position:sticky; top:0; background:#f7f5ff; z-index:1;
}
.p_table tbody tr.p_row{
  transition: background 0.3s, transform 0.25s;
}
.p_table tbody tr.p_row:hover{ background:#f2f0ff; }
.p_table input.form-control-sm{ height:36px; border-radius:8px; }
.p_cell-tight{ width:160px; }

/* Table Footer */
.p_tfoot th{ background:#f7f5ff; }

/* ---------------- Row Highlight Animation ---------------- */
.p_row-highlight{ animation: pFlash 1.5s ease-in-out 1; }
@keyframes pFlash{ 0%{ background:#dcd4ff; } 100%{ background:transparent; } }

/* ---------------- New Line Fade Animation ---------------- */
.p_row-new{
  animation: fadeInGlow 0.8s ease forwards;
}
@keyframes fadeInGlow{
  0%{ background:#dcd4ff; transform:translateY(-6px); opacity:0; }
  50%{ background:#e6d9ff; transform:translateY(2px); opacity:0.8; }
  100%{ background:transparent; transform:translateY(0); opacity:1; }
}
</style>


