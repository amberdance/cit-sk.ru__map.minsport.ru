<template>
  <el-row>
    <el-col :sm="10" class="map-controls right-filter">
      <component
        :is="geoFilterComponent"
        :yandexMapInstance="yandexMapInstance"
        :clusterer="clusterer"
      />
    </el-col>

    <el-col :sm="10" class="map-controls right-filter">
      <component
        :is="multiRouteFilteComponent"
        :yandexMapInstance="yandexMapInstance"
        :clusterer="clusterer"
      />
    </el-col>

    <el-col>
      <div id="yandex-map"></div>
    </el-col>
  </el-row>
</template>

<script>
import HomePageManage from "@/mixins/HomePageManage";

export default {
  mixins: [HomePageManage],
};
</script>
