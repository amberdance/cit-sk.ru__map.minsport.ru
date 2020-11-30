<template>
  <div>
    <div class="form-item a-center">
      <div class="form-label">Превью - фото:</div>
      <el-upload
        ref="upload1"
        accept="image/jpeg"
        class="multiple-upload"
        action="#"
        :limit="1"
        :headers="authorizationHeader"
        :file-list="previewImage"
        list-type="picture-card"
        :on-change="handleUploadSingleFile"
        :on-remove="handleRemoveSingle"
        :auto-upload="false"
      >
        <el-button size="small">загрузить</el-button>
      </el-upload>
    </div>

    <div class="form-item a-center">
      <div class="form-label">Фото-галерея:</div>

      <el-upload
        ref="upload2"
        class="multiple-upload"
        multiple
        action="#"
        :limit="10"
        :headers="authorizationHeader"
        :file-list="fileList"
        list-type="picture-card"
        :on-change="handleUploadMultipleFiles"
        :on-remove="handleRemoveMultiple"
        :auto-upload="false"
      >
        <el-button size="small">загрузить</el-button>
      </el-upload>
    </div>
  </div>
</template>
<script>
import HandleUploadFile from "@/mixins/HandleUploadFile";
import { isEmptyObject } from "@/utils/common";

export default {
  mixins: [HandleUploadFile],

  props: {
    entity: {
      type: String,
      required: true,
    },

    isUpdatePage: {
      type: Boolean,
      required: false,
    },
  },

  created() {
    const params = this.$route.params;

    if (isEmptyObject(params)) return;

    if (params.previewImage.length) {
      this.previewImage = [
        { id: params.previewImage[0].id, url: params.previewImage[0].src },
      ];
    }

    if (params.photogallery.length) {
      params.photogallery.forEach((item) => {
        this.fileList.push({ id: item.id, url: item.src });
      });
    }
  },

  methods: {
    hasPreviewPhoto() {
      return Boolean(this.previewImage.length);
    },

    hasPhotogallery() {
      return Boolean(this.fileList.length);
    },
  },
};
</script>
