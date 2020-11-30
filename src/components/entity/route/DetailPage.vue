<template>
  <MainLayout>
    <el-card class="card-wrapper">
      <div slot="header" class="card-header">
        <i class="el-icon-location-outline" style="margin-right:0.5rem"></i>
        <span class="heading">Маршрут "{{ route.label }}"</span>
      </div>

      <div class="description link">
        <el-link
          icon="el-icon-arrow-left"
          type="primary"
          @click="$router.push('/map')"
          >назад</el-link
        >
      </div>

      <div class="sub-heading">Общая информация:</div>

      <el-row type="flex" class="description responsive">
        <el-col>
          <div>
            <span class="description-label">Вид маршрута:</span>
            <span class="description-value">{{ routingMode }}</span>
          </div>

          <DetailedProperties :properties="route.properties" />

          <el-divider />

          <div class="description-value">
            <el-link
              :underline="false"
              type="primary"
              @click="isShowCaloriesCalc = !isShowCaloriesCalc"
              >Сколько калорий я затрачу на этот маршрут?</el-link
            >
          </div>

          <transition name="el-zoom-in-top">
            <component
              :is="caloriesComponent"
              v-show="isShowCaloriesCalc"
              :routeProperties="route"
            />
          </transition>
        </el-col>

        <el-col :lg="10" :md="8">
          <div class="image-wrapper">
            <el-image :src="previewImage">
              <div slot="error" class="empty-image">
                <i class="el-icon-picture-outline"></i>
              </div>
            </el-image>
          </div>
        </el-col>
      </el-row>

      <el-divider />
      <div class="form-item">
        <div id="yandex-map" style="width:100%; height:450px;"></div>
      </div>
      <el-divider />

      <div v-if="route.photogallery.length">
        <div class="sub-heading">Фото:</div>
        <div class="description">
          <photogallery :images="route.photogallery" />
        </div>
      </div>

      <div v-if="route.videogallery.length">
        <div class="sub-heading">Видео:</div>
        <div class="description">
          <div class="video-wrapper">
            <div v-for="id in route.videogallery" :key="id" class="video-item">
              <youtube :id="id" :isFitParent="true" />
            </div>
          </div>
        </div>
      </div>
    </el-card>
  </MainLayout>
</template>

<script>
import MainLayout from "@/components/layouts/MainLayout";
import DetailedProperties from "@/components/common/DetailedProperties";
import Youtube from "@/components/common/Youtube";
import Photogallery from "@/components/common/Photogallery";
import RouteManage from "@/mixins/admin/RouteManage";
import YandexMapInit from "@/mixins/map/YandexMapInit";
import MultiRouteMange from "@/mixins/map/MultiRouteMange";
import YandexMapManage from "@/mixins/map/YandexMapManage";

export default {
  components: {
    MainLayout,
    DetailedProperties,
    Photogallery,
    Youtube,
  },

  mixins: [YandexMapInit, MultiRouteMange],

  data() {
    return {
      routeId: null,
      previewImage: null,
      routingMode: null,
      caloriesComponent: null,
      isShowCaloriesCalc: false,

      route: {
        videogallery: [],
        photogallery: [],
      },
    };
  },

  async created() {
    try {
      this.$isLoading();

      await this.loadYandexMap();
      await this.initializeYmap();

      this.routeId = this.$route.params.id || this.$route.query.id;

      const route = await this.$HTTPGet({
        route: "/route/get-list",
        payload: { id: this.routeId },
      });

      this.route = route;
      this.routingMode =
        route.routingMode == "bicycle" ? "велосипедный" : "пешеходный";

      this.previewImage = this.route.previewImage.length
        ? this.route.previewImage[0].src
        : null;

      this.multiRouteInit();
      this.caloriesComponent = () => import("./CaloriesCalculator");
    } catch (e) {
      return;
    } finally {
      this.$isLoading(false);
    }
  },
};
</script>
