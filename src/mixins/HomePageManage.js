import YandexMapInit from '@/mixins/map/YandexMapInit'
import YandexMapManage from '@/mixins/map/YandexMapManage'

export default {
  mixins: [YandexMapInit, YandexMapManage],

  computed: {
    placemarks () {
      return this.$store.getters['geoobject/placemarks']
    },

    routes () {
      return this.$store.getters['route/list']
    }
  },

  async created () {
    this.$isLoading()

    try {
      await this.loadYandexMap()
      await this.initializeYmap()
      await this.loadPlacemarks()
      await this.$store.dispatch('district/loadDistricts')

      this.initializeControlButtons(ymaps)
      this.setPlacemarks(ymaps)
      this.setMapEventListeners()

      this.geoFilterComponent = () => import('@/components/map/GeoFilter')
      this.multiRouteFilteComponent = () =>
        import('@/components/map/MultiRouteFilter')

      if (!this.placemarks.length) return this.$onWarning('Объекты не найдены')
    } catch (e) {
      console.log(e)
      this.$onError(
        'К сожалению, мне не удалось проинициализировать карту',
        5000
      )
    } finally {
      this.$isLoading(false)
    }
  }
}
