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
      return this.$store.getters['hall/geoLabels']
    }
  },

  async created () {
    if (isEmptyObject(this.geoLabels)) await this.loadGeolabel()
  },

  methods: {
    async loadGeolabel () {
      await this.$store.dispatch('hall/loadData', {
        route: 'geo-labels',
        state: 'geoLabels'
      })
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
