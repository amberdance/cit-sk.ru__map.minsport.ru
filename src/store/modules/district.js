import commonMutations from "../commonMutations";
import commonActions from "../commonActions";
import { dispatch } from "../../api";

export default {
  namespaced: true,

  state: {
    items: {},
  },

  getters: {
    list: (state) => {
      return Object.values(state.items);
    },
  },

  mutations: {
    ...commonMutations,
  },

  actions: {
    ...commonActions,

    async loadDistricts({ commit }, payload) {
      const districts = await dispatch.HTTPPost({
        route: "/district/get-list",
        payload,
      });

      if (Array.isArray(districts)) {
        return districts.forEach((item) => {
          commit("set", { key: "items", props: item });
        });
      }

      commit("set", { key: "items", props: districts });
    },
  },
};
