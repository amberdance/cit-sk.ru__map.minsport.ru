export default {
  data() {
    return {
      placemarksCollection: [],
    };
  },

  methods: {
    async loadPlacemarks(payload = null) {
      try {
        this.$isLoading();

        await this.$store.dispatch("geoobject/loadData", {
          route: "get-placemarks",
          state: "placemarks",
          payload,
        });
      } catch (e) {
        return;
      } finally {
        this.$isLoading(false);
      }
    },

    setBounds() {
      if (!this.filter.districts.length)
        return this.yandexMapInstance.setZoom(9);

      this.yandexMapInstance.setBounds(this.clusterer.getBounds());

      this.filter.districts.length > 1
        ? this.yandexMapInstance.setZoom(9)
        : this.yandexMapInstance.setZoom(13);
    },

    setPlacemarks(ymaps) {
      this.placemarks.forEach((item, index) => {
        this.placemarksCollection[index] = new ymaps.Placemark(
          item.coords,
          this.setPlacemarkProperties(item),
          {
            preset: "islands#redSportIcon",
            iconColor: "#3c3e4c",
          }
        );
      });

      this.clusterer.add(this.placemarksCollection);
      this.yandexMapInstance.geoObjects.add(this.clusterer);
      this.setPlacemarksEventListeners();
    },

    setPlacemarkProperties(props) {
      const { label, category } = props;

      return {
        ...props,
        hintContent: label,
        balloonContentHeader: `<div class='ym-pm-title'>${label}</div> <div class='ym-pm-category'>${category ||
          ""}</div>`,
      };
    },

    setPlacemarksEventListeners(entity = "geoobject") {
      this.clusterer.events.add("balloonopen", async (event) => {
        try {
          this.$isLoading();

          let target = event.get("target");
          target.properties.set("balloonContent", "Данные загружаются...");

          const props = target.properties._data;
          // const { coords } = props;

          // if (this.yandexMapInstance.getZoom() <= 13)
          //   this.yandexMapInstance.setZoom(16);

          // await this.yandexMapInstance.panTo([coords]);

          const {
            balloonContentBody,
            balloonContentFooter,
          } = this.getPlacemarkTemplate(props, entity);

          target.properties.set({
            balloonContentBody,
            balloonContentFooter,
          });
        } catch (e) {
          let target = event.get("target");
          target.properties.set("balloonContent", "Ошибка загрузки");
          this.$onError("Не удалось получить данные об объекте");
          return;
        } finally {
          this.$isLoading(false);
        }
      });
    },

    getPlacemarkTemplate(props, entity) {
      const { id, properties, previewImage } = props;
      let previewImageStyleStroke = "";
      let balloonContentBody = `<div class="ym-pm-wrapper">`;

      if (previewImage.length) {
        previewImageStyleStroke = `style='background:url("${previewImage[0].src}") 50% 0% / cover';`;
        balloonContentBody += `<div class="ym-pm-picture" ${previewImageStyleStroke}></div></div>`;
      }

      for (let i = 0; i < properties.length; i++) {
        let value =
          typeof properties[i].value == "boolean"
            ? ""
            : `: ${properties[i].value}`;

        balloonContentBody += `<div class='ym-pm-item'>${properties[i].label} ${value}</div>`;
      }

      balloonContentBody += "</div>";

      const balloonContentFooter = `<div class='ym-pm-link'><a href="#" id="route">Проложить маршрут</a></div>
            <div class='ym-pm-link'><a href='/${entity}/${id}' target='_blank'>Подробнее</a></div>`;

      return { balloonContentBody, balloonContentFooter };
    },

    initializeControlButtons(ymaps) {
      this.purgeRouteButton = new ymaps.control.Button({
        data: {
          content: "Удалить маршрут",
          title: "Удалить проложенный маршрут",
        },
        options: { selectOnClick: false, visible: false, maxWidth: 160 },
      });

      this.yandexMapInstance.controls.add(this.purgeRouteButton);
      this.yandexMapInstance.controls.add("routePanelControl", {
        visible: false,
      });
    },

    setRouteListener() {
      try {
        this.$isLoading();
        this.yandexMapInstance.balloon.close();

        let routePanelControl = this.yandexMapInstance.controls.get(
          "routePanelControl"
        );

        const activeBalloon = this.yandexMapInstance.balloon.getData();

        routePanelControl.routePanel.geolocate("from");
        routePanelControl.routePanel.state.set({
          type: "pedestrian",
          to: activeBalloon.geometry.getCoordinates().join(","),
        });

        routePanelControl.options.set({
          visible: true,
          allowSwitch: false,
          float: "right",

          types: {
            pedestrian: true,
            bicycle: true,
            auto: false,
            masstransit: false,
          },
        });

        this.purgeRouteButton.options.set("visible", true);

        this.purgeRouteButton.events.add("click", (event) => {
          event.get("target").options.set("visible", false);
          routePanelControl.options.set("visible", false);
          routePanelControl.routePanel.state.set({
            to: null,
            from: null,
          });
        });
      } catch (e) {
        this.$onError("Не удалось построить маршрут");
        return;
      } finally {
        this.$isLoading(false);
      }
    },

    setMapEventListeners() {
      this.yandexMapInstance.events.add("click", (event) => {
        if (this.yandexMapInstance.balloon.isOpen())
          this.yandexMapInstance.balloon.close();
      });

      document.querySelector("#yandex-map").addEventListener("click", () => {
        if (event.target.id == "route") return this.setRouteListener();
      });
    },

    async getAddressLine(coords, ymaps) {
      try {
        const response = await ymaps.geocode(coords);
        const firstGeoObject = response.geoObjects.get(0);

        return firstGeoObject.getAddressLine();
      } catch (e) {
        return;
      }
    },

    unsetPlacemarks() {
      this.$store.commit("geoobject/clear", "placemarks");
    },
  },
};
