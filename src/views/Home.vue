<template>
  <MainLayout>
    <component :is="homeComponent" :isDevice="isDevice" />
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layouts/MainLayout'

export default {
  components: { MainLayout },

  data () {
    return {
      homeComponent: null
    }
  },

  computed: {
    clientWidth () {
      return document.documentElement.clientWidth
    },

    isDevice () {
      return this.clientWidth <= 760
    }
  },

  created () {
    this.homeComponent = this.isDevice
      ? () => import('./DeviceView')
      : () => import('./DesktopView')
  }
}
</script>
