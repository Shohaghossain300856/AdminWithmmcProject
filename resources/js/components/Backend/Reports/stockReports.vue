<!-- resources/js/components/Backend/Stock/StockTopFilters.vue -->
<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="p_filters-card">
      <div class="p_filters-header">
        <div class="d-flex align-items-center gap-2">
          <i class="fa fa-list-check"></i>
          <h5 class="mb-0">Stock Filters</h5>
        </div>
      </div>

      <div class="p_memo-section">
        <div class="row g-3">
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
                v-model="subCatagorie_id"
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
      <!-- /Only these four fields kept -->
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance, computed } from 'vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http

// kept fields
const fund_id = ref('')
const subCatagorie_id = ref('')
const start_date = ref('')
const end_date = ref('')

// options state
const funds = ref([])
const subCatagories = ref([])

// computed options (dynamic mapping kept)
const fundsOptions = computed(() =>
  (funds.value || []).map(f => ({ value: f.id, label: String(f.fund ?? '') }))
)
const subCatagoriesOptions = computed(() =>
  (Array.isArray(subCatagories.value) ? subCatagories.value : []).map(r => ({
    value: Number(r.id),
    label: String((r.sub_category ?? '') + (r.sub_category_code ? ` — [${r.sub_category_code}]` : ''))
  }))
)

// loaders (dynamic)
async function fetchFunds () {
  try {
    const res = await http.get('/fund/create')
    funds.value = Array.isArray(res?.data?.funds) ? res.data.funds : (res?.data?.data ?? [])
  } catch (e) {
    console.error('Failed to load funds', e)
    funds.value = []
  }
}
async function fetchSubcats () {
  try {
    const res = await http.get('/subCatagories/create')
    subCatagories.value = res?.data?.data ?? []
  } catch (e) {
    console.error('Failed to load subcategories', e)
    subCatagories.value = []
  }
}

onMounted(() => {
  const today = new Date().toISOString().split('T')[0]
  if (!start_date.value) start_date.value = today
  if (!end_date.value) end_date.value = today
  fetchFunds()
  fetchSubcats()
})

// (optional) expose values to parent if needed
// defineExpose({ fund_id, subCatagorie_id, start_date, end_date })
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
:deep(.p_ms .multiselect) { min-height:40px; border-radius:10px; border:1px solid #e5e7eb; background:#fff; }
:deep(.p_ms .multiselect:focus-within) { border-color:#7367f0; box-shadow:0 0 0 3px rgba(115,103,240,.12); }
:deep(.p_ms .multiselect-input) { padding-left:34px; }
</style>
