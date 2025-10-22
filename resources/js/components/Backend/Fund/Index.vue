<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-2 flex-wrap">
        <h5 class="mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-coins me-2"></i> Fund Management
        </h5>

        <!-- Counts -->
        <span class="badge bg-primary ms-2">
          {{ filteredFund.length }} Items
        </span>

        <!-- Search -->
        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
          <div class="input-group input-group-sm w-auto">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input
              v-model.trim="Search_fund"
              class="form-control"
              placeholder="Search fund name…"
            />
          </div>

          <!-- Per page -->
          <select v-model.number="itemsPerPage" class="form-select form-select-sm w-auto">
            <option :value="10">10 / page</option>
            <option :value="25">25 / page</option>
            <option :value="50">50 / page</option>
            <option :value="100">100 / page</option>
          </select>

          <!-- Refresh -->
          <button class="btn btn-sm btn-outline-primary" @click="getFunds" :disabled="loader || isLoading">
            <i :class="['fa', (loader || isLoading) ? 'fa-spinner fa-spin' : 'fa-rotate']"></i>
            <span class="ms-1">{{ (loader || isLoading) ? 'Loading…' : 'Refresh' }}</span>
          </button>

          <!-- Add New -->
          <button @click="openCreate" type="button" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-2"></i>Add New
          </button>
        </div>
      </div>
    </div>

    <!-- Full-page Loader -->
    <Loading :active="isLoading" :is-full-page="true" />

    <!-- Table Card -->
    <div class="card">
      <div class="card-datatable text-nowrap">
        <div class="table-scroll">
          <table class="table table-hover align-middle mb-0 subcat-table">
            <thead class="table-head">
              <tr>
                <th style="width:70px">Sl</th>
                <th class="sortable">Fund <i class="fa fa-sort ms-1"></i></th>
                <th class="text-end" style="width:160px">Action</th>
              </tr>
            </thead>

            <tbody>
              <!-- Loading -->
              <tr v-if="loader">
                <td colspan="3" class="text-center py-4">
                  <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                </td>
              </tr>

              <!-- Rows -->
              <tr v-for="(fund, index) in visibleFunds" :key="fund.id" v-else>
                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-medium">{{ fund.fund }}</span>
                  </div>
                </td>
                <td class="text-end">
                  <button @click="openEdit(fund)" class="btn btn-warning btn-sm me-2">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="openDelete(fund)" class="btn btn-danger btn-sm btn-danger-glow">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>

              <!-- Empty states -->
              <tr v-if="!loader && funds.length > 0 && filteredFund.length === 0">
                <td colspan="3" class="text-center text-muted py-4">No funds match your search.</td>
              </tr>
              <tr v-if="funds.length === 0 && !loader">
                <td colspan="3" class="text-center text-muted py-4">No data. Please add a fund.</td>
              </tr>
            </tbody>

            <!-- (optional) aggregate footer -->
            <!--
            <tfoot v-if="filteredFund.length">
              <tr class="table-light">
                <th colspan="3" class="text-end">Total: {{ filteredFund.length }}</th>
              </tr>
            </tfoot>
            -->
          </table>
        </div>
      </div>

      <!-- Pagination (new styled footer like Stock Summary) -->
      <div class="card-footer d-flex align-items-center justify-content-between" v-if="!loader && filteredFund.length">
        <div class="text-muted small">
          Showing {{ showingInfo.start }}–{{ showingInfo.end }} of {{ showingInfo.total }} items
          <span v-if="Search_fund.trim() && filteredFund.length !== funds.length">
            (filtered from {{ funds.length }} total)
          </span>
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

            <!-- compact numbered pages -->
            <button
              v-if="visiblePages[0] > 1"
              class="btn btn-outline-secondary"
              @click="goToPage(1)"
            >1</button>
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

    <!-- ===== Create Modal ===== -->
    <div v-if="createModal" class="modal-backdrop" @click.self="cancelCreate">
      <div class="modal-card animate-pop">
        <div class="modal-header">
          <h5 class="m-0">Create Fund</h5>
          <button class="btn-close" @click="cancelCreate"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Fund Name</label>
          <input
            type="text"
            class="form-control"
            v-model.trim="formData.fund"
            :disabled="creating"
            placeholder="Enter fund name"
          />
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="cancelCreate" :disabled="creating">Cancel</button>
          <button class="btn btn-primary" @click="confirmCreate" :disabled="creating || !formData.fund.trim()">
            <span v-if="creating"><i class="fas fa-spinner fa-spin me-2"></i>Submitting...</span>
            <span v-else>Submit</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ===== Edit Modal ===== -->
    <div v-if="editModal" class="modal-backdrop" @click.self="cancelEdit">
      <div class="modal-card animate-pop">
        <div class="modal-header">
          <h5 class="m-0">Edit Fund</h5>
          <button class="btn-close" @click="cancelEdit" :disabled="updating"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Fund Name</label>
          <input
            type="text"
            class="form-control"
            v-model.trim="formData.fund"
            :disabled="updating"
            placeholder="Enter fund name"
          />
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="cancelEdit" :disabled="updating">Cancel</button>
          <button class="btn btn-primary" @click="confirmEdit" :disabled="updating || !formData.fund.trim()">
            <span v-if="updating"><i class="fas fa-spinner fa-spin me-2"></i>Saving...</span>
            <span v-else>Save</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ===== Delete Modal ===== -->
    <div v-if="deleteModal" class="modal-backdrop" @click.self="cancelDelete">
      <div class="modal-card animate-pop">
        <div class="modal-header danger">
          <h5 class="m-0">Confirm Deletion</h5>
          <button class="btn-close btn-close-white" @click="cancelDelete" :disabled="deleting"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>#{{ deleteItem?.id }}</strong>?</p>
          <p><strong>Name:</strong> {{ deleteItem?.fund }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" @click="cancelDelete" :disabled="deleting">Cancel</button>
          <button class="btn btn-danger" @click="confirmDelete" :disabled="deleting">
            <span v-if="deleting"><i class="fas fa-spinner fa-spin me-2"></i>Deleting...</span>
            <span v-else>Delete</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, getCurrentInstance } from "vue";
import { useToast } from "vue-toastification";
const toast = useToast();

const { appContext } = getCurrentInstance();
const http = appContext.config.globalProperties.$http;

// Loaders
const isLoading = ref(false);
const loader = ref(false);
const creating = ref(false);
const updating = ref(false);
const deleting = ref(false);

// Data
const funds = ref([]);
const Search_fund = ref("");
const itemsPerPage = ref(10);
const currentPage = ref(1);

// Modals & payloads
const createModal = ref(false);
const editModal = ref(false);
const deleteModal = ref(false);
const editId = ref(null);
const deleteItem = ref(null);
const formData = ref({ fund: "" });

// ==== API ====
async function getFunds() {
  isLoading.value = true;
  loader.value = true;
  try {
    const res = await http.get("/fund/create"); // expects { funds: [...] }
    funds.value = Array.isArray(res.data?.funds) ? res.data.funds : [];
  } catch (e) {
    console.error("load funds:", e);
    toast.error(e?.response?.data?.message || "Failed to load funds");
  } finally {
    isLoading.value = false;
    loader.value = false;
  }
}

// ==== Create ====
function openCreate() { formData.value = { fund: "" }; createModal.value = true; }
function cancelCreate() { if (!creating.value) createModal.value = false; }
async function confirmCreate() {
  creating.value = true;
  try {
    await http.post("/fund", { fund: formData.value.fund });
    await getFunds();
    createModal.value = false;
    toast.success("Fund created successfully 🎉");
  } catch (e) {
    console.error("create:", e);
    toast.error(e?.response?.data?.message || "Failed to create fund");
  } finally { creating.value = false; }
}

// ==== Edit ====
function openEdit(fund) {
  editId.value = fund.id;
  formData.value = { fund: fund.fund ?? "" };
  editModal.value = true;
}
function cancelEdit() { if (!updating.value) editModal.value = false; }
async function confirmEdit() {
  if (!editId.value) return;
  updating.value = true;
  try {
    await http.put(`/fund/${editId.value}`, { fund: formData.value.fund });
    await getFunds();
    editModal.value = false;
    editId.value = null;
    toast.success("Fund updated successfully ✅");
  } catch (e) {
    console.error("update:", e);
    toast.error(e?.response?.data?.message || "Failed to update fund");
  } finally { updating.value = false; }
}

// ==== Delete ====
function openDelete(fund) { deleteItem.value = fund; deleteModal.value = true; }
function cancelDelete() { if (!deleting.value) deleteModal.value = false; }
async function confirmDelete() {
  if (!deleteItem.value?.id) return;
  deleting.value = true;
  try {
    await http.delete(`/fund/${deleteItem.value.id}`);
    funds.value = funds.value.filter(f => f.id !== deleteItem.value.id);
    deleteModal.value = false;
    toast.success("Fund deleted successfully 🗑️");
  } catch (e) {
    console.error("delete:", e);
    toast.error(e?.response?.data?.message || "Failed to delete fund");
  } finally {
    deleting.value = false;
    deleteItem.value = null;
  }
}

// ===== Filtering & Pagination =====
const filteredFund = computed(() => {
  if (!funds.value?.length) return [];
  const q = Search_fund.value.toLowerCase().trim();
  if (!q) return funds.value;
  return funds.value.filter(f => (f?.fund || "").toLowerCase().includes(q));
});

const visibleFunds = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredFund.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredFund.value.length / itemsPerPage.value)));

const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= maxVisible) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    let start = Math.max(1, current - 2);
    let end = Math.min(total, start + maxVisible - 1);
    if (end - start < maxVisible - 1) start = Math.max(1, end - maxVisible + 1);
    for (let i = start; i <= end; i++) pages.push(i);
  }
  return pages;
});

const showingInfo = computed(() => {
  if (!filteredFund.value.length) return { start: 0, end: 0, total: 0 };
  const start = (currentPage.value - 1) * itemsPerPage.value + 1;
  const end = Math.min(currentPage.value * itemsPerPage.value, filteredFund.value.length);
  return { start, end, total: filteredFund.value.length };
});

const goToPage = (p) => { if (p >= 1 && p <= totalPages.value) currentPage.value = p; };
const previousPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

watch(Search_fund, () => (currentPage.value = 1));
watch(itemsPerPage, () => (currentPage.value = 1));
onMounted(() => getFunds());
</script>

<style scoped>
/* Table polish (same as “Stock Summary” style) */
.table-scroll { overflow-x: auto; max-width: 100%; }
.table thead th { color: #fff; position: sticky; top: 0; z-index: 1; }
.table-head { background: #7367f0; color: #fff; }
.sortable { cursor: pointer; user-select: none; }
.input-group .form-control { min-width: 260px; }
.badge { font-weight: 600; }
.subcat-table tfoot th { background: #f5f6f8; }

/* Modal system */
.modal-backdrop{ position:fixed; inset:0; display:grid; place-items:center; background:rgba(15,18,30,.55); z-index:99999; padding:12px; }
.modal-card{ width:min(640px,96vw); background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.25); overflow:hidden; max-height:92vh; display:flex; flex-direction:column; position:relative; }
.modal-header{ position:sticky; top:0; z-index:10; background:#fff; flex:0 0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; justify-content:space-between; border-bottom:1px solid #f0f0f0; }
.modal-header.danger{ background:#fff5f5; border-bottom-color:#fecaca; }
.modal-body{ flex:1 1 auto; min-height:0; overflow:auto; padding:16px; background:#fff; }
.modal-footer{ flex:0 0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; border-top:1px solid #f0f0f0; background:#fff; }

/* Buttons */
.btn{ display:inline-flex; align-items:center; justify-content:center; gap:6px; height:34px; padding:0 12px; border-radius:8px; border:1px solid transparent; font-weight:600; cursor:pointer; background:#f3f4f6; color:#111827; transition:transform .12s ease, filter .12s ease, background .12s ease; }
.btn:hover{ background:#e5e7eb; }
.btn:disabled{ opacity:.6; cursor:not-allowed; }
.btn-primary{ background:#7367f0; color:#fff; box-shadow:0 6px 14px rgba(115,103,240,.25); }
.btn-primary:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-danger{ background:#ef4444; color:#fff; }
.btn-danger:hover{ filter:brightness(1.05); transform:translateY(-1px); }
.btn-warning{ background:#facc15; color:#000; }
</style>
