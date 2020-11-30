<template>
  <div class="form-wrapper">
    <el-form :model="geoobject" :rules="rules" ref="form">
      <div class="sub-heading section-heading">
        Объект: {{ geoobject.label }}
      </div>

      <div class="d-flex align-center">
        <div class="form-item">
          <div class="form-label">Состояние: {{ geoobject.stateLabel }}</div>
        </div>

        <div class="form-item">
          <div class="form-label">Создан: {{ geoobject.created }}</div>
        </div>

        <div class="form-item">
          <div class="form-label">Опубликован: {{ geoobject.published }}</div>
        </div>
      </div>

      <el-divider />

      <div class="form-item">
        <div class="form-label">Наименование:</div>
        <el-form-item prop="label">
          <el-input v-model="geoobject.label"></el-input>
        </el-form-item>
      </div>

      <div class="form-item">
        <div class="form-label">
          Категории:
        </div>

        <el-select
          v-for="root in categories"
          v-model="geoobject.categories[root.code]"
          :key="root.id"
          :placeholder="root.label"
          clearable
          multiple
          filterable
        >
          <el-option
            v-for="sub in root.items"
            :key="sub.id"
            :label="sub.label"
            :value="sub.id"
          >
          </el-option>
        </el-select>
      </div>

      <el-divider />

      <div class="form-item">
        <div class="form-label">Кординаты:</div>
        <el-form-item prop="coords">
          <el-input
            disabled
            v-model="geoobject.coords"
            placeholder="кликните по карте для получения кординат"
          ></el-input>
        </el-form-item>
        <div id="yandex-map" style="width:100%; height:450px;"></div>
      </div>

      <el-divider />

      <component
        :is="propertiesComponent"
        :params="{ ...geoobject.properties }"
        :entity="entity"
        ref="entityProperties"
      />

      <el-divider />

      <Entity-photo :entity="entity" :is-update-page="true" ref="entityPhoto" />

      <div class="form-item">
        <div class="form-label">Видео-галерея:</div>
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
import YandexMapInit from "@/mixins/map/YandexMapInit";
import YandexMapManage from "@/mixins/map/YandexMapManage";
import EntityManage from "@/mixins/admin/EntityManage";
import GeoobjectManage from "@/mixins/admin/GeoobjectManage";
import EntityPhoto from "@/components/admin/common/EntityPhoto";
import EntityVideo from "@/components/admin/common/EntityVideo";

import { isEmptyObject } from "@/utils/common";

export default {
  components: { EntityPhoto, EntityVideo },

  mixins: [YandexMapInit, YandexMapManage, EntityManage, GeoobjectManage],

  data() {
    return {
      entity: "geoobject",
      propertiesComponent: null,
      isDataChanged: false,
      properties: {},

      geoobject: {
        id: null,
        label: null,
        coords: null,
        created: null,
        published: null,
        stateLabel: null,
        deleted: null,
        categories: {},
        properties: {},
      },
    };
  },

  async created() {
    try {
      if (isEmptyObject(this.$route.params))
        return this.$router.push("/admin/geo-list");

      await this.initializeYandexMap();

      this.initializeUpdateFields();
      this.initializeYmapsPlacemark();
    } catch (e) {
      return;
    }
  },

  methods: {
    initializeUpdateFields() {
      const geo = this.geoobject,
        params = this.$route.params;

      geo.id = params.id;
      geo.label = params.label;
      geo.coords = params.coords;
      geo.stateLabel = params.stateLabel;
      geo.created = params.created;
      geo.published = params.published;
      geo.deleted = params.deleted;

      params.categories.forEach((item) => {
        geo.categories[item.code].push(Number(item.id));
      });

      this.initializeUpdateProperties("geoobject");
      this.propertiesComponent = () =>
        import("@/components/admin/common/EntityProperties");
    },

    initializeYmapsPlacemark() {
      this.placemark = new ymaps.Placemark(this.geoobject.coords, null, {
        preset: "islands#redSportIcon",
        iconColor: "#3c3e4c",
      });

      this.yandexMapInstance.geoObjects.add(this.placemark);
      this.geoobject.coords = this.geoobject.coords.join(",");
    },

    async updateGeoobject() {
      try {
        this.$isLoading();

        this.geoobject.properties = this.$refs.entityProperties.getUpdateFields();
        this.geoobject.properties.videogallery = this.$refs.entityVideo.getVideos();

        console.log(this.geoobject.properties, this.properties);
        if (!this.geoobject.properties.videogallery)
          delete this.geoobject.properties.videogallery;

        return;

        await this.$store.dispatch("geoobject/update", {
          route: "/geoobject/update",
          payload: {
            ...this.geoobject,
            categories: this.mergeCategories(),
          },
        });

        this.uploadPhotos(this.geoobject.id);
        this.$onSuccess("Объект обновлен");
      } catch (e) {
        return;
      } finally {
        this.$isLoading(false);
      }
    },
  },
};
</script>
