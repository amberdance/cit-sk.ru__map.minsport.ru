<template>
  <div class="form-wrapper">
    <div class="sub-heading section-heading">
      Добавление маршрута
    </div>

    <el-form :model="route" :rules="rules" ref="form">
      <div class="form-item">
        <div class="form-label">Наименование:</div>
        <el-form-item prop="label">
          <el-input v-model="route.label"></el-input>
        </el-form-item>
      </div>

      <div class="form-item">
        <div class="form-label">Тип маршрута:</div>
        <el-select v-model="route.routingMode" @change="onMultiRouteModeChange">
          <el-option label="велосипедный" value="bicycle"></el-option>
          <el-option label="пешеходный" value="pedestrian"></el-option>
        </el-select>
      </div>

      <el-divider />

      <component
        :is="propertiesComponent"
        :params="{ ...route.properties }"
        :entity="entity"
        ref="entityProperties"
      />

      <el-divider />

      <div class="form-item">
        <div class="form-label a-center">Мультимаршрут:</div>
        <div id="yandex-map" style="width:100%; height:450px;"></div>
      </div>

      <el-divider />

      <Entity-photo :entity="entity" ref="entityPhoto" />

      <div class="form-item">
        <div class="form-label">Видео-галерея:</div>
        <Entity-video ref="entityVideo" />
      </div>

      <div class="a-right">
        <el-button size="small" type="primary" @click="handleSubmit()"
          >добавить</el-button
        >
      </div>
    </el-form>
  </div>
</template>
<script>
import EntityProperties from '@/components/admin/common/EntityProperties'
import EntityPhoto from '@/components/admin/common/EntityPhoto'
import EntityVideo from '@/components/admin/common/EntityVideo'
import EntityManage from '@/mixins/admin/EntityManage'
import RouteManage from '@/mixins/admin/RouteManage'
import YandexMapInit from '@/mixins/map/YandexMapInit'
import YandexMapManage from '@/mixins/map/YandexMapManage'
import MultiRouteMange from '@/mixins/map/MultiRouteMange'

export default {
  components: { EntityProperties, EntityPhoto, EntityVideo },

  mixins: [
    YandexMapInit,
    YandexMapManage,
    EntityManage,
    RouteManage,
    MultiRouteMange
  ],

  data () {
    return {
      entity: 'route',
      multiRoute: {},
      waypoints: [],
      propertiesComponent: null,

      route: {
        id: null,
        label: '',
        routingMode: 'bicycle',
        waypoints: [],
        duration: null,
        distance: null,

        properties: {
          duration: null,
          distance: null
        }
      }
    }
  },

  async created () {
    await this.initializeYandexMap()
    this.propertiesComponent = () =>
      import('@/components/admin/common/EntityProperties')
  },

  methods: {
    async addRoute () {
      try {
        this.$isLoading()

        this.route.distance = this.getRouteDistance('value')
        this.route.duration = this.getRouteDuration('value')
        this.route.properties = this.$refs.entityProperties.getFields()
        this.route.properties.distance = this.getRouteDistance()
        this.route.properties.duration = this.getRouteDuration()
        this.route.properties.videogallery = this.$refs.entityVideo.getVideos()
        this.route.waypoints = this.multiRoute.properties.get('waypoints')

        if (!this.route.properties.videogallery) { delete this.route.properties.videogallery }

        const { data } = await this.$HTTPPost({
          route: '/route/add',
          payload: this.route
        })

        this.uploadPhotos(data.id)

        this.purge()
        this.$onSuccess('Маршрут добавлен')
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    purge () {
      this.route.id = null
      this.route.label = null
      this.route.waypoints = []
      this.waypoints = []
      this.multiRoute = {}
      this.route.properties = {}
      this.$refs.form.resetFields()
      this.$refs.entityProperties.resetFields()
      this.$refs.entityVideo.resetFields()
    }
  }
}
</script>
<style module>
.formWrapper {
  padding: 1rem;
}
.multipleUpload div {
  width: 100%;
}
</style>
