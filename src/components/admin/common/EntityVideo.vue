<template>
  <div>
    <el-row
      type="flex"
      :gutter="10"
      v-for="(id, index) in inputsCount"
      :key="index"
    >
      <el-col :span="12">
        <el-row type="flex" style="align-items:center;" :gutter="20">
          <el-col>
            <el-input
              v-model="videogallery[index]"
              placeholder="ссылка на youtube видео"
            >
            </el-input>
          </el-col>
          <el-col>
            <el-button
              v-if="!index"
              size="small"
              icon="el-icon-plus"
              @click="increment(index)"
            ></el-button>

            <el-button
              v-if="!isUpdatePage && index"
              size="small"
              icon="el-icon-minus"
              @click="decrement(index)"
            ></el-button>
          </el-col>
        </el-row>

        <div v-if="typeof videogallery[index] == 'string'">
          <youtube
            :id="videogallery[index]"
            :ref="videogallery[index]"
            class="form-item"
          />

          <el-button
            size="danger"
            icon="el-icon-delete"
            title="удалить"
            style="width:100%;margin:0.3rem 0;"
            @click="detachYoutube(videogallery[index], index)"
            >удалить</el-button
          >
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import Youtube from "@/components/common/Youtube";
import { getIdFromUrl } from "vue-youtube";

export default {
  props: {
    isUpdatePage: { type: Boolean, required: false },
  },

  components: { Youtube },

  data() {
    return {
      inputsCount: 1,
      videogallery: [],
    };
  },

  computed: {
    videoInputs() {
      return this.inputsCount;
    },
  },

  created() {
    this.videogallery = this.$route.params.videogallery || [];
    this.inputsCount = this.videogallery.length || 1;
  },

  methods: {
    increment() {
      this.inputsCount++;
      this.videogallery.push(null);
    },

    decrement(index) {
      delete this.videogallery[index];
      this.inputsCount--;
      if (!this.inputsCount) this.inputsCount = 1;
    },

    detachYoutube(videoId, index) {
      this.videogallery = this.videogallery.filter((id, i) => i !== index);
      this.inputsCount--;
      if (!this.inputsCount) this.inputsCount = 1;
    },

    getVideos() {
      let result = this.videogallery;

      this.videogallery.forEach((item) => {
        const videoId = getIdFromUrl(item);
        if (videoId) result.push(videoId);
      });

      return result.filter((src) => src.indexOf("https")).length || null;
    },

    resetFields() {
      this.inputsCount = 1;
      this.videogallery = [];
    },
  },
};
</script>
