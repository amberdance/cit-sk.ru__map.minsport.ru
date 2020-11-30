export default {
  data() {
    return {
      rules: {
        label: [
          {
            required: true,
            message: "Обязательное поле",
          },
        ],
      },
    };
  },

  methods: {
    async initializeYandexMap() {
      try {
        this.$isLoading();

        await this.loadYandexMap();
        await this.initializeYmap();
        this.multiRouteInit();
      } catch (e) {
        return;
      } finally {
        this.$isLoading(false);
      }
    },

    async handleSubmit(isUpdatePage = false) {
      try {
        await this.$refs.form.validate();
      } catch (e) {
        return this.$onWarning("Заполните обязательные поля");
      }

      return isUpdatePage ? await this.updateRoute() : await this.addRoute();
    },
  },
};
