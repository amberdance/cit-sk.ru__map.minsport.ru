<template>
  <el-row type="flex" :gutter="5">
    <el-col>
      <el-table
        height="80vh"
        class="table-area"
        ref="dataTable"
        border
        :data="dataTable"
        :default-sort="{ prop: 'id', order: 'descending' }"
        :row-class-name="rowClassNameByState"
        @selection-change="handleSelectionChange"
      >
        <el-table-column align="center" type="selection" width="55" prop="id" />
        <el-table-column
          align="center"
          label="id"
          prop="id"
          width="100"
          sortable
        />

        <el-table-column
          align="center"
          width="150"
          label="создано"
          prop="created"
          sortable
        />

        <el-table-column
          align="center"
          width="150"
          label="опубликовано"
          prop="published"
          sortable
        />

        <el-table-column label="Наименование" prop="label" sortable />
        <el-table-column label="Район" prop="districtLabel" sortable />

        <el-table-column
          label="состояние"
          align="center"
          prop="stateLabel"
          width="150"
          sortable
        />

        <el-table-column align="center" width="150">
          <template slot="header" slot-scope="scope">
            <el-input v-model="search" clearable placeholder="поиск.." />
          </template>

          <template slot-scope="{ row }">
            <div class="table-btn-group">
              <el-button
                v-if="isAdmin && row.stateId !== 1"
                size="mini"
                type="primary"
                @click="
                  updatePublishState({
                    stateId: 1,
                    stateLabel: 'опубликовано',
                    rows: row,
                  })
                "
                >опубликовать</el-button
              >

              <el-button
                size="mini"
                @click="
                  $router.push({
                    name: 'updateGeo',
                    params: row,
                    query: { id: row.id },
                  })
                "
                >редактировать</el-button
              >
            </div>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        class="pagination"
        background
        layout="prev, pager, next, jumper, sizes, total"
        :page-sizes="pageSizes"
        :page-size="pageSize"
        :total="geoobjects.length"
        :current-page.sync="currentPage"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      ></el-pagination>
    </el-col>

    <el-col :lg="4" class="control-wrapper right-filter">
      <component
        :is="filterComponent"
        :entity="entity"
        :multipleSelection="multipleSelection"
        @onRemove="removeItem"
        @onChangePublishState="updatePublishState"
      />
    </el-col>
  </el-row>
</template>

<script>
import TableManage from '@/mixins/TableManage'
import Pagination from '@/mixins/Pagination'

export default {
  mixins: [TableManage, Pagination],

  data () {
    return {
      entity: 'geoobject',
      filterComponent: null,
      search: ''
    }
  },

  computed: {
    geoobjects () {
      return this.$store.getters['geoobject/list']
    },

    displayedGeoobjects () {
      return this.paginate(this.geoobjects)
    },

    dataTable () {
      return this.displayedGeoobjects.filter((item) => {
        return (
          !this.search ||
          item.label.toLowerCase().includes(this.search.toLowerCase()) ||
          item.districtLabel.toLowerCase().includes(this.search.toLowerCase())
        )
      })
    }
  },

  async created () {
    try {
      this.$isLoading()

      await this.$store.dispatch('geoobject/loadData', {
        route: 'get-list'
      })

      this.filterComponent = () =>
        import('@/components/admin/common/EntityListFilter')
    } catch (e) {
      return
    } finally {
      this.$isLoading(false)
    }
  }
}
</script>
