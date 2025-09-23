<template>
  <div class="container-xxl flex-grow-1 container-p-y">
           <div class="card-body">
          <!-- Header & Add New Button -->
          <div class="d-flex align-items-center mb-5">
            <h5 class="card-header mb-0">Fund Management</h5>
            <button @click="openModal" type="button" class="btn btn-primary ms-auto"><i class="fa fa-plus me-2"></i>
              Add New
            </button>
          </div>
        </div>
    <div class="card">
      <div class="card-datatable text-nowrap">
 

        <!-- Loader -->
    <Loading :active="isLoading" :is-full-page="true" />


        <!-- Table -->
        <div class="card-datatable text-nowrap">
          <table class="table" id="datatable">
            <thead>
              <tr>
                <th>Sl</th>
                <th>Phase</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="fund,index in funds" :key="fund.id" >
                <td>{{index + 1}}</td>
                <td>{{fund.fund}}</td>
                <td>
                  <button @click="editModeData(fund)" class="btn btn-warning me-2">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="DeleteModeData(fund.id)"  class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Create Modal -->
  <div v-if="createModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Add New</h5>
          <button type="button" class="btn-close" @click="closeModal"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="SubmitFund">
            <input type="text" v-model="formData.fund" class="form-control" placeholder="Enter fund name" />
            <button type="submit" class="btn btn-primary mt-3">Submit</button>
          </form>
        </div>
      </div>
    </div>
  </div>

    <!-- Edit Modal -->
      <div v-if="editModel" class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Fund</h5>
                    <button type="button" class="btn-close" @click="editModel = false" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form @submit.prevent="updateFund()">
                        <div class="mb-3">
                            <label class="form-label">Fund:</label>
                            <input type="text" class="form-control" v-model="formData.fund" placeholder="Name" required/>
                        </div>

                        <button type="submit" class="btn btn-primary" :disabled="loading">
                            <span v-if="loading">Submitting...</span> <span v-else>Submit</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


     <!-- Delete Modal -->
<div
  v-if="deleteModel"
  class="modal fade show d-block modal-danger"
  role="dialog"
  aria-modal="true"
  aria-labelledby="deleteTitle"
  @keydown.esc="deleteModel = false"
  tabindex="-1"
  style="background-color: rgba(17, 24, 39, 0.65);"
>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3 animate-pop">
      <!-- Header -->
      <div class="modal-header border-0 text-white gradient-header">
        <div class="d-flex align-items-center gap-2">
          <!-- Warning Icon -->
          <svg class="text-white" width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M12 9v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="17" r="1.2" fill="currentColor"/>
            <path d="M10.3 3.9 1.8 18.3A2 2 0 0 0 3.6 21h16.8a2 2 0 0 0 1.8-2.7L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.2" fill="none"/>
          </svg>
          <h5 style="color:white;" id="deleteTitle" class="modal-title fw-semibold mb-0">Confirm Deletion</h5>
        </div>
        <button style="color:white;" @click="deleteModel = false" type="button" class="btn-close btn-close-white"></button>
      </div>

      <!-- Body -->
      <div class="modal-body py-4">
        <div class="d-flex align-items-start gap-3">
          <div class="danger-chip">Delete</div>
          <div class="flex-grow-1">
            <p class="mb-1 fs-6 text-muted">
              You’re about to permanently delete this item.
            </p>
            <p class="mb-0 fw-medium">
              ID: <span class="text-dark">{{ deleteId }}</span>
            </p>
          </div>
        </div>
        <div class="small text-muted mt-3">
          This action cannot be undone. Please confirm to proceed.
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0 pt-0 pb-4 px-4">
        <button type="button" @click="deleteModel = false" class="btn btn-light px-4">
          Cancel
        </button>
        <button
          type="button"
          @click="confirmDelete()"
          class="btn btn-danger btn-danger-glow px-4"
        >
          <span class="me-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M3 6h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
              <path d="M8 6v14a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V6" stroke="currentColor" stroke-width="1.6"/>
              <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="1.6"/>
            </svg>
          </span>
          Delete Item
        </button>
      </div>
    </div>
  </div>
</div>


</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from "vue";
const isLoading = ref(false);
const { appContext } = getCurrentInstance();
const http = appContext.config.globalProperties.$http;
import { useToast } from "vue-toastification";
const toast = useToast();


const createModal = ref(false);
const editModel = ref(false);
const deleteModel = ref(false);
const editId = ref(null);
const deleteId = ref(null);
const funds = ref([]);

const formData = ref({
  fund: ''
});

function openModal() {
  createModal.value = true;
}
function closeModal() {
  createModal.value = false;
}

async function getFunds() {
  isLoading.value = true;
  try {
    const res = await http.get("/fund/create");
    funds.value = res.data.funds;
    console.log(funds.value)
  } catch (e) {
    console.error("Error loading funds:", e);
  }finally{
      isLoading.value = false;
  }
}

async function SubmitFund() {
  isLoading.value = true;
  try {
    const res = await http.post("/fund", formData.value);
    console.log("Response from backend:", res.data);
      getFunds();
    closeModal();
    formData.value.fund = "";
    toast.success("Fund created successfully!");
  } catch (e) {
    console.error("Error:", e);
  }finally{
    isLoading.value = false;
  }
}


const editModeData = (fund) => {
  editId.value = fund.id;          
  editModel.value = true;          
  formData.value = { ...fund };
  console.log("Editing fund:", fund);
};


const updateFund = async () => {
    isLoading.value = true;
  try {

    const res = await http.put(`/fund/${editId.value}`, {
      fund: formData.value.fund,
    });

    console.log("Update response:", res.data);

    const index = funds.value.findIndex((f) => f.id === editId.value);
    if (index !== -1) {
      funds.value[index] = { ...funds.value[index], ...res.data.data };
    }
    editModel.value = false;
    editId.value = null;
    formData.value = {
     fund: ""

      };

  } catch (error) {
    console.error("Error updating fund:", error);
  }finally{
      isLoading.value = false;
  }
};

const DeleteModeData = async (deleteData) => {
 deleteId.value = deleteData
 deleteModel.value = true

}


const confirmDelete = async () => {
    isLoading.value = true;
  try {
    const res = await http.delete(`/fund/${deleteId.value}`);

    console.log("Delete response:", res.data); 

    // remove deleted item from funds list
    funds.value = funds.value.filter((f) => f.id !== deleteId.value);

    // close modal
    deleteModel.value = false;
    deleteId.value = null;

  } catch (error) {
    console.error("Error deleting fund:", error.response?.data || error);
  }finally {
      isLoading.value = false;
  }
};


onMounted(() => {
  getFunds();
});
</script>
<style scoped>
.my-toast {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
  color: #fff !important;
  font-weight: bold;
  border-radius: 8px;
}

.gradient-header {
    padding: 10px;
    background: linear-gradient(135deg, #7367f0, #0009d9);
}

/* Little red chip label */
.danger-chip {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fecaca;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  letter-spacing: .3px;
}

/* Glow + press effect on Delete */
.btn-danger-glow {
  box-shadow: 0 8px 20px rgba(239, 68, 68, .35);
  transition: transform .08s ease, box-shadow .2s ease;
}
.btn-danger-glow:active {
  transform: translateY(1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, .25);
}

/* Subtle pop-in animation */
@keyframes pop {
  0%   { transform: scale(.96); opacity: .0; }
  100% { transform: scale(1);    opacity: 1; }
}
.animate-pop {
  animation: pop .15s ease-out;
}


</style>