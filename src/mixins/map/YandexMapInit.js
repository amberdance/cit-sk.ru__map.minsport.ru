import { loadYmap } from 'vue-yandex-maps'
import { YANDEX_API_SETTINGS } from '../../config'

export default {
  data () {
    return {
      yandexMapInstance: null,
      clusterer: null,
      geoFilterComponent: null,
      multiRouteFilteComponent: null,
      isMultiRouteActive: false,
      userLocation: null,
      geolocation: null,
      purgeRouteButton: null,
      center: [44.953551, 43.344521],

      restrictedAreaCoords: [
        [43.573821, 40.767839],
        [46.249287, 46.011095]
      ],

      zoom: 7,
      mapType: 'yandex#hybrid',
      controls: ['geolocationControl', 'zoomControl', 'searchControl']
    }
  },

  methods: {
    async loadYandexMap () {
      await loadYmap({ ...YANDEX_API_SETTINGS })
    },

    async initializeYmap () {
      try {
        this.geolocation = ymaps.geolocation

        const userLocationPromise = await this.geolocation.get()
        this.userLocation = userLocationPromise.geoObjects
          .get(0)
          .geometry.getCoordinates()

        this.yandexMapInstance = new ymaps.Map(
          'yandex-map',
          {
            center: this.userLocation,
            zoom: this.zoom,
            controls: this.controls
          },
          {
            restrictMapArea: this.restrictedAreaCoords,
            autoFitToViewport: 'always'
          }
        )

        this.clusterer = new ymaps.Clusterer({
          preset: 'islands#blackClusterIcons',
          clusterHideIconOnBalloonOpen: false,
          geoObjectHideIconOnBalloonOpen: false
        })
      } catch (e) {

      }
    },

    getCoords () {
      this.yandexMapInstance.events.add('click', (event) => {
        return event.get('coords')
      })
    }
  }
}
