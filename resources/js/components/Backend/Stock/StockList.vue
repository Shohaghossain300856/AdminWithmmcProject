<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
      <!-- Header -->
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">
          <i class="fa fa-database me-2"></i> Stock Summary (Product × Category × Fund)
        </h5>

        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary">{{ displayRows.length }} Groups</span>

          <div class="input-group input-group-sm w-auto">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input
              v-model.trim="q"
              class="form-control"
              placeholder="Search product / category / fund"
            />
          </div>
          <button class="btn btn-sm btn-outline-primary" @click="fetchData" :disabled="loading">
            <i :class="['fa', loading ? 'fa-spinner fa-spin' : 'fa-rotate']"></i>
            <span class="ms-1">{{ loading ? 'Loading...' : 'Refresh' }}</span>
          </button>
        </div>
      </div>

      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table class="table table-hover align-middle mb-0 subcat-table">
            <thead class="table-head">
              <tr>
                <th style="width:70px">Sl</th>
                <th class="sortable" @click="toggleSort('product_name')">
                  Product <i class="fa fa-sort ms-1"></i>
                </th>
                <th class="sortable" @click="toggleSort('category_name')">
                  Category <i class="fa fa-sort ms-1"></i>
                </th>
                <th class="sortable" @click="toggleSort('fund_name')">
                  Fund <i class="fa fa-sort ms-1"></i>
                </th>
                <th class="text-end sortable" style="width:140px" @click="toggleSort('total_qty')">
                  Total Qty <i class="fa fa-sort ms-1"></i>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="text-center py-4">
                  <i class="fa fa-spinner fa-spin me-2"></i> Loading summary...
                </td>
              </tr>
              <tr v-for="(row, i) in pagedRows" :key="row.key" v-else>
                <td>{{ (page - 1) * perPage + i + 1 }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-medium">{{ row.product_name || '—' }}</span>
                    <small v-if="row.unit" class="text-muted">({{ row.unit }})</small>
                  </div>
                </td>
                <td>{{ row.category_name || '—' }}</td>
                <td>{{ row.fund_name || '—' }}</td>
                <td class="text-end">{{ formatNumber(row.total_qty) }}</td>
              </tr>
              <tr v-if="!loading && !displayRows.length">
                <td colspan="5" class="text-center py-4 text-muted">
                  <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                  No grouped records found
                </td>
              </tr>
            </tbody>
            <tfoot v-if="!loading && displayRows.length">
              <tr class="table-light">
                <th colspan="4" class="text-end">Total (visible page)</th>
                <th class="text-end">{{ formatNumber(pageTotalQty) }}</th>
              </tr>
              <tr class="table-light">
                <th colspan="4" class="text-end">Grand Total (all filtered)</th>
                <th class="text-end">{{ formatNumber(filteredTotalQty) }}</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="card-footer d-flex align-items-center justify-content-between" v-if="displayRows.length">
        <div class="text-muted small">
          Showing {{ (page - 1) * perPage + 1 }}–{{ Math.min(page * perPage, displayRows.length) }}
          of {{ displayRows.length }} groups
        </div>
        <div class="d-flex align-items-center gap-2">
          <select class="form-select form-select-sm w-auto" v-model.number="perPage">
            <option :value="10">10 / page</option>
            <option :value="25">25 / page</option>
            <option :value="50">50 / page</option>
            <option :value="100">100 / page</option>
          </select>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" :disabled="page === 1" @click="page--">
              <i class="fa fa-chevron-left"></i>
            </button>
            <button class="btn btn-outline-secondary" :disabled="page >= totalPages" @click="page++">
              <i class="fa fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, getCurrentInstance, watch } from 'vue'
import { useToast } from 'vue-toastification'
const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()

const rawRows = ref([])     
const loading = ref(false)

// search & sort & paging
const q = ref('')
const sortBy = ref('product_name')
const sortDir = ref('asc') 
const page = ref(1)
const perPage = ref(25)

const formatNumber = (v) => {
  const n = parseFloat(v ?? 0)
  if (Number.isNaN(n)) return '0.00'
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function fetchData () {
  loading.value = true
  try {
    const res = await http.get('/stock-create/create')
    rawRows.value = Array.isArray(res?.data?.data) ? res.data.data : (Array.isArray(res?.data) ? res.data : [])
  } catch (e) {
    console.error(e)
    toast.error('Failed to load stock summary')
  } finally {
    loading.value = false
  }
}

const displayRows = computed(() => {
  return rawRows.value.map(r => {
    const productName = r?.product?.product ?? ''
    const unit        = r?.product?.unit ?? ''
    const category    = r?.category?.name ?? r?.category?.catagory_name ?? '' 
    const fundName    = r?.fund?.fund ?? r?.fund?.fund_name ?? ''           

    return {
      key: `${r.product_id}-${r.category_id}-${r.fund_id}`,
      product_id: r.product_id,
      category_id: r.category_id,
      fund_id: r.fund_id,
      product_name: productName,
      unit,
      category_name: category,
      fund_name: fundName,
      total_qty: Number(r?.total_qty ?? 0),
    }
  })
})
const filtered = computed(() => {
  const term = q.value.toLowerCase()
  if (!term) return displayRows.value
  return displayRows.value.filter(r => {
    return (
      (r.product_name || '').toLowerCase().includes(term) ||
      (r.category_name || '').toLowerCase().includes(term) ||
      (r.fund_name || '').toLowerCase().includes(term)
    )
  })
})

const sorted = computed(() => {
  const list = [...filtered.value]
  const by = sortBy.value
  const dir = sortDir.value

  list.sort((a, b) => {
    const av = a[by]
    const bv = b[by]

    if (typeof av === 'number' && typeof bv === 'number') {
      return dir === 'asc' ? av - bv : bv - av
    }

    return dir === 'asc'
      ? String(av).localeCompare(String(bv))
      : String(bv).localeCompare(String(av))
  })
  return list
})

const totalPages = computed(() => Math.max(1, Math.ceil(sorted.value.length / perPage.value)))
const pagedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return sorted.value.slice(start, start + perPage.value)
})

const pageTotalQty = computed(() => pagedRows.value.reduce((sum, r) => sum + (Number(r.total_qty) || 0), 0))
const filteredTotalQty = computed(() => filtered.value.reduce((sum, r) => sum + (Number(r.total_qty) || 0), 0))

function toggleSort(field) {
  if (sortBy.value === field) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortDir.value = 'asc'
  }
}

watch([q, sortBy, sortDir, perPage], () => { page.value = 1 })

onMounted(fetchData)
</script>

<style scoped>
.table-scroll { overflow-x: auto; max-width: 100%; }
.table thead th { color: #fff; position: sticky; top: 0; z-index: 1; }
.table-head { background: #7367f0; color: #fff; }

.sortable { cursor: pointer; user-select: none; }

/* Small polish */
.input-group .form-control { min-width: 260px; }
.badge { font-weight: 600; }
</style>
