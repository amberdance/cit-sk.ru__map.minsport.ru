<template>
  <div class="form-wrapper">
    <div class="sub-heading section-heading">
      Добавление объекта
    </div>

    <el-form :model="geoobject" :rules="rules" ref="form">
      <div class="form-item">
        <div class="form-label">Наименование:</div>
        <el-form-item prop="label">
          <el-input v-model="geoobject.label"></el-input>
        </el-form-item>
      </div>

      <div class="form-item">
        <div class="form-label">Категории:</div>

        <el-select
          v-for="item in categories"
          v-model="geoobject.categories[item.code]"
          :key="item.id"
          :placeholder="item.label"
          clearable
          multiple
          filterable
        >
          <el-option
            v-for="subItem in item.items"
            :key="subItem.id"
            :label="subItem.label"
            :value="subItem.id"
          >
          </el-option>
        </el-select>
      </div>

      <el-divider />

      <div class="form-item">
        <div class="form-label">Кординаты:</div>
        <el-form-item prop="coords">
          <el-input
            disabled
            v-model="geoobject.coords"
            placeholder="кликните по карте для получения кординат"
          ></el-input>
        </el-form-item>
        <div id="yandex-map" style="width:100%; height:450px;"></div>
      </div>

      <el-divider />

      <component
        :is="propertiesComponent"
        :params="{ ...geoobject.properties }"
        :entity="entity"
        ref="entityProperties"
      />

      <el-divider />
      <Entity-photo :entity="entity" ref="entityPhoto" />

      <div class="form-item a-center">
        <div class="form-label">Видео-галерея:</div>
        <Entity-video ref="entityVideo" />
      </div>

      <div style="text-align:right">
        <el-button size="small" type="primary" @click="handleSubmit()"
          >Добавить</el-button
        >
      </div>
    </el-form>
  </div>
</template>
<script>
import YandexMapInit from '@/mixins/map/YandexMapInit'
import YandexMapManage from '@/mixins/map/YandexMapManage'
import EntityManage from '@/mixins/admin/EntityManage'
import GeoobjectManage from '@/mixins/admin/GeoobjectManage'
import EntityPhoto from '@/components/admin/common/EntityPhoto'
import EntityVideo from '@/components/admin/common/EntityVideo'

export default {
  components: { EntityPhoto, EntityVideo },

  mixins: [YandexMapInit, YandexMapManage, EntityManage, GeoobjectManage],

  data () {
    return {
      entity: 'geoobject',
      propertiesComponent: null,

      geoobject: {
        label: null,
        coords: null,
        categories: {},
        properties: {}
      }
    }
  },

  async created () {
    await this.initializeYandexMap()

    this.propertiesComponent = () =>
      import('@/components/admin/common/EntityProperties')
  },

  methods: {
    async addGeoobject () {
      try {
        this.$isLoading()

        this.geoobject.properties = this.$refs.entityProperties.getFields()
        this.geoobject.properties.videogallery = this.$refs.entityVideo.getVideos()

        if (!this.geoobject.properties.videogallery) { delete this.geoobject.properties.videogallery }

        const { data } = await this.$HTTPPost({
          route: '/geoobject/add',
          payload: {
            ...this.geoobject,
            categories: this.mergeCategories()
          }
        })

        this.uploadPhotos(data.id)

        this.purge()
        this.$onSuccess('Объект добавлен')
      } catch (e) {
        if (e.code === 102) { return this.$onWarning('Объект с таким названием уже существует') }
      } finally {
        this.$isLoading(false)
      }
    },

    purge () {
      this.geoobject.label = null
      this.geoobject.categories = {}
      this.geoobject.properties = {}
      this.$refs.entityVideo.resetFields()
      this.$refs.entityProperties.resetFields()
      this.$refs.form.resetFields()
      this.yandexMapInstance.geoObjects.remove(this.placemark)
    }
  }
}
</script>
