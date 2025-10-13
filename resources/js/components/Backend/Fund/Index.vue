<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header & Add -->
    <div class="card-body">
      <div class="d-flex align-items-center mb-5">
        <h5 class="card-header mb-0">Fund Management</h5>
        <button @click="openCreate" type="button" class="btn btn-primary ms-auto">
          <i class="fa fa-plus me-2"></i>Add New
        </button>
      </div>
    </div>

    <!-- Full-page Loader -->
    <Loading :active="isLoading" :is-full-page="true" />

    <div class="card">
      <div class="card-datatable text-nowrap">
        <!-- Search + ItemsPerPage -->
        <div class="row m-2">
          <div class="col-sm-6 col-md-9 mt-1">
            <div class="dataTables_filter">
              <label>
                Search
                <input type="search" class="form-control" placeholder="Search by" v-model="Search_fund" />
              </label>
            </div>
          </div>
          <div class="col-sm-6 col-md-3 mt-5">
            <div class="text-end dataTables_length d-flex gap-2">
              <label class="mt-2">Show</label>
              <select v-model.number="itemsPerPage" class="form-select">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              <label class="mt-2">entries</label>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="card-datatable text-nowrap">
          <table class="table">
            <thead>
              <tr>
                <th>Sl</th>
                <th>Phase</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(fund, index) in visibleFunds" :key="fund.id">
                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td>{{ fund.fund }}</td>
                <td class="text-end">
                  <button @click="openEdit(fund)" class="btn btn-warning me-2">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="openDelete(fund)" class="btn btn-danger btn-danger-glow">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>

              <tr v-if="!loader && funds.length > 0 && filteredFund.length === 0">
                <td colspan="3" class="text-center text-muted">No departments match your search.</td>
              </tr>

              <tr v-if="funds.length === 0 && !loader">
                <td colspan="3" class="text-center text-muted">No data. Please add a fund.</td>
              </tr>

              <tr v-if="loader">
                <td colspan="3" class="text-center">
                  <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  Loading...
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="row mt-3" v-if="!loader && filteredFund.length > 0">
            <div class="col-sm-6">
              <div class="dataTables_info p-3">
                Showing {{ showingInfo.start }} to {{ showingInfo.end }} of {{ showingInfo.total }} entries
                <span v-if="Search_fund.trim() && filteredFund.length !== funds.length">
                  (filtered from {{ funds.length }} total)
                </span>
              </div>
            </div>
            <div class="col-sm-6" v-if="totalPages > 1">
              <div class="dataTables_paginate paging_simple_numbers float-end">
                <ul class="pagination">
                  <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link" @click="previousPage" :disabled="currentPage === 1">Previous</button>
                  </li>

                  <li v-if="visiblePages[0] > 1" class="page-item">
                    <button class="page-link" @click="goToPage(1)">1</button>
                  </li>
                  <li v-if="visiblePages[0] > 2" class="page-item disabled">
                    <span class="page-link">...</span>
                  </li>

                  <li
                    v-for="page in visiblePages"
                    :key="page"
                    class="page-item"
                    :class="{ active: page === currentPage }"
                  >
                    <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                  </li>

                  <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
                    <span class="page-link">...</span>
                  </li>
                  <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
                    <button class="page-link" @click="goToPage(totalPages)">{{ totalPages }}</button>
                  </li>

                  <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <button class="page-link" @click="nextPage" :disabled="currentPage === totalPages">Next</button>
                  </li>
                </ul>
              </div>
            </div>
          </div> <!-- /pagination -->
        </div>
      </div>
    </div>

    <!-- ===== Create Modal ===== -->
    <div v-if="createModal" class="modal-backdrop" @click.self="cancelCreate" role="dialog" aria-modal="true" aria-labelledby="createTitle">
      <div class="modal-card animate-pop">
        <div class="modal-header">
          <h5 id="createTitle" class="m-0">Create Fund</h5>
          <button class="btn-close" @click="cancelCreate" aria-label="Close"></button>
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
    <div v-if="editModal" class="modal-backdrop" @click.self="cancelEdit" role="dialog" aria-modal="true" aria-labelledby="editTitle">
      <div class="modal-card animate-pop">
        <div class="modal-header">
          <h5 id="editTitle" class="m-0">Edit Fund</h5>
          <button class="btn-close" @click="cancelEdit" aria-label="Close" :disabled="updating"></button>
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


    <!-- ===== Delete Modal (SAME DESIGN) ===== -->
    <div v-if="deleteModal" class="modal-backdrop" @click.self="cancelDelete" role="dialog" aria-modal="true" aria-labelledby="deleteTitle">
      <div class="modal-card animate-pop">
        <!-- শুধু হেডারে danger ক্লাস, বাকি সব Create/Edit–এর মতোই -->
        <div class="modal-header danger">
          <h5 id="deleteTitle" class="m-0">Confirm Deletion</h5>
          <button class="btn-close btn-close-white" @click="cancelDelete" aria-label="Close" :disabled="deleting"></button>
        </div>

        <div class="modal-body">
          <p class="mb-1">
            Are you sure you want to delete <strong>#{{ deleteItem?.id }}</strong>?
          </p>
          <p class="mb-0"><strong>Name:</strong> {{ deleteItem?.fund }}</p>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" @click="cancelDelete" :disabled="deleting">Cancel</button>
          <button class="btn btn-danger btn-danger-glow" @click="confirmDelete" :disabled="deleting">
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
const isLoading = ref(false); // full page
const loader = ref(false);    // table area
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
    // backend should return: { funds: [...] }
    const res = await http.get("/fund/create");
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
function openCreate() {
  formData.value = { fund: "" };
  createModal.value = true;
}
function cancelCreate() {
  if (!creating.value) createModal.value = false;
}
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
  } finally {
    creating.value = false;
  }
}

// ==== Edit ====
function openEdit(fund) {
  editId.value = fund.id;
  formData.value = { fund: fund.fund ?? "" };
  editModal.value = true;
}
function cancelEdit() {
  if (!updating.value) editModal.value = false;
}
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
  } finally {
    updating.value = false;
  }
}

// ==== Delete ====
function openDelete(fund) {
  deleteItem.value = fund;
  deleteModal.value = true;
}
function cancelDelete() {
  if (!deleting.value) deleteModal.value = false;
}
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

// Mount
onMounted(() => getFunds());

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

const totalPages = computed(() => Math.ceil(filteredFund.value.length / itemsPerPage.value));

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

watch(Search_fund, () => (currentPage.value = 1));
watch(itemsPerPage, () => (currentPage.value = 1));

const goToPage = (p) => { if (p >= 1 && p <= totalPages.value) currentPage.value = p; };
const previousPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
</script>

<style scoped>
/* ========== Reusable modal design like your Confirm Modal ========== */
.modal-backdrop{
  position: fixed; inset: 0;
  background: rgba(0,0,0,.5);
  display: grid; place-items: center;
  padding: 16px;
  z-index: 9999;
}

.modal-card{
  overflow: hidden;
  box-shadow: 0 15px 40px rgba(0,0,0,.25);
}

.modal-header{
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 1px solid #eef0f3;
  background: linear-gradient(135deg, #f8f9ff, #eef2ff);
}
.modal-header.danger{
  color:#fff;
  background: linear-gradient(135deg, #ef4444, #b91c1c);
  border-bottom-color: transparent;
}

.modal-body{ padding: 16px; }
.modal-footer{
  display: flex; gap: 10px; justify-content: flex-end;
  padding: 12px 16px; border-top: 1px solid #eef0f3;
}

/* Subtle pop-in animation */
@keyframes pop {
  0% { transform: scale(.96); opacity: 0; }
  100%{ transform: scale(1); opacity: 1; }
}
.animate-pop{ animation: pop .15s ease-out; }

/* Buttons polish */
.btn-danger-glow {
  box-shadow: 0 8px 20px rgba(239, 68, 68, .35);
  transition: transform .08s ease, box-shadow .2s ease;
}
.btn-danger-glow:active {
  transform: translateY(1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, .25);
}

/* Ensure tables look tidy */
.table th, .table td { vertical-align: middle; }
</style>
