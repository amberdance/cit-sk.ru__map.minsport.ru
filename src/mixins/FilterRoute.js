export default {
  computed: {
    routes() {
      return this.$store.getters["route/list"];
    },
  },

  data() {
    return {
      routeId: null,
      multiRoute: {},
      routingMode: "bicycle",
    };
  },

  async created() {
    await this.loadRoutes();
    this.multiRouteInit();
  },

  methods: {
    async remoteSearchRoute(query) {
      try {
        this.isLoading = true;

        if (!query || query.length < 2) return;

        await this.$store.dispatch("route/loadData", {
          payload: { keywords: query },
        });
      } catch (e) {
        return;;
      } finally {
        this.isLoading = false;
      }
    },

    async changeRouteMode() {
      try {
        await this.$store.dispatch("route/loadData", {
          payload: { routingMode: this.filters.routingMode },
        });
      } catch (e) {
        return;;
      }
    },

    async loadRoutes() {
      try {
        await this.$store.dispatch("route/loadData", {});
      } catch (e) {
        return;;
      }
    },

    multiRouteInit() {
      const multiRoute = new ymaps.multiRouter.MultiRoute(
        {
          referencePoints: [],
          params: { routingMode: this.routingMode },
        },
        {
          // wayPointStartIconLayout: "default#image",
          // wayPointStartIconColor: "#333",
          // wayPointStartIconFillColor: "#B3B3B3",
          boundsAutoApply: true,
        }
      );

      this.yandexMapInstance.geoObjects.add(multiRoute);
      this.multiRoute = multiRoute;
    },

    setWaypoints(coords = []) {
      this.multiRoute.model.setReferencePoints(coords);
    },

    setBallonTemplate(params) {
      this.multiRoute.model.events.once("requestsuccess", () => {
        const startPoint = this.multiRoute.getWayPoints().get(0);

        ymaps.geoObject.addon.balloon.get(startPoint);
        startPoint.options.set(this.getBallonTemplate(params));
      });
    },

    getBallonTemplate(params) {
      const { id, label, mode, properties, previewImage } = params;
      const preset =
        mode == "bicycle"
          ? "islands#redBicycleIcon"
          : "islands#redRunCircleIcon";

      let template = '<div class="ym-pm-wrapper">';
      let previewImageStyleStroke = "";

      template += `<div class='ym-pm-title'>${label}</div> <div class='ym-pm-category'>${
        mode == "bicycle" ? "Пешеходный" : "Велосипедный"
      } маршрут</div>`;

      if (previewImage.src) {
        previewImageStyleStroke = `style='background:url("${previewImage.src}") 50% 0% / cover';`;
        template += `<div class="ym-pm-picture" ${previewImageStyleStroke}></div>`;
      }

      for (let i = 0; i < properties.length; i++) {
        template += `<div class='ym-pm-item'>${properties[i].label}: ${properties[i].value}</div>`;
      }

      template += `<div class='ym-pm-link'><a href='/route/${id}' target='_blank'>Подробнее</a></div>`;
      template += "</div>";

      let balloonContentLayout = ymaps.templateLayoutFactory.createClass(
        template
      );

      return { preset, balloonContentLayout };
    },

    showMultiRoute() {
      if (!this.routeId) {
        return this.setWaypoints();
      }

      const properties = this.routes.filter(
        (item) => item.id == this.routeId
      )[0];

      this.setWaypoints(properties.waypoints);
      this.setBallonTemplate(properties);
    },
  },
};
