<template>
  <MainLayout>
    <el-card class="card-wrapper">
      <div slot="header" class="card-header">
        <span class="heading">"{{ hall.label }}"</span>
      </div>

      <div class="sub-heading">Общая информация:</div>
      <div class="description">
        <el-row type="flex" class="responsive">
          <el-col>
            <DetailedProperties :properties="hall.properties" />
          </el-col>

          <el-col :lg="10" :md="8">
            <div class="image-wrapper">
              <el-image :src="previewImage">
                <div slot="error" class="empty-image">
                  <i class="el-icon-picture-outline"></i>
                </div>
              </el-image>
            </div>
          </el-col>
        </el-row>
      </div>
      <div v-if="hall.photogallery.length">
        <div class="sub-heading">Фото:</div>
        <div class="description">
          <photogallery :images="hall.photogallery" />
        </div>
      </div>

      <div v-if="hall.videogallery.length">
        <div class="sub-heading">Видео:</div>
        <div class="description">
          <div class="video-wrapper">
            <div v-for="id in hall.videogallery" :key="id" class="video-item">
              <youtube :id="id" />
            </div>
          </div>
        </div>
      </div>

      <div class="description link">
        <el-link
          icon="el-icon-arrow-left"
          type="primary"
          @click="
            $router.push({
              name: 'geoobject',
              query: { id: hall.geoId },
              params: { id: hall.geoId },
            })
          "
          >назад</el-link
        >
      </div>
    </el-card>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layouts/MainLayout'
import DetailedProperties from '@/components/common/DetailedProperties'
import Youtube from '@/components/common/Youtube'
import Photogallery from '@/components/common/Photogallery'

export default {
  components: { DetailedProperties, Photogallery, Youtube, MainLayout },

  data () {
    return {
      previewImage: null,

      hall: {
        videogallery: [],
        photogallery: []
      }
    }
  },

  async created () {
    try {
      this.$isLoading()

      this.hall = await this.$HTTPGet({
        route: '/hall/get-list',
        payload: { id: this.$route.query.id }
      })

      this.previewImage = this.hall.previewImage.length
        ? this.hall.previewImage[0].src
        : null
    } catch (e) {
      this.$onError()
      return
    } finally {
      this.$isLoading(false)
    }
  }
}
</script>
