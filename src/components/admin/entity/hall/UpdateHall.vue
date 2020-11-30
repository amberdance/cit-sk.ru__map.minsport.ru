<template>
  <div class="form-wrapper">
    <el-form :model="hall" :rules="rules" ref="form">
      <div class="sub-heading section-heading">Зал: {{ hall.label }}</div>

      <div class="d-flex align-center">
        <div class="form-item">
          <div class="form-label">Состояние: {{ hall.stateLabel }}</div>
        </div>

        <div class="form-item">
          <div class="form-label">Создан: {{ hall.created }}</div>
        </div>

        <div class="form-item">
          <div class="form-label">Опубликован: {{ hall.published }}</div>
        </div>
      </div>

      <el-divider />

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
          >
            <el-option
              v-for="item in geoLabels"
              :key="item.id"
              :value="Number(item.id)"
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

      <Entity-photo :entity="entity" :is-update-page="true" ref="entityPhoto" />

      <div class="form-item">
        <div class="form-label a-center">Видео-галерея:</div>
        <Entity-video :is-update-page="true" ref="entityVideo" />
      </div>

      <div style="text-align:right">
        <el-button size="small" type="primary" @click="handleSubmit(true)"
          >Обновить</el-button
        >
      </div>
    </el-form>
  </div>
</template>

<script>
import EntityManage from '@/mixins/admin/EntityManage'
import HallManage from '@/mixins/admin/HallManage'
import EntityPhoto from '@/components/admin/common/EntityPhoto'
import EntityVideo from '@/components/admin/common/EntityVideo'
import { isEmptyObject } from '@/utils/common'

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
        created: null,
        published: null,
        stateLabel: null,
        properties: {}
      }
    }
  },

  created () {
    if (isEmptyObject(this.$route.params)) { return this.$router.push('/hall/list') }

    const hall = this.hall
    const params = this.$route.params

    hall.id = params.id
    hall.geoId = params.geoId
    hall.label = params.label
    hall.stateLabel = params.stateLabel
    hall.created = params.created
    hall.published = params.published
    hall.deleted = params.deleted

    this.initializeUpdateProperties('hall')
    this.propertiesComponent = () =>
      import('@/components/admin/common/EntityProperties')
  },

  methods: {
    async updateHall () {
      try {
        this.$isLoading()

        this.hall.properties = this.$refs.entityProperties.getUpdateFields()
        this.hall.properties.videogallery = this.$refs.entityVideo.getVideos()

        if (!this.hall.properties.videogallery) { delete this.hall.properties.videogallery }

        await this.$store.dispatch('hall/update', {
          route: '/hall/update-hall',
          payload: this.hall
        })

        this.uploadPhotos(this.hall.id)
        this.$onSuccess('Зал обновлен')
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    }
  }
}
</script>
