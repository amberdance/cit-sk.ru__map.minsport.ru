import Vue from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import VueYoutube from "vue-youtube";
import "@/utils/globals";
import "@/utils/alerts";
import "@/plugins/axios";
import "@/plugins/element.js";
import "@/plugins/fontawesome";
import "@/plugins/ymaps.js";
import "./css/app.css";
import "./css/media.css";

Vue.config.productionTip = false;

Vue.use(VueYoutube);

new Vue({
  router,
  store,
  render: (h) => h(App),
}).$mount("#app");
