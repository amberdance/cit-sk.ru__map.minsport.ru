import Vue from "vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faHome, faSignOutAlt } from "@fortawesome/free-solid-svg-icons";

library.add(faHome, faSignOutAlt);
Vue.component("font-awesome-icon", FontAwesomeIcon);
