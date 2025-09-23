<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card-body" :class="{ p_loading: isLoading }">
      <!-- Body -->
      <div class="p_card-body">
        <!-- Info Section -->
        <div class="p_info-section">
          <div class="p_info-grid">
            <div class="p_form-group">
              <label class="p_form-label">Memo No.</label>
              <input type="text" class="p_form-input" :value="memoNo" placeholder="Auto-generated" readonly />
            </div>

            <div class="p_info-divider">
              <i class="fa fa-arrow-right"></i>
              <div style="margin-top: 4px;"></div>
            </div>

            <div class="p_form-group">
              <label class="p_form-label">Date</label>
              <input type="date" class="p_form-input" v-model="formData.date" />
            </div>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="p_filters-section">
          <div class="p_filters-grid">
            <!-- FUND -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Fund</label>
                <select class="p_select-input" v-model="formData.fund_id" @change="onFundChange">
                  <option :value="null" disabled>Select a fund</option>
                  <option v-for="fund in funds" :key="fund.id" :value="fund.id">
                    {{ fund.fund }}
                  </option>
                </select>
              </div>

              <button class="p_addon-btn" type="button" @click="onAddFund">
                <i class="fa fa-plus"></i>
              </button>
            </div>

            <!-- CATEGORY -->
            <div class="p_filter-group">
              <div class="p_custom-select">
                <label class="p_form-label">Categories</label>
                <select class="p_select-input" v-model="formData.categorie_id" :disabled="!formData.fund_id">
                  <option :value="null" disabled>Select a Categories</option>
                  <option
                    v-for="cat in fundByCategories"
                    :key="cat.id"
                    :value="cat.id"
                  >
                    {{ cat.name }}
                  </option>
                </select>
              </div>

              <button class="p_addon-btn" type="button" @click="onAddCategory" :disabled="!formData.fund_id">
                <i class="fa fa-plus"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="p_table-container">
          <table class="p_modern-table">
            <thead>
              <tr>
                <th>#SN</th>
                <th>Budget</th>
                <th>Revised</th>
                <th>Disbursement</th>
                <th>Withdrawal</th>
                <th>Total</th>
                <th>Expense<br />Pending</th>
                <th>Actual Expense</th>
                <th>Balance</th>
                <th>Disbursed Spend Rate (%)</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(row, i) in rows" :key="row.uid">
                <td>
                  <div class="p_serial-number">{{ i + 1 }}</div>
                </td>

                <td>
                  <input type="number" class="p_table-input" v-model.number="row.budget" min="0" step="0.01" @input="recalc(row)" placeholder="0.00" />
                </td>

                <td>
                  <input type="number" class="p_table-input" v-model.number="row.revised" min="0" step="0.01" @input="recalc(row)" placeholder="0.00" />
                </td>

                <td>
                  <input type="number" class="p_table-input" v-model.number="row.disbursement" min="0" step="0.01" @input="recalc(row)" placeholder="0.00" />
                </td>

                <td>
                  <input type="number" class="p_table-input" v-model.number="row.withdrawal" min="0" step="0.01" @input="recalc(row)" placeholder="0.00" />
                </td>

                <td>
                  <input type="text" class="p_table-input" :value="fmt(row.total)" readonly />
                </td>

                <td>
                  <input type="number" class="p_table-input" v-model.number="row.expense_pending" min="0" step="0.01" @input="recalc(row)" placeholder="0.00" />
                </td>

                <td>
                  <input type="number" class="p_table-input" v-model.number="row.actual_expense" min="0" step="0.01" @input="recalc(row)" placeholder="0.00" />
                </td>

                <td>
                  <input type="text" class="p_table-input" :value="fmt(row.balance)" readonly />
                </td>

                <td>
                  <div class="p_rate-badge" :class="rateBadgeClass(row)">{{ fmt(row.rate) }}%</div>
                </td>

                <td>
                  <div class="p_action-buttons">
                    <button class="p_table-btn p_btn-duplicate" title="Duplicate" type="button" @click="duplicateRow(i)">
                      <i class="fa fa-copy"></i>
                    </button>
                    <button class="p_table-btn p_btn-delete" title="Delete" type="button" @click="deleteRow(i)">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>

            <tfoot>
              <tr>
                <td colspan="10" style="text-align: right;">Total Budget</td>
                <td style="text-align: right; font-weight: 700;">{{ fmt(grand.totalBudget) }}</td>
              </tr>
              <tr>
                <td colspan="10" style="text-align: right;">Expense (Pending)</td>
                <td style="text-align: right; font-weight: 700;">{{ fmt(grand.totalPending) }}</td>
              </tr>
              <tr>
                <td colspan="10" style="text-align: right;">Remaining Balance</td>
                <td style="text-align: right; font-weight: 700;">{{ fmt(grand.totalBalance) }}</td>
              </tr>
            </tfoot>
          </table>

          <div class="p_add-row-section">
            <button class="p_add-row-btn" type="button" @click="addRow">
              <i class="fa fa-plus"></i> Add New Row
            </button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p_card-footer">
        <div class="p_footer-tip">Tip: press <code>Tab</code> to switch cells quickly</div>
        <div class="p_footer-actions">
          <button class="p_footer-btn p_btn-cancel" type="button" @click="onCancel">Cancel</button>
          <button class="p_footer-btn p_btn-draft" type="button" @click="onSaveDraft">Save Draft</button>
          <button class="p_footer-btn p_btn-submit" type="button" @click="submitForm" :disabled="submitting">
            <span v-if="submitting"><i class="fa fa-spinner fa-spin"></i></span>
            <span v-else>Submit</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue'
import { useToast } from 'vue-toastification'

const { appContext } = getCurrentInstance()
const http = appContext.config.globalProperties.$http
const toast = useToast()
const isLoading = ref(false)
const submitting = ref(false)

const memoNo = ref(`MEMO-${Date.now()}`)

const funds = ref([])
const fundByCategories = ref([])

const formData = ref({
  fund_id: null,
  categorie_id: null,
  date: new Date().toISOString().slice(0, 10), // yyyy-mm-dd
})

// ---- Table rows state ----
const rows = ref([
  makeRow(),
])

function makeRow() {
  return {
    uid: cryptoRandom(),
    budget: 0,
    revised: 0,
    disbursement: 0,
    withdrawal: 0,
    total: 0,            // disbursement + withdrawal
    expense_pending: 0,
    actual_expense: 0,
    balance: 0,          // (budget + revised) - (actual_expense + expense_pending)
    rate: 0,             // (actual_expense / max(disbursement, 1)) * 100
  }
}

function cryptoRandom() {
  try {
    return crypto.randomUUID()
  } catch (_) {
    return 'uid-' + Math.random().toString(36).slice(2)
  }
}

// ---- Calculations ----
function recalc(row) {
  const b = nz(row.budget)
  const r = nz(row.revised)
  const d = nz(row.disbursement)
  const w = nz(row.withdrawal)
  const p = nz(row.expense_pending)
  const a = nz(row.actual_expense)

  row.total = d + w
  row.balance = (b + r) - (a + p)
  row.rate = d > 0 ? (a / d) * 100 : 0
}

const grand = computed(() => {
  let totalBudget = 0
  let totalPending = 0
  let totalBalance = 0
  rows.value.forEach(r => {
    totalBudget += nz(r.budget) + nz(r.revised)
    totalPending += nz(r.expense_pending)
    totalBalance += nz(r.balance)
  })
  return { totalBudget, totalPending, totalBalance }
})

function rateBadgeClass(row) {
  const x = row.rate
  if (x >= 80) return 'rate-high'
  if (x >= 40) return 'rate-medium'
  return 'rate-low'
}

function nz(v) {
  const n = Number(v)
  return isNaN(n) ? 0 : n
}

function fmt(n) {
  const v = Number(n)
  if (isNaN(v)) return '0.00'
  return v.toFixed(2)
}

// ---- Row ops ----
function addRow() {
  rows.value.push(makeRow())
}

function duplicateRow(i) {
  const base = rows.value[i]
  const copy = JSON.parse(JSON.stringify(base))
  copy.uid = cryptoRandom()
  rows.value.splice(i + 1, 0, copy)
}

function deleteRow(i) {
  if (rows.value.length === 1) {
    toast.warning('At least one row is required.')
    return
  }
  rows.value.splice(i, 1)
}

// ---- Filters fetch ----
async function getFunds() {
  isLoading.value = true
  try {
    const res = await http.get('/fund/create')
    console.log(res.data)
    funds.value = res?.data?.funds ?? []
    // Preselect first fund if none selected
    if (!formData.value.fund_id && funds.value.length) {
      formData.value.fund_id = funds.value[0].id
      await getCategoriesByFund(formData.value.fund_id)
    }
  } catch (e) {
    console.error('Error loading funds:', e)
    toast.error('Failed to load funds')
  } finally {
    isLoading.value = false
  }
}

async function getCategoriesByFund(fundId) {
  if (!fundId) {
    fundByCategories.value = []
    return
  }
  isLoading.value = true
  try {
    const res = await http.get('/subCatagories/show', { params: { fund_id: fundId } })
    fundByCategories.value = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
    // Reset selected category if not in the new list
    if (!fundByCategories.value.find(c => c.id === formData.value.categorie_id)) {
      formData.value.categorie_id = null
    }
  } catch (e) {
    console.error('Error loading categories:', e)
    toast.error('Failed to load categories')
    fundByCategories.value = []
    formData.value.categorie_id = null
  } finally {
    isLoading.value = false
  }
}

function onFundChange() {
  formData.value.categorie_id = null
  getCategoriesByFund(formData.value.fund_id)
}

// ---- Footer actions ----
function onCancel() {
  // Your desired cancel behavior
  rows.value = [makeRow()]
  formData.value.categorie_id = null
  toast.info('Form cleared')
}

function onSaveDraft() {
  // Optionally persist to localStorage
  const draft = { memoNo: memoNo.value, formData: formData.value, rows: rows.value }
  localStorage.setItem('budget_draft', JSON.stringify(draft))
  toast.success('Draft saved locally')
}

async function submitForm() {
  // Basic validations
  if (!formData.value.fund_id) {
    toast.error('Please select a Fund')
    return
  }
  if (!formData.value.categorie_id) {
    toast.error('Please select a Category')
    return
  }
  if (!formData.value.date) {
    toast.error('Please select a Date')
    return
  }

  // Recalculate all rows once more to be safe
  rows.value.forEach(recalc)

  // Optional: row-level validation
  const invalid = rows.value.find(r => (nz(r.budget) + nz(r.revised)) <= 0 && (nz(r.disbursement) + nz(r.withdrawal)) <= 0)
  if (invalid) {
    toast.error('Each row should have some numeric values')
    return
  }

  const payload = {
    memo_no: memoNo.value,
    date: formData.value.date,
    fund_id: formData.value.fund_id,
    categorie_id: formData.value.categorie_id,
    totals: {
      total_budget: grand.value.totalBudget,
      total_pending: grand.value.totalPending,
      total_balance: grand.value.totalBalance,
    },
    rows: rows.value.map((r, idx) => ({
      sn: idx + 1,
      budget: nz(r.budget),
      revised: nz(r.revised),
      disbursement: nz(r.disbursement),
      withdrawal: nz(r.withdrawal),
      total: nz(r.total),
      expense_pending: nz(r.expense_pending),
      actual_expense: nz(r.actual_expense),
      balance: nz(r.balance),
      rate: Number(nz(r.rate).toFixed(2)),
    })),
  }

  try {
    submitting.value = true
    const res = await http.post('/subCatagories', payload)
     console.log("Response data:", res.data) 
    toast.success('Submitted successfully')
    // Optionally: reset or navigate
    // rows.value = [makeRow()]
    // formData.value.categorie_id = null
  } catch (e) {
    console.error('Submit error:', e)
  } finally {
    submitting.value = false
  }
}

// ---- Plus buttons (optional hooks) ----
function onAddFund() {
  toast.info('Open "Add Fund" modal (implement as needed)')
}
function onAddCategory() {
  toast.info('Open "Add Category" modal (implement as needed)')
}

onMounted(() => {
  getFunds();
  rows.value.forEach(recalc)
  const draftRaw = localStorage.getItem('budget_draft')
  if (draftRaw) {
    try {
      const draft = JSON.parse(draftRaw)
      if (draft?.formData) formData.value = { ...formData.value, ...draft.formData }
      if (Array.isArray(draft?.rows) && draft.rows.length) rows.value = draft.rows
      memoNo.value = draft?.memoNo || memoNo.value
      // Ensure categories match selected fund
      if (formData.value.fund_id) getCategoriesByFund(formData.value.fund_id)
      rows.value.forEach(recalc)
      toast.success('Draft restored')
    } catch (_) {}
  }
})
</script>
