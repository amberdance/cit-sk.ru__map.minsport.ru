import commonMutations from "../commonMutations";
import commonActions from "../commonActions";
import { dispatch } from "../../api";

export default {
  namespaced: true,

  state: {
    items: {},
    geoLabels: {},
    properties: {},
  },

  getters: {
    get: (state) => (key) => {
      return Object.values(state[key]);
    },

    list: (state) => {
      return Object.values(state.items);
    },

    geoLabels: (state) => {
      return Object.values(state.geoLabels);
    },

    properties: (state) => {
      return Object.values(state.properties);
    },
  },

  mutations: {
    ...commonMutations,
  },

  actions: {
    ...commonActions,

    async loadData({ commit }, { route, payload, state = "items" }) {
      route = `/hall/${route}`;

      const responseData = await dispatch.HTTPPost({ route, payload });

      commit("clear", state);

      if (Array.isArray(responseData)) {
        responseData.forEach((item) => {
          commit("set", { key: state, props: item });
        });

        return;
      }

      commit("set", { key: state, props: responseData });
    },
  },
};
