<template>
  <div class="form-item" v-if="globalProperties">
    <div class="form-label a-center">Свойства:</div>
    <div
      class="form-item"
      v-for="item in globalProperties.string"
      :key="item.serviceId"
    >
      <div class="form-label">{{ item.label }} :</div>
      <el-input
        v-model="properties[item.code]"
        :type="
          item.code == 'detail_text' || item.code == 'preview_text'
            ? 'textarea'
            : 'text'
        "
        :disabled="entity == 'routes' ? getDisabled(item.code) : false"
        :rows="5"
      ></el-input>
    </div>

    <div
      class="form-item"
      v-for="item in globalProperties.number"
      :key="item.serviceId"
    >
      <div class="form-label">{{ item.label }} :</div>
      <el-input v-model="properties[item.code]" type="number"></el-input>
    </div>

    <div
      class="form-item"
      v-for="item in globalProperties.array"
      :key="item.serviceId"
    >
      <div class="form-label">{{ item.label }} :</div>
      <el-select
        v-if="item.code == 'ownership'"
        v-model="properties[item.code]"
        placeholder=""
      >
        <el-option
          v-for="subItem in ownership"
          :label="subItem.label"
          :value="subItem.label"
          :key="subItem.serviceId"
        ></el-option>
      </el-select>
    </div>

    <div class="d-flex flex-center">
      <div
        :class="[$style.flexBox, 'form-item']"
        v-for="item in globalProperties.bool"
        :key="item.serviceId"
      >
        <el-switch v-model="properties[item.code]" :active-text="item.label">
        </el-switch>
      </div>
    </div>
  </div>
</template>
<script>
import { removeEmptyFields } from '@/utils/common'
export default {
  props: {
    params: { type: Object, required: true },
    entity: { type: String, required: true }
  },

  data () {
    return {
      properties: {},

      ownership: [
        {
          id: 1,
          label: 'Муниципальная'
        },
        {
          id: 2,
          label: 'Частная'
        },
        {
          id: 3,
          label: 'Государственная'
        }
      ]
    }
  },

  computed: {
    globalProperties () {
      return this.$store.getters[`${this.entity}/get`]('properties')[0]
    }
  },

  async created () {
    await this.getProperties()

    this.properties = this.params
  },

  methods: {
    async getProperties () {
      try {
        await this.$store.dispatch(`${this.entity}/loadData`, {
          route: 'get-properties',
          state: 'properties'
        })
      } catch (e) {

      }
    },

    getDisabled (propertyCode) {
      if (propertyCode === 'duration' || propertyCode === 'distance') return true
    },

    getUpdateFields () {
      return this.properties
    },

    setUpdateFields () {
      if (this.$route.params.properties.length) {
        this.$route.params.properties.forEach((item) => {
          this.properties[item.code] = item.value
        })
      }
    },

    setDefaultValue (property) {
      if (property.value == null) property.value = ''
      if (property.value === 0) property.value = 1

      return property
    },

    getFields () {
      return removeEmptyFields(this.properties)
    },

    resetFields () {
      this.properties = {}
    }
  }
}
</script>
<style module>
.flexBox {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}
</style>
