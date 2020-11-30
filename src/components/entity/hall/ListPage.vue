<template>
  <el-card v-if="halls.length" class="card-wrapper">
    <div slot="header" class="card-header">
      <span class="heading">Залы:</span>
    </div>
    <el-row type="flex" class="hall-wrapper">
      <el-col class="hall-item description" v-for="item in halls" :key="item.id"
        ><div class="sub-heading">{{ item.label }}:</div>

        <div v-if="item.previewImage.length" class="image-wrapper a-center">
          <el-image :src="item.previewImage[0].src">
            <div slot="error" class="empty-image">
              <i class="el-icon-picture-outline"></i>
            </div>
          </el-image>
        </div>

        <div
          v-for="item in item.properties"
          :key="item.id"
          class="description-item"
        >
          <span class="description-label">{{ item.label }}:</span>
          <span class="description-value">{{ item.value }}</span>
        </div>

        <div class="description">
          <el-link
            icon="el-icon-arrow-right"
            style="margin-top:0.5rem"
            type="primary"
            @click="getHallById(item.id)"
            >подробнее</el-link
          >
        </div>
      </el-col>
    </el-row>
  </el-card>
</template>

<script>
export default {
  props: {
    geoId: {
      requried: true
    }
  },

  data () {
    return {
      halls: {}
    }
  },

  async created () {
    try {
      this.$isLoading()

      const halls = await this.$HTTPGet({
        route: '/hall/get-list',
        payload: { id: this.geoId }
      })

      if (!halls) this.halls = []
      if (!Array.isArray(halls)) this.halls = [halls]
    } catch (e) {
      return
    } finally {
      this.$isLoading(false)
    }
  },

  methods: {
    getHallById (id) {
      this.$router.push({
        name: 'hallDetail',
        query: { id }
      })
    }
  }
}
</script>
