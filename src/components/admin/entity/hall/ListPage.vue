<template>
  <div>
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
          <el-table-column
            align="center"
            type="selection"
            width="55"
            prop="id"
          />
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

          <el-table-column
            label="Наименование объекта"
            prop="geoLabel"
            sortable
          />

          <el-table-column label="Наименование зала" prop="label" sortable />
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
              <el-input v-model="search" placeholder="поиск.." />
            </template>

            <template slot-scope="{ row }">
              <div class="table-btn-group">
                <el-button
                  size="mini"
                  @click="
                    $router.push({
                      name: 'updateHall',
                      params: row,
                      query: { id: row.id }
                    })
                  "
                  >редактировать</el-button
                >
                <el-button
                  v-if="isAdmin && row.stateId !== 1"
                  size="mini"
                  type="primary"
                  @click="
                    updatePublishState({
                      stateId: 1,
                      stateLabel: 'опубликовано',
                      rows: row
                    })
                  "
                  >опубликовать</el-button
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
          :total="halls.length"
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
  </div>
</template>
<script>
import TableManage from '@/mixins/TableManage'
import Pagination from '@/mixins/Pagination'

export default {
  mixins: [TableManage, Pagination],

  data () {
    return {
      entity: 'hall',
      filterComponent: null,
      search: ''
    }
  },

  computed: {
    halls () {
      return this.$store.getters['hall/list']
    },

    displayedHalls () {
      return this.paginate(this.halls)
    },

    dataTable () {
      return this.displayedHalls.filter(row => {
        return (
          !this.search ||
          row.created.toLowerCase().includes(this.search.toLowerCase()) ||
          row.label.toLowerCase().includes(this.search.toLowerCase())
        )
      })
    }
  },

  async created () {
    try {
      this.$isLoading()

      await this.$store.dispatch('hall/loadData', {
        route: 'get-list'
      })

      if (this.halls.length) {
        this.filterComponent = () =>
          import('@/components/admin/common/EntityListFilter')
      }
    } catch (e) {
      return
    } finally {
      this.$isLoading(false)
    }
  }
}
</script>
