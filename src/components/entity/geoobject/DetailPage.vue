<template>
  <MainLayout>
    <el-card class="card-wrapper">
      <div slot="header">
        <i class="el-icon-location-outline" style="margin-right:0.5rem"></i>
        <span class="heading">"{{ geoobject.label }}"</span>
      </div>

      <div class="description link">
        <el-link
          icon="el-icon-arrow-left"
          type="primary"
          @click="$router.push('/map')"
          >назад</el-link
        >
      </div>

      <div class="sub-heading">Общая информация:</div>

      <div class="description">
        <el-row type="flex" class="responsive">
          <el-col>
            <span class="description-label">Доступные виды спорта:</span>
            <span class="description-value">{{ geoobject.category }}</span>
            <DetailedProperties :properties="geoobject.properties" />
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

      <div v-if="geoobject.photogallery.length">
        <div class="sub-heading">Фото:</div>
        <div class="description">
          <photogallery :images="geoobject.photogallery" />
        </div>
      </div>

      <div v-if="geoobject.videogallery.length">
        <div class="sub-heading">Видео:</div>
        <div class="description">
          <div class="video-wrapper">
            <div
              v-for="id in geoobject.videogallery"
              :key="id"
              class="video-item"
            >
              <youtube :id="id" :fitParent="true" />
            </div>
          </div>
        </div>
      </div>
    </el-card>

    <halls :geoId="geoId" />
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layouts/MainLayout'
import ListPage from '@/components/entity/hall/ListPage'
import Photogallery from '@/components/common/Photogallery'
import Youtube from '@/components/common/Youtube'
import DetailedProperties from '@/components/common/DetailedProperties'

export default {
  components: {
    MainLayout,
    DetailedProperties,
    Photogallery,
    Youtube,
    Halls: ListPage
  },

  data () {
    return {
      geoId: null,
      previewImage: null,

      geoobject: {
        videogallery: [],
        photogallery: []
      }
    }
  },

  async created () {
    try {
      this.$isLoading()

      this.geoId = this.$route.params.id || this.$route.query.id

      this.geoobject = await this.$HTTPGet({
        route: '/geoobject/get-list',
        payload: { id: this.geoId }
      })

      this.previewImage = this.geoobject.previewImage.length
        ? this.geoobject.previewImage[0].src
        : null
    } catch (e) {
      if (e.code === 404) this.$router.push('/map')
    } finally {
      this.$isLoading(false)
    }
  }
}
</script>
