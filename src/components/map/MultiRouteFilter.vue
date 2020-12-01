/* eslint-disable no-undef */
<template>
  <div class="map-control-wrapper">
    <div class="sub-heading a-center" style="font-size:15px;">
      Маршруты:
    </div>

    <el-select
      class="control-item"
      v-model="routingMode"
      placeholder="тип маршрута"
      filterable
      clearable
      @change="changeFilter"
      @clear="loadRoutes(true)"
    >
      <el-option label="пешеходный" value="pedestrian"> </el-option>
      <el-option label="велосипедный" value="bicycle"> </el-option>
    </el-select>

    <el-select
      class="control-item"
      placeholder="Населенный пункт"
      v-model="filter.districts"
      clearable
      filterable
      multiple
      @change="changeFilter"
    >
      <el-option
        v-for="item in districts"
        :key="item.id"
        :value="item.id"
        :label="item.label"
      >
      </el-option>
    </el-select>

    <el-select
      v-if="isRouteModeChanged && routes.length"
      class="control-item"
      placeholder="Список маршрутов"
      v-model="routeId"
      clearable
      remote
      filterable
      @change="onRouteChange"
    >
      <el-option
        v-for="item in routes"
        :key="item.id"
        :value="item.id"
        :label="item.label"
      >
      </el-option>
    </el-select>
  </div>
</template>

<script>
import MultiRouteMange from '@/mixins/map/MultiRouteMange'
import YandexMapManage from '@/mixins/map/YandexMapManage'

export default {
  mixins: [YandexMapManage, MultiRouteMange],

  props: {
    yandexMapInstance: {
      type: Object,
      required: true
    },

    clusterer: {
      type: Object,
      required: true
    }
  },

  data () {
    return {
      isLoading: false,
      isRouteModeChanged: false,
      routeId: null,
      multiRoute: {},
      routingMode: 'bicycle',

      filter: {
        districts: []
      }
    }
  },

  computed: {
    routes () {
      return this.$store.getters['route/list'].filter(
        item => item.routingMode === this.routingMode
      )
    },

    districts () {
      return this.$store.getters['district/list']
    }
  },

  async created () {
    await this.loadRoutes()

    this.multiRouteInit()
  },

  methods: {
    async loadRoutes (resetRouteMode = false) {
      if (resetRouteMode) this.isRouteModeChanged = false

      await this.$store.dispatch('route/loadData', {
        route: 'get-placemarks'
      })
    },

    async changeFilter () {
      try {
        this.$isLoading()

        const placemarks = await this.$HTTPPost({
          route: '/route/get-placemarks',

          payload: {
            routingMode: this.routingMode,
            districts: this.filter.districts
          }
        })

        if (!placemarks) return this.$onWarning('Маршруты не найдены')

        this.clusterer.removeAll()
        this.placemarksCollection = placemarks

        if (Array.isArray(this.placemarksCollection)) {
          this.placemarksCollection.forEach((item, index) => {
            this.placemarksCollection[index] = new ymaps.Placemark(
              item.coords,
              this.setPlacemarkProperties(item),
              {
                preset: 'islands#redSportIcon',
                iconColor: '#3c3e4c'
              }
            )
          })
        } else {
          this.placemarksCollection = new ymaps.Placemark(
            this.placemarksCollection.coords,
            this.setPlacemarkProperties(this.placemarksCollection),
            {
              preset: 'islands#redSportIcon',
              iconColor: '#3c3e4c'
            }
          )
        }

        this.clusterer.add(this.placemarksCollection)
        this.yandexMapInstance.geoObjects.add(this.clusterer)
        this.setPlacemarksEventListeners('route')

        this.isRouteModeChanged = true
        this.routeId = null

        this.setBounds()
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    async onRouteChange () {
      try {
        this.$isLoading()

        if (!this.routeId) return

        const coords = this.routes.filter(item => item.id === this.routeId)[0]
          .coords

        await this.yandexMapInstance.panTo([coords])
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    setWaypoints (coords = []) {
      this.multiRoute.model.setReferencePoints(coords)
      this.yandexMapInstance.setBounds(this.clusterer.getBounds())
    },

    setBallonTemplate (params) {
      this.multiRoute.model.events.once('requestsuccess', () => {
        const startPoint = this.multiRoute.getWayPoints().get(0)

        ymaps.geoObject.addon.balloon.get(startPoint)
        startPoint.options.set(this.getBallonTemplate(params))
      })
    },

    getBallonTemplate (params) {
      const { id, label, mode, properties, previewImage } = params

      const preset =
        mode === 'bicycle'
          ? 'islands#blackBicycleIcon'
          : 'islands#blackRunCircleIcon'

      let template = '<div class="ym-pm-wrapper">'
      let previewImageStyleStroke = ''

      template += `<div class='ym-pm-title'>${label}</div> <div class='ym-pm-category'>${
        mode === 'bicycle' ? 'Велосипедный' : 'Пешеходный'
      } маршрут</div>`

      if (previewImage.length) {
        previewImageStyleStroke = `style='background:url("${previewImage[0].src}") 50% 0% / cover';`
        template += `<div class="ym-pm-picture" ${previewImageStyleStroke}></div>`
      }

      for (let i = 0; i < properties.length; i++) {
        template += `<div class='ym-pm-item'>${properties[i].label}: ${properties[i].value}</div>`
      }

      template += `<div class='ym-pm-link'><a href='/route/${id}' target='_blank'>Подробнее</a></div>`
      template += '</div>'

      const balloonContentLayout = ymaps.templateLayoutFactory.createClass(
        template
      )

      return { preset, balloonContentLayout }
    },

    changePlacemarksVisibility (isVisible = false) {
      this.clusterer.options
        .set('visible', isVisible)
        .set('geoObjectVisible', isVisible)
    }
  }
}
</script>
