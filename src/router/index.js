import Vue from "vue";
import VueRouter from "vue-router";
import { admin } from "./admin";

const originalPush = VueRouter.prototype.push;

VueRouter.prototype.push = function push(location) {
  return originalPush.call(this, location).catch((err) => err);
};

Vue.use(VueRouter);

const routes = [
  { path: "*", component: () => import("@/views/NotFoundView") },
  { path: "/", redirect: "/map" },

  {
    path: "/login",
    component: () => import("@/views/Login"),
  },
  
  {
    path: "/map",
    component: () => import("@/views/Home"),
  },

  {
    path: "/geoobject/:id(\\d+)",
    component: () => import("@/components/entity/geoobject/DetailPage"),
  },

  {
    path: "/geoobject",
    name: "geoobject",
    component: () => import("@/components/entity/geoobject/DetailPage"),
  },

  {
    path: "/hall",
    name: "hallDetail",
    component: () => import("@/components/entity/hall/DetailPage"),
  },

  {
    path: "/route/:id(\\d+)",
    component: () => import("@/components/entity/route/DetailPage"),
  },

  {
    path: "/route",
    name: "route",
    component: () => import("@/components/entity/route/DetailPage"),
  },

  ...admin,
];

const router = new VueRouter({
  mode: "history",
  base: process.env.BASE_URL,
  routes,
});

export default router;
