export default {
  computed: {
    globalProperties () {
      return this.$store.getters[`${this.entity}/get`]('properties')
    }
  },

  methods: {
    initializeUpdateProperties (entity) {
      if (this.$route.params.properties.length) {
        this.$route.params.properties.forEach((item) => {
          this[entity].properties[item.code] = item.value
        })
      } else {
        this.globalProperties.forEach((item) => {
          this[entity].properties[item.code] = item.value
        })
      }
    },

    uploadPhotos (id) {
      if (this.$refs.entityPhoto.hasPreviewPhoto()) {
        this.$refs.entityPhoto.uploadFiles(id, 'preview_image')
      }

      if (this.$refs.entityPhoto.hasPhotogallery()) {
        this.$refs.entityPhoto.uploadFiles(id, 'photogallery')
      }
    }
  }
}
