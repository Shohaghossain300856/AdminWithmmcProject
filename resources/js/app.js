import { createApp } from "vue";
import Dashboard from "./components/Dashboard/Dashboard.vue";
import Fund from "./components/Backend/Fund/Index.vue";
import catagories from "./components/Backend/Catagories/Index.vue";
import subcategories from "./components/Backend/subCategories/Index.vue";
import subcategorieslist from "./components/Backend/subCategories/List.vue";
import stockcreate from "./components/Backend/Stock/create.vue";
import http from "./lib/http";

// loader
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";

// Select2
import 'select2/dist/css/select2.min.css'
import $ from 'jquery'
import 'select2'

// toast
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const app = createApp({});
app.config.globalProperties.$http = http;
app.component("Loading", Loading);
app.component("dashboard", Dashboard);
app.component("fund", Fund);
app.component("catagories", catagories);
app.component("subcategories", subcategories);
app.component("subcategorieslist", subcategorieslist);
app.component("stockcreate", stockcreate);

app.use(Toast, {
  position: "top-right",
  timeout: 3000,
  toastClassName: "my-toast"
});

app.mount("#app");
