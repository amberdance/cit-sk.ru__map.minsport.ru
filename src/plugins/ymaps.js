import Vue from "vue";
import YmapPlugin from "vue-yandex-maps";
import { YANDEX_API_SETTINGS } from "../config";

Vue.use(YmapPlugin, YANDEX_API_SETTINGS);
