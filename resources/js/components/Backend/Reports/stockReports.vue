<!-- resources/js/components/Backend/Stock/StockTopFilters.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Filters Card -->
    <div class="p_filters-card">
      <div class="p_filters-header">
        <div class="d-flex align-items-center gap-2">
          <i class="fa fa-list-check"></i>
          <h5 class="mb-0">Stock Filters</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-sm btn-outline-secondary" @click="resetFilters" :disabled="loading">
            <i class="fa fa-rotate-left me-1"></i> Reset
          </button>
          <button class="btn btn-sm btn-primary" @click="printA4">
            <i class="fa fa-print me-2"></i> Print A4
          </button>
          <button class="btn btn-sm btn-primary" @click="filterStockData" :disabled="loading">
            <i :class="['fa', loading ? 'fa-spinner fa-spin' : 'fa-filter', 'me-1']"></i> Apply
          </button>
        </div>
      </div>

      <div class="p_memo-section">
        <div class="row g-3">
          <!-- Fund -->
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
                placeholder="Select fund..."
                class="w-100 p_ms p_like-input"
              />
            </div>
          </div>

          <!-- Subcategory -->
          <div class="col-md-3">
            <label class="p_label"><i class="fa fa-layer-group me-1"></i> Subcategory</label>
            <div class="p_input-group">
              <span class="p_input-icon"><i class="fa fa-list"></i></span>
              <Multiselect
                v-model="subCategorie_id"
                :options="subCatagoriesOptions"
                :searchable="true"
                :close-on-select="true"
                :can-clear="true"
                placeholder="Select subcategory…"
                class="w-100 p_ms p_like-input"
              />
            </div>
          </div>

          <!-- Start Date -->
          <div class="col-md-3">
            <label class="p_label"><i class="fa fa-calendar me-1"></i> Start Date</label>
            <div class="p_input-group">
              <span class="p_input-icon"><i class="fa fa-calendar-days"></i></span>
              <input type="date" class="form-control p_input" v-model="start_date" />
            </div>
          </div>

          <!-- End Date -->
          <div class="col-md-3">
            <label class="p_label"><i class="fa fa-calendar me-1"></i> End Date</label>
            <div class="p_input-group">
              <span class="p_input-icon"><i class="fa fa-calendar-day"></i></span>
              <input type="date" class="form-control p_input" v-model="end_date" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Results Table -->
    <div class="card">
      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table id="a4-sheet" class="table table-hover align-middle mb-0 subcat-table">
            <thead class="table-head">
              <tr>
                <th style="width:70px">Sl</th>
                <th class="sortable">Fund <i class="fa fa-sort ms-1"></i></th>
                <th class="sortable">Category <i class="fa fa-sort ms-1"></i></th>
                <th class="sortable">Subcategory <i class="fa fa-sort ms-1"></i></th>
                <th class="sortable">Country <i class="fa fa-sort ms-1"></i></th>
                <th class="sortable">Unit <i class="fa fa-sort ms-1"></i></th>
                <th class="sortable">Qty <i class="fa fa-sort ms-1"></i></th>
              </tr>
            </thead>

            <tbody>
              <!-- Loading -->
              <tr v-if="loading">
                <td colspan="7" class="text-center py-4">
                  <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                </td>
              </tr>

              <!-- Empty -->
              <tr v-else-if="!loading && pagedRows.length === 0">
                <td colspan="7" class="text-center text-muted py-4">
                  No data found. Try adjusting filters.
                </td>
              </tr>

              <!-- Rows -->
              <tr v-else v-for="(row, index) in pagedRows" :key="row._key || index">
                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-medium">{{ toText(row.fund) }}</span>
                  </div>
                </td>
                <td>{{ toText(row.category) }}</td>
                <td>{{ toText(row.subcategory || row.sub_category) }}</td>
                <td>{{ toText(row.country) }}</td>
                <td>{{ toText(row.unit) }}</td>
                <td>{{ toNumber(row.qty) }}</td>
              </tr>
            </tbody>

            <tfoot v-if="!loading && rows.length">
              <tr class="table-light">
                <th colspan="6" class="text-end">Total Qty:</th>
                <th>{{ totalQty }}</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div
        class="card-footer d-flex align-items-center justify-content-between"
        v-if="!loading && rows.length"
      >
        <div class="text-muted small">
          Showing {{ showingInfo.start }}–{{ showingInfo.end }} of {{ showingInfo.total }} items
          <span v-if="isFiltered && filteredCount !== rows.length">(filtered from {{ rows.length }} total)</span>
        </div>

        <div class="d-flex align-items-center gap-2">
          <select v-model.number="itemsPerPage" class="form-select form-select-sm w-auto">
            <option :value="10">10 / page</option>
            <option :value="25">25 / page</option>
            <option :value="50">50 / page</option>
            <option :value="100">100 / page</option>
          </select>

          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" :disabled="currentPage === 1" @click="previousPage">
              <i class="fa fa-chevron-left"></i>
            </button>

            <button v-if="visiblePages[0] > 1" class="btn btn-outline-secondary" @click="goToPage(1)">1</button>
            <button v-if="visiblePages[0] > 2" class="btn btn-outline-secondary" disabled>…</button>

            <button
              v-for="p in visiblePages"
              :key="p"
              class="btn"
              :class="p === currentPage ? 'btn-primary' : 'btn-outline-secondary'"
              @click="goToPage(p)"
            >{{ p }}</button>

            <button
              v-if="visiblePages[visiblePages.length - 1] < totalPages - 1"
              class="btn btn-outline-secondary"
              disabled
            >…</button>
            <button
              v-if="visiblePages[visiblePages.length - 1] < totalPages"
              class="btn btn-outline-secondary"
              @click="goToPage(totalPages)"
            >{{ totalPages }}</button>

            <button class="btn btn-outline-secondary" :disabled="currentPage === totalPages" @click="nextPage">
              <i class="fa fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
      <!-- /Pagination -->
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance, computed, watch } from 'vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const { appContext } = getCurrentInstance()
const http = appContext?.config?.globalProperties?.$http

const fund_id = ref('')
const subCategorie_id = ref('')
const start_date = ref('')
const end_date = ref('')
const funds = ref([])
const subCatagories = ref([])

const rows = ref([])
const loading = ref(false)
const errorMsg = ref('')

const itemsPerPage = ref(10)
const currentPage = ref(1)

const fundsOptions = computed(() =>
  (funds.value || []).map(f => ({ value: f.id, label: String(f.fund ?? '') }))
)

const subCatagoriesOptions = computed(() =>
  (Array.isArray(subCatagories.value) ? subCatagories.value : []).map(r => ({
    value: Number(r.id),
    label: String((r.sub_category ?? '') + (r.sub_category_code ? ` — [${r.sub_category_code}]` : ''))
  }))
)

const totalQty = computed(() => {
  try {
    return (rows.value || []).reduce((sum, r) => sum + (Number(r.qty) || 0), 0)
  } catch {
    return 0
  }
})
const filteredRows = computed(() => rows.value || [])
const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredRows.value.length / itemsPerPage.value))
)

const pagedRows = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredRows.value.slice(start, start + itemsPerPage.value)
})

const showingInfo = computed(() => {
  if (!filteredRows.value.length) return { start: 0, end: 0, total: 0 }
  const start = (currentPage.value - 1) * itemsPerPage.value + 1
  const end = Math.min(start + itemsPerPage.value - 1, filteredRows.value.length)
  return { start, end, total: filteredRows.value.length }
})

const isFiltered = computed(() =>
  !!(fund_id.value || subCategorie_id.value || start_date.value || end_date.value)
)
const filteredCount = computed(() => filteredRows.value.length)

/* Visible page numbers (compact) */
const visiblePages = computed(() => {
  const maxButtons = 5
  const pages = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= maxButtons) {
    for (let i = 1; i <= total; i++) pages.push(i)
    return pages
  }
  let start = Math.max(1, current - 2)
  let end = Math.min(total, start + maxButtons - 1)
  if (end - start + 1 < maxButtons) start = Math.max(1, end - maxButtons + 1)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

/* ---------- Helpers ---------- */
function toText(v) {
  if (v === null || v === undefined) return ''
  return String(v)
}
function toNumber(v) {
  const n = Number(v)
  return Number.isFinite(n) ? n : 0
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

/* ---------- Get Selected Labels ---------- */
function getSelectedFundLabel() {
  if (!fund_id.value) return 'All'
  const found = fundsOptions.value.find(f => f.value === fund_id.value)
  return found ? found.label : 'All'
}

function getSelectedSubcategoryLabel() {
  if (!subCategorie_id.value) return 'All'
  const found = subCatagoriesOptions.value.find(s => s.value === subCategorie_id.value)
  return found ? found.label : 'All'
}

/* ---------- Pagination actions ---------- */
function goToPage(p) {
  currentPage.value = Math.min(Math.max(1, p), totalPages.value)
}
function previousPage() { goToPage(currentPage.value - 1) }
function nextPage() { goToPage(currentPage.value + 1) }

/* ---------- API loaders ---------- */
async function fetchFunds() {
  try {
    const res = await http.get('/fund/create')
    funds.value = Array.isArray(res?.data?.funds) ? res.data.funds : (res?.data?.data ?? [])
  } catch (e) {
    console.error('Failed to load funds', e)
    funds.value = []
  }
}

async function fetchSubcats() {
  try {
    const res = await http.get('/subCatagories/create')
    subCatagories.value = res?.data?.data ?? []
  } catch (e) {
    console.error('Failed to load subcategories', e)
    subCatagories.value = []
  }
}

async function filterStockData() {
  loading.value = true
  errorMsg.value = ''
  rows.value = []
  currentPage.value = 1

  try {
    const params = {
      fund_id: fund_id.value || '',
      subCategorie_id: subCategorie_id.value || '',
      start_date: start_date.value || '',
      end_date: end_date.value || ''
    }

    const res = await http.get('/stock-reports/create', { params })
    const data = res?.data?.data ?? res?.data ?? []
    rows.value = Array.isArray(data) ? data.map((r, i) => ({ ...r, _key: r.id ?? i })) : []
    console.log('map data', rows.value)
  } catch (e) {
    console.error('Failed to load stock report', e)
    errorMsg.value = 'Failed to load stock report.'
    rows.value = []
  } finally {
    loading.value = false
  }
}

/* ---------- PRINT A4 WITH HEADER ---------- */
function printA4() {
  if (!rows.value || rows.value.length === 0) {
    alert('No data available to print!')
    return
  }

  const win = window.open('', '', 'width=900,height=650')
  if (!win) return

  // Get filter info
  const fundLabel = getSelectedFundLabel()
  const subcatLabel = getSelectedSubcategoryLabel()
  const startFormatted = formatDate(start_date.value)
  const endFormatted = formatDate(end_date.value)
  const printDate = new Date().toLocaleString('en-GB', { 
    day: '2-digit', 
    month: 'short', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })

  // Build table rows (all data, not paginated)
  let tableRows = ''
  rows.value.forEach((row, idx) => {
    tableRows += `
      <tr>
        <td class="text-center">${idx + 1}</td>
        <td>${toText(row.fund)}</td>
        <td>${toText(row.category)}</td>
        <td>${toText(row.subcategory || row.sub_category)}</td>
        <td>${toText(row.country)}</td>
        <td class="text-center">${toText(row.unit)}</td>
        <td class="text-end">${toNumber(row.qty)}</td>
      </tr>
    `
  })

  win.document.write(`
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8">
        <title>Stock Report - Mymensingh Medical College</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <style>
          @page { 
            size: A4 portrait; 
            margin: 15mm 12mm; 
          }
          
          body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            color: #333;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }

          .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #7367f0;
          }

          .print-header h1 {
            font-size: 22pt;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
          }

          .print-header h2 {
            font-size: 16pt;
            font-weight: 600;
            color: #7367f0;
            margin: 0 0 12px 0;
          }

          .filter-info {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            border-left: 4px solid #7367f0;
          }

          .filter-info h3 {
            font-size: 11pt;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
          }

          .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 20px;
            font-size: 10pt;
          }

          .filter-item {
            display: flex;
            align-items: center;
          }

          .filter-item strong {
            color: #475569;
            margin-right: 6px;
          }

          .filter-item span {
            color: #1e293b;
            font-weight: 500;
          }

          .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
          }

          .print-table thead {
            background: #7367f0;
            color: white;
          }

          .print-table thead th {
            padding: 10px 8px;
            font-weight: 600;
            text-align: left;
            font-size: 10pt;
            border: 1px solid #5b4fd6;
          }

          .print-table tbody td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 10pt;
          }

          .print-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
          }

          .print-table tbody tr:hover {
            background-color: #f1f5f9;
          }

          .print-table tfoot {
            background: #f1f5f9;
            font-weight: 700;
          }

          .print-table tfoot td {
            padding: 10px 8px;
            border: 1px solid #cbd5e1;
            font-size: 11pt;
          }

          .text-center { text-align: center; }
          .text-end { text-align: right; }
          .text-start { text-align: left; }

          .print-footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            color: #64748b;
          }

          @media print {
            body { margin: 0; }
            .print-footer { position: fixed; bottom: 0; width: 100%; }
          }
        </style>
      </head>
      <body>
        <div class="container-fluid">
          <!-- Header -->
          <div class="print-header">
            <h1>Mymensingh Medical College</h1>
            <h2>Stock Report</h2>
          </div>

          <!-- Filter Information -->
          <div class="filter-info">
            <h3>Report Criteria</h3>
            <div class="filter-row">
              <div class="filter-item">
                <strong>Fund:</strong>
                <span>${fundLabel}</span>
              </div>
              <div class="filter-item">
                <strong>Subcategory:</strong>
                <span>${subcatLabel}</span>
              </div>
              <div class="filter-item">
                <strong>Date Range:</strong>
                <span>${startFormatted} to ${endFormatted}</span>
              </div>
            </div>
          </div>

          <!-- Table -->
          <table class="print-table">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">Sl</th>
                <th>Fund</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Country</th>
                <th class="text-center" style="width: 80px;">Unit</th>
                <th class="text-end" style="width: 90px;">Qty</th>
              </tr>
            </thead>
            <tbody>
              ${tableRows}
            </tbody>
            <tfoot>
              <tr>
                <td colspan="6" class="text-end"><strong>Total Quantity:</strong></td>
                <td class="text-end"><strong>${totalQty.value}</strong></td>
              </tr>
            </tfoot>
          </table>

          <!-- Footer -->
          <div class="print-footer">
            <span>Generated on: ${printDate}</span>
            <span>Total Records: ${rows.value.length}</span>
          </div>
        </div>

        <script>
          window.addEventListener('load', function() {
            window.focus();
            setTimeout(function() {
              window.print();
            }, 250);
          });

          window.addEventListener('afterprint', function() {
            window.close();
          });
        <\/script>
      </body>
    </html>
  `)

  win.document.close()
}

/* ---------- UX actions ---------- */
function resetFilters() {
  fund_id.value = ''
  subCategorie_id.value = ''
  const today = new Date().toISOString().split('T')[0]
  start_date.value = today
  end_date.value = today
  filterStockData()
}

/* ---------- Auto fetch on mount ---------- */
onMounted(async () => {
  const today = new Date().toISOString().split('T')[0]
  if (!start_date.value) start_date.value = today
  if (!end_date.value) end_date.value = today
  await Promise.all([fetchFunds(), fetchSubcats()])
  await filterStockData()
})

/* Optional: auto re-fetch when filters change (debounced) */
let debounceTimer
watch([fund_id, subCategorie_id, start_date, end_date], () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => filterStockData(), 400)
})
</script>

<style scoped>
.table-scroll { overflow-x: auto; max-width: 100%; }
.table thead th { color: #fff; position: sticky; top: 0; z-index: 1; }
.table-head { background: #7367f0; color: #fff; }
.sortable { cursor: pointer; user-select: none; }
.input-group .form-control { min-width: 260px; }
.badge { font-weight: 600; }
.subcat-table tfoot th { background: #f5f6f8; }

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

/* Multiselect look */
:deep(.p_like-input .multiselect) {
  min-height:40px; border-radius:10px; border:1px solid #e5e7eb; background:#fff;
  padding-left:26px;
}
:deep(.p_like-input .multiselect.is-active) {
  border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12);
}
:deep(.p_like-input .multiselect-search) { height:38px; }
:deep(.p_like-input .multiselect-single-label),
:deep(.p_like-input .multiselect-placeholder) { padding-left:10px; }

/* Older selector compatibility */
:deep(.p_ms .multiselect) {
  min-height:40px; border-radius:10px; border:1px solid #e5e7eb; background:#fff;
}
:deep(.p_ms .multiselect:focus-within) {
  border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12);
}
:deep(.p_ms .multiselect-input) { padding-left:34px; }

.no-print { display: flex; justify-content: flex-end; }
.print-sheet { background: white; padding: 20px; border-radius: 6px; box-shadow: 0 0 6px rgba(0, 0, 0, 0.1); }

@media print {
  .no-print { display: none !important; }
}
</style>