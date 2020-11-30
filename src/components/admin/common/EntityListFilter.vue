<template>
  <div class="control-inner">
    <el-select
      v-if="isAdmin"
      class="control-item"
      v-model="districtId"
      clearable
      filterable
      multiple
      placeholder="населенный пункт"
      @change="setFilter"
    >
      <el-option
        v-for="item in districts"
        :label="item.label"
        :key="item.id"
        :value="item.id"
      ></el-option>
    </el-select>

    <el-select
      class="control-item"
      v-model="itemStateId"
      multiple
      clearable
      placeholder="состояние публикации"
      @change="setFilter"
    >
      <el-option
        v-for="item in publishStates"
        :key="item.id"
        :label="item.label"
        :value="item.id"
      ></el-option>
    </el-select>

    <div class="filter-btn-group">
      <transition v-if="isAdmin" name="el-fade-in-linear">
        <el-button
          key="1"
          v-show="unPublishedRows.length"
          class="a-center"
          type="primary"
          size="small"
          @click="
            $emit('onChangePublishState', {
              stateId: 1,
              stateLabel: 'опубликовано',
              rows: unPublishedRows,
            })
          "
          >опубликовать ({{ unPublishedRows.length }})</el-button
        >
      </transition>

      <transition name="el-fade-in-linear">
        <el-button
          key="2"
          v-show="unTrashedRows.length"
          class="a-center"
          size="small"
          @click="
            $emit('onChangePublishState', {
              stateId: 4,
              stateLabel: 'не активен',
              rows: selectedRows,
            })
          "
          >деактивировать ({{ unTrashedRows.length }})</el-button
        >
      </transition>

      <transition name="el-fade-in-linear">
        <el-button
          key="3"
          v-show="selectedRows.length"
          class="a-center"
          type="danger"
          size="small"
          @click="$emit('onRemove')"
          >удалить ({{ selectedRows.length }})</el-button
        >
      </transition>
    </div>
  </div>
</template>
<script>
import DistrictManage from '@/mixins/DistrictManage'
import { isEmptyObject } from '@/utils/common'

export default {
  mixins: [DistrictManage],

  props: {
    entity: {
      type: String,
      required: true
    },

    multipleSelection: {
      type: Array,
      required: false
    }
  },

  data () {
    return {
      localEntityItemsList: [],
      districtId: [],
      itemStateId: []
    }
  },

  computed: {
    isAdmin () {
      return this.$isAdmin()
    },

    selectedRows () {
      return this.multipleSelection
    },

    unPublishedRows () {
      return this.multipleSelection.filter((item) => item.stateId !== 1)
    },

    unTrashedRows () {
      return this.multipleSelection.filter((item) => item.stateId !== 4)
    },

    publishStates () {
      return this.$store.getters['common/list']('publishStates')
    }
  },

  async created () {
    this.localEntityItemsList = this.$store.getters[`${this.entity}/list`]

    if (this.isAdmin) await this.loadDistricts()
    if (!isEmptyObject(this.publishStates)) return

    try {
      await this.$store.dispatch('common/loadData', {
        route: '/common/get-states',
        state: 'publishStates'
      })
    } catch (e) {
      return this.$onWarning('Не удалось получить состояниеы публикаций')
    }
  },

  methods: {
    setFilter () {
      const payload = this.localEntityItemsList
        .filter((item) => {
          return (
            !this.districtId.length || this.districtId.includes(item.districtId)
          )
        })
        .filter((item) => {
          return (
            !this.itemStateId.length || this.itemStateId.includes(item.stateId)
          )
        })

      this.$store.dispatch(`${this.entity}/setFilter`, payload)
    }
  }
}
</script>
