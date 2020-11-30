<template>
  <youtube :video-id="videoId" :ref="id" :fitParent="isFitParent"></youtube>
</template>

<script>
import { getIdFromUrl } from 'vue-youtube'

export default {
  props: {
    id: { type: String, required: true },
    isFitParent: { type: Boolean, required: false, default: true }
  },

  methods: {
    detachVideo () {
      this.player.destroy()
    }
  },

  computed: {
    videoId () {
      return this.id.indexOf('https')
        ? this.id
        : getIdFromUrl(this.id)
    },

    player () {
      return this.$refs[this.id].player
    }
  }
}
</script>
