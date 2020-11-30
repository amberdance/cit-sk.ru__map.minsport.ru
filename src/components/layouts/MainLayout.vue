<template>
  <div class="base-width">
    <Preloader v-show="isLoading" />
    <HeaderLayout />
    <main>
      <slot />
    </main>
  </div>
</template>

<script>
import Preloader from './Preloader'
import HeaderLayout from './HeaderLayout'

export default {
  components: {
    Preloader,
    HeaderLayout
  },

  computed: {
    isLoading () {
      return this.$store.getters['common/isLoading']
    }
  }
}
</script>
