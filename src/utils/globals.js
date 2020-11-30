import Vue from "vue";

const isLoading = () => {
  Vue.prototype.$isLoading = function(state = true) {
    this.$store.commit("common/isLoading", state);
  };
};

Vue.use(isLoading);
