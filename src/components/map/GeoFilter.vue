/* eslint-disable no-undef */
<template>
  <div class="map-control-wrapper">
    <div class="sub-heading a-center" style="font-size:15px;">
      Спортивные объекты:
    </div>

    <el-divider />
    <div class="geo-select-wrapper">
      <el-select
        ref="test"
        class="control-item"
        placeholder="Населенный пункт"
        v-model="filter.districts"
        clearable
        filterable
        multiple
        @change="changeFilter"
      >
        <el-option
          v-for="item in districts"
          :key="item.id"
          :value="item.id"
          :label="item.label"
        >
        </el-option>
      </el-select>

      <el-select
        class="control-item"
        v-for="item in categories"
        v-model="filter.category[item.code]"
        :key="item.id"
        :placeholder="item.label"
        multiple
        filterable
        clearable
        @change="changeFilter"
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

    <div class="filter-item">
      <div class="filter-label">Стоимость занятий до, руб. :</div>
      <el-slider
        v-model="filter.price"
        :step="100"
        :min="0"
        :max="5000"
        class="filter-slider"
        @change="setFilter('price')"
      >
      </el-slider>
    </div>

    <div class="filter-item">
      <div class="filter-label">Минимальный возраст, лет:</div>
      <el-slider
        v-model="filter.minAge"
        :step="1"
        :min="1"
        class="filter-slider"
        @change="setFilter('minAge')"
      >
      </el-slider>
    </div>

    <el-divider />

    <div class="geo-switcher-wrapper">
      <div class="filter-item">
        <el-switch
          v-model="switcher.isInvalid"
          active-text="Доступ для инвалидов"
          @change="setFilter()"
        >
        </el-switch>
      </div>

      <div class="filter-item">
        <el-switch
          v-model="switcher.isFree"
          active-text="Бесплатные посещения"
          @change="setFilter()"
        >
        </el-switch>
      </div>

      <div class="filter-item">
        <el-switch
          v-model="switcher.isAnytime"
          active-text="Круглосуточный режим работы"
          @change="setFilter()"
        >
        </el-switch>
      </div>
    </div>

    <div v-if="isSomethingChanged" class="filter-item a-center">
      <el-divider />
      <div class="filter-label">
        <el-button size="mini" type="primary" @click="resetFilter"
          >Сброс</el-button
        >
      </div>
    </div>

    <el-divider />
  </div>
</template>

<script>
import YandexMapManage from '@/mixins/map/YandexMapManage'
import { removeEmptyFields, throttle } from '@/utils/common'
export default {

  mixins: [YandexMapManage],

  props: {
    yandexMapInstance: {
      type: Object,
      required: true
    },

    clusterer: {
      type: Object,
      required: true
    }
  },

  data () {
    return {
      geoobjectId: null,
      isSomethingChanged: false,
      placemarksCollection: [],

      sliders: {},

      switcher: {
        isAnytime: false,
        ifFree: false,
        isInvalid: false
      },

      filter: {
        districts: [],
        minAge: null,
        price: null,
        category: {}
      }
    }
  },

  computed: {
    categories () {
      return this.$store.getters['geoobject/categories']
    },

    placemarks () {
      return this.$store.getters['geoobject/placemarks']
    },

    districts () {
      return this.$store.getters['district/list']
    }
  },

  async created () {
    try {
      this.$isLoading()

      await this.$store.dispatch('geoobject/loadData', {
        route: 'get-categories',
        state: 'categories'
      })
    } catch (e) {
      return
    } finally {
      this.$isLoading(false)
    }
  },

  methods: {
    changeFilter: throttle(function (isSliderHere = null) {
      this.setFilter(isSliderHere)
    }, 1200),

    async setFilter (isSliderHere) {
      // if (!this.placemarks.length) {
      //   this.clusterer.removeAll();
      //   this.placemarksCollection = [];

      //   return;
      // }

      console.log(this.placemarksCollection)

      this.$isLoading()
      this.hideMultiRoute()

      this.isSomethingChanged = true

      if (isSliderHere) {
        this.sliders[isSliderHere] = true
      }

      const payload = this.getPayloadData()

      try {
        await this.loadGeoobjects(payload)
        this.setBounds()
      } catch (e) {
        if (e === 'Unhandled data') return this.$onWarning('Ничего не найдено')
      } finally {
        this.$isLoading(false)
      }
    },

    async loadGeoobjects (payload = null) {
      this.clusterer.removeAll()
      this.placemarksCollection = []

      await this.loadPlacemarks(payload)

      this.setPlacemarks(ymaps)
    },

    getPayloadData () {
      const payload = {
        districts: this.filter.districts,
        category: [],
        switcher: {},
        slider: {}
      }

      for (const key in this.sliders) {
        if (this.sliders[key]) payload.slider[key] = this.filter[key]
      }

      for (const key in this.switcher) {
        if (this.switcher[key]) payload.switcher[key] = this.switcher[key]
      }

      for (const key in this.filter.category) {
        this.filter.category[key].forEach((item) => {
          payload.category.push(item)
        })
      }

      if (payload.category.length >= 6) { return this.$onWarning('Уменьшите критерии поиска по категориям') }

      if (payload.slider.minAge === 1) delete payload.slider.minAge
      if (payload.slider.price === 0) delete payload.slider.price

      return removeEmptyFields(payload)
    },

    hideMultiRoute () {
      this.clusterer.options.set('visible', true).set('geoObjectVisible', true)
    },

    async resetFilter () {
      this.filter.price = 0
      this.filter.minAge = 1
      this.filter.districts = []

      this.filter.category = {}
      this.sliders = {}

      this.switcher.isInvalid = false
      this.switcher.isFree = false
      this.switcher.isAnytime = false

      this.isSomethingChanged = false

      this.hideMultiRoute()

      this.yandexMapInstance.setZoom(9)

      await this.loadGeoobjects()
    }
  }
}
</script>
