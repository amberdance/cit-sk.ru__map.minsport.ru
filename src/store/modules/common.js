import commonMutations from "@/store/commonMutations";
import { dispatch } from "../../api";

export default {
  namespaced: true,

  state: {
    isLoading: false,
    publishStates: [],
  },

  getters: {
    list: (state) => (key) => {
      return Object.values(state[key]);
    },

    isLoading: (state) => {
      return state.isLoading;
    },
  },

  mutations: {
    isLoading(state, status = true) {
      state.isLoading = status;
    },

    ...commonMutations,
  },

  actions: {
    async loadData({ commit }, { route, state, payload }) {
      const responseData = await dispatch.HTTPPost({ route, payload });

      if (Array.isArray(responseData)) {
        return responseData.forEach((item) => {
          commit("set", { key: state, props: item });
        });
      }

      commit("set", { key: state, props: responseData });
    },
  },
};
