export default {
  data () {
    return {
      previewImage: [],
      fileList: [],
      isFileChanged: false,
      isFilesChanged: false,
      isVideoGalleryChanged: false
    }
  },

  computed: {
    authorizationHeader () {
      return {
        Authorization: `Bearer ${this.$getJWT()}`
      }
    }
  },

  methods: {
    async uploadFiles (entityId, propertyCode) {
      try {
        this.$isLoading()

        const formData = new FormData()

        formData.append('id', entityId)
        formData.append('propertyCode', propertyCode)

        if (
          this.isFileChanged &&
          this.previewImage.length &&
          propertyCode === 'preview_image'
        ) {
          formData.append('file', this.previewImage[0].raw)

          await this.sendFiles(formData)

          if (this.isUpdatePage) return

          this.isFileChanged = false
          this.previewImage = []
          this.$refs.upload1.clearFiles()
        }

        if (
          this.isFilesChanged &&
          this.fileList.length &&
          propertyCode === 'photogallery'
        ) {
          this.fileList.forEach((item, index) => {
            formData.append(`file${++index}`, item.raw)
          })

          await this.sendFiles(formData)

          if (this.isUpdatePage) return

          this.isFilesChanged = false
          this.fileList = []
          this.$refs.upload2.clearFiles()
        }
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    async sendFiles (payload) {
      await this.$HTTPPost({
        route: `${this.entity}/upload-file`,
        payload
      })
    },

    async detachFile (id) {
      try {
        this.$isLoading()

        await this.$HTTPPost({
          route: `${this.entity}/detach-file`,
          payload: {
            id
          }
        })
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    handleUploadSingleFile (file) {
      this.previewImage.push(file)
      this.isFileChanged = true
    },

    handleUploadMultipleFiles (file) {
      this.fileList.push(file)
      this.isFilesChanged = true
    },

    handleVideogalleryUpload (file) {
      this.videogallery.push(file)
      this.isVideoGalleryChanged = true
    },

    async handleVideogalleryRemove (file) {
      this.videogallery = this.videogallery.filter(
        item => item.uid !== file.uid
      )

      this.isVideoGalleryChanged = false

      if (this.isUpdatePage) await this.detachFile(file.id)
    },

    async handleRemoveSingle (file) {
      this.previewImage = []
      this.isFileChanged = false

      if (this.isUpdatePage) await this.detachFile(file.id)
    },

    async handleRemoveMultiple (file) {
      this.fileList = this.fileList.filter(item => item.uid !== file.uid)
      this.isFilesChanged = false

      if (this.isUpdatePage) await this.detachFile(file.id)
    }
  }
}
