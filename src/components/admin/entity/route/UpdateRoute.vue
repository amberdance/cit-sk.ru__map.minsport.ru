<template>
  <div class="form-wrapper">
    <el-form :model="route" :rules="rules" ref="form">
      <div class="sub-heading section-heading">Маршрут: {{ route.label }}</div>

      <div class="d-flex align-center">
        <div class="form-item">
          <div class="form-label">Состояние: {{ route.stateLabel }}</div>
        </div>

        <div class="form-item">
          <div class="form-label">Создан: {{ route.created }}</div>
        </div>

        <div class="form-item">
          <div class="form-label">Опубликован: {{ route.published }}</div>
        </div>
      </div>

      <el-divider />
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
        <div class="form-label">Мультимаршрут:</div>
        <div id="yandex-map" style="width:100%; height:450px;"></div>
      </div>

      <el-divider />

      <Entity-photo :entity="entity" :is-update-page="true" ref="entityPhoto" />

      <div class="form-item">
        <div class="form-label">Видео-галерея:</div>
        <Entity-video :is-update-page="true" ref="entityVideo" />
      </div>

      <div style="text-align:right">
        <el-button size="small" type="primary" @click="handleSubmit(true)"
          >обновить</el-button
        >
      </div>
    </el-form>
  </div>
</template>
<script>
import EntityPhoto from '@/components/admin/common/EntityPhoto'
import EntityVideo from '@/components/admin/common/EntityVideo'
import EntityManage from '@/mixins/admin/EntityManage'
import RouteManage from '@/mixins/admin/RouteManage'
import YandexMapInit from '@/mixins/map/YandexMapInit'
import MultiRouteMange from '@/mixins/map/MultiRouteMange'
import { isEmptyObject } from '@/utils/common'

export default {
  components: { EntityPhoto, EntityVideo },

  mixins: [YandexMapInit, MultiRouteMange, EntityManage, RouteManage],

  data () {
    return {
      entity: 'route',
      propertiesComponent: null,
      isMultiRouteChanged: false,
      multiRoute: {},
      waypoints: [],
      duration: null,
      distance: null,

      route: {
        id: null,
        label: null,
        created: null,
        published: null,
        stateLabel: null,
        routingMode: 'bicycle',
        waypoints: [],
        properties: {}
      }
    }
  },

  async created () {
    if (isEmptyObject(this.$route.params)) { return this.$router.push('/route/list') }

    this.initializeUpdateFields()
    await this.initializeYandexMap()
  },

  methods: {
    async updateRoute () {
      try {
        this.$isLoading()

        if (!this.isMultiRouteChanged) delete this.route.waypoints

        this.route.properties = this.$refs.entityProperties.getUpdateFields()
        this.route.properties.videogallery = this.$refs.entityVideo.getVideos()
        this.route.waypoints = this.multiRoute.properties.get('waypoints')

        if (!this.route.properties.videogallery) { delete this.route.properties.videogallery }

        await this.$store.dispatch('route/update', {
          route: '/route/update',
          payload: this.route
        })

        this.uploadPhotos(this.route.id)
        this.$onSuccess('Маршрут обновлен')
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    initializeUpdateFields () {
      const route = this.route
      const params = this.$route.params

      route.id = params.id
      route.label = params.label
      route.waypoints = params.waypoints
      route.routingMode = params.routingMode
      route.distance = params.distance
      route.duration = params.duration
      route.stateLabel = params.stateLabel
      route.created = params.created
      route.published = params.published
      route.deleted = params.deleted

      this.waypoints = params.waypoints

      this.initializeUpdateProperties('route')
      this.propertiesComponent = () =>
        import('@/components/admin/common/EntityProperties')
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
