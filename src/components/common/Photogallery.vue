<template>
  <div class="images-wrapper">
    <el-image
      v-for="(item, index) in imagesCount"
      :key="index"
      class="image-item"
      :src="srcList[index]"
      :preview-src-list="srcList"
    >
      <div slot="error" class="empty-image">
        <i class="el-icon-picture-outline"></i>
      </div>
    </el-image>
  </div>
</template>

<script>
export default {
  props: {
    images: {
      type: Array,
      required: true
    }
  },

  data () {
    return {
      url: '',
      srcList: []
    }
  },

  computed: {
    imagesCount () {
      return this.srcList.length >= 6 ? 6 : this.srcList.length
    }
  },

  created () {
    this.srcList = this.images.map((item) => item.src)
  }
}
</script>
