import { isEmptyObject } from '@/utils/common'

export default {
  data () {
    return {
      placemark: {},

      rules: {
        label: [
          {
            required: true,
            message: 'Обязательное поле'
          }
        ],

        coords: [
          {
            required: true,
            message: 'Укажите точку на карте'
          }
        ]
      }
    }
  },

  computed: {
    categories () {
      return this.$store.getters['geoobject/categories']
    }
  },

  methods: {
    async initializeYandexMap () {
      try {
        this.$isLoading()

        await this.loadYandexMap()
        await this.initializeYmap()

        if (isEmptyObject(this.categories)) await this.getCategories()

        this.yandexMapInstance.events.add('click', (event) => {
          const coords = event.get('coords')

          this.yandexMapInstance.geoObjects.remove(this.placemark)
          this.placemark = new ymaps.Placemark(coords, null, {
            preset: 'islands#redSportIcon',
            iconColor: '#3c3e4c'
          })

          this.yandexMapInstance.geoObjects.add(this.placemark)
          this.geoobject.coords = coords.join(',')
        })
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    async getCategories () {
      try {
        await this.$store.dispatch('geoobject/loadData', {
          route: 'get-categories',
          state: 'categories'
        })
      } catch (e) {

      }
    },

    mergeCategories () {
      const mergedCategories = []

      for (const category in this.geoobject.categories) {
        this.geoobject.categories[category].forEach((item) => {
          mergedCategories.push(item)
        })
      }

      return mergedCategories.length ? mergedCategories : null
    },

    async handleSubmit (isUpdatePage = false) {
      try {
        await this.$refs.form.validate()
      } catch (e) {
        return this.$onWarning('Заполните обязательные поля')
      }

      return isUpdatePage
        ? await this.updateGeoobject()
        : await this.addGeoobject()
    }
  }
}
