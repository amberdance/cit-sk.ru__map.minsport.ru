<template>
  <div class="form-wrapper">
    <div class="sub-heading section-heading">
      Добавление зала
    </div>

    <el-form :model="hall" :rules="rules" ref="form">
      <div class="form-item">
        <div class="form-label">Наименование зала:</div>
        <el-form-item prop="label">
          <el-input v-model="hall.label"></el-input>
        </el-form-item>
      </div>

      <div class="form-item">
        <div class="form-label">Привязка к объекту:</div>
        <el-form-item prop="geoId">
          <el-select
            placeholder="выберите объект"
            v-model="hall.geoId"
            clearable
            filterable
          >
            <el-option
              v-for="item in geoLabels"
              :key="item.id"
              :value="item.id"
              :label="item.label"
            >
            </el-option>
          </el-select>
        </el-form-item>
      </div>

      <el-divider />
      <component
        :is="propertiesComponent"
        :params="{ ...hall.properties }"
        :entity="entity"
        ref="entityProperties"
      />
      <el-divider />

      <Entity-photo :entity="entity" ref="entityPhoto" />

      <div class="form-item">
        <div class="form-label a-center">Видео-галерея:</div>
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
import EntityPhoto from '@/components/admin/common/EntityPhoto'
import EntityVideo from '@/components/admin/common/EntityVideo'
import EntityManage from '@/mixins/admin/EntityManage'
import HallManage from '@/mixins/admin/HallManage'

export default {
  components: { EntityPhoto, EntityVideo },

  mixins: [EntityManage, HallManage],

  data () {
    return {
      entity: 'hall',
      propertiesComponent: null,

      hall: {
        id: null,
        geoId: null,
        label: null,
        properties: {}
      }
    }
  },

  created () {
    this.propertiesComponent = () =>
      import('@/components/admin/common/EntityProperties')
  },

  methods: {
    async addHal () {
      try {
        this.$isLoading()

        this.hall.properties = this.$refs.entityProperties.getFields()
        this.hall.properties.videogallery = this.$refs.entityVideo.getVideos()

        if (!this.hall.properties.videogallery) {
          delete this.hall.properties.videogallery
        }

        const { data } = await this.$HTTPPost({
          route: '/hall/add',
          payload: this.hall
        })

        this.uploadPhotos(data.id)

        this.purge()
        this.$onSuccess('Зал добавлен')
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    purge () {
      this.hall.id = null
      this.hall.label = null
      this.hall.geoId = null
      this.hall.properties = {}
      this.$refs.entityProperties.resetFields()
      this.$refs.entityVideo.resetFields()
      this.$refs.form.resetFields()
    }
  }
}
</script>
