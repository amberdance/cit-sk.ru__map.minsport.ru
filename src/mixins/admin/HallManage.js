import { isEmptyObject } from '@/utils/common'

export default {
  data () {
    return {
      rules: {
        label: [
          {
            required: true,
            message: 'Обязательное поле'
          }
        ],

        geoId: [
          {
            required: true,
            message: 'Обязательное поле'
          }
        ]
      }
    }
  },

  computed: {
    geoLabels () {
      return this.$store.getters['hall/get']('geoLabels')
    }
  },

  async created () {
    if (isEmptyObject(this.geoLabels)) await this.loadGeolabel()
  },

  methods: {
    async loadGeolabel () {
      try {
        await this.$store.dispatch('hall/loadData', {
          route: 'geo-labels',
          state: 'geoLabels'
        })
      } catch (e) {

      }
    },

    async remoteSearch (query) {
      try {
        this.isLoading = true

        if (!query || query.length < 2) {
          this.searchMatches = []
          return
        }

        this.searchMatches = await this.$HTTPPost({
          route: '/geoobject/get-placemarks',
          payload: { keywords: query }
        })
      } catch (e) {
        return
      } finally {
        this.isLoading = false
      }
    },

    async handleSubmit (isUpdatePage = false) {
      try {
        await this.$refs.form.validate()
      } catch (e) {
        return this.$onWarning('Заполните обязательные поля')
      }

      return isUpdatePage ? await this.updateHall() : await this.addHal()
    }
  }
}
