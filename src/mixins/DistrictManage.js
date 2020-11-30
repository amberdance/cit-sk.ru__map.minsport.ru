import { isEmptyObject } from '@/utils/common'

export default {
  computed: {
    districts () {
      return this.$store.getters['district/list']
    }
  },

  methods: {
    async loadDistricts () {
      if (!isEmptyObject(this.districts)) return

      await this.$store.dispatch('district/loadDistricts')
    }
  }
}
